<?php

namespace Admin\Fields\ModelsField;

use Illuminate\Support\ServiceProvider;
use InWeb\Admin\App\Admin;
use InWeb\Admin\App\AdminRoute;
use InWeb\Admin\App\Events\ServingAdmin;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Admin::serving(function (ServingAdmin $event) {
            Admin::script('models-field', __DIR__.'/../dist/js/field.js');
            Admin::style('models-field', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        AdminRoute::api('\Admin\Fields\ModelsField', function () {
            $this->registerRoutes();
        });
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
