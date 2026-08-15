<?php

namespace Tokalink\Inermin\controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Tokalink\Inermin\helpers\Inermin;

class InerminDashboardController extends Controller
{
    public function getIndex()
    {
        $users_count = 0;
        $modules_count = 0;
        $logs_count = 0;

        try {
            $users_count = DB::table('cms_users')->count();
            $modules_count = DB::table('cms_moduls')->count();
            $logs_count = DB::table('cms_logs')->count();
        } catch (\Exception $e) {
            // fallback
        }

        return Inertia::render('Inermin/Dashboard', [
            'page_title' => 'Dashboard Overview',
            'stats' => [
                'users' => $users_count,
                'modules' => $modules_count,
                'logs' => $logs_count,
            ],
            'user' => [
                'name' => Inermin::myName(),
                'privilege' => Inermin::myPrivilegeName(),
            ]
        ]);
    }
}
