<?php

namespace Tokalink\Inermin\controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Tokalink\Inermin\helpers\Inermin;

class InerminAuthController extends Controller
{
    public function getLogin()
    {
        if (Session::get('admin_id')) {
            return redirect()->to(Inermin::adminPath());
        }

        return Inertia::render('Inermin/Auth/Login', [
            'app_name' => config('inermin.APP_NAME', 'Inermin Admin'),
        ]);
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table(config('crudbooster.USER_TABLE', 'cms_users'))
            ->where('email', $request->email)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $priv = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();

            Session::put('admin_id', $user->id);
            Session::put('admin_is_superadmin', $priv ? $priv->is_superadmin : 0);
            Session::put('admin_name', $user->name);
            $photo = ($user->photo && !str_contains($user->photo, 'crudbooster')) ? $user->photo : asset('vendor/inermin/avatar.svg');
            Session::put('admin_photo', $photo);
            Session::put('admin_privileges', $user->id_cms_privileges);
            Session::put('admin_privileges_name', $priv ? $priv->name : 'Administrator');
            Session::put('admin_theme_color', $priv ? $priv->theme_color : 'theme-indigo');

            return redirect()->to(Inermin::adminPath());
        }

        return redirect()->back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function getLogout()
    {
        Session::forget(['admin_id', 'admin_is_superadmin', 'admin_name', 'admin_photo', 'admin_privileges', 'admin_privileges_name']);

        return redirect()->to(Inermin::adminPath('login'))->with('message', 'Logged out successfully');
    }
}
