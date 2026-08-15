<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InerminUsersController extends InerminController
{
    public function cbInit()
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        $this->table = 'cms_users';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->orderby = 'id,desc';

        $privileges = [];
        try {
            $privList = DB::table('cms_privileges')->get();
            foreach ($privList as $p) {
                $privileges[$p->id] = $p->name;
            }
        } catch (\Exception $e) {}

        $this->col = [
            ['label' => 'Photo', 'name' => 'photo', 'image' => true],
            ['label' => 'Name', 'name' => 'name'],
            ['label' => 'Email', 'name' => 'email'],
            ['label' => 'Status', 'name' => 'status'],
        ];

        $this->form = [
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Photo', 'name' => 'photo', 'type' => 'upload', 'required' => false],
            ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'required' => true],
            ['label' => 'Password', 'name' => 'password', 'type' => 'password', 'help' => 'Leave blank if not changing'],
            ['label' => 'Privilege', 'name' => 'id_cms_privileges', 'type' => 'select', 'dataenum' => $privileges, 'required' => true],
        ];
    }

    public function hook_before_add(&$postdata)
    {
        if (!empty($postdata['password'])) {
            $postdata['password'] = Hash::make($postdata['password']);
        }
    }

    public function hook_before_edit(&$postdata, $id)
    {
        if (!empty($postdata['password'])) {
            $postdata['password'] = Hash::make($postdata['password']);
        } else {
            unset($postdata['password']);
        }
    }
}

