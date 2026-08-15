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
