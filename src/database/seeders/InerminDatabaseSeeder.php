<?php

namespace Tokalink\Inermin\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InerminDatabaseSeeder extends Seeder
{
    public $adminPassword;         // injected by installer command
    public $createdAdminPassword;  // set when a fresh admin user is created

    public function run()
    {
        // 1. Super Admin Privilege
        if (DB::table('cms_privileges')->count() == 0) {
            DB::table('cms_privileges')->insert([
                'id' => 1,
                'name' => 'Super Admin',
                'is_superadmin' => 1,
                'theme_color' => 'theme-indigo',
                'created_at' => now(),
            ]);
        }

        // 2. Default User (credentials from ENV - never use weak hardcoded default in production)
        if (DB::table('cms_users')->count() == 0) {
            $adminEmail = env('INERMIN_ADMIN_EMAIL', 'admin@inermin.com');
            $adminPassword = $this->adminPassword ?: env('INERMIN_ADMIN_PASSWORD', Str::random(16));

            DB::table('cms_users')->insert([
                'id' => 1,
                'name' => 'Super Admin',
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'id_cms_privileges' => 1,
                'status' => 'Active',
                'created_at' => now(),
            ]);

            $this->createdAdminPassword = $adminPassword;

            if (!env('INERMIN_ADMIN_PASSWORD')) {
                $this->command?->info("Generated admin password: {$adminPassword}");
                $this->command?->warn('Set INERMIN_ADMIN_PASSWORD in .env to control it explicitly.');
            }
        }

        // 3. System Modules
        $modules = [
            ['name' => 'Users Management', 'icon' => 'bi bi-people-fill', 'path' => 'users', 'table_name' => 'cms_users', 'controller' => 'InerminUsersController', 'is_protected' => 1],
            ['name' => 'Privileges Roles', 'icon' => 'bi bi-key-fill', 'path' => 'privileges', 'table_name' => 'cms_privileges', 'controller' => 'InerminPrivilegesController', 'is_protected' => 1],
            ['name' => 'Menu Management', 'icon' => 'bi bi-list-nested', 'path' => 'menus', 'table_name' => 'cms_menus', 'controller' => 'InerminMenusController', 'is_protected' => 1],
            ['name' => 'Settings', 'icon' => 'bi bi-gear-fill', 'path' => 'settings', 'table_name' => 'cms_settings', 'controller' => 'InerminSettingsController', 'is_protected' => 1],
            ['name' => 'Module Generator', 'icon' => 'bi bi-boxes', 'path' => 'modules', 'table_name' => 'cms_moduls', 'controller' => 'InerminModulsController', 'is_protected' => 1],
            ['name' => 'Statistic Builder', 'icon' => 'bi bi-graph-up-arrow', 'path' => 'statistic_builder', 'table_name' => 'cms_statistics', 'controller' => 'InerminStatisticController', 'is_protected' => 1],
            ['name' => 'API Generator', 'icon' => 'bi bi-code-slash', 'path' => 'api_generator', 'table_name' => 'cms_apicustom', 'controller' => 'InerminApiController', 'is_protected' => 1],
            ['name' => 'Email Templates', 'icon' => 'bi bi-envelope-at', 'path' => 'email_templates', 'table_name' => 'cms_email_templates', 'controller' => 'InerminEmailController', 'is_protected' => 1],
            ['name' => 'Log User Access', 'icon' => 'bi bi-journal-text', 'path' => 'logs', 'table_name' => 'cms_logs', 'controller' => 'InerminLogsController', 'is_protected' => 1],
        ];

        foreach ($modules as $m) {
            if (DB::table('cms_moduls')->where('path', $m['path'])->count() == 0) {
                DB::table('cms_moduls')->insert(array_merge($m, ['is_active' => 1, 'created_at' => now()]));
            }
        }

        // 4. Default Settings
        $settings = [
            ['name' => 'appname', 'label' => 'Application Name', 'content' => 'Inermin Admin', 'group_setting' => 'General Setting', 'content_input_type' => 'text'],
            ['name' => 'logo', 'label' => 'Application Logo', 'content' => '', 'group_setting' => 'General Setting', 'content_input_type' => 'upload_image'],
            ['name' => 'favicon', 'label' => 'Favicon', 'content' => '', 'group_setting' => 'General Setting', 'content_input_type' => 'upload_image'],
            ['name' => 'email_sender', 'label' => 'Email Sender', 'content' => 'admin@inermin.com', 'group_setting' => 'Email Setting', 'content_input_type' => 'email'],
            ['name' => 'smtp_host', 'label' => 'SMTP Host', 'content' => 'smtp.gmail.com', 'group_setting' => 'Email Setting', 'content_input_type' => 'text'],
            ['name' => 'smtp_port', 'label' => 'SMTP Port', 'content' => '587', 'group_setting' => 'Email Setting', 'content_input_type' => 'text'],
        ];

        foreach ($settings as $s) {
            if (DB::table('cms_settings')->where('name', $s['name'])->count() == 0) {
                DB::table('cms_settings')->insert(array_merge($s, ['created_at' => now()]));
            }
        }


    }
}
