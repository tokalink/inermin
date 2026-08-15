<?php

namespace Tokalink\Inermin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tokalink\Inermin\commands\InerminInstallCommand;
use Tokalink\Inermin\commands\InerminMakeViewCommand;
use Tokalink\Inermin\helpers\Inermin;

class InerminServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('inermin', function ($app) {
            return new Inermin();
        });

        $this->mergeConfigFrom(__DIR__ . '/configs/inermin.php', 'inermin');

        // Register global class aliases: Inermin::, CRUDBooster::, and CB::
        if (class_exists(\Illuminate\Foundation\AliasLoader::class)) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Inermin', \Tokalink\Inermin\helpers\Inermin::class);
            $loader->alias('CRUDBooster', \Tokalink\Inermin\helpers\Inermin::class);
            $loader->alias('CB', \Tokalink\Inermin\helpers\Inermin::class);
        }
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'inermin');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::setRootView('inermin::app');
        }

        // Dynamic routing for custom generated modules
        $this->registerModuleRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InerminInstallCommand::class,
                InerminMakeViewCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/configs/inermin.php' => config_path('inermin.php'),
            ], 'inermin-config');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/Pages/Inermin'),
            ], 'inermin-resources');

            $this->publishes([
                __DIR__ . '/database/migrations' => database_path('migrations'),
            ], 'inermin-migrations');
        }
    }

    private function registerModuleRoutes()
    {
        try {
            if (Schema::hasTable('cms_moduls')) {
                $modules = DB::table('cms_moduls')
                    ->where('is_protected', 0)
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($modules as $m) {
                    if ($m->controller && class_exists($m->controller)) {
                        Inermin::routeController($m->path, $m->controller);
                    }
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet before migration
        }
    }
}
