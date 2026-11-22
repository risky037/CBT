@extends('layouts.admin')

@section('title', 'Pantau Ujian')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Pantau Pelaksanaan Ujian</h1>
            <p class="text-xs sm:text-sm text-slate-500">Status peserta, jumlah soal terjawab, dan pelanggaran diperbarui otomatis setiap 10 detik.</p>
        </div>

        <form method="GET" action="{{ route('admin.monitor.index') }}" class="w-full sm:w-auto">
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
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($selectedExam)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                <p class="text-xs text-slate-500">Token Masuk</p>
                <p class="text-xl font-mono font-bold text-indigo-600 mt-1">{{ $selectedExam->token }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                <p class="text-xs text-slate-500">Sedang Mengerjakan</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $sessions->where('status', 'ongoing')->count() }} Siswa</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                <p class="text-xs text-slate-500">Terblokir (Pelanggaran)</p>
                <p class="text-xl font-bold text-rose-600 mt-1">{{ $sessions->where('status', 'blocked')->count() }} Siswa</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                <p class="text-xs text-slate-500">Selesai Ujian</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $sessions->where('status', 'completed')->count() }} Siswa</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[640px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold whitespace-nowrap">
                        <tr>
                            <th class="px-5 py-3.5">Nama Siswa</th>
                            <th class="px-5 py-3.5">Progres Soal</th>
                            <th class="px-5 py-3.5">Pelanggaran</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi Pengawas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-800">{{ $session->student->name ?? 'Siswa' }}</div>
                                    <div class="text-xs text-slate-400">Mulai: {{ \Carbon\Carbon::parse($session->started_at)->format('H:i:s') }}</div>
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-700 whitespace-nowrap">
                                    {{ $session->answers_count }} / {{ $selectedExam->questions->count() }} terjawab
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $session->violation_count > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $session->violation_count }} / {{ $selectedExam->max_violations }}x
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($session->status === 'completed')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Selesai</span>
                                    @elseif($session->status === 'blocked')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Terblokir</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Mengerjakan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                                    @if($session->status === 'blocked')
                                        <form action="{{ route('admin.monitor.unlock', $session) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                                                Buka Blokir
                                            </button>
                                        </form>
                                    @endif
                                    @if($session->status !== 'completed')
                                        <form action="{{ route('admin.monitor.force-finish', $session) }}" method="POST" class="inline" onsubmit="return confirm('Paksa selesaikan ujian siswa ini sekarang?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-300">
                                                Paksa Selesai
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada siswa yang memulai paket ujian ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    setTimeout(() => { window.location.reload(); }, 10000);
</script>
@endsection