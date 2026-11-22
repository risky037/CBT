@extends('layouts.admin')
@section('title', 'Manajemen Ujian')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-800">Daftar Paket Ujian</h3>
            <p class="text-xs text-slate-500">Kelola jadwal, token masuk siswa, dan komposisi soal ujian</p>
        </div>
        <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition">
            + Buat Ujian
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($exams as $exam)
            <div class="p-5 border border-slate-200 rounded-2xl hover:border-indigo-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-md {{ $exam->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $exam->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <div class="flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-lg">
                            <span class="text-xs text-indigo-600">Token:</span>
                            <span class="font-mono font-bold text-sm text-indigo-800 tracking-wider">{{ $exam->token }}</span>
                        </div>
                    </div>

                    <h4 class="font-bold text-slate-800 text-base mb-1">{{ $exam->title }}</h4>
                    <p class="text-xs text-slate-500 mb-4">{{ $exam->subject->name }} • {{ $exam->kelas }} • {{ $exam->duration }} Menit</p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="text-slate-600 font-medium">{{ $exam->questions->count() }} Soal Terpasang</span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.exams.show', $exam->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Kelola Soal & Token</a>
                        <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Hapus ujian ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 text-slate-500 text-sm">
                Belum ada ujian yang dibuat. Klik <strong>+ Buat Ujian</strong> untuk memulai.
            </div>
        @endforelse
    </div>
</div>
@endsection