<?php

use Illuminate\Support\Facades\Route;
use Tokalink\Inermin\controllers\InerminAuthController;
use Tokalink\Inermin\controllers\InerminDashboardController;
use Tokalink\Inermin\controllers\InerminUsersController;
use Tokalink\Inermin\controllers\InerminPrivilegesController;
use Tokalink\Inermin\controllers\InerminMenusController;
use Tokalink\Inermin\controllers\InerminSettingsController;
use Tokalink\Inermin\controllers\InerminModulsController;
use Tokalink\Inermin\controllers\InerminLogsController;
use Tokalink\Inermin\controllers\InerminApiController;
use Tokalink\Inermin\controllers\InerminEmailController;
use Tokalink\Inermin\controllers\InerminStatisticController;
use Tokalink\Inermin\controllers\InerminApiEngineController;
use Tokalink\Inermin\controllers\InerminAppsController;
use Tokalink\Inermin\controllers\InerminTenantsController;
use Tokalink\Inermin\middleware\InerminAuthMiddleware;
use Tokalink\Inermin\middleware\InerminShareInertiaData;

// Public & Authenticated Custom API Generator Endpoints
Route::match(['get', 'post', 'put', 'delete'], '/api/{permalink}/{id?}', [InerminApiEngineController::class, 'handleApi']);

$prefix = config('inermin.ADMIN_PATH', 'administrator');

Route::group([
    'prefix' => $prefix,
    'middleware' => ['web', InerminShareInertiaData::class]
], function () {
    // Guest Routes
    Route::get('/login', [InerminAuthController::class, 'getLogin'])->name('inermin.login');
    Route::post('/login', [InerminAuthController::class, 'postLogin']);

    // Protected Admin Routes
    Route::group(['middleware' => [InerminAuthMiddleware::class]], function () {
        Route::get('/', [InerminDashboardController::class, 'getIndex'])->name('inermin.dashboard');
        Route::get('/logout', [InerminAuthController::class, 'getLogout'])->name('inermin.logout');
        Route::get('/profile', [InerminUsersController::class, 'getProfile'])->name('inermin.profile');
        Route::post('/profile', [InerminUsersController::class, 'postSaveProfile']);
        Route::match(['get', 'post'], '/lov-data', [InerminApiController::class, 'getLovData']);

        // Built-in System Modules
        Route::get('/tenants', [InerminTenantsController::class, 'getIndex']);
        Route::get('/tenants/add', [InerminTenantsController::class, 'getAdd']);
        Route::post('/tenants/add', [InerminTenantsController::class, 'postAddSave']);
        Route::get('/tenants/edit/{id}', [InerminTenantsController::class, 'getEdit']);
        Route::post('/tenants/edit/{id}', [InerminTenantsController::class, 'postEditSave']);
        Route::get('/tenants/delete/{id}', [InerminTenantsController::class, 'getDelete']);
        Route::get('/tenants/impersonate/{id}', [InerminTenantsController::class, 'getImpersonate']);
        Route::get('/tenants/stop-impersonate', [InerminTenantsController::class, 'getStopImpersonate']);

        Route::get('/apps', [InerminAppsController::class, 'getIndex']);
        Route::get('/apps/add', [InerminAppsController::class, 'getAdd']);
        Route::post('/apps/add', [InerminAppsController::class, 'postAddSave']);
        Route::get('/apps/edit/{id}', [InerminAppsController::class, 'getEdit']);
        Route::post('/apps/edit/{id}', [InerminAppsController::class, 'postEditSave']);
        Route::get('/apps/delete/{id}', [InerminAppsController::class, 'getDelete']);

        Route::get('/users', [InerminUsersController::class, 'getIndex']);
        Route::get('/users/add', [InerminUsersController::class, 'getAdd']);
        Route::post('/users/add', [InerminUsersController::class, 'postAddSave']);
        Route::get('/users/edit/{id}', [InerminUsersController::class, 'getEdit']);
        Route::post('/users/edit/{id}', [InerminUsersController::class, 'postEditSave']);
        Route::get('/users/delete/{id}', [InerminUsersController::class, 'getDelete']);

        Route::get('/privileges', [InerminPrivilegesController::class, 'getIndex']);
        Route::get('/privileges/add', [InerminPrivilegesController::class, 'getAdd']);
        Route::post('/privileges/add', [InerminPrivilegesController::class, 'postAddSave']);
        Route::get('/privileges/edit/{id}', [InerminPrivilegesController::class, 'getEdit']);
        Route::post('/privileges/edit/{id}', [InerminPrivilegesController::class, 'postEditSave']);
        Route::get('/privileges/delete/{id}', [InerminPrivilegesController::class, 'getDelete']);

        Route::get('/menus', [InerminMenusController::class, 'getIndex']);
        Route::get('/menus/add', [InerminMenusController::class, 'getAdd']);
        Route::post('/menus/add', [InerminMenusController::class, 'postAddSave']);
        Route::post('/menus/save', [InerminMenusController::class, 'postSave']);
        Route::post('/menus/save-sorting', [InerminMenusController::class, 'postSaveSorting']);
        Route::get('/menus/move-order/{id}/{direction}', [InerminMenusController::class, 'postMoveOrder']);
        Route::get('/menus/edit/{id}', [InerminMenusController::class, 'getEdit']);
        Route::post('/menus/edit/{id}', [InerminMenusController::class, 'postEditSave']);
        Route::get('/menus/delete/{id}', [InerminMenusController::class, 'getDelete']);

        Route::get('/settings', [InerminSettingsController::class, 'getIndex']);
        Route::post('/settings/save', [InerminSettingsController::class, 'postSave']);
        Route::post('/settings/add', [InerminSettingsController::class, 'postAddSave']);
        Route::get('/settings/delete-setting/{id}', [InerminSettingsController::class, 'getDeleteSetting']);
        Route::get('/settings/delete/{id}', [InerminSettingsController::class, 'getDelete']);

        Route::get('/modules', [InerminModulsController::class, 'getIndex']);
        Route::get('/modules/step1/{id?}', [InerminModulsController::class, 'getStep1']);
        Route::post('/modules/step2', [InerminModulsController::class, 'postStep2']);
        Route::get('/modules/step2/{id}', [InerminModulsController::class, 'getStep2']);
        Route::post('/modules/step3', [InerminModulsController::class, 'postStep3']);
        Route::get('/modules/step3/{id}', [InerminModulsController::class, 'getStep3']);
        Route::post('/modules/step4', [InerminModulsController::class, 'postStep4']);
        Route::get('/modules/step4/{id}', [InerminModulsController::class, 'getStep4']);
        Route::post('/modules/finish', [InerminModulsController::class, 'postFinish']);
        Route::get('/modules/delete/{id}', [InerminModulsController::class, 'getDelete']);

        Route::get('/logs', [InerminLogsController::class, 'getIndex']);
        Route::get('/logs/delete/{id}', [InerminLogsController::class, 'getDelete']);

        Route::get('/api_generator', [InerminApiController::class, 'getIndex']);
        Route::get('/api_generator/add', [InerminApiController::class, 'getAdd']);
        Route::post('/api_generator/add', [InerminApiController::class, 'postAddSave']);
        Route::get('/api_generator/edit/{id}', [InerminApiController::class, 'getEdit']);
        Route::post('/api_generator/edit/{id}', [InerminApiController::class, 'postEditSave']);
        Route::get('/api_generator/delete/{id}', [InerminApiController::class, 'getDelete']);

        Route::get('/email_templates', [InerminEmailController::class, 'getIndex']);
        Route::get('/email_templates/add', [InerminEmailController::class, 'getAdd']);
        Route::post('/email_templates/add', [InerminEmailController::class, 'postAddSave']);
        Route::get('/email_templates/edit/{id}', [InerminEmailController::class, 'getEdit']);
        Route::post('/email_templates/edit/{id}', [InerminEmailController::class, 'postEditSave']);
        Route::get('/email_templates/delete/{id}', [InerminEmailController::class, 'getDelete']);

        Route::get('/statistic_builder', [InerminStatisticController::class, 'getIndex']);
        Route::get('/statistic_builder/add', [InerminStatisticController::class, 'getAdd']);
        Route::post('/statistic_builder/add', [InerminStatisticController::class, 'postAddSave']);
        Route::get('/statistic_builder/edit/{id}', [InerminStatisticController::class, 'getEdit']);
        Route::post('/statistic_builder/edit/{id}', [InerminStatisticController::class, 'postEditSave']);
        Route::get('/statistic_builder/delete/{id}', [InerminStatisticController::class, 'getDelete']);
        Route::get('/statistic_builder/builder/{id}', [InerminStatisticController::class, 'getBuilder']);
        Route::get('/statistic_builder/show/{slug}', [InerminStatisticController::class, 'getShow']);
        Route::post('/statistic_builder/add-component', [InerminStatisticController::class, 'postAddComponent']);
        Route::post('/statistic_builder/update-area-component', [InerminStatisticController::class, 'postUpdateAreaComponent']);
        Route::post('/statistic_builder/save-component', [InerminStatisticController::class, 'postSaveComponent']);
        Route::get('/statistic_builder/delete-component/{id}', [InerminStatisticController::class, 'getDeleteComponent']);
    });
});

// Dynamic Custom Generated Modules Routes
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('cms_moduls')) {
        $modules = \Illuminate\Support\Facades\DB::table('cms_moduls')
            ->where('path', '!=', '')
            ->where('controller', '!=', '')
            ->whereNotNull('path')
            ->whereNotNull('controller')
            ->where('is_protected', 0)
            ->whereNull('deleted_at')
            ->get();

        foreach ($modules as $mod) {
            if (!empty($mod->path) && !empty($mod->controller)) {
                $appCode = $mod->app_code ?? null;
                \Tokalink\Inermin\helpers\Inermin::routeController($mod->path, $mod->controller, 'App\Http\Controllers', $appCode);
            }
        }
    }
} catch (\Exception $e) {
    // Schema or table not ready fallback
}

// Dynamic App Suite Landing Routes (Standalone /mutasi and Central /administrator/mutasi)
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('cms_apps')) {
        $apps = \Illuminate\Support\Facades\DB::table('cms_apps')
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->where('is_active', 1)
            ->get();

        foreach ($apps as $appItem) {
            $appCode = trim($appItem->code, '/');
            
            // Standalone Root Route (e.g. /mutasi)
            Route::get('/' . $appCode, function () use ($appItem) {
                return (new \Tokalink\Inermin\controllers\InerminAppsController)->getAppLanding($appItem->code);
            })->middleware(['web', InerminShareInertiaData::class, InerminAuthMiddleware::class]);

            // Central Admin Route (e.g. /administrator/mutasi)
            Route::get($prefix . '/' . $appCode, function () use ($appItem) {
                return (new \Tokalink\Inermin\controllers\InerminAppsController)->getAppLanding($appItem->code);
            })->middleware(['web', InerminShareInertiaData::class, InerminAuthMiddleware::class]);
        }
    }
} catch (\Exception $e) {
    // Schema or table not ready fallback
}

