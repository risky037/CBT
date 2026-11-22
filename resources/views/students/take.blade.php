<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian: {{ $exam->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen select-none">

    <!-- Header Ujian -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <h1 class="font-bold text-slate-800 text-base">{{ $exam->title }}</h1>
                <p class="text-xs text-slate-500">{{ $exam->subject->name ?? '-' }} • {{ auth()->user()->name }}</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                    Pelanggaran: <span id="violation-count">{{ $session->violation_count }}</span> / {{ $exam->max_violations ?? 3 }}x
                </div>
                <div class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white font-mono font-bold text-sm" id="timer">
                    --:--:--
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Area Soal -->
        <div class="lg:col-span-3 space-y-4">
            @forelse($questions as $index => $q)
                @php
                    $saved = $savedAnswers->get($q->id)?->answer ?? null;
                @endphp
                <div class="question-card bg-white rounded-2xl border border-slate-200 p-6 {{ $index === 0 ? '' : 'hidden' }}"
                     id="q-card-{{ $index }}">

                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                        <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs">
                            Soal No. {{ $index + 1 }} dari {{ $questions->count() }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400 uppercase">
                            {{ str_replace('_', ' ', $q->type) }}
                        </span>
                    </div>

                    <!-- Isi Pertanyaan -->
                    <div class="text-slate-800 text-base leading-relaxed whitespace-pre-line mb-6">
                        {!! $q->content !!}
                    </div>

                    <!-- Pilihan Ganda -->
                    @if($q->type === 'pilihan_ganda' && is_array($q->options))
                        <div class="space-y-2.5">
                            @foreach($q->options as $optKey => $optText)
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-indigo-50/50 hover:border-indigo-300 cursor-pointer transition">
                                    <input type="radio"
                                           name="answer_{{ $q->id }}"
                                           value="{{ $optKey }}"
                                           {{ (string)$saved === (string)$optKey ? 'checked' : '' }}
                                           onchange="saveAnswer({{ $q->id }}, '{{ $optKey }}', {{ $index }})"
                                           class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-bold text-sm text-slate-700 uppercase">
                                        {{ is_numeric($optKey) ? chr(65 + $optKey) : $optKey }}.
                                    </span>
                                    <span class="text-sm text-slate-700">{{ $optText }}</span>
                                </label>
                            @endforeach
                        </div>

                    <!-- Isian Singkat -->
                    @elseif($q->type === 'isian_singkat')
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">Ketik Jawaban Singkat Anda:</label>
                            <input type="text"
                                   value="{{ is_string($saved) ? $saved : '' }}"
                                   onblur="saveAnswer({{ $q->id }}, this.value, {{ $index }})"
                                   placeholder="Tulis jawaban di sini..."
                                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                    <!-- Uraian -->
                    @elseif($q->type === 'uraian')
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">Tulis Jawaban Uraian Anda:</label>
                            <textarea rows="5"
                                      onblur="saveAnswer({{ $q->id }}, this.value, {{ $index }})"
                                      placeholder="Tulis penjelasan lengkap Anda di sini..."
                                      class="w-full rounded-xl border border-slate-300 p-4 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ is_string($saved) ? $saved : '' }}</textarea>
                        </div>

                    <!-- Menjodohkan -->
                    @elseif($q->type === 'menjodohkan' && is_array($q->options))
                        @php
                            $kiri = $q->options['kiri'] ?? [];
                            $kanan = $q->options['kanan'] ?? [];
                            $savedMap = is_array($saved) ? $saved : [];
                        @endphp
                        <div class="space-y-3">
                            @foreach($kiri as $itemKiri)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <div class="text-sm font-medium text-slate-800">{{ $itemKiri }}</div>
                                    <select onchange="saveMatching({{ $q->id }}, {{ $index }})"
                                            data-kiri="{{ $itemKiri }}"
                                            class="match-select-{{ $q->id }} rounded-xl border-slate-300 text-sm">
                                        <option value="">-- Pilih Pasangan --</option>
                                        @foreach($kanan as $itemKanan)
                                            <option value="{{ $itemKanan }}" {{ ($savedMap[$itemKiri] ?? '') === $itemKanan ? 'selected' : '' }}>
                                                {{ $itemKanan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Navigasi Sebelumnya / Selanjutnya -->
                    <div class="flex items-center justify-between mt-8 pt-4 border-t border-slate-100">
                        <button type="button"
                                onclick="showQuestion({{ $index - 1 }})"
                                {{ $index === 0 ? 'disabled' : '' }}
                                class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600 disabled:opacity-40 hover:bg-slate-50">
                            &larr; Sebelumnya
                        </button>

                        @if($index < $questions->count() - 1)
                            <button type="button"
                                    onclick="showQuestion({{ $index + 1 }})"
                                    class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                                Selanjutnya &rarr;
                            </button>
                        @else
                            <form action="{{ route('student.sessions.finish', $session->id) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan dan mengumpulkan ujian ini?')">
                                @csrf
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                    Selesai & Kumpulkan Ujian
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">
                    <p class="text-slate-500 font-semibold">Soal belum dipasangkan pada paket ujian ini.</p>
                    <a href="{{ route('student.dashboard') }}" class="inline-block mt-3 text-xs text-indigo-600 font-semibold hover:underline">
                        &larr; Kembali ke Dashboard
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Navigasi Nomor Soal -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 h-fit">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Navigasi Nomor</h3>
            <div class="grid grid-cols-5 gap-2">
                @foreach($questions as $index => $q)
                    @php $hasAnswer = !empty($savedAnswers->get($q->id)?->answer); @endphp
                    <button type="button"
                            id="nav-btn-{{ $index }}"
                            onclick="showQuestion({{ $index }})"
                            class="h-10 rounded-xl font-bold text-xs border transition
                                   {{ $hasAnswer ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-indigo-400' }}
                                   {{ $index === 0 ? 'ring-2 ring-indigo-500' : '' }}">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>

            <form action="{{ route('student.sessions.finish', $session->id) }}" method="POST" class="mt-6 pt-4 border-t border-slate-100"
                  onsubmit="return confirm('Kumpulkan semua jawaban sekarang?')">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">
                    Kumpulkan Ujian
                </button>
            </form>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const totalQuestions = {{ $questions->count() }};

        function showQuestion(index) {
            if (index < 0 || index >= totalQuestions) return;
            document.querySelectorAll('.question-card').forEach(el => el.classList.add('hidden'));
            document.getElementById('q-card-' + index).classList.remove('hidden');

            for (let i = 0; i < totalQuestions; i++) {
                document.getElementById('nav-btn-' + i).classList.remove('ring-2', 'ring-indigo-500');
            }
            document.getElementById('nav-btn-' + index).classList.add('ring-2', 'ring-indigo-500');
        }

        function markAnswered(index) {
            const btn = document.getElementById('nav-btn-' + index);
            btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200');
            btn.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
        }

        function saveAnswer(questionId, answerValue, index) {
            if (answerValue !== null && answerValue !== '') {
                markAnswered(index);
            }
            fetch("{{ route('student.sessions.answer', $session->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ question_id: questionId, answer: answerValue })
            });
        }

        function saveMatching(questionId, index) {
            const selects = document.querySelectorAll('.match-select-' + questionId);
            const map = {};
            selects.forEach(sel => {
                if (sel.value) map[sel.dataset.kiri] = sel.value;
            });
            saveAnswer(questionId, map, index);
        }

        // Timer berbasis remainingSeconds dari Controller
        let remaining = {{ (int) $remainingSeconds }};
        const timerEl = document.getElementById('timer');

        function updateTimer() {
            if (remaining <= 0) {
                timerEl.innerText = '00:00:00';
                window.location.href = "{{ route('student.dashboard') }}";
                return;
            }
            const h = String(Math.floor(remaining / 3600)).padStart(2, '0');
            const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            timerEl.innerText = `${h}:${m}:${s}`;
            remaining--;
        }
        updateTimer();
        setInterval(updateTimer, 1000);

        // Anti-Cheat: Deteksi Pindah Tab
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                fetch("{{ route('student.sessions.violation', $session->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        type: 'tab_switch',
                        description: 'Siswa berpindah tab atau keluar jendela browser.'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'blocked') {
                        alert('Sesi ujian Anda terkunci karena melebihi batas pelanggaran.');
                        window.location.href = "{{ route('student.dashboard') }}";
                    } else if (data.status === 'warning') {
                        document.getElementById('violation-count').innerText = data.violation_count;
                        alert(`PERINGATAN! Dilarang berpindah tab (${data.violation_count}/${data.max_violations} pelanggaran).`);
                    }
                });
            }
        });
    </script>
</body>
</html>