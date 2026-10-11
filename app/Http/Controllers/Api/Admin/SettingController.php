<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Setting::orderBy('group')->get()->groupBy('group'));
    }

    public function update(Request $request)
    {
        $data = $request->validate(['settings' => 'required|array']);
        foreach ($data['settings'] as $key => $value) {
            Setting::set($key, is_array($value) ? json_encode($value) : $value);
        }
        ActivityLogger::log('Settings updated');
        return response()->json(['message' => 'Settings updated', 'settings' => Setting::orderBy('group')->get()->groupBy('group')]);
    }
}