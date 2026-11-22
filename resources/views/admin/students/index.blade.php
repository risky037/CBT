@extends('layouts.admin')

@section('title', 'Siswa & Kelas')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Manajemen Siswa & Kelas</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola akun login peserta ujian dan pengelompokan kelas.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.students.template') }}" class="px-4 py-2 rounded-xl bg-slate-100 border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                Unduh Template CSV
            </a>

            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                @csrf
                <input type="file" name="file" accept=".csv" required class="text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 max-w-[200px] sm:max-w-none">
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shrink-0">
                    Impor CSV
                </button>
            </form>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white p-5 rounded-2xl border border-slate-200 mb-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-700 mb-3">Tambah Akun Siswa Baru</h2>
        <form action="{{ route('admin.students.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Nama Lengkap Siswa" required class="rounded-xl border-slate-300 text-sm px-3 py-2 border">
            <input type="email" name="email" placeholder="Email / Username Login" required class="rounded-xl border-slate-300 text-sm px-3 py-2 border">
            <input type="text" name="kelas" placeholder="Kelas (cth: Kls 7)" required class="rounded-xl border-slate-300 text-sm px-3 py-2 border">
            <input type="text" name="password" value="password" placeholder="Password" required class="rounded-xl border-slate-300 text-sm px-3 py-2 border">
            <button type="submit" class="sm:col-span-2 lg:col-span-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 py-2 shadow-sm transition">
                + Simpan Siswa
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-3 justify-between">
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="rounded-xl border-slate-300 text-sm px-3 py-1.5 border">
                <select name="kelas" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-sm px-3 py-1.5 border">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls }}" {{ request('kelas') === $cls ? 'selected' : '' }}>{{ $cls }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200">Filter</button>
            </form>
            <div class="text-xs text-slate-400 self-center">Format CSV: Nama, Email, Kelas, Password</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[620px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold whitespace-nowrap">
                    <tr>
                        <th class="px-5 py-3.5">Nama Siswa</th>
                        <th class="px-5 py-3.5">Email Login</th>
                        <th class="px-5 py-3.5">Kelas</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 font-semibold text-slate-800 whitespace-nowrap">{{ $student->name }}</td>
                            <td class="px-5 py-3.5 text-slate-600 font-mono text-xs whitespace-nowrap">{{ $student->email }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <form action="{{ route('admin.students.update', $student) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $student->name }}">
                                    <input type="text" name="kelas" value="{{ $student->kelas }}" class="w-24 rounded-lg border-slate-300 text-xs py-1 px-2 border">
                                    <button type="submit" class="text-xs text-indigo-600 font-semibold hover:underline">Simpan</button>
                                </form>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada data siswa terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $students->withQueryString()->links() }}
    </div>
</div>
@endsection