@extends('layouts.admin')

@section('title', 'Lembar Jawaban Siswa')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.results.index', ['exam_id' => $session->exam_id]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; Kembali ke Laporan Hasil</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-1">{{ $session->student->name ?? 'Siswa' }}</h1>
        <p class="text-sm text-slate-500">{{ $session->exam->title }} • Total Skor: <strong class="text-indigo-600">{{ $session->total_score ?? 0 }}</strong></p>
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
@endsection