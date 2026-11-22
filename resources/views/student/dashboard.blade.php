<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ujian Siswa - CBT</title>
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 font-sans">

    <!-- Navbar Siswa -->
    <nav class="bg-white border-b border-slate-200 px-4 sm:px-6 py-4">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0 shadow-sm">
                    CBT
                </div>
                <div class="overflow-hidden">
                    <h1 class="font-bold text-slate-800 leading-tight text-sm sm:text-base truncate">Portal Ujian Siswa</h1>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->name }} • Kelas {{ auth()->user()->kelas }}</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="px-3 sm:px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Keluar
                </button>
            </form>
        </div>
    </nav>

    <!-- Konten Utama -->
    <main class="max-w-5xl mx-auto p-4 sm:p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800">Daftar Ujian Tersedia</h2>
            <p class="text-sm text-slate-500">Pilih paket ujian aktif di bawah ini dan masukkan token dari pengawas/guru.</p>
        </div>

        @if(session('info'))
            <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($exams as $exam)
                @php $session = $sessions->get($exam->id); @endphp
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 truncate">
                                {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                            </span>
                            <span class="text-xs font-medium text-slate-500 shrink-0">
                                {{ $exam->duration }} Menit • {{ $exam->questions_count }} Soal
                            </span>
                        </div>

                        <h3 class="text-base sm:text-lg font-bold text-slate-800 mt-2">{{ $exam->title }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Target Kelas: <strong>{{ $exam->kelas }}</strong></p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        @if($session && $session->status === 'finished')
                            <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-2 bg-emerald-50 border border-emerald-200 px-4 py-3 rounded-xl">
                                <div>
                                    <span class="text-xs font-bold text-emerald-700 block">SELESAI DIKERJAKAN</span>
                                    <span class="text-xs text-emerald-600">Selesai pukul {{ \Carbon\Carbon::parse($session->finished_at)->format('H:i') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-500 block">Skor</span>
                                    <span class="text-lg font-extrabold text-emerald-700">{{ $session->total_score ?? 0 }}</span>
                                </div>
                            </div>
                        @elseif($session && $session->status === 'blocked')
                            <div class="bg-rose-50 border border-rose-200 px-4 py-3 rounded-xl text-xs font-semibold text-rose-700">
                                SESI TERKUNCI (Pelanggaran Maksimal). Hubungi pengawas untuk membuka kunci.
                            </div>
                        @elseif($session && $session->status === 'ongoing')
                            <a href="{{ route('student.sessions.take', $session->id) }}"
                               class="block w-full text-center py-2.5 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition">
                                Lanjutkan Ujian &rarr;
                            </a>
                        @else
                            <form action="{{ route('student.exams.start', $exam->id) }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="token" placeholder="Masukkan Token Ujian" required
                                       class="flex-1 min-w-0 rounded-xl border border-slate-300 px-3 py-2 text-sm uppercase font-mono tracking-wider focus:ring-2 focus:ring-indigo-500">
                                <button type="submit"
                                        class="px-4 sm:px-5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shrink-0">
                                    Mulai
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-10 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
                    Belum ada jadwal ujian aktif untuk kelas <strong>{{ auth()->user()->kelas }}</strong> saat ini.
                </div>
            @endforelse
        </div>
    </main>

    <!-- Jendela Pop-up Notifikasi Selesai Ujian -->
    @if(session('exam_finished'))
        @php $summary = session('exam_finished'); @endphp
        <div id="finishModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 text-center shadow-2xl border border-slate-100">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-slate-800">Ujian Telah Selesai!</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Terima kasih <strong class="text-slate-700">{{ $summary['student_name'] }}</strong>, jawaban Anda untuk
                    <strong class="text-slate-700">{{ $summary['exam_title'] }}</strong> ({{ $summary['subject_name'] }}) telah tersimpan.
                </p>

                <div class="my-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Waktu Selesai</span>
                        <span class="font-semibold text-slate-800">{{ $summary['finished_at'] }} WIB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Soal Terjawab</span>
                        <span class="font-semibold text-slate-800">{{ $summary['answered'] }} dari {{ $summary['total_questions'] }} soal</span>
                    </div>
                    <div class="flex justify-between pt-2.5 border-t border-slate-200 items-center">
                        <span class="text-slate-600 font-medium">
                            {{ $summary['has_essay'] ? 'Skor Sementara (Non-Esai)' : 'Total Skor Diperoleh' }}
                        </span>
                        <span class="text-lg font-extrabold text-indigo-600">
                            {{ $summary['total_score'] }} <span class="text-xs font-normal text-slate-400">/ {{ $summary['max_score'] }}</span>
                        </span>
                    </div>
                </div>

                @if($summary['has_essay'])
                    <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl p-2.5 mb-4">
                        Paket ini memiliki soal uraian/esai. Nilai akhir akan diperbarui setelah dikoreksi oleh guru.
                    </p>
                @endif

                <button type="button" onclick="document.getElementById('finishModal').remove()"
                        class="w-full py-3 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 transition">
                    Tutup & Kembali ke Beranda
                </button>
            </div>
        </div>
    @endif

</body>
</html>
