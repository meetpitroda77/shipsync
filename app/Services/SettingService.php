<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public static function get($key, $default = null)
    {
        $value = Setting::where('key', $key)->value('value');

        return $value !== null ? $value : $default;
    }

    public static function set($key, $value)
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}