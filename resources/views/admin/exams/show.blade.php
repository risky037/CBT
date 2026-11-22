@extends('layouts.admin')
@section('title', 'Detail & Soal Ujian')

@section('content')
<div class="space-y-6">
    <!-- Kartu Info Token -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">{{ $exam->title }}</h3>
            <p class="text-sm text-slate-500">{{ $exam->subject->name }} • {{ $exam->kelas }} • Durasi {{ $exam->duration }} Menit • Maks. Pelanggaran: {{ $exam->max_violations }}x</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-xl text-center">
                <span class="block text-[11px] uppercase tracking-wider text-indigo-600 font-semibold">Token Ujian</span>
                <span class="font-mono text-xl font-bold text-indigo-900 tracking-widest">{{ $exam->token }}</span>
            </div>
            <form action="{{ route('admin.exams.refresh-token', $exam->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 rounded-xl text-xs font-medium text-slate-700">
                    Acak Token
                </button>
            </form>
        </div>
    </div>

    <!-- Form Pilih Soal dari Bank Soal -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ route('admin.exams.sync-questions', $exam->id) }}" method="POST">
            @csrf
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h4 class="font-bold text-slate-800">Pilih Soal dari Bank Soal ({{ $exam->subject->name }})</h4>
                    <p class="text-xs text-slate-500">Centang soal yang ingin dimasukkan ke dalam paket ujian ini</p>
                </div>
                <button type="submit" class="self-start sm:self-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition">
                    Simpan Komposisi Soal
                </button>
            </div>

            <div class="space-y-2.5 mt-4">
                @forelse($availableQuestions as $q)
                    <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-xl hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="question_ids[]" value="{{ $q->id }}"
                            {{ in_array($q->id, $selectedQuestionIds) ? 'checked' : '' }}
                            class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 break-words">{{ strip_tags($q->content) }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded capitalize">{{ str_replace('_', ' ', $q->type) }}</span>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded">{{ $q->kelas }}</span>
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded">Skor: {{ $q->score }}</span>
                            </div>
                        </div>
                    </label>
                @empty
                    <p class="text-sm text-slate-500 py-6 text-center">Belum ada soal untuk mata pelajaran ini di Bank Soal.</p>
                @endforelse
            </div>
        </form>
    </div>
</div>
@endsection