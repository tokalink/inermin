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

        // 4. Publish Static Assets (Default Avatar, Icons)
        $assetsSource = __DIR__ . '/../../assets';
        $assetsTarget = public_path('vendor/inermin');
        if (File::exists($assetsSource)) {
            File::ensureDirectoryExists($assetsTarget);
            File::copyDirectory($assetsSource, $assetsTarget);
            $this->info('Published assets to public/vendor/inermin');
        }

        // 5. Publish Customizable Dashboard.vue to host Laravel application
        $targetDashboard = resource_path('js/Pages/Inermin/Dashboard.vue');
        $sourceDashboard = __DIR__ . '/../../resources/js/Dashboard.vue';
        if (File::exists($sourceDashboard) && !File::exists($targetDashboard)) {
            File::ensureDirectoryExists(dirname($targetDashboard));
            $content = File::get($sourceDashboard);
            $content = str_replace(["'./InerminAppLayout.vue'", "'../InerminAppLayout.vue'"], "'@inermin/InerminAppLayout.vue'", $content);
            File::put($targetDashboard, $content);
            $this->info('Published customizable Dashboard.vue to resources/js/Pages/Inermin/Dashboard.vue');
        }

        // 5. Install & Configure Inertia / Vue NPM Dependencies
        if (!$this->option('skip-npm')) {
            $this->installNpmDependencies();
        }

        $this->info('=====================================================');
        $this->info(' Inermin SPA Admin installed successfully! 🎉');
        $this->info(' Superadmin Credentials:');
        
        $adminEmail = env('INERMIN_ADMIN_EMAIL', 'admin@inermin.com');
        $this->info(' Email    : ' . $adminEmail);
        
        if (!env('INERMIN_ADMIN_PASSWORD')) {
            $this->info(' Password : (Auto-generated and shown during DB seeding)');
            $this->warn(' Note: Set INERMIN_ADMIN_PASSWORD in .env to define explicitly.');
        } else {
            $this->info(' Password : (As defined in .env)');
        }
        
        $this->info(' Admin URL: /' . config('inermin.ADMIN_PATH', 'administrator'));
        $this->info('=====================================================');
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

        // 1. Ensure vue() plugin import
        if (!str_contains($content, '@vitejs/plugin-vue')) {
            $content = "import vue from '@vitejs/plugin-vue';\n" . $content;
            if (str_contains($content, 'plugins: [')) {
                $content = str_replace('plugins: [', "plugins: [\n        vue(),", $content);
            }
        }

        // 2. Ensure path import
        if (!str_contains($content, "import path from 'path';")) {
            $content = "import path from 'path';\n" . $content;
        }

        // 3. Ensure @inermin path alias
        if (!str_contains($content, '@inermin')) {
            $packageJsPath = file_exists(base_path('packages/inermin/resources/js'))
                ? "path.resolve(__dirname, 'packages/inermin/resources/js')"
                : "path.resolve(__dirname, 'vendor/tokalink/inermin/resources/js')";

            if (str_contains($content, 'alias: {')) {
                $content = str_replace("alias: {", "alias: {\n            '@inermin': {$packageJsPath},", $content);
            } else if (str_contains($content, 'export default defineConfig({')) {
                $aliasConfig = <<<JS

    resolve: {
        alias: {
            '@': '/resources/js',
            '@inermin': {$packageJsPath},
        },
    },
JS;
                $content = str_replace('export default defineConfig({', "export default defineConfig({" . $aliasConfig, $content);
            }
            $this->info('Configured @inermin path alias in vite.config.js');
        }

        File::put($vitePath, $content);
    }

    protected function configureAppJs()
    {
        $appJsPath = resource_path('js/app.js');

        $inertiaAppSetup = <<<JS
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

// Load App-level Custom Vue Components from project resources/js/Pages
const appPages = import.meta.glob('./Pages/**/*.vue', { eager: true })

// Load Core Inermin Package Vue Components (supports vendor or local packages/)
const packagePagesVendor = import.meta.glob('../../vendor/tokalink/inermin/resources/js/**/*.vue', { eager: true })
const packagePagesLocal = import.meta.glob('../../packages/inermin/resources/js/**/*.vue', { eager: true })
const packagePages = { ...packagePagesVendor, ...packagePagesLocal }

createInertiaApp({
  resolve: name => {
    const cleanName = name.replace(/^Inermin\//, '').replace(/^\//, '')

    const keysToTry = [
      `./Pages/\${name}.vue`,
      `./Pages/Inermin/\${cleanName}.vue`,
      `./Pages/\${cleanName}.vue`,
      `../../vendor/tokalink/inermin/resources/js/\${cleanName}.vue`,
      `../../packages/inermin/resources/js/\${cleanName}.vue`
    ]

    for (const key of keysToTry) {
      if (appPages[key]) return appPages[key]
      if (packagePages[key]) return packagePages[key]
    }

    const lowerClean = cleanName.toLowerCase() + '.vue'
    for (const key in appPages) {
      if (key.toLowerCase().endsWith(lowerClean)) {
        return appPages[key]
      }
    }
    for (const key in packagePages) {
      if (key.toLowerCase().endsWith(lowerClean)) {
        return packagePages[key]
      }
    }

    console.error(`[Inermin Error] Component "\${name}" is missing from the Vite bundle.`)
    throw new Error(`Page component "\${name}" not found. Please run "npm run build".`)
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
JS;

        File::put($appJsPath, $inertiaAppSetup . "\n");
        $this->info('Configured Inertia Vue 3 setup in resources/js/app.js');
    }
}
