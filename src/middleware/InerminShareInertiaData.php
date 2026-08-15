<?php

namespace Tokalink\Inermin\middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Tokalink\Inermin\helpers\Inermin;

class InerminShareInertiaData
{
    public function handle(Request $request, Closure $next)
    {
        if (class_exists(\Inertia\Inertia::class)) {
            Inertia::share([
                'app_name' => config('inermin.APP_NAME', 'Inermin Admin'),
                'admin_path' => config('inermin.ADMIN_PATH', 'administrator'),
                'default_theme' => config('inermin.DEFAULT_THEME', 'dark'),
                'primary_color' => Inermin::parseThemeColor(Session::get('admin_theme_color') ?: config('inermin.PRIMARY_COLOR', 'indigo')),
                'auth' => [
                    'user' => Session::get('admin_id') ? [
                        'id' => Session::get('admin_id'),
                        'name' => Session::get('admin_name'),
                        'photo' => Inermin::myPhoto(),
                        'privilege_name' => Session::get('admin_privileges_name'),
                        'is_superadmin' => Session::get('admin_is_superadmin'),
                    ] : null,
                ],
                'menu' => $this->getMenuTree(),
                'notifications' => $this->getNotifications(),
                'flash' => [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'message' => Session::get('message'),
                ]
            ]);
        }

        return $next($request);
    }

    private function getNotifications()
    {
        if (! Session::get('admin_id')) return [];

        try {
            if (Schema::hasTable('cms_notifications')) {
                return DB::table('cms_notifications')
                    ->where('id_cms_users', Session::get('admin_id'))
                    ->orderBy('id', 'desc')
                    ->take(10)
                    ->get();
            }

            if (Schema::hasTable('cms_logs')) {
                return DB::table('cms_logs')
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'content' => $log->description,
                            'url' => '#',
                            'is_read' => 0,
                            'created_at' => $log->created_at ? date('M d, H:i', strtotime($log->created_at)) : 'Just now',
                            'icon' => 'bi bi-info-circle-fill',
                            'color' => 'amber'
                        ];
                    });
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMenuTree()
    {
        if (! Session::get('admin_id')) return [];

        try {
            $privId = Session::get('admin_privileges');
            $isSuperadmin = Session::get('admin_is_superadmin');

            $query = DB::table('cms_menus')
                ->where('is_active', 1)
                ->where('is_dashboard', 0);

            if (! $isSuperadmin) {
                $query->whereRaw("cms_menus.id IN (select id_cms_menus from cms_menus_privileges where id_cms_privileges = ?)", [$privId]);
            }

            $menus = $query->orderBy('sorting', 'asc')->get();

            $tree = [];
            foreach ($menus as $m) {
                $m->url = $this->parseMenuUrl($m);
                if (! $m->parent_id) {
                    $children = $menus->where('parent_id', $m->id)->values();
                    foreach ($children as $c) {
                        $c->url = $this->parseMenuUrl($c);
                    }
                    $m->children = $children;
                    $tree[] = $m;
                }
            }
            return $tree;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function parseMenuUrl($menu)
    {
        $path = $menu->path;

        if (empty($path) || $path === '#') {
            return '#';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        // Handle CRUDBooster ControllerGetIndex paths
        if (str_contains($path, 'ControllerGetIndex') || str_contains($path, 'Controller')) {
            $controllerName = str_replace(['GetIndex', 'getIndex'], '', $path);
            
            $modul = DB::table('cms_moduls')->where('controller', $controllerName)->first();
            if ($modul && $modul->path) {
                return Inermin::adminPath($modul->path);
            }

            $cleanName = str_replace(['App\\Http\\Controllers\\', 'Tokalink\\Inermin\\controllers\\', 'crocodicstudio\\crudbooster\\controllers\\', 'Admin', 'Controller'], '', $controllerName);
            $slug = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $cleanName));
            $slug = ltrim($slug, '_');
            return Inermin::adminPath($slug);
        }

        return Inermin::adminPath($path);
    }
}
