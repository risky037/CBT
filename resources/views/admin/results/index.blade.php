@extends('layouts.admin')

@section('title', 'Hasil Ujian')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Laporan Hasil & Penilaian</h1>
            <p class="text-xs sm:text-sm text-slate-500">Rekapitulasi nilai otomatis, koreksi uraian manual, dan unduh laporan Excel/CSV.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('admin.results.index') }}" class="w-full sm:w-auto">
                <select name="exam_id" onchange="this.form.submit()" class="w-full sm:w-auto rounded-xl border-slate-300 text-sm font-medium text-slate-700">
                    @forelse($exams as $exam)
                        <option value="{{ $exam->id }}" {{ $selectedExam?->id == $exam->id ? 'selected' : '' }}>
                            {{ $exam->title }} — {{ $exam->subject->name ?? '' }} ({{ $exam->kelas }})
                        </option>
                    @empty
                        <option value="">Belum ada paket ujian</option>
                    @endforelse
                </select>
            </form>

            @if($selectedExam)
                <a href="{{ route('admin.results.export', $selectedExam) }}" class="w-full sm:w-auto text-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 shadow-sm transition">
                    Unduh CSV
                </a>
            @endif
        </div>
    </div>

    @if($selectedExam)
        @php $maxPossible = $selectedExam->questions->sum('score'); @endphp
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[580px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold whitespace-nowrap">
                        <tr>
                            <th class="px-5 py-3.5">Peringkat & Nama Siswa</th>
                            <th class="px-5 py-3.5">Kelas</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Total Skor</th>
                            <th class="px-5 py-3.5 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sessions as $index => $session)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-block w-6 text-slate-400 font-bold">#{{ $index + 1 }}</span>
                                    <span class="font-semibold text-slate-800">{{ $session->student->name ?? 'Siswa' }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 whitespace-nowrap">{{ $session->student->kelas ?? '-' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ strtoupper($session->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="text-base font-bold text-indigo-600">{{ $session->total_score ?? 0 }}</span>
                                    <span class="text-xs text-slate-400">/ {{ $maxPossible }}</span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.results.show', $session) }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition">
                                        Koreksi / Lembar Jawaban
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada hasil ujian pada paket ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection