<?php

namespace Tokalink\Inermin\controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Tokalink\Inermin\helpers\Inermin;

class InerminSettingsController extends Controller
{
    public function getIndex()
    {
        Inermin::insertLog("Viewed System Settings");

        // Ensure default login_style and login_background_image exist in cms_settings
        $hasLoginStyle = DB::table('cms_settings')->where('name', 'login_style')->exists();
        if (!$hasLoginStyle) {
            DB::table('cms_settings')->insert([
                'name' => 'login_style',
                'label' => 'Login Page Theme Style',
                'group_setting' => 'Application Setting',
                'content_input_type' => 'select',
                'dataenum' => 'glassmorphism:Aether Glassmorphism (Centered Floating Card),split-screen:Split Screen (Left Branding / Right Form),minimal-clean:Executive Minimalist (Clean Compact),gradient-glow:Cyber Mesh Gradient (Animated Radial)',
                'helper' => 'Choose visual layout theme style for the login page',
                'content' => 'glassmorphism',
                'created_at' => now(),
            ]);
        }

        $hasLoginBg = DB::table('cms_settings')->where('name', 'login_background_image')->exists();
        if (!$hasLoginBg) {
            DB::table('cms_settings')->insert([
                'name' => 'login_background_image',
                'label' => 'Custom Login Background Image',
                'group_setting' => 'Application Setting',
                'content_input_type' => 'upload_image',
                'helper' => 'Upload a custom background wallpaper image for login page (Optional)',
                'content' => '',
                'created_at' => now(),
            ]);
        }

        $allSettings = DB::table('cms_settings')->orderBy('group_setting', 'asc')->orderBy('id', 'asc')->get();

        $groups = $allSettings->pluck('group_setting')->unique()->filter()->values();
        if ($groups->isEmpty()) {
            $groups = collect(['General Setting']);
        }

        $groupedSettings = [];
        foreach ($groups as $group) {
            $groupedSettings[$group] = $allSettings->where('group_setting', $group)->values();
        }

        return Inertia::render('Inermin/Settings', [
            'page_title' => 'System Settings',
            'groups' => $groups,
            'settings' => $groupedSettings,
        ]);
    }

    public function postSave(Request $request)
    {
        $allSettings = DB::table('cms_settings')->get();

        foreach ($allSettings as $setting) {
            $name = $setting->name;

            // Handle file/image uploads for upload_image or upload_file
            if (in_array($setting->content_input_type, ['upload_image', 'upload_file', 'file', 'image'])) {
                if ($request->hasFile($name)) {
                    $file = $request->file($name);
                    $path = $file->store('uploads/' . date('Y-m'), 'public');
                    $publicUrl = asset('storage/' . $path);

                    DB::table('cms_settings')
                        ->where('id', $setting->id)
                        ->update([
                            'content' => $publicUrl,
                            'updated_at' => now()
                        ]);
                }
                continue;
            }

            // Handle regular inputs
            if ($request->has($name)) {
                $val = $request->input($name);
                DB::table('cms_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'content' => is_array($val) ? implode(',', $val) : $val,
                        'updated_at' => now()
                    ]);
            }
        }

        Inermin::insertLog("Updated System Settings");

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    public function postAddSave(Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_dash|unique:cms_settings,name',
            'label' => 'required|string',
            'group_setting' => 'required|string',
            'content_input_type' => 'required|string',
        ]);

        DB::table('cms_settings')->insert([
            'name' => strtolower($request->input('name')),
            'label' => $request->input('label'),
            'group_setting' => $request->input('group_setting'),
            'content_input_type' => $request->input('content_input_type'),
            'dataenum' => $request->input('dataenum'),
            'helper' => $request->input('helper'),
            'content' => $request->input('content') ?: '',
            'created_at' => now(),
        ]);

        Inermin::insertLog("Added new setting: {$request->input('name')}");

        return redirect()->back()->with('success', 'New setting key added successfully!');
    }

    public function getDeleteSetting($id)
    {
        DB::table('cms_settings')->where('id', $id)->delete();
        Inermin::insertLog("Deleted setting #{$id}");

        return redirect()->back()->with('success', 'Setting key deleted successfully!');
    }
}
