@extends('layouts.admin')
@section('title', 'Tambah Soal Baru')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm max-w-4xl" x-data="{ type: 'pilihan_ganda' }">
    <form action="{{ route('admin.questions.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mata Pelajaran</label>
                <select name="subject_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                <input type="text" name="kelas" value="Kls 7" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bab / Materi</label>
                <input type="text" name="bab" placeholder="Misal: Pancasila" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Soal</label>
                <select name="type" x-model="type" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm font-medium text-indigo-700 bg-indigo-50/50">
                    <option value="pilihan_ganda">Pilihan Ganda</option>
                    <option value="menjodohkan">Menjodohkan</option>
                    <option value="isian_singkat">Isian Singkat</option>
                    <option value="uraian">Uraian (Esai)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tingkat Kesulitan</label>
                <select name="difficulty" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    <option value="mudah">Mudah</option>
                    <option value="sedang" selected>Sedang</option>
                    <option value="sulit">Sulit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bobot Skor</label>
                <input type="number" name="score" value="10" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Pertanyaan / Isi Soal</label>
            <textarea name="content" rows="4" required class="w-full px-4 py-2 border border-slate-300 rounded-xl text-sm" placeholder="Tuliskan soal di sini..."></textarea>
        </div>

        <!-- Form Khusus Pilihan Ganda -->
        <div x-show="type === 'pilihan_ganda'" class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
            <h3 class="font-semibold text-sm text-slate-700">Opsi Pilihan Ganda & Kunci Jawaban</h3>
            @foreach(['A', 'B', 'C', 'D'] as $index => $huruf)
                <div class="flex items-center gap-3">
                    <input type="radio" name="pg_correct" value="{{ $index }}" {{ $index === 0 ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                    <span class="font-bold text-sm w-5">{{ $huruf }}.</span>
                    <input type="text" name="pg_options[]" placeholder="Isi pilihan {{ $huruf }}" class="flex-1 px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
                </div>
            @endforeach
            <p class="text-xs text-slate-500">*Pilih lingkaran radio di sebelah kiri untuk menandai kunci jawaban yang benar.</p>
        </div>

        <!-- Form Khusus Menjodohkan -->
        <div x-show="type === 'menjodohkan'" class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
            <h3 class="font-semibold text-sm text-slate-700">Pasangan Pernyataan & Jawaban (Akan diacak otomatis saat ujian)</h3>
            @for($i = 0; $i < 3; $i++)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" name="match_left[]" placeholder="Pernyataan Kiri {{ $i+1 }}" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
                    <input type="text" name="match_right[]" placeholder="Pasangan Benar {{ $i+1 }}" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
                </div>
            @endfor
        </div>

        <!-- Form Khusus Isian Singkat -->
        <div x-show="type === 'isian_singkat'" class="p-4 bg-slate-50 rounded-xl border border-slate-200">
            <label class="block font-semibold text-sm text-slate-700 mb-1">Kunci Jawaban Isian Singkat</label>
            <input type="text" name="short_answer" placeholder="Contoh: 5, lima (pisahkan dengan koma jika ada alternatif jawaban)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>

        <!-- Info Khusus Uraian -->
        <div x-show="type === 'uraian'" class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
            Soal tipe <strong>Uraian</strong> akan dikoreksi dan diberikan skor secara manual oleh Guru melalui menu <strong>Hasil</strong> setelah ujian selesai.
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-sm">Simpan Soal</button>
            <a href="{{ route('admin.questions.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
@endsection