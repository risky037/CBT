@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header & Tombol Generator AI -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Bank Soal Terstruktur</h1>
            <p class="text-sm text-slate-500">Kelola soal secara terpisah per jenis untuk menjamin akurasi penilaian otomatis.</p>
        </div>

        <button type="button" onclick="document.getElementById('aiModal').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-semibold shadow-sm hover:opacity-95 flex items-center gap-2">
            <span>✨ Buat Soal Otomatis (AI)</span>
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Tab Navigasi Terpisah per Jenis Soal -->
    @php
        $tabs = [
            'pilihan_ganda' => 'Pilihan Ganda',
            'isian_singkat' => 'Isian Singkat',
            'menjodohkan'   => 'Menjodohkan',
            'esai'          => 'Uraian / Esai',
        ];
    @endphp
    <div class="flex flex-wrap gap-2 mb-6 border-b border-slate-200 pb-3">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.questions.index', ['type' => $key]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-2 {{ $activeType === $key ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <span>{{ $label }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $activeType === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Form Tambah Soal Manual Sesuai Tipe Aktif -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8">
        <h2 class="text-base font-bold text-slate-800 mb-4">
            Tambah Soal {{ $tabs[$activeType] }} Baru
        </h2>

        <form action="{{ route('admin.questions.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="type" value="{{ $activeType }}">

            <!-- BAGIAN INPUT YANG DIPERBAIKI (Ditambahkan Input Kelas di Sini) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mata Pelajaran</label>
                    <select name="subject_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                    <input type="text" name="kelas" required placeholder="Contoh: 10, 11, atau 12"
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Bobot Nilai</label>
                    <input type="number" name="score" value="10" min="1" required
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pertanyaan / Instruksi Soal</label>
                <textarea name="content" rows="3" required placeholder="Tuliskan butir soal di sini..."
                          class="w-full rounded-xl border border-slate-300 p-3 text-sm"></textarea>
            </div>

            <!-- Input Spesifik Berdasarkan Jenis Soal -->
            @if($activeType === 'pilihan_ganda')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                Opsi {{ $opt }} {{ $opt === 'E' ? '(Opsional)' : '' }}
                            </label>
                            <input type="text" name="options[{{ $opt }}]" {{ $opt !== 'E' ? 'required' : '' }}
                                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-bold text-emerald-700 mb-1">Kunci Jawaban Benar</label>
                        <select name="correct_option" required class="w-full rounded-lg border border-emerald-400 px-3 py-1.5 text-sm bg-emerald-50 font-bold">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>

            @elseif($activeType === 'isian_singkat')
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <label class="block text-xs font-bold text-emerald-700 mb-1">
                        Kunci Jawaban Singkat (Pisahkan variasi jawaban benar dengan tanda koma)
                    </label>
                    <input type="text" name="short_answers" required
                           placeholder="Contoh: Soekarno, Ir. Soekarno, Bung Karno"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                    <p class="text-xs text-slate-500 mt-1">Penilaian otomatis mengabaikan huruf besar/kecil dan spasi berlebih.</p>
                </div>

            @elseif($activeType === 'menjodohkan')
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2.5">
                    <label class="block text-xs font-bold text-emerald-700">
                        Pasangan Premis (Kiri) & Jawaban Tepat (Kanan) — Sistem otomatis mengacak kolom kanan saat ujian
                    </label>
                    
                    <div id="pasangan-container" class="space-y-2.5">
                        @for($i = 0; $i < 4; $i++)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <input type="text" name="match_left[]" placeholder="Premis Kiri {{ $i + 1 }}" {{ $i < 2 ? 'required' : '' }}
                                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                                <input type="text" name="match_right[]" placeholder="Pasangan Benar {{ $i + 1 }}" {{ $i < 2 ? 'required' : '' }}
                                       class="rounded-lg border border-emerald-300 px-3 py-1.5 text-sm bg-white">
                            </div>
                        @endfor
                    </div>
                    
                    <!-- Tombol Tambah Pasangan -->
                    <button type="button" onclick="tambahPasangan()" class="mt-3 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition">
                        + Tambah Pasangan
                    </button>
                </div>

            @elseif($activeType === 'esai')
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Pedoman Penskoran / Rubrik Jawaban (Referensi Guru saat Koreksi Manual)
                    </label>
                    <textarea name="rubric" rows="2" placeholder="Tuliskan poin-poin utama jawaban yang diharapkan..."
                              class="w-full rounded-lg border border-slate-300 p-2.5 text-sm bg-white"></textarea>
                </div>
            @endif

            <div class="flex justify-end mt-4">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    Simpan Soal {{ $tabs[$activeType] }}
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Soal Tersimpan -->
    <div class="space-y-3">
        @forelse($questions as $q)
            <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-start justify-between gap-4 shadow-sm">
                <div class="space-y-2 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold">
                            {{ $q->subject->name ?? '-' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold">
                            Kelas: {{ $q->kelas ?? '-' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold">
                            Bobot: {{ $q->score }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-800 font-medium break-words">{!! $q->content !!}</p>

                    @if($q->type === 'pilihan_ganda' && is_array($q->options))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-xs text-slate-600 pt-1">
                            @foreach($q->options as $k => $v)
                                <div class="{{ in_array($k, $q->correct_answer ?? []) ? 'text-emerald-700 font-bold' : '' }} break-words">
                                    {{ $k }}. {{ $v }} {{ in_array($k, $q->correct_answer ?? []) ? '✓' : '' }}
                                </div>
                            @endforeach
                        </div>
                    @elseif($q->type === 'isian_singkat')
                        <p class="text-xs text-emerald-700 font-semibold break-words">
                            Kunci Valid: {{ implode(' | ', $q->correct_answer ?? []) }}
                        </p>
                    @elseif($q->type === 'menjodohkan' && is_array($q->correct_answer))
                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            @foreach($q->correct_answer as $kiri => $kanan)
                                <div class="break-words">• <strong>{{ $kiri }}</strong> &rarr; <span class="text-emerald-700 font-semibold">{{ $kanan }}</span></div>
                            @endforeach
                        </div>
                    @elseif($q->type === 'esai' && !empty($q->correct_answer[0]))
                        <p class="text-xs text-slate-500 italic break-words">Rubrik: {{ $q->correct_answer[0] }}</p>
                    @endif
                </div>

                <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')" class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition">
                        Hapus
                    </button>
                </form>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-sm text-slate-400">
                Belum ada soal pada kategori <strong>{{ $tabs[$activeType] }}</strong>.
            </div>
        @endforelse

        <div class="mt-4">{{ $questions->links() }}</div>
    </div>
</div>

<!-- Modal Generator Soal Otomatis AI -->
<div id="aiModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">✨ Generator Soal Otomatis AI</h3>
            <button type="button" onclick="document.getElementById('aiModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.questions.generate-ai') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- BAGIAN INPUT KELAS JUGA DITAMBAHKAN PADA MODAL AI -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mata Pelajaran</label>
                    <select name="subject_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                    <input type="text" name="kelas" required placeholder="Contoh: 10"
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Soal</label>
                    <select name="type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach($tabs as $key => $label)
                            <option value="{{ $key }}" {{ $activeType === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Topik / Materi Pembelajaran</label>
                <input type="text" name="topic" required placeholder="Contoh: Hukum Newton, Sistem Pencernaan, Teks Eksposisi"
                       class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tingkat Kesulitan</label>
                    <select name="difficulty" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="mudah">Mudah</option>
                        <option value="sedang" selected>Sedang</option>
                        <option value="sulit">Sulit (HOTS)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah Soal</label>
                    <input type="number" name="count" value="5" min="1" max="10" required
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Bobot / Soal</label>
                    <input type="number" name="score" value="10" min="1" required
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('aiModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" onclick="this.innerText='Sedang Membuat Soal...'"
                        class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                    Generate & Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let jumlahPasangan = 4;
    function tambahPasangan() {
        jumlahPasangan++;
        const container = document.getElementById('pasangan-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 md:grid-cols-2 gap-2 mt-2';
        div.innerHTML = `
            <input type="text" name="match_left[]" placeholder="Premis Kiri ${jumlahPasangan}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
            <input type="text" name="match_right[]" placeholder="Pasangan Benar ${jumlahPasangan}" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-sm bg-white">
        `;
        container.appendChild(div);
    }
</script>
@endpush