<?php

namespace Tokalink\Inermin\commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tokalink\Inermin\database\seeders\InerminDatabaseSeeder;

class InerminInstallCommand extends Command
{
    protected $signature = 'inermin:install';
    protected $description = 'Install and publish all Inermin SPA assets, Vue components, database tables, and config';

    public function handle()
    {
        $this->info('Installing Inermin SPA Admin Package...');

        // 1. Publish Config
        $this->call('vendor:publish', [
            '--provider' => "Tokalink\\Inermin\\InerminServiceProvider",
            '--tag' => 'inermin-config',
            '--force' => true,
        ]);

        // 2. Run Database Migrations
        $this->info('Migrating Inermin database tables...');
        $this->call('migrate');

        // 3. Seed Database
        $this->info('Seeding default Inermin superadmin and settings...');
        $seeder = new InerminDatabaseSeeder();
        $seeder->run();

        // 4. Publish Vue 3 Pages & Layouts to host Laravel application
        $targetDir = resource_path('js/Pages/Inermin');
        $sourceDir = __DIR__ . '/../../resources/js';

        if (File::exists($sourceDir)) {
            File::copyDirectory($sourceDir, $targetDir);
            $this->info('Published Vue 3 Pages & Layouts to resources/js/Pages/Inermin');
        }

        $this->info('Inermin SPA Admin installed successfully!');
        $this->info('Superadmin Login: admin@crudbooster.com / 123456');
    }
}

