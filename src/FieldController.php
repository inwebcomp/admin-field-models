<?php

namespace Admin\Fields\ModelsField;

use App\Models\Order;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InWeb\Admin\App\Admin;
use InWeb\Admin\App\Fields\Model;
use InWeb\Admin\App\Http\Controllers\Controller;
use InWeb\Admin\App\Http\Requests\ResourceCreateRequest;
use InWeb\Admin\App\Http\Requests\ResourceDeleteRequest;
use InWeb\Admin\App\Http\Requests\ResourceDetailRequest;
use InWeb\Admin\App\Http\Requests\ResourceUpdateRequest;
use InWeb\Admin\App\Resources\Resource;
use InWeb\Base\Contracts\Cacheable;
use InWeb\Base\Entity;
use InWeb\Base\Events\PositionChanged;
use InWeb\Base\Traits\Positionable;
use InWeb\Base\Traits\WithStatus;

class FieldController extends Controller
{
    public function index(ResourceDetailRequest $request)
    {
        $model = $request->findModelOrFail();

        $method = Str::plural($request->relatedResource);

        $models = $model->$method()->orderBy('pivot_position')->withTranslation()->get();

        $result = [];

        foreach ($models as $model) {
            $resource = Admin::newResourceFromModel($model);

            $result[] = [
                'resourceName'  => $resource::uriKey(),
                'resourceTitle' => $resource::label(),
                'title'         => $resource->title(),
                'subTitle'      => $resource->subtitle(),
                'image'         => $resource->preview(),
                'id'            => $model->getKey(),
                'url'           => $resource->editPath(),
                'visibility'    => in_array(WithStatus::class, class_uses($model)) ? $model->isPublished() : true,
                'position'      => in_array(Positionable::class, class_uses($model)) ? $model->position : null,
            ];
        }

        return [
            'data' => $result
        ];
    }

    /**
     * @todo
     * @param ResourceUpdateRequest $request
     * @param Order $order
     * @return mixed
     */
    public function update(ResourceUpdateRequest $request, Order $order)
    {
        $id = $request->input('id');
        $quantity = (int) $request->input('quantity');

        $method = Str::plural($request->relatedResource);

        return $order->$method()->updateExistingPivot($id, [
            'quantity' => $quantity,
        ]);
    }

    public function destroy(ResourceDeleteRequest $request)
    {
        $model = $request->findModelOrFail();
        /** @var Resource $resource */
        $resource = $request->newRelatedResource();

        $method = Str::plural($resource::uriKey());
        $key = $resource->model()->getForeignKey();

        return $model->$method()->wherePivot($key, $request->relatedResourceId)->detach();
    }

    /**
     * @param ResourceCreateRequest $request
     * @return array
     */
    public function create(ResourceCreateRequest $request)
    {
        /** @var Resource $resource */
        $resource = $request->newRelatedResource();

        return $this->fieldsForResource($resource);
    }

    public function fieldsForResource(Resource $resource)
    {
        return [
            Model::make($resource::singularLabel(), $resource->model()->getForeignKey())->resource($resource::uriKey())->rules('required')->size('full'),
        ];
    }

    /**
     * @param ResourceDetailRequest $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(ResourceDetailRequest $request, Order $order)
    {
        $model = $request->findModelOrFail();

        /** @var Resource $resource */
        $resource = $request->newRelatedResource();

        // @todo Validation
        return DB::transaction(function () use ($model, $request, $resource) {
            $method = Str::plural($resource::uriKey());

            if ($model->$method()->where($key = $resource->model()->getForeignKey(), $request->$key)->exists())
                return abort(422, __('Такой объект уже в списке'));

            $model->$method()->attach($request->$key);
        });
    }

    /**
     * Update positions
     *
     * @param ResourceDetailRequest $request
     * @return void
     */
    public function updatePositions(ResourceDetailRequest $request)
    {
        /** @var Entity|Positionable $model */
        $model = $request->findModelOrFail();

        /** @var Resource $resource */
        $resource = $request->newRelatedResource();

        $this->updateRelationPositionsById($model, Str::plural($resource::uriKey()), $request->input('items'));
    }

    public static function updateRelationPositionsById(Entity $resource, string $relation, $ids)
    {
        $values = [];

        foreach ($ids as $pos => $id) {
            $values[$id] = '(' . (int) $id . ', ' . (int) $pos . ')';
            if (! $id)
                return false;
        }

        /** @var BelongsToMany $relation */
        $relation = $resource->$relation();

        $table = $relation->getTable();
        $key = $resource->getForeignKey();
        $foreignKey = $relation->getRelatedPivotKeyName();
        $posColumn = $resource->orderColumnName();

        $q = [];
        foreach ($ids as $pos => $id) {
            $q[] = "WHEN `" . $foreignKey . "` = " . $id . " THEN " . $pos;
        }

        if ($q) {
            \DB::statement(
                "UPDATE `{$table}` SET 
                `{$posColumn}` = CASE " . implode("\t", $q) . " END 
                WHERE " . $foreignKey . " IN (" . implode(',', $ids) . ") AND " . $key . " = " . $resource->id);

            $tags = $resource instanceof Cacheable ? $resource->cacheTagAll() : [];

            event(new PositionChanged($tags, $ids));
        }
    }
}
