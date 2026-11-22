<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\AppSetting;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil data pengaturan, jika kosong buat bawaan otomatis
        $setting = AppSetting::firstOrCreate(
            ['id' => 1],
            ['app_name' => 'Aplikasi CBT', 'school_name' => 'Nama Sekolah', 'logo' => null]
        );

        return view('admin.settings.index', compact('setting'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password berhasil diperbarui!');
    }

    public function updateApp(Request $request)
    {
        $request->validate([
            'app_name'    => 'required|string',
            'school_name' => 'required|string',
            'logo'        => 'nullable|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        $setting = AppSetting::firstOrCreate(['id' => 1]);
        $setting->app_name = $request->app_name;
        $setting->school_name = $request->school_name;

        // Proses unggah logo baru jika ada
        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo); // Hapus logo lama
            }
            $setting->logo = $request->file('logo')->store('logos', 'public');
        }

        $setting->save();
        return back()->with('success', 'Pengaturan aplikasi dan logo berhasil diperbarui!');
    }

    public function resetPassword(Request $request)
    {
        $user = auth()->user();
        // Reset sandi menjadi 'password'
        $user->password = bcrypt('password'); 
        $user->save();

        return back()->with('success', 'Password berhasil direset menjadi: password');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        $user = auth()->user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profil akun berhasil diperbarui!');
    }
}