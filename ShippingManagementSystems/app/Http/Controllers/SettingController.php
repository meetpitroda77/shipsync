<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|unique:settings,key',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $setting = Setting::create([
            'key' => $request->key,
            'value' => $request->value
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting added successfully',
            'data' => $setting
        ], 201);
    }

    public function update(Request $request, Setting $setting)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $setting->update(['value' => $request->value]);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting
        ]);
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }
}
