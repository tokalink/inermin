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
        if ($photo && !str_contains($photo, 'crudbooster')) {
            if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                return $photo;
            }
            if (file_exists(public_path(ltrim($photo, '/')))) {
                return asset(ltrim($photo, '/'));
            }
        }

        if (Session::get('admin_id')) {
            try {
                $user = DB::table('cms_users')->where('id', Session::get('admin_id'))->first();
                if ($user && $user->photo && !str_contains($user->photo, 'crudbooster')) {
                    if (str_starts_with($user->photo, 'http://') || str_starts_with($user->photo, 'https://')) {
                        return $user->photo;
                    }
                    if (file_exists(public_path(ltrim($user->photo, '/')))) {
                        return asset(ltrim($user->photo, '/'));
                    }
                }
            } catch (\Exception $e) {}
        }

        return asset('vendor/inermin/avatar.svg');
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

    public static function parseThemeColor($themeColor = null)
    {
        if (!$themeColor) {
            $themeColor = config('inermin.PRIMARY_COLOR', 'amber');
        }

        $colorStr = strtolower($themeColor);

        $map = [
            'skin-blue' => 'ocean',
            'skin-blue-light' => 'ocean',
            'skin-purple' => 'violet',
            'skin-purple-light' => 'violet',
            'skin-green' => 'emerald',
            'skin-green-light' => 'emerald',
            'skin-red' => 'crimson',
            'skin-red-light' => 'crimson',
            'skin-yellow' => 'amber',
            'skin-yellow-light' => 'amber',
            'indigo' => 'violet',
            'blue' => 'ocean',
            'purple' => 'violet',
            'red' => 'crimson',
            'rose' => 'crimson',
        ];

        if (isset($map[$colorStr])) {
            return $map[$colorStr];
        }

        if (str_contains($colorStr, 'emerald') || str_contains($colorStr, 'green')) return 'emerald';
        if (str_contains($colorStr, 'crimson') || str_contains($colorStr, 'red') || str_contains($colorStr, 'rose')) return 'crimson';
        if (str_contains($colorStr, 'ocean') || str_contains($colorStr, 'cyan') || str_contains($colorStr, 'blue')) return 'ocean';
        if (str_contains($colorStr, 'violet') || str_contains($colorStr, 'purple')) return 'violet';
        if (str_contains($colorStr, 'bronze') || str_contains($colorStr, 'brown')) return 'bronze';

        return 'amber';
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
            return false;
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

    public static function getCurrentModule()
    {
        $segments = Request::segments();
        $adminPath = config('inermin.ADMIN_PATH', 'administrator');
        $modulePath = (count($segments) > 1 && $segments[0] == $adminPath) ? $segments[1] : '';

        try {
            return DB::table('cms_moduls')->where('path', $modulePath)->first() ?: (object)[
                'name' => ucwords(str_replace('_', ' ', $modulePath ?: 'Module')),
                'path' => $modulePath,
            ];
        } catch (\Exception $e) {
            return (object)[
                'name' => ucwords(str_replace('_', ' ', $modulePath ?: 'Module')),
                'path' => $modulePath,
            ];
        }
    }

    public static function pk($table)
    {
        return 'id';
    }

    public static function parseSqlTable($table)
    {
        return ['table' => $table];
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

    public static function routeController($prefix, $controller, $namespace = 'App\Http\Controllers', $appCode = null)
    {
        $adminPath = config('inermin.ADMIN_PATH', 'administrator');
        $prefix = ltrim($prefix, '/');
        $controllerClass = str_contains($controller, '\\') ? $controller : $namespace . '\\' . $controller;

        $shortClass = str_replace(['App\\Http\\Controllers\\', 'Tokalink\\Inermin\\controllers\\'], '', $controllerClass);
        $filePath = app_path('Http/Controllers/' . $shortClass . '.php');

        if (!class_exists($controllerClass) && file_exists($filePath)) {
            require_once $filePath;
        }

        if (!class_exists($controllerClass)) {
            return;
        }

        $prefixes = [ $adminPath . '/' . $prefix ];

        // Support standalone app prefix like /mutasi/banks
        if ($appCode && $appCode !== 'core') {
            $prefixes[] = trim($appCode, '/') . '/' . $prefix;
        }

        foreach ($prefixes as $fullPrefix) {
            Route::group(['prefix' => $fullPrefix, 'middleware' => ['web', \Tokalink\Inermin\middleware\InerminShareInertiaData::class, \Tokalink\Inermin\middleware\InerminAuthMiddleware::class]], function () use ($controllerClass) {
                Route::get('/', [$controllerClass, 'getIndex']);
                Route::get('/add', [$controllerClass, 'getAdd']);
                Route::post('/add', [$controllerClass, 'postAddSave']);
                Route::post('/send', [$controllerClass, 'postSendMessage']);
                Route::get('/edit/{id?}', [$controllerClass, 'getEdit']);
                Route::post('/edit/{id?}', [$controllerClass, 'postEditSave']);
                Route::get('/detail/{id?}', [$controllerClass, 'getDetail']);
                Route::get('/delete/{id?}', [$controllerClass, 'getDelete']);
                Route::post('/action-selected', [$controllerClass, 'postActionSelected']);
                Route::match(['get', 'post'], '/export-data', [$controllerClass, 'postExportData']);
                Route::match(['get', 'post'], '/import-data', [$controllerClass, 'postImportData']);
            });
        }
    }

    public static function getTableColumns($table)
    {
        try {
            return Schema::getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function listTables($connection = null)
    {
        try {
            $conn = DB::connection($connection);
            $driver = $conn->getDriverName();

            if ($driver === 'pgsql') {
                $tables = $conn->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name");
                return array_map(function ($t) {
                    return $t->table_name;
                }, $tables);
            } elseif ($driver === 'sqlsrv') {
                $tables = $conn->select("SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' ORDER BY table_name");
                return array_map(function ($t) {
                    return $t->table_name;
                }, $tables);
            } else {
                $tables = $conn->select('SHOW TABLES');
                return array_map(function ($t) {
                    return array_values((array) $t)[0];
                }, $tables);
            }
        } catch (\Exception $e) {
            return [];
        }
    }
}
