<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Validator;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return view('pages.shipment.settings', compact('settings'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|unique:settings,key',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        Setting::create([
            'key' => $request->key,
            'value' => $request->value
        ]);

        session()->flash('success', 'Request successfully');

        return response()->json([
            'success' => true,
            'redirect' => route('getsetting'),
            'message' => 'Setting added'
        ]);
    }
    public function update(Request $request, Setting $setting)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $setting->update(['value' => $request->value]);

        return response()->json([
            'success' => true,
            'redirect' => route('getsetting'),
        ]);
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();
        session()->flash('success', 'Delete successfully');
        return response()->json([
            'success' => true,
            'redirect' => route('getsetting'),
            'message' => 'Setting deleted'
        ]);
    }

}
