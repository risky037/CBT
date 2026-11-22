<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $activeType = $request->get('type', 'pilihan_ganda');
        
        // Daftar mata pelajaran bawaan
        $defaultSubjects = [
            'Pendidikan Agama Islam',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'Ilmu Pengetahuan Alam (IPA)',
            'Ilmu Pengetahuan Sosial (IPS)',
            'Bahasa Inggris',
            'PJOK',
            'Informatika',
            'Seni Budaya',
            'Prakarya',
            'Bahasa Sunda'
        ];

        // Masukkan ke database jika belum ada
        foreach ($defaultSubjects as $subjectName) {
            Subject::firstOrCreate(['name' => $subjectName]);
        }

        // Ambil semua mata pelajaran dari database dan urutkan abjad
        $subjects = Subject::orderBy('name')->get();

        $questions = Question::with('subject')
            ->where('type', $activeType)
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pilihan_ganda' => Question::where('type', 'pilihan_ganda')->count(),
            'isian_singkat' => Question::where('type', 'isian_singkat')->count(),
            'menjodohkan'   => Question::where('type', 'menjodohkan')->count(),
            'esai'          => Question::where('type', 'esai')->count(),
        ];

        return view('admin.questions.index', compact('questions', 'subjects', 'activeType', 'counts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'type'       => 'required|in:pilihan_ganda,isian_singkat,menjodohkan,esai',
            'content'    => 'required|string',
            'score'      => 'required|numeric|min:1',
        ]);

        $options = null;
        $correctAnswer = null;

        if ($request->type === 'pilihan_ganda') {
            $request->validate([
                'options.A'      => 'required|string',
                'options.B'      => 'required|string',
                'options.C'      => 'required|string',
                'options.D'      => 'required|string',
                'correct_option' => 'required|in:A,B,C,D,E',
            ]);
            $options = array_filter($request->options, fn($v) => !is_null($v) && trim($v) !== '');
            $correctAnswer = [$request->correct_option];
        } elseif ($request->type === 'isian_singkat') {
            $request->validate([
                'short_answers' => 'required|string',
            ]);
            $keys = array_values(array_filter(array_map('trim', explode(',', $request->short_answers))));
            $correctAnswer = $keys;
        } elseif ($request->type === 'menjodohkan') {
            $request->validate([
                'match_left'  => 'required|array|min:2',
                'match_right' => 'required|array|min:2',
            ]);
            $leftList = [];
            $rightList = [];
            $pairs = [];

            foreach ($request->match_left as $idx => $leftText) {
                $rightText = $request->match_right[$idx] ?? '';
                if (trim($leftText) !== '' && trim($rightText) !== '') {
                    $l = trim($leftText);
                    $r = trim($rightText);
                    $leftList[] = $l;
                    $rightList[] = $r;
                    $pairs[$l] = $r;
                }
            }
            shuffle($rightList);
            $options = [
                'left'  => $leftList,
                'right' => $rightList,
            ];
            $correctAnswer = $pairs;
        } elseif ($request->type === 'esai') {
            $correctAnswer = $request->rubric ? [trim($request->rubric)] : [];
        }

        Question::create([
            'subject_id'     => $request->subject_id,
            'kelas'      => 'required|string',
            'teacher_id'     => auth()->id(),
            'type'           => $request->type,
            'content'        => $request->content,
            'options'        => $options,
            'correct_answer' => $correctAnswer,
            'score'          => $request->score,
        ]);

        return redirect()
            ->route('admin.questions.index', ['type' => $request->type])
            ->with('success', 'Soal berhasil ditambahkan ke Bank Soal!');
    }

    public function generateAi(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            
            'type'       => 'required|in:pilihan_ganda,isian_singkat,menjodohkan,esai',
            'topic'      => 'required|string|max:255',
            'difficulty' => 'required|in:mudah,sedang,sulit',
            'count'      => 'required|integer|min:1|max:10',
            'score'      => 'required|numeric|min:1',
        ]);

        // Karena menggunakan model gemini-3.8-flash tidak tersedia, kita gunakan gemini-1.5-flash
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return back()->withErrors(['ai' => 'GEMINI_API_KEY belum diisi di file .env.']);
        }

        $subject = Subject::findOrFail($request->subject_id);
        $type = $request->type;

        $formatInstruction = match ($type) {
            'pilihan_ganda' => 'Setiap item wajib memiliki key: "content" (teks soal), "options" (objek dengan key "A", "B", "C", "D"), dan "correct_answer" (string huruf kunci, misal "B").',
            'isian_singkat' => 'Setiap item wajib memiliki key: "content" (teks soal yang membutuhkan jawaban singkat 1-3 kata) dan "correct_answer" (array berisi 1-3 variasi jawaban benar, misal ["Jakarta", "DKI Jakarta"]).',
            'menjodohkan'   => 'Setiap item wajib memiliki key: "content" (instruksi menjodohkan) dan "pairs" (objek 4 pasangan premis kiri dan jawaban kanan yang tepat, misal {"Ibu kota Jepang": "Tokyo", "Ibu kota Korea Selatan": "Seoul"}).',
            'esai'          => 'Setiap item wajib memiliki key: "content" (pertanyaan uraian analitis) dan "rubric" (pedoman jawaban/poin penilaian untuk guru).',
        };

        $prompt = "Buatlah {$request->count} soal ujian mata pelajaran '{$subject->name}' topik '{$request->topic}' dengan tingkat kesulitan '{$request->difficulty}' dan tipe soal '{$type}' dalam Bahasa Indonesia yang baku. {$formatInstruction} Balas HANYA dengan JSON array valid tanpa markdown atau teks tambahan.";

        try {
            $response = Http::withOptions([
                'verify'           => false,
                'force_ip_resolve' => 'v4',
                'connect_timeout'  => 20,
            ])->timeout(90)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ],
                ]
            );
        } catch (\Throwable $e) {
            return back()->withErrors([
                'ai' => 'Koneksi ke server AI gagal/timeout. Pastikan internet aktif dan tidak diblokir firewall/proxy. Detail: ' . $e->getMessage()
            ]);
        }

        if (!$response->successful()) {
            return back()->withErrors(['ai' => 'Gagal menghubungi AI: ' . $response->body()]);
        }

        $rawText = $response->json('candidates.0.content.parts.0.text');
        $generatedItems = json_decode($rawText, true);

        if (!is_array($generatedItems)) {
            return back()->withErrors(['ai' => 'Format respons AI tidak valid. Silakan coba lagi.']);
        }

        $saved = 0;
        foreach ($generatedItems as $item) {
            if (empty($item['content'])) {
                continue;
            }

            $options = null;
            $correctAnswer = null;

            if ($type === 'pilihan_ganda' && !empty($item['options']) && !empty($item['correct_answer'])) {
                $options = $item['options'];
                $correctAnswer = [strtoupper(trim($item['correct_answer']))];
            } elseif ($type === 'isian_singkat' && !empty($item['correct_answer'])) {
                $correctAnswer = is_array($item['correct_answer'])
                    ? array_values($item['correct_answer'])
                    : [trim((string) $item['correct_answer'])];
            } elseif ($type === 'menjodohkan' && !empty($item['pairs']) && is_array($item['pairs'])) {
                $leftList = array_keys($item['pairs']);
                $rightList = array_values($item['pairs']);
                shuffle($rightList);
                $options = [
                    'left'  => $leftList,
                    'right' => $rightList,
                ];
                $correctAnswer = $item['pairs'];
            } elseif ($type === 'esai') {
                $correctAnswer = !empty($item['rubric']) ? [$item['rubric']] : [];
            } else {
                continue;
            }

            Question::create([
                'subject_id'     => $request->subject_id,
                'kelas'          => $request->kelas,
                'created_by'     => auth()->id(),
                'type'           => $type,
                'content'        => $item['content'],
                'options'        => $options,
                'correct_answer' => $correctAnswer,
                'score'          => $request->score,
            ]);
            $saved++;
        }

        return redirect()
            ->route('admin.questions.index', ['type' => $type])
            ->with('success', "AI berhasil membuat dan menyimpan {$saved} soal {$type}!");
    }

    public function destroy(Question $question)
    {
        $type = $question->type;
        $question->delete();

        return redirect()
            ->route('admin.questions.index', ['type' => $type])
            ->with('success', 'Soal berhasil dihapus.');
    }
}