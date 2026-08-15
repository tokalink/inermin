<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tokalink\Inermin\helpers\Inermin;
use Inertia\Inertia;

class InerminModulsController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_moduls';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->limit = 100;
        $this->orderby = 'is_protected,asc,name,asc';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Module Name', 'name' => 'name'],
            ['label' => 'Table Name', 'name' => 'table_name'],
            ['label' => 'Path Slug', 'name' => 'path'],
            ['label' => 'Controller', 'name' => 'controller'],
        ];

        $this->form = [
            ['label' => 'Module Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Table Name', 'name' => 'table_name', 'type' => 'text', 'required' => true],
            ['label' => 'Path Slug', 'name' => 'path', 'type' => 'text', 'required' => true],
            ['label' => 'Icon', 'name' => 'icon', 'type' => 'text'],
        ];
    }

    public function getIndex(\Illuminate\Http\Request $request = null)
    {
        $data = [];
        $data['page_title'] = 'Module Generator';
        $data['modules'] = DB::table('cms_moduls')
            ->where('is_protected', 0)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();
        $data['tables'] = Inermin::listTables();

        return Inertia::render('Inermin/Modules/Index', $data);
    }

    public function getStep1($id = 0)
    {
        $data = [];
        $data['page_title'] = 'Module Generator - Step 1 (Module Info)';
        $data['step'] = 1;
        $data['id'] = $id;
        $data['row'] = $id ? DB::table('cms_moduls')->where('id', $id)->first() : null;
        $data['tables'] = Inermin::listTables();

        return Inertia::render('Inermin/Modules/Wizard', $data);
    }

    public function postStep2()
    {
        $name = Request::input('name');
        $module_type = Request::input('module_type', 'crud');
        $table_name = Request::input('table_name') ?: '';
        $icon = Request::input('icon') ?: 'bi bi-boxes';
        
        $rawPath = Request::input('path');
        if (!$rawPath) {
            $rawPath = $name ?: $table_name;
        }
        $path = Str::slug(str_replace('_', ' ', $rawPath), '_');
        $controller = Request::input('controller') ?: 'Admin' . Str::studly($path) . 'Controller';
        $id = Request::input('id');

        if (!$id) {
            if (DB::table('cms_moduls')->where('path', $path)->whereNull('deleted_at')->exists()) {
                return redirect()->back()->with('error', "Slug path '{$path}' already exists! Please choose another path.");
            }

            // Create Controller file if it does not exist
            $controllerPath = app_path('Http/Controllers/' . $controller . '.php');
            if (!file_exists($controllerPath)) {
                if ($module_type === 'custom') {
                    $studlyPath = Str::studly($path);
                    $stub = $this->generateCustomControllerStub($controller, $studlyPath, $name);
                    
                    // Auto generate Custom Vue View Scaffold
                    $viewPath = resource_path('js/Pages/Inermin/' . $studlyPath . '/Index.vue');
                    $viewDir = dirname($viewPath);
                    if (!File::isDirectory($viewDir)) {
                        File::makeDirectory($viewDir, 0755, true);
                    }
                    if (!File::exists($viewPath)) {
                        File::put($viewPath, $this->generateCustomVueViewStub($studlyPath, $name));
                    }
                } else {
                    $stub = $this->generateControllerStub($controller, $table_name, $name);
                }
                File::put($controllerPath, $stub);
            }

            $id = DB::table('cms_moduls')->insertGetId([
                'name' => $name,
                'table_name' => $table_name,
                'icon' => $icon,
                'path' => $path,
                'controller' => $controller,
                'is_protected' => 0,
                'is_active' => 1,
                'created_at' => now(),
            ]);

            // Auto create menu item
            if (Request::input('create_menu')) {
                $menuSort = DB::table('cms_menus')->where('parent_id', 0)->max('sorting') + 1;
                $menuId = DB::table('cms_menus')->insertGetId([
                    'name' => $name,
                    'icon' => $icon,
                    'path' => $controller . 'GetIndex',
                    'type' => 'Route',
                    'is_active' => 1,
                    'sorting' => $menuSort,
                    'parent_id' => 0,
                    'created_at' => now(),
                ]);
                DB::table('cms_menus_privileges')->insert([
                    'id_cms_menus' => $menuId,
                    'id_cms_privileges' => Inermin::myPrivilegeId(),
                ]);
            }

            // Grant privilege roles for all existing privileges
            $allPrivileges = DB::table('cms_privileges')->get();
            foreach ($allPrivileges as $p) {
                DB::table('cms_privileges_roles')->updateOrInsert(
                    [
                        'id_cms_moduls' => $id,
                        'id_cms_privileges' => $p->id,
                    ],
                    [
                        'is_visible' => 1,
                        'is_create' => 1,
                        'is_read' => 1,
                        'is_edit' => 1,
                        'is_delete' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        } else {
            DB::table('cms_moduls')->where('id', $id)->update([
                'name' => $name,
                'table_name' => $table_name,
                'icon' => $icon,
                'path' => $path,
                'updated_at' => now(),
            ]);
        }

        if ($module_type === 'custom') {
            return redirect(Inermin::adminPath('modules/step4/' . $id));
        }

        return redirect(Inermin::adminPath('modules/step2/' . $id));
    }

    public function getStep2($id)
    {
        $row = DB::table('cms_moduls')->where('id', $id)->first();
        if (!$row) return redirect(Inermin::adminPath('modules'));

        $data = [];
        $data['page_title'] = 'Module Generator - Step 2 (Table Display Columns)';
        $data['step'] = 2;
        $data['id'] = $id;
        $data['row'] = $row;
        $data['columns'] = Inermin::getTableColumns($row->table_name);
        $data['tables'] = Inermin::listTables();

        return Inertia::render('Inermin/Modules/Wizard', $data);
    }

    public function postStep3()
    {
        $id = Request::input('id');
        $row = DB::table('cms_moduls')->where('id', $id)->first();
        $cols = Request::input('columns', []);

        $controllerPath = app_path('Http/Controllers/' . $row->controller . '.php');
        if (file_exists($controllerPath)) {
            $code = file_get_contents($controllerPath);

            $colPhp = "\$this->col = [\n";
            foreach ($cols as $c) {
                if (!empty($c['label']) && !empty($c['name'])) {
                    $colPhp .= "            ['label' => '" . addslashes($c['label']) . "', 'name' => '" . addslashes($c['name']) . "'";
                    if (!empty($c['image'])) $colPhp .= ", 'image' => true";
                    $colPhp .= "],\n";
                }
            }
            $colPhp .= "        ];";

            $code = preg_replace('/\$this->col\s*=\s*\[.*?\];/s', $colPhp, $code);
            file_put_contents($controllerPath, $code);
        }

        return redirect(Inermin::adminPath('modules/step3/' . $id));
    }

    public function getStep3($id)
    {
        $row = DB::table('cms_moduls')->where('id', $id)->first();
        if (!$row) return redirect(Inermin::adminPath('modules'));

        $data = [];
        $data['page_title'] = 'Module Generator - Step 3 (Form Fields Configuration)';
        $data['step'] = 3;
        $data['id'] = $id;
        $data['row'] = $row;
        $data['columns'] = Inermin::getTableColumns($row->table_name);

        return Inertia::render('Inermin/Modules/Wizard', $data);
    }

    public function postStep4()
    {
        $id = Request::input('id');
        $row = DB::table('cms_moduls')->where('id', $id)->first();
        $forms = Request::input('forms', []);

        $controllerPath = app_path('Http/Controllers/' . $row->controller . '.php');
        if (file_exists($controllerPath)) {
            $code = file_get_contents($controllerPath);

            $formPhp = "\$this->form = [\n";
            foreach ($forms as $f) {
                if (!empty($f['label']) && !empty($f['name'])) {
                    $formPhp .= "            ['label' => '" . addslashes($f['label']) . "', 'name' => '" . addslashes($f['name']) . "'";
                    $formPhp .= ", 'type' => '" . ($f['type'] ?? 'text') . "'";
                    if (!empty($f['required'])) $formPhp .= ", 'required' => true";
                    if (!empty($f['help'])) $formPhp .= ", 'help' => '" . addslashes($f['help']) . "'";
                    $formPhp .= "],\n";
                }
            }
            $formPhp .= "        ];";

            $code = preg_replace('/\$this->form\s*=\s*\[.*?\];/s', $formPhp, $code);
            file_put_contents($controllerPath, $code);
        }

        return redirect(Inermin::adminPath('modules/step4/' . $id));
    }

    public function getStep4($id)
    {
        $row = DB::table('cms_moduls')->where('id', $id)->first();
        if (!$row) return redirect(Inermin::adminPath('modules'));

        $data = [];
        $data['page_title'] = 'Module Generator - Step 4 (Module Privileges & Finish)';
        $data['step'] = 4;
        $data['id'] = $id;
        $data['row'] = $row;
        $data['privileges'] = DB::table('cms_privileges')->get();

        return Inertia::render('Inermin/Modules/Wizard', $data);
    }

    public function postFinish()
    {
        $id = Request::input('id');
        $privs = Request::input('privileges', []);

        foreach ($privs as $privId => $role) {
            DB::table('cms_privileges_roles')->updateOrInsert(
                ['id_cms_moduls' => $id, 'id_cms_privileges' => $privId],
                [
                    'is_visible' => !empty($role['is_visible']) ? 1 : 0,
                    'is_create' => !empty($role['is_create']) ? 1 : 0,
                    'is_read' => !empty($role['is_read']) ? 1 : 0,
                    'is_edit' => !empty($role['is_edit']) ? 1 : 0,
                    'is_delete' => !empty($role['is_delete']) ? 1 : 0,
                ]
            );
        }

        return redirect(Inermin::adminPath('modules'))->with('success', 'Module created successfully!');
    }

    private function generateControllerStub($className, $tableName, $moduleName)
    {
        return '<?php

namespace App\Http\Controllers;

use Tokalink\Inermin\controllers\InerminController;

class ' . $className . ' extends InerminController
{
    public function cbInit()
    {
        $this->table = "' . $tableName . '";
        $this->primary_key = "id";
        $this->title_field = "name";

        $this->col = [];

        $this->form = [];
    }
}
';
    }

    private function generateCustomControllerStub($className, $studlyPath, $moduleName)
    {
        return '<?php

namespace App\Http\Controllers;

use Tokalink\Inermin\controllers\InerminController;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ' . $className . ' extends InerminController
{
    public function cbInit()
    {
        $this->table = "";
        $this->primary_key = "id";
        $this->title_field = "name";
    }

    public function getIndex(Request $request = null)
    {
        return Inertia::render(\'Inermin/' . $studlyPath . '/Index\', [
            \'page_title\' => \'' . addslashes($moduleName) . '\',
        ]);
    }
}
';
    }

    private function generateCustomVueViewStub($studlyPath, $moduleName)
    {
        return '<script setup>
import { ref, computed } from \'vue\'
import { Head, Link, usePage } from \'@inertiajs/vue3\'
import InerminAppLayout from \'@inermin/InerminAppLayout.vue\'

const props = defineProps({
  page_title: {
    type: String,
    default: \'' . addslashes($moduleName) . '\'
  },
  data: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()
const currentPath = computed(() => page.url.split(\'?\')[0])
</script>

<template>
  <Head :title="page_title" />

  <InerminAppLayout>
    <div class="max-w-6xl mx-auto space-y-6 font-sans antialiased">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-stone-400">
            <Link :href="currentPath.replace(/\/add|\/edit\/.*/, \'\')" class="hover:text-stone-200 transition">
              Module List
            </Link>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-[rgb(var(--accent-rgb))] font-bold">' . addslashes($moduleName) . '</span>
          </div>

          <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 dark:text-white mt-1">
            {{ page_title }}
          </h1>
          <p class="text-xs text-stone-400 font-medium mt-0.5">
            Custom Vue page component scaffolded for Inermin admin panel
          </p>
        </div>

        <Link
          :href="currentPath.replace(/\/add|\/edit\/.*/, \'\')"
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
          <span>' . addslashes($moduleName) . ' Content</span>
        </h2>
        
        <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
          Start developing your custom content inside this Vue component.
        </p>
      </div>

    </div>
  </InerminAppLayout>
</template>
';
    }
}
