<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} - Ujian CBT</title>
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 select-none">

    <!-- Header Ujian Sticky -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-6 py-3.5 shadow-sm">
        <div class="max-w-5xl mx-auto flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-bold text-slate-800 text-base md:text-lg">{{ $exam->title }}</h1>
                <p class="text-xs text-slate-500">{{ $exam->subject->name ?? '' }} • {{ auth()->user()->name }}</p>
            </div>

            <div class="flex items-center gap-3">
                <span id="saveStatus" class="text-xs text-emerald-600 font-medium hidden md:inline">Tersimpan otomatis</span>

                <div class="px-4 py-1.5 rounded-xl bg-slate-900 text-white font-mono text-sm font-bold" id="timerDisplay">
                    --:--:--
                </div>

                <button type="button" onclick="openConfirmModal()"
                        class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs md:text-sm font-semibold hover:bg-emerald-700 transition">
                    Selesai Ujian
                </button>
            </div>
        </div>
    </header>

    <!-- Daftar Soal -->
    <main class="max-w-4xl mx-auto p-6 space-y-6">
        @foreach($questions as $index => $q)
            @php
                $existing = $savedAnswers->get($q->id)?->answer;
                $options = is_array($q->options) ? $q->options : (json_decode($q->options, true) ?? []);
            @endphp
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm" id="soal-{{ $index + 1 }}">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold">
                        Soal No. {{ $index + 1 }}
                    </span>
                    <span class="text-xs text-slate-400 uppercase font-semibold">{{ str_replace('_', ' ', $q->type) }}</span>
                </div>

                <div class="text-slate-800 text-base leading-relaxed mb-5">
                    {!! $q->content !!}
                </div>

                @if($q->type === 'pilihan_ganda')
                    <div class="space-y-2.5">
                        @foreach($options as $key => $label)
                            @php $optVal = is_string($key) ? $key : $label; @endphp
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-indigo-50/40 cursor-pointer transition">
                                <input type="radio" name="q_{{ $q->id }}" value="{{ $optVal }}"
                                       {{ (string)$existing === (string)$optVal ? 'checked' : '' }}
                                       onchange="saveAnswer({{ $q->id }}, this.value)"
                                       class="mt-1 text-indigo-600">
                                <span class="text-sm text-slate-700">
                                    @if(is_string($key)) <strong class="mr-1">{{ $key }}.</strong> @endif
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                @elseif($q->type === 'isian_singkat')
                    <input type="text" value="{{ is_string($existing) ? $existing : '' }}"
                           placeholder="Ketik jawaban singkat Anda di sini..."
                           onblur="saveAnswer({{ $q->id }}, this.value)"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">

                @elseif($q->type === 'menjodohkan')
                    @php
                        $leftItems = $options['left'] ?? array_keys($q->correct_answer ?? []);
                        $rightItems = $options['right'] ?? array_values($q->correct_answer ?? []);
                        $existingMap = is_array($existing) ? $existing : [];
                    @endphp
                    <div class="space-y-3" data-match-question="{{ $q->id }}">
                        @foreach($leftItems as $left)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-sm font-medium text-slate-700">{{ $left }}</span>
                                <select data-left="{{ $left }}" onchange="saveMatchAnswer({{ $q->id }})"
                                        class="rounded-xl border border-slate-300 text-sm px-3 py-1.5 bg-white">
                                    <option value="">-- Pilih Pasangan --</option>
                                    @foreach($rightItems as $right)
                                        <option value="{{ $right }}" {{ ($existingMap[$left] ?? '') === $right ? 'selected' : '' }}>
                                            {{ $right }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                @else
                    <textarea rows="4" placeholder="Tuliskan jawaban uraian Anda di sini..."
                              onblur="saveAnswer({{ $q->id }}, this.value)"
                              class="w-full rounded-xl border border-slate-300 p-3.5 text-sm">{{ is_string($existing) ? $existing : '' }}</textarea>
                @endif
            </div>
        @endforeach

        <div class="flex justify-end pb-10">
            <button type="button" onclick="openConfirmModal()"
                    class="px-6 py-3 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 shadow-sm">
                Kumpulkan & Selesaikan Ujian
            </button>
        </div>
    </main>

    <!-- Form Submit Selesai (Tersembunyi) -->
    <form id="finishExamForm" action="{{ route('student.sessions.finish', $session->id) }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Jendela Konfirmasi Selesai Ujian -->
    <div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl">
            <h2 class="text-lg font-bold text-slate-800">Akhiri & Kumpulkan Ujian?</h2>
            <p class="text-sm text-slate-500 mt-2">
                Pastikan seluruh soal sudah diperiksa. Setelah dikumpulkan, Anda tidak dapat mengubah jawaban kembali.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeConfirmModal()"
                        class="flex-1 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Periksa Lagi
                </button>
                <button type="button" onclick="submitExamNow()"
                        class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                    Ya, Selesai
                </button>
            </div>
        </div>
    </div>

    <!-- Peringatan Pelanggaran Anti-Cheat -->
    <div id="violationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-rose-950/80 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl border-2 border-rose-500">
            <h2 class="text-lg font-bold text-rose-600">Peringatan Pelanggaran!</h2>
            <p id="violationText" class="text-sm text-slate-600 mt-2">
                Anda terdeteksi meninggalkan layar ujian. Pelanggaran dicatat oleh sistem.
            </p>
            <button type="button" onclick="document.getElementById('violationModal').classList.add('hidden')"
                    class="mt-5 w-full py-2.5 rounded-xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700">
                Kembali ke Ujian
            </button>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let remaining = {{ (int) $remainingSeconds }};
        let isSubmitting = false;

        // Timer Hitung Mundur
        function updateTimer() {
            if (remaining <= 0 && !isSubmitting) {
                isSubmitting = true;
                document.getElementById('finishExamForm').submit();
                return;
            }
            const h = String(Math.floor(remaining / 3600)).padStart(2, '0');
            const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            document.getElementById('timerDisplay').textContent = `${h}:${m}:${s}`;
            remaining--;
        }
        updateTimer();
        setInterval(updateTimer, 1000);

        // Simpan Jawaban Otomatis
        function saveAnswer(questionId, value) {
            const statusEl = document.getElementById('saveStatus');
            if (statusEl) statusEl.textContent = 'Menyimpan...';

            fetch("{{ route('student.sessions.answer', $session->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ question_id: questionId, answer: value })
            }).then(() => {
                if (statusEl) statusEl.textContent = 'Tersimpan otomatis';
            });
        }

        function saveMatchAnswer(questionId) {
            const container = document.querySelector(`[data-match-question="${questionId}"]`);
            const result = {};
            container.querySelectorAll('select').forEach(sel => {
                if (sel.value) result[sel.dataset.left] = sel.value;
            });
            saveAnswer(questionId, result);
        }

        // Modal Konfirmasi Selesai
        function openConfirmModal() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }
        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }
        function submitExamNow() {
            isSubmitting = true;
            document.getElementById('finishExamForm').submit();
        }

        // Deteksi Pindah Tab / Keluar Layar (Anti-Cheat)
        document.addEventListener('visibilitychange', function () {
            if (document.hidden && !isSubmitting) {
                fetch("{{ route('student.sessions.violation', $session->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        type: 'tab_switch',
                        description: 'Siswa berpindah tab atau meminimalkan jendela ujian.'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'blocked') {
                        window.location.href = "{{ route('student.dashboard') }}";
                    } else if (data.status === 'warning') {
                        document.getElementById('violationText').textContent =
                            `Peringatan ke-${data.violation_count} dari ${data.max_violations}. Jangan meninggalkan halaman ujian!`;
                        document.getElementById('violationModal').classList.remove('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>
