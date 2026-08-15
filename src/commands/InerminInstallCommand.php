<?php

namespace Tokalink\Inermin\commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tokalink\Inermin\database\seeders\InerminDatabaseSeeder;

class InerminInstallCommand extends Command
{
    protected $signature = 'inermin:install {--skip-npm : Skip installing NPM dependencies}';
    protected $description = 'Install and publish all Inermin SPA assets, Vue components, NPM dependencies, database tables, and config';

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

        // 5. Install & Configure Inertia / Vue NPM Dependencies
        if (!$this->option('skip-npm')) {
            $this->installNpmDependencies();
        }

        $this->info('Inermin SPA Admin installed successfully!');
        $this->info('Superadmin Login: admin@crudbooster.com / 123456');
    }

    protected function installNpmDependencies()
    {
        $this->info('Configuring Inertia & Vue 3 NPM dependencies...');

        $packageJsonPath = base_path('package.json');
        if (!File::exists($packageJsonPath)) {
            $this->warn('package.json not found in root directory.');
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true) ?? [];
        $devDependencies = $packageJson['devDependencies'] ?? [];
        $dependencies = $packageJson['dependencies'] ?? [];

        $packagesToInstall = [
            '@inertiajs/vue3' => '^2.0.0',
            'vue' => '^3.5.0',
            '@vitejs/plugin-vue' => '^5.2.0',
        ];

        $updated = false;
        foreach ($packagesToInstall as $package => $version) {
            if (!isset($devDependencies[$package]) && !isset($dependencies[$package])) {
                $devDependencies[$package] = $version;
                $updated = true;
            }
        }

        if ($updated) {
            $packageJson['devDependencies'] = $devDependencies;
            File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated package.json with Inertia & Vue 3 packages.');
        }

        // Configure vite.config.js
        $this->configureVite();

        // Configure resources/js/app.js
        $this->configureAppJs();

        // Run npm install
        $this->info('Running npm install to install Inertia & Vue packages...');
        $command = ['npm', 'install', '--legacy-peer-deps'];
        if (str_starts_with(PHP_OS, 'WIN')) {
            $command = ['cmd', '/c', 'npm', 'install', '--legacy-peer-deps'];
        }

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if ($process->getExitCode() === 0) {
            $this->info('NPM dependencies installed successfully!');
        } else {
            $this->warn('npm install completed with status code ' . $process->getExitCode());
        }

        // Run initial npm run build
        $this->info('Running npm run build to compile initial SPA assets...');
        $buildCommand = ['npm', 'run', 'build'];
        if (str_starts_with(PHP_OS, 'WIN')) {
            $buildCommand = ['cmd', '/c', 'npm', 'run', 'build'];
        }

        $buildProcess = new Process($buildCommand, base_path());
        $buildProcess->setTimeout(300);
        $buildProcess->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if ($buildProcess->getExitCode() === 0) {
            $this->info('Initial Inermin SPA assets built successfully!');
        } else {
            $this->warn('npm run build finished with status code ' . $buildProcess->getExitCode());
        }
    }

    protected function configureVite()
    {
        $vitePath = base_path('vite.config.js');
        if (!File::exists($vitePath)) {
            return;
        }

        $content = File::get($vitePath);

        if (!str_contains($content, '@vitejs/plugin-vue')) {
            $content = "import vue from '@vitejs/plugin-vue';\n" . $content;
            if (str_contains($content, 'plugins: [')) {
                $content = str_replace('plugins: [', "plugins: [\n        vue(),", $content);
            }
            $this->info('Added vue() plugin to vite.config.js');
        }

        if (!str_contains($content, "alias:")) {
            if (str_contains($content, 'export default defineConfig({')) {
                $aliasConfig = <<<JS

    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
JS;
                $content = str_replace('export default defineConfig({', "export default defineConfig({" . $aliasConfig, $content);
            }
        }

        File::put($vitePath, $content);
    }

    protected function configureAppJs()
    {
        $appJsPath = resource_path('js/app.js');
        $content = File::exists($appJsPath) ? File::get($appJsPath) : '';

        if (!str_contains($content, 'createInertiaApp')) {
            $inertiaAppSetup = <<<JS
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/\${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
JS;

            if (trim($content) === '' || trim($content) === '//') {
                File::put($appJsPath, $inertiaAppSetup . "\n");
            } else {
                File::append($appJsPath, "\n\n" . $inertiaAppSetup . "\n");
            }
            $this->info('Configured Inertia Vue 3 setup in resources/js/app.js');
        }
    }
}


