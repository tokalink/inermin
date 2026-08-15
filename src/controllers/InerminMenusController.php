<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Tokalink\Inermin\helpers\Inermin;
use Inertia\Inertia;

class InerminMenusController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_menus';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->orderby = 'sorting,asc';
    }

    public function getIndex(\Illuminate\Http\Request $request = null)
    {
        if (!Inermin::isSuperadmin()) {
            return redirect(Inermin::adminPath())->with('error', 'Access Denied!');
        }

        $data = [];
        $data['page_title'] = 'Menu Management';
        
        // Fetch all menus & tree structure
        $rawMenus = DB::table('cms_menus')->orderBy('sorting', 'asc')->get();
        $menuTree = [];
        foreach ($rawMenus as $m) {
            $m->privileges = DB::table('cms_menus_privileges')
                ->where('id_cms_menus', $m->id)
                ->pluck('id_cms_privileges')
                ->toArray();

            if ($m->parent_id == 0) {
                $m->children = [];
                $menuTree[$m->id] = $m;
            }
        }
        foreach ($rawMenus as $m) {
            if ($m->parent_id != 0 && isset($menuTree[$m->parent_id])) {
                $menuTree[$m->parent_id]->children[] = $m;
            }
        }

        $data['menus'] = array_values($menuTree);
        $data['all_menus'] = $rawMenus;
        $data['modules'] = DB::table('cms_moduls')->where('is_protected', 0)->orderBy('name', 'asc')->get();
        $data['privileges'] = DB::table('cms_privileges')->orderBy('name', 'asc')->get();
        $data['parent_menus'] = DB::table('cms_menus')->where('parent_id', 0)->orderBy('name', 'asc')->get();

        return Inertia::render('Inermin/Menus/Index', $data);
    }

    public function postSave()
    {
        $id = Request::input('id');
        $name = Request::input('name');
        $type = Request::input('type', 'Module');
        $icon = Request::input('icon', 'bi bi-grid');
        $path = Request::input('path');
        $module_id = Request::input('module_id');
        $parent_id = Request::input('parent_id', 0);
        $is_active = Request::input('is_active', 1);
        $privileges = Request::input('privileges', []);

        if ($type === 'Module' && $module_id) {
            $mod = DB::table('cms_moduls')->where('id', $module_id)->first();
            if ($mod) {
                $path = $mod->controller ? $mod->controller . 'GetIndex' : $mod->path;
            }
        }

        $menuData = [
            'name' => $name,
            'type' => $type,
            'icon' => $icon,
            'path' => $path,
            'parent_id' => $parent_id,
            'is_active' => $is_active ? 1 : 0,
            'updated_at' => now(),
        ];

        if (!$id) {
            $menuData['sorting'] = DB::table('cms_menus')->where('parent_id', $parent_id)->max('sorting') + 1;
            $menuData['created_at'] = now();
            $id = DB::table('cms_menus')->insertGetId($menuData);
        } else {
            DB::table('cms_menus')->where('id', $id)->update($menuData);
        }

        // Sync Menu Privileges
        DB::table('cms_menus_privileges')->where('id_cms_menus', $id)->delete();
        foreach ($privileges as $privId) {
            DB::table('cms_menus_privileges')->insert([
                'id_cms_menus' => $id,
                'id_cms_privileges' => $privId,
            ]);
        }

        return redirect(Inermin::adminPath('menus'))->with('success', 'Menu saved successfully!');
    }

    public function postSaveSorting()
    {
        $menus = Request::input('menus', []);
        foreach ($menus as $index => $m) {
            DB::table('cms_menus')->where('id', $m['id'])->update([
                'sorting' => $index + 1,
                'parent_id' => $m['parent_id'] ?? 0,
            ]);
        }
        return response()->json(['status' => true]);
    }

    public function getDelete($id = null)
    {
        $id = $id ?: request('id');
        DB::table('cms_menus')->where('id', $id)->delete();
        DB::table('cms_menus')->where('parent_id', $id)->delete();
        DB::table('cms_menus_privileges')->where('id_cms_menus', $id)->delete();

        return redirect(Inermin::adminPath('menus'))->with('success', 'Menu deleted successfully!');
    }
}

