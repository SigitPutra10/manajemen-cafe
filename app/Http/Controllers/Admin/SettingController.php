<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $setting = Setting::query()->first() ?? new Setting([
            'cafe_name' => config('app.name', 'Nama Cafe'),
            'address' => '',
            'phone' => '',
        ]);

        return view('admin.settings.edit', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $address = trim((string) $request->input('address', ''));
        $phone = trim((string) $request->input('phone', ''));

        $request->merge([
            'cafe_name' => trim((string) $request->input('cafe_name', '')),
            'address' => $address === '' ? null : $address,
            'phone' => $phone === '' ? null : $phone,
        ]);

        $validated = $request->validate([
            'cafe_name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [
            'cafe_name.required' => 'Nama kafe wajib diisi.',
            'cafe_name.max' => 'Nama kafe maksimal 100 karakter.',
            'address.max' => 'Alamat kafe maksimal 500 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
        ]);

        $setting = Setting::query()->first() ?? new Setting();
        $setting->fill($validated);
        $setting->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan kafe berhasil disimpan.');
    }
}
