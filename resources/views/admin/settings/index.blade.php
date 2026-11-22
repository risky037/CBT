@extends('layouts.admin')

@section('title', 'Pengaturan Aplikasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Profil Akun (Ditambahkan di atas sini) -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Profil Akun
        </h3>
        <form action="{{ route('admin.settings.profile') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ auth()->user()->name }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ auth()->user()->email }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
            </div>

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                Simpan Profil
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Form Ganti Password -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    Ganti Password
                </h3>
                <form action="{{ route('admin.settings.password') }}" method="POST" id="passwordForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" required class="w-full border border-slate-300 rounded-lg py-2 pl-4 pr-10 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" onclick="togglePassword('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                👁️
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required class="w-full border border-slate-300 rounded-lg py-2 pl-4 pr-10 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                👁️
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full border border-slate-300 rounded-lg py-2 pl-4 pr-10 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                👁️
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl transition-colors mb-3">
                        Simpan Password
                    </button>
                </form>
            </div>

            <!-- Tombol Reset Password (Form Terpisah) -->
            <form action="{{ route('admin.settings.password.reset') }}" method="POST" onsubmit="return confirm('Yakin ingin mereset password menjadi default (password)?')">
                @csrf
                <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-semibold py-2 px-4 rounded-xl transition-colors text-sm">
                    Reset Password ke Default
                </button>
            </form>
        </div>

        <!-- Form Pengaturan Aplikasi & Logo -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Pengaturan & Logo Aplikasi
            </h3>
            <form action="{{ route('admin.settings.app') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $setting->app_name ?? '') }}" required class="w-full border border-slate-300 px-4 py-2 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Sekolah / Instansi</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $setting->school_name ?? '') }}" required class="w-full border border-slate-300 px-4 py-2 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Logo Saat Ini</label>
                    @if($setting && $setting->logo)
                        <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="h-16 mb-3 rounded border border-slate-200 p-1 bg-slate-50 object-contain">
                    @else
                        <span class="text-sm text-slate-500 block mb-3 italic">Belum ada logo khusus (menggunakan bawaan)</span>
                    @endif
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unggah Logo Baru (Kosongkan jika tidak diubah)</label>
                    <input type="file" name="logo" accept="image/*" class="w-full border border-slate-300 rounded-lg p-1.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                </div>
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl transition-colors">
                    Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Script untuk Toggle Lihat Sandi -->
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
</script>
@endsection