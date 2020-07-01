<?php

namespace Admin\Fields\ModelsField;

use InWeb\Admin\App\Fields\Number;

class ModelsField extends Number
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'models-field';

    public function resource($value)
    {
        return $this->withMeta(['relatedResource' => $value]);
    }
}
