<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method', 'logo');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/img');
            // We want to store the relative path for asset() helper
            // But Storage::url() usually expects 'public/...' or just filename depending on config.
            // Let's store the filename and use Storage::url or asset('storage/...')
            // Actually, existing logo is in public/img/Logo.png.
            // Let's overwrite or store new one.
            
            // For simplicity, let's store the path relative to storage/app/public
            $filename = $request->file('logo')->hashName();
            $request->file('logo')->storeAs('public/img', $filename);
            
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'storage/img/' . $filename]);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Configuración actualizada con éxito.');
    }
}
