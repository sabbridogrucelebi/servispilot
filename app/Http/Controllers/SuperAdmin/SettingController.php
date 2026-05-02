<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalSetting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = GlobalSetting::all()->groupBy('group');
        
        // Ensure groups exist
        if (!$settings->has('general')) $settings->put('general', collect());
        if (!$settings->has('smtp')) $settings->put('smtp', collect());
        if (!$settings->has('bank')) $settings->put('bank', collect());

        return view('super-admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $group = in_array($key, ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption', 'smtp_from_address', 'smtp_from_name']) ? 'smtp' : (in_array($key, ['bank_name', 'bank_iban', 'bank_account_holder']) ? 'bank' : 'general');
            GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        // Cache temzilemek faydalı olabilir
        \Illuminate\Support\Facades\Cache::forget('global_settings');

        return redirect()->route('super-admin.settings.index')->with('success', 'Sistem ayarları başarıyla güncellendi.');
    }
}
