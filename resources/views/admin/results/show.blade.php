@extends('layouts.admin')

@section('title', 'Lembar Jawaban Siswa')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Nama Siswa & Tombol Bukti Pengawasan -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.results.index', ['exam_id' => $session->exam_id]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                &larr; Kembali ke Laporan Hasil
            </a>
            <h1 class="text-2xl font-bold text-slate-800 mt-1">{{ $session->student->name ?? 'Siswa' }}</h1>
            <p class="text-sm text-slate-500">
                {{ $session->exam->title }} &bull; Total Skor: <strong class="text-indigo-600">{{ $session->total_score ?? 0 }}</strong>
            </p>
        </div>

        <!-- Tombol Bukti Pengawasan & Pelanggaran -->
        <button type="button" onclick="document.getElementById('proctorModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold border transition shadow-sm
                {{ $session->violation_count > 0 ? 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
            <svg class="w-4 h-4 {{ $session->violation_count > 0 ? 'text-rose-600' : 'text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <span>Bukti Pengawasan ({{ isset($proctorLogs) ? $proctorLogs->count() : 0 }} Foto)</span>
            @if($session->violation_count > 0)
                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold">
                    {{ $session->violation_count }} Pelanggaran
                </span>
            @endif
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.results.grade', $session) }}" method="POST" class="space-y-4">
        @csrf
        @foreach($session->answers as $index => $ans)
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase text-slate-400">Soal #{{ $index + 1 }} ({{ str_replace('_', ' ', $ans->question->type) }})</span>
                    <span class="text-xs font-semibold text-slate-500">Bobot Maks: {{ $ans->question->score }} poin</span>
                </div>

                <div class="text-slate-800 font-medium mb-3">{!! $ans->question->content !!}</div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-sm mb-3">
                    <div class="text-xs text-slate-400 mb-1">Jawaban Siswa:</div>
                    <div class="font-semibold text-slate-800">
                        {{ is_array($ans->answer) ? json_encode($ans->answer) : ($ans->answer ?? '(Tidak dijawab)') }}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div class="text-xs text-slate-500">
                        Kunci: <strong class="text-emerald-600">{{ is_array($ans->question->correct_answer) ? json_encode($ans->question->correct_answer) : ($ans->question->correct_answer ?? '-') }}</strong>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Skor Diperoleh:</label>
                        <input type="number" step="0.5" min="0" max="{{ $ans->question->score }}"
                               name="scores[{{ $ans->id }}]" value="{{ $ans->score_obtained ?? 0 }}"
                               class="w-24 rounded-lg border-slate-300 text-sm font-bold text-right">
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Simpan & Hitung Ulang Total Skor
            </button>
        </div>
    </form>
</div>

<!-- Modal Bukti Kamera Pengawas & Pelanggaran -->
<div id="proctorModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Log Kamera Pengawas & Pelanggaran</h3>
                <p class="text-xs text-slate-500">Peserta: {{ $session->student->name ?? 'Siswa' }} &bull; {{ $session->exam->title }}</p>
            </div>
            <button type="button" onclick="document.getElementById('proctorModal').classList.add('hidden')"
                    class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center font-bold">
                &times;
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-6">
            <!-- Bagian 1: Catatan Pelanggaran Layar -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                    Riwayat Pelanggaran Layar ({{ $session->violations->count() }}x)
                </h4>
                @if($session->violations->isEmpty())
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                        Siswa menyelesaikan ujian tanpa pelanggaran pindah tab atau keluar layar.
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($session->violations as $v)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs">
                                <span class="font-medium text-rose-800">{{ $v->description }}</span>
                                <span class="font-mono text-rose-600 font-semibold shrink-0 ml-3">
                                    {{ $v->created_at->format('H:i:s') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Bagian 2: Galeri Foto Wajah Siswa -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
                    Rekaman Kamera Pengawas ({{ isset($proctorLogs) ? $proctorLogs->count() : 0 }} Tangkapan)
                </h4>
                @if(!isset($proctorLogs) || $proctorLogs->isEmpty())
                    <div class="p-8 rounded-2xl border border-dashed border-slate-300 text-center text-xs text-slate-500">
                        Belum ada foto kamera yang terekam pada sesi ujian ini.
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
                        @foreach($proctorLogs as $log)
                            <div class="group border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 shadow-sm">
                                <a href="{{ asset('storage/' . $log->image_path) }}" target="_blank" title="Klik untuk perbesar foto">
                                    <img src="{{ asset('storage/' . $log->image_path) }}" alt="Webcam Siswa"
                                         class="w-full h-36 object-cover group-hover:scale-105 transition duration-200">
                                </a>
                                <div class="px-3 py-2 bg-white border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Waktu</span>
                                    <span class="font-mono font-bold text-slate-700">{{ $log->created_at->format('H:i:s') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection