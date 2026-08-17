<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Http\Request;
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

    public function getIndex(?Request $request = null)
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        return parent::getIndex($request);
    }

    public function getAdd()
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        return parent::getAdd();
    }

    public function postAddSave()
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        return parent::postAddSave();
    }

    public function getEdit($id = null)
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        return parent::getEdit($id);
    }

    public function postEditSave($id = null)
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        return parent::postEditSave($id);
    }

    public function getDelete($id = null)
    {
        if (!\Tokalink\Inermin\helpers\Inermin::isSuperadmin()) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath())->with('error', 'Access Denied!');
        }

        return parent::getDelete($id);
    }

    public function getProfile()
    {
        $adminId = \Illuminate\Support\Facades\Session::get('admin_id');
        if (!$adminId) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath('login'));
        }

        $user = DB::table('cms_users')->where('id', $adminId)->first();
        if (!$user) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath());
        }

        $privilege = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();

        return \Inertia\Inertia::render('Inermin/Profile/Edit', [
            'page_title' => 'Profile & Security Settings',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo' => \Tokalink\Inermin\helpers\Inermin::myPhoto(),
                'privilege_name' => $privilege ? $privilege->name : 'Administrator',
                'created_at' => $user->created_at ? date('d M Y', strtotime($user->created_at)) : 'N/A',
            ],
            'action_url' => \Tokalink\Inermin\helpers\Inermin::adminPath('profile'),
            'tab' => request('tab', 'profile'),
        ]);
    }

    public function postSaveProfile(\Illuminate\Http\Request $request)
    {
        $adminId = \Illuminate\Support\Facades\Session::get('admin_id');
        if (!$adminId) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath('login'));
        }

        $user = DB::table('cms_users')->where('id', $adminId)->first();
        if (!$user) {
            return redirect(\Tokalink\Inermin\helpers\Inermin::adminPath());
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:cms_users,email,' . $adminId,
        ]);

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'updated_at' => now(),
        ];

        // Handle Profile Photo Avatar Upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('uploads/' . date('Y-m'), 'public');
            $data['photo'] = 'storage/' . $path;
            \Illuminate\Support\Facades\Session::put('admin_photo', asset('storage/' . $path));
        }

        // Handle Password Change if requested
        if ($request->filled('new_password')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6',
                'new_password_confirmation' => 'required|same:new_password',
            ]);

            if (!Hash::check($request->input('current_password'), $user->password)) {
                return redirect()->back()->with('error', 'Current password is incorrect!');
            }

            $data['password'] = Hash::make($request->input('new_password'));
        }

        DB::table('cms_users')->where('id', $adminId)->update($data);

        \Illuminate\Support\Facades\Session::put('admin_name', $data['name']);

        \Tokalink\Inermin\helpers\Inermin::insertLog('Updated profile settings');

        return redirect()->back()->with('success', 'Profile & security settings updated successfully!');
    }
}

