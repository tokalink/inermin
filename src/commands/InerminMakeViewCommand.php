<?php

namespace Tokalink\Inermin\commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InerminMakeViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inermin:make-view 
                            {name : The name of the Vue page component (e.g. Chats/Index or CustomPage)} 
                            {--force : Overwrite existing view file if it exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new scaffolded Vue page component with Inermin Layout and imports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rawName = trim($this->argument('name'));

        // Normalize view path name
        $normalizedName = str_replace('\\', '/', $rawName);
        $normalizedName = ltrim($normalizedName, '/');

        // Remove .vue extension if user included it
        if (str_ends_with(strtolower($normalizedName), '.vue')) {
            $normalizedName = substr($normalizedName, 0, -4);
        }

        // Determine destination file path inside resources/js/Pages/Inermin or resources/js/Pages/
        if (str_starts_with($normalizedName, 'Inermin/')) {
            $relativePath = 'js/Pages/' . $normalizedName . '.vue';
        } else {
            $relativePath = 'js/Pages/Inermin/' . $normalizedName . '.vue';
        }

        $fullPath = resource_path($relativePath);
        $directory = dirname($fullPath);

        // Ensure target directory exists
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Check if file already exists
        if (File::exists($fullPath) && !$this->option('force')) {
            $this->error("View component already exists at: [resources/{$relativePath}]");
            $this->info("Use --force flag to overwrite.");
            return 1;
        }

        // Generate scaffold content or copy vendor core template
        $componentName = basename($normalizedName);
        $corePackageVue = __DIR__ . '/../../resources/js/' . $normalizedName . '.vue';
        
        if (File::exists($corePackageVue)) {
            $content = File::get($corePackageVue);
            $content = str_replace(["'./InerminAppLayout.vue'", "'../InerminAppLayout.vue'"], "'@inermin/InerminAppLayout.vue'", $content);
            $this->info("Published core vendor template [{$normalizedName}.vue] to local project for customization.");
        } else {
            $content = $this->getStubContent($componentName);
        }

        File::put($fullPath, $content);

        $this->info("Successfully generated Vue page component!");
        $this->line("<comment>File Created:</comment> resources/{$relativePath}");
        $this->line("<comment>Imported Layout:</comment> @inermin/InerminAppLayout.vue");
        
        return 0;
    }

    /**
     * Get Vue 3 SFC Scaffold Stub Content
     */
    protected function getStubContent(string $componentName): string
    {
        return <<<VUE
<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '@inermin/InerminAppLayout.vue'

const props = defineProps({
  page_title: {
    type: String,
    default: '{$componentName}'
  },
  // Define custom module props here
  data: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

// Custom logic & reactive state
const isLoading = ref(false)
</script>

<template>
  <Head :title="page_title" />

  <InerminAppLayout>
    <div class="max-w-6xl mx-auto space-y-6 font-sans antialiased">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-stone-400">
            <Link :href="currentPath.replace(/\/add|\/edit\/.*/, '')" class="hover:text-stone-200 transition">
              Module List
            </Link>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-[rgb(var(--accent-rgb))] font-bold">{$componentName}</span>
          </div>

          <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 dark:text-white mt-1">
            {{ page_title }}
          </h1>
          <p class="text-xs text-stone-400 font-medium mt-0.5">
            Custom Vue page component scaffolded for Inermin admin panel
          </p>
        </div>

        <!-- Action Header Button -->
        <Link
          :href="currentPath.replace(/\/add|\/edit\/.*/, '')"
          class="px-4 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-white/5 font-bold text-xs shadow-xs transition flex items-center gap-2 self-start sm:self-auto"
        >
          <i class="bi bi-arrow-left text-sm"></i>
          <span>Back to Registry</span>
        </Link>
      </div>

      <!-- Main Content Card -->
      <div class="card rounded-3xl border border-stone-200 dark:border-white/5 shadow-2xl p-6 lg:p-8 space-y-6">
        <h2 class="text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
          <i class="bi bi-layout-text-window-reverse text-[rgb(var(--accent-rgb))]"></i>
          <span>Content Section</span>
        </h2>
        
        <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
          Start developing your custom content inside this Vue component. All Inermin layout, theme settings, and UI styling are automatically integrated.
        </p>
      </div>

    </div>
  </InerminAppLayout>
</template>
VUE;
    }
}
