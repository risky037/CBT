<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Guru - SMA Idrisiyyah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800 font-sans">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between">
        <div>
            <!-- Header Logo -->
            <div class="p-5 flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-bold shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h1 class="font-bold text-slate-800 leading-tight">CBT Guru</h1>
                    <p class="text-xs text-slate-500">SMA Idrisiyyah</p>
                </div>
            </div>

            <!-- Profil Pengguna -->
            <div class="px-4 mb-4">
                <div class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="overflow-hidden">
                        <h2 class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</h2>
                        <p class="text-xs text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>

            <!-- Menu Navigasi -->
            <nav class="px-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-100">
                    <span>Beranda</span>
                </a>
                <a href="{{ route('admin.questions.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('admin.questions.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>Bank Soal</span>
                </a>
                <a href="{{ route('admin.exams.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('admin.exams.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
               <span>Ujian</span>
               </a>
                
                <a href="{{ route('admin.monitor.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('admin.monitor.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>Pantau</span>
                </a>
     <a href="{{ route('admin.results.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.results.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
    Hasil
</a>

<a href="{{ route('admin.students.index') }}"
   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
    Siswa & Kelas
</a>
            </nav>
        </div>

        <!-- Tombol Keluar -->
        <div class="p-4 border-t border-slate-200">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 transition">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- Konten Utama -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8">
            <h2 class="text-lg font-bold text-slate-800">@yield('title', 'Bank Soal')</h2>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>