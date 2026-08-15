<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Tokalink\Inermin\helpers\Inermin;

class InerminPrivilegesController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_privileges';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->orderby = 'id,asc';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Privilege Name', 'name' => 'name'],
            ['label' => 'Is Superadmin', 'name' => 'is_superadmin'],
        ];

        $this->form = [
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Is Superadmin', 'name' => 'is_superadmin', 'type' => 'select', 'dataenum' => ['1' => 'Yes', '0' => 'No']],
            ['label' => 'Theme Color', 'name' => 'theme_color', 'type' => 'text'],
        ];
    }

    public function getIndex()
    {
        $this->cbInit();

        $privileges = DB::table('cms_privileges')->orderBy('id', 'asc')->get();
        foreach ($privileges as &$p) {
            $p->users_count = DB::table('cms_users')->where('id_cms_privileges', $p->id)->count();
        }

        $stats = [
            'total_roles' => count($privileges),
            'total_users' => DB::table('cms_users')->count(),
            'total_modules' => DB::table('cms_moduls')->where('is_protected', 0)->count(),
            'recent_logs' => DB::table('cms_logs')->count(),
        ];

        return Inertia::render('Inermin/Privileges/Index', [
            'page_title' => 'Privileges & Roles — Aether Console',
            'privileges' => $privileges,
            'stats' => $stats,
        ]);
    }

    public function getAdd()
    {
        $modules = DB::table('cms_moduls')
            ->where('is_protected', 0)
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();

        $rolesMap = [];
        foreach ($modules as $m) {
            $rolesMap[$m->id] = [
                'is_visible' => 1,
                'is_create' => 1,
                'is_read' => 1,
                'is_edit' => 1,
                'is_delete' => 1,
            ];
        }

        return Inertia::render('Inermin/Privileges/Form', [
            'page_title' => 'Add Privilege',
            'row' => null,
            'modules' => $modules,
            'roles' => $rolesMap,
            'action_url' => Inermin::adminPath('privileges/add'),
        ]);
    }

    public function postAddSave()
    {
        $request = request();
        $name = $request->input('name');
        $is_superadmin = (int) $request->input('is_superadmin', 0);
        $theme_color = $request->input('theme_color', 'theme-indigo');

        $privilegeId = DB::table('cms_privileges')->insertGetId([
            'name' => $name,
            'is_superadmin' => $is_superadmin,
            'theme_color' => $theme_color,
            'created_at' => now(),
        ]);

        $this->savePrivilegeRoles($privilegeId, $request->input('roles', []));

        Inermin::insertLog('Added new privilege: ' . $name);

        return redirect(Inermin::adminPath('privileges'))->with('success', 'Privilege saved successfully!');
    }

    public function getEdit($id = null)
    {
        $id = $id ?: request('id');
        $row = DB::table('cms_privileges')->where('id', $id)->first();
        if (!$row)
            return redirect(Inermin::adminPath('privileges'));

        $modules = DB::table('cms_moduls')
            ->where('is_protected', 0)
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();

        $existingRoles = DB::table('cms_privileges_roles')
            ->where('id_cms_privileges', $id)
            ->get()
            ->keyBy('id_cms_moduls');

        $rolesMap = [];
        foreach ($modules as $m) {
            $role = $existingRoles->get($m->id);
            $rolesMap[$m->id] = [
                'is_visible' => $role ? (int) $role->is_visible : 0,
                'is_create' => $role ? (int) $role->is_create : 0,
                'is_read' => $role ? (int) $role->is_read : 0,
                'is_edit' => $role ? (int) $role->is_edit : 0,
                'is_delete' => $role ? (int) $role->is_delete : 0,
            ];
        }

        return Inertia::render('Inermin/Privileges/Form', [
            'page_title' => 'Edit Privilege',
            'row' => $row,
            'modules' => $modules,
            'roles' => $rolesMap,
            'action_url' => Inermin::adminPath('privileges/edit/' . $id),
        ]);
    }

    public function postEditSave($id = null)
    {
        $id = $id ?: request('id');
        $request = request();
        $name = $request->input('name');
        $is_superadmin = (int) $request->input('is_superadmin', 0);
        $theme_color = $request->input('theme_color', 'theme-indigo');

        DB::table('cms_privileges')->where('id', $id)->update([
            'name' => $name,
            'is_superadmin' => $is_superadmin,
            'theme_color' => $theme_color,
            'updated_at' => now(),
        ]);

        $this->savePrivilegeRoles($id, $request->input('roles', []));

        Inermin::insertLog('Updated privilege #' . $id);

        return redirect(Inermin::adminPath('privileges'))->with('success', 'Privilege updated successfully!');
    }

    private function savePrivilegeRoles($privilegeId, $rolesInput)
    {
        if (!is_array($rolesInput))
            return;

        foreach ($rolesInput as $modulId => $role) {
            DB::table('cms_privileges_roles')->updateOrInsert(
                [
                    'id_cms_privileges' => $privilegeId,
                    'id_cms_moduls' => $modulId,
                ],
                [
                    'is_visible' => !empty($role['is_visible']) ? 1 : 0,
                    'is_create' => !empty($role['is_create']) ? 1 : 0,
                    'is_read' => !empty($role['is_read']) ? 1 : 0,
                    'is_edit' => !empty($role['is_edit']) ? 1 : 0,
                    'is_delete' => !empty($role['is_delete']) ? 1 : 0,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
