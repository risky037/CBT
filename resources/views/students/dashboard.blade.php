<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ujian Siswa - CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 font-sans">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-bold">CBT</div>
            <div>
                <h1 class="font-bold text-slate-800 text-sm">Portal Ujian Siswa</h1>
                <p class="text-xs text-slate-500">{{ auth()->user()->name }} â€¢ {{ auth()->user()->kelas }}</p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                Keluar
            </button>
        </form>
    </header>

    <main class="max-w-4xl mx-auto p-6 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">{{ session('info') }}</div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-1">Daftar Ujian Tersedia ({{ auth()->user()->kelas }})</h2>
            <p class="text-xs text-slate-500 mb-6">Masukkan kode token dari pengawas untuk memulai pengerjaan ujian.</p>

            <div class="space-y-4">
                @forelse($exams as $exam)
                    @php $session = $sessions->get($exam->id); @endphp
                    <div class="p-5 border border-slate-200 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-md">
                                {{ $exam->subject->name }}
                            </span>
                            <h3 class="font-bold text-slate-800 text-base mt-2">{{ $exam->title }}</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Durasi: <strong>{{ $exam->duration }} Menit</strong> â€¢ Jumlah Soal: <strong>{{ $exam->questions->count() }}</strong> â€¢ Maks. Pelanggaran: <strong>{{ $exam->max_violations }}x</strong>
                            </p>
                        </div>

                        <div>
                            @if($session && $session->status === 'finished')
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl">
                                        Selesai â€¢ Skor Sementara: {{ $session->total_score }}
                                    </span>
                                </div>
                            @elseif($session && $session->status === 'blocked')
                                <span class="inline-block px-3 py-1.5 bg-red-100 text-red-700 text-xs font-bold rounded-xl">
                                    Terkunci (Pelanggaran)
                                </span>
                            @elseif($session && $session->status === 'ongoing')
                                <a href="{{ route('student.sessions.take', $session->id) }}" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-sm inline-block">
                                    Lanjutkan Ujian â†’
                                </a>
                            @else
                                <form action="{{ route('student.exams.start', $exam->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="text" name="token" placeholder="TOKEN" maxlength="6" required
                                        class="w-28 px-3 py-2 border border-slate-300 rounded-xl text-sm font-mono uppercase text-center tracking-widest focus:ring-2 focus:ring-indigo-500">
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm">
                                        Mulai
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-500 text-sm">
                        Belum ada jadwal ujian aktif untuk kelas Anda saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</body>
</html>
