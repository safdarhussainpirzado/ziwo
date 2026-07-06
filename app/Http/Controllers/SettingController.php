<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $this->authorize('system.settings.view');
        $settings = SystemSetting::all()->groupBy('group_name');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorize('system.settings.update');
        $payload = $request->except(['_token', '_method', 'group']);
        $group = $request->input('group', 'general');
        
        foreach ($payload as $key => $value) {
            SystemSetting::set($key, $value, $group);
        }

        return redirect()->back()->with('success', "System configuration for [{$group}] updated successfully.");
    }
}
