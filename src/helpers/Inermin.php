<?php

namespace Tokalink\Inermin\helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Request;

class Inermin
{
    public static function adminPath($path = null)
    {
        $prefix = config('inermin.ADMIN_PATH', 'administrator');
        return url($prefix . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public static function mainpath($path = null)
    {
        $adminPath = config('inermin.ADMIN_PATH', 'administrator');
        $segments = Request::segments();
        
        $mainpath = $adminPath;
        if (count($segments) > 1 && $segments[0] == $adminPath) {
            $mainpath = $adminPath . '/' . $segments[1];
        }

        return url($mainpath . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public static function myId()
    {
        return Session::get('admin_id');
    }

    public static function myName()
    {
        return Session::get('admin_name') ?: 'Admin';
    }

    public static function myPhoto()
    {
        $photo = Session::get('admin_photo');
        return $photo ? asset($photo) : asset('vendor/crudbooster/avatar.jpg');
    }

    public static function myPrivilegeId()
    {
        return Session::get('admin_privileges');
    }

    public static function myPrivilegeName()
    {
        return Session::get('admin_privileges_name') ?: 'Administrator';
    }

    public static function isSuperadmin()
    {
        return (bool) Session::get('admin_is_superadmin');
    }

    public static function getModuleRole($action = 'is_read', $modulePath = null)
    {
        if (self::isSuperadmin()) {
            return true;
        }

        $privId = self::myPrivilegeId();
        if (!$privId) return false;

        if (!$modulePath) {
            $segments = Request::segments();
            $adminPath = config('inermin.ADMIN_PATH', 'administrator');
            if (count($segments) > 1 && $segments[0] == $adminPath) {
                $modulePath = $segments[1];
            }
        }

        if (!$modulePath) return true;

        try {
            $module = DB::table('cms_moduls')
                ->where('path', $modulePath)
                ->orWhere('table_name', $modulePath)
                ->first();

            if (!$module) return true;

            $role = DB::table('cms_privileges_roles')
                ->where('id_cms_privileges', $privId)
                ->where('id_cms_moduls', $module->id)
                ->first();

            return $role ? (bool) $role->{$action} : false;
        } catch (\Exception $e) {
            return true;
        }
    }

    public static function isView($modulePath = null)
    {
        return self::getModuleRole('is_visible', $modulePath);
    }

    public static function isCreate($modulePath = null)
    {
        return self::getModuleRole('is_create', $modulePath);
    }

    public static function isRead($modulePath = null)
    {
        return self::getModuleRole('is_read', $modulePath);
    }

    public static function isUpdate($modulePath = null)
    {
        return self::getModuleRole('is_edit', $modulePath);
    }

    public static function isDelete($modulePath = null)
    {
        return self::getModuleRole('is_delete', $modulePath);
    }

    public static function getSetting($name)
    {
        try {
            $setting = DB::table('cms_settings')->where('name', $name)->first();
            return $setting ? $setting->content : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function insertLog($description)
    {
        try {
            DB::table('cms_logs')->insert([
                'ipaddress' => Request::ip(),
                'useragent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'description' => $description,
                'id_cms_users' => self::myId(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log insert fallback
        }
    }

    public static function redirect($to, $message, $type = 'info')
    {
        return redirect($to)->with($type, $message);
    }

    public static function routeController($prefix, $controller, $namespace = 'App\Http\Controllers')
    {
        $adminPath = config('inermin.ADMIN_PATH', 'administrator');
        $prefix = ltrim($prefix, '/');
        $fullPrefix = $adminPath . '/' . $prefix;
        $controllerClass = str_contains($controller, '\\') ? $controller : $namespace . '\\' . $controller;

        $shortClass = str_replace(['App\\Http\\Controllers\\', 'Tokalink\\Inermin\\controllers\\'], '', $controllerClass);
        $filePath = app_path('Http/Controllers/' . $shortClass . '.php');

        if (!class_exists($controllerClass) && file_exists($filePath)) {
            require_once $filePath;
        }

        if (!class_exists($controllerClass)) {
            return;
        }

        Route::group(['prefix' => $fullPrefix, 'middleware' => ['web', \Tokalink\Inermin\middleware\InerminShareInertiaData::class, \Tokalink\Inermin\middleware\InerminAuthMiddleware::class]], function () use ($controllerClass) {
            Route::get('/', [$controllerClass, 'getIndex']);
            Route::get('/add', [$controllerClass, 'getAdd']);
            Route::post('/add', [$controllerClass, 'postAddSave']);
            Route::get('/edit/{id}', [$controllerClass, 'getEdit']);
            Route::post('/edit/{id}', [$controllerClass, 'postEditSave']);
            Route::get('/detail/{id}', [$controllerClass, 'getDetail']);
            Route::get('/delete/{id}', [$controllerClass, 'getDelete']);
            Route::post('/action-selected', [$controllerClass, 'postActionSelected']);
            Route::get('/export-data', [$controllerClass, 'getExportData']);
            Route::post('/import-data', [$controllerClass, 'postImportData']);
        });
    }

    public static function getTableColumns($table)
    {
        try {
            return Schema::getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function listTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            return array_map(function ($table) {
                return array_values((array) $table)[0];
            }, $tables);
        } catch (\Exception $e) {
            return [];
        }
    }
}

