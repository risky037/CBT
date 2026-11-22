@extends('layouts.admin')
@section('title', 'Buat Ujian Baru')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm max-w-3xl">
    <form action="{{ route('admin.exams.store') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Judul Ujian</label>
            <input type="text" name="title" required placeholder="Contoh: Penilaian Tengah Semester (PTS) Ganjil" class="w-full px-4 py-2 border border-slate-300 rounded-xl text-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mata Pelajaran</label>
                <select name="subject_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Target Kelas</label>
                <input type="text" name="kelas" value="Kls 7" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Durasi Pengerjaan (Menit)</label>
                <input type="number" name="duration" value="60" min="5" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Batas Maksimal Pelanggaran (Anti-Curang)</label>
                <input type="number" name="max_violations" value="3" min="1" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                <p class="text-xs text-slate-500 mt-1">Ujian otomatis terkunci jika siswa pindah tab melebihi batas ini.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Mulai</label>
                <input type="datetime-local" name="start_time" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Selesai</label>
                <input type="datetime-local" name="end_time" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 sm:gap-6 pt-2">
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                <input type="checkbox" name="is_active" checked class="rounded border-slate-300 text-indigo-600">
                <span>Aktifkan Ujian Sekarang</span>
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                <input type="checkbox" name="randomize_questions" checked class="rounded border-slate-300 text-indigo-600">
                <span>Acak Urutan Soal</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-slate-100">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition">Simpan & Pilih Soal</button>
            <a href="{{ route('admin.exams.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
@endsection