<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamViolation;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExamSessionController extends Controller
{
    public function index()
    {
        $student = auth()->user();
        $now = now();

        // Ambil semua ujian aktif sesuai kelas siswa (termasuk soal dari admin & guru)
        $exams = Exam::with(['subject', 'questions'])
            ->withCount('questions')
            ->where('is_active', true)
            ->where('kelas', $student->kelas)
            ->latest()
            ->get();

        // Riwayat sesi ujian siswa ini
        $sessions = ExamSession::where('student_id', $student->id)
            ->get()
            ->keyBy('exam_id');

        return view('student.dashboard', compact('exams', 'sessions', 'now'));
    }

    public function start(Request $request, Exam $exam)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Validasi Token
        if (strtoupper(trim($request->token)) !== strtoupper($exam->token)) {
            return back()->withErrors(['token' => 'Token ujian tidak valid! Silakan periksa kembali.']);
        }

        // Pastikan soal sudah dipasangkan ke paket ujian
        if ($exam->questions()->count() === 0) {
            return back()->withErrors(['token' => 'Paket ujian ini belum memiliki soal. Hubungi guru/admin.']);
        }

        $student = auth()->user();

        // Cek apakah sudah pernah memulai sesi
        $session = ExamSession::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'started_at' => now(),
                'status' => 'ongoing',
                'violation_count' => 0,
            ]
        );

        if ($session->status === 'finished') {
            return back()->withErrors(['token' => 'Anda sudah menyelesaikan ujian ini.']);
        }

        if ($session->status === 'blocked') {
            return back()->withErrors(['token' => 'Sesi ujian Anda terkunci karena melebihi batas pelanggaran. Hubungi pengawas/guru.']);
        }

        return redirect()->route('student.sessions.take', $session->id);
    }

    public function take(ExamSession $session)
    {
        abort_if($session->student_id !== auth()->id(), 403);

        if ($session->status !== 'ongoing') {
            return redirect()->route('student.dashboard')->with('info', 'Sesi ujian telah berakhir atau terkunci.');
        }

        // Muat seluruh soal yang terhubung ke ujian (baik buatan Admin maupun Guru)
        $exam = $session->exam()->with(['subject', 'questions'])->first();

        // Hitung sisa waktu (dalam detik) berdasarkan started_at + durasi ujian
        $endTime = Carbon::parse($session->started_at)->addMinutes($exam->duration);
        $remainingSeconds = max(0, now()->diffInSeconds($endTime, false));

        if ($remainingSeconds <= 0) {
            return $this->finalizeSession($session);
        }

        $questions = $exam->randomize_questions ? $exam->questions->shuffle() : $exam->questions;
        $savedAnswers = StudentAnswer::where('exam_session_id', $session->id)
            ->get()
            ->keyBy('question_id');

        return view('student.take', compact('session', 'exam', 'questions', 'savedAnswers', 'remainingSeconds'));
    }

    public function saveAnswer(Request $request, ExamSession $session)
    {
        abort_if($session->student_id !== auth()->id() || $session->status !== 'ongoing', 403);

        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable',
        ]);

        StudentAnswer::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'question_id' => $request->question_id,
            ],
            [
                'answer' => $request->answer,
            ]
        );

        return response()->json(['status' => 'saved']);
    }

    public function logViolation(Request $request, ExamSession $session)
    {
        abort_if($session->student_id !== auth()->id(), 403);

        if ($session->status !== 'ongoing') {
            return response()->json(['status' => $session->status]);
        }

        ExamViolation::create([
            'exam_session_id' => $session->id,
            'type' => $request->type ?? 'tab_switch',
            'description' => $request->description ?? 'Siswa meninggalkan layar ujian / keluar mode layar penuh.',
        ]);

        $session->increment('violation_count');
        $maxViolations = $session->exam->max_violations;

        if ($session->violation_count >= $maxViolations) {
            $session->update([
                'status' => 'blocked',
                'finished_at' => now(),
            ]);

            return response()->json([
                'status' => 'blocked',
                'violation_count' => $session->violation_count,
                'max_violations' => $maxViolations,
            ]);
        }

        return response()->json([
            'status' => 'warning',
            'violation_count' => $session->violation_count,
            'max_violations' => $maxViolations,
        ]);
    }

    public function finish(ExamSession $session)
    {
        abort_if($session->student_id !== auth()->id(), 403);
        return $this->finalizeSession($session);
    }

    private function finalizeSession(ExamSession $session)
    {
        if ($session->status === 'finished') {
            return redirect()->route('student.dashboard');
        }

        $exam = $session->exam->load(['subject', 'questions']);
        $answers = StudentAnswer::where('exam_session_id', $session->id)->get()->keyBy('question_id');

        $totalScore = 0;
        $answeredCount = 0;
        $hasEssay = false;

        foreach ($exam->questions as $question) {
            if ($question->type === 'esai') {
                $hasEssay = true;
            }

            $studentAns = $answers->get($question->id);
            if (!$studentAns || empty($studentAns->answer)) {
                continue;
            }

            $answeredCount++;
            $userValue = $studentAns->answer;
            $correctValue = $question->correct_answer;
            $isCorrect = false;
            $earnedScore = 0;

            if ($question->type === 'pilihan_ganda') {
                if (isset($correctValue[0]) && (string)$userValue === (string)$correctValue[0]) {
                    $isCorrect = true;
                    $earnedScore = $question->score;
                }
            } elseif ($question->type === 'isian_singkat') {
                $normalizedUser = strtolower(trim((string)$userValue));
                $normalizedKeys = array_map(fn($k) => strtolower(trim($k)), $correctValue ?? []);
                if (in_array($normalizedUser, $normalizedKeys)) {
                    $isCorrect = true;
                    $earnedScore = $question->score;
                }
            } elseif ($question->type === 'menjodohkan') {
                if (is_array($userValue) && is_array($correctValue) && count($correctValue) > 0) {
                    $benar = 0;
                    foreach ($correctValue as $kiri => $kananBenar) {
                        if (isset($userValue[$kiri]) && $userValue[$kiri] === $kananBenar) {
                            $benar++;
                        }
                    }
                    $earnedScore = round(($benar / count($correctValue)) * $question->score, 2);
                    $isCorrect = ($benar === count($correctValue));
                }
            }

            $studentAns->update([
                'is_correct' => $isCorrect,
                'score_obtained' => $earnedScore,
            ]);

            $totalScore += $earnedScore;
        }

        $finishedAt = now();

        $session->update([
            'status' => 'finished',
            'finished_at' => $finishedAt,
            'total_score' => $totalScore,
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Ujian telah selesai dan jawaban Anda berhasil dikumpulkan!')
            ->with('exam_finished', [
                'student_name' => auth()->user()->name,
                'exam_title' => $exam->title,
                'subject_name' => $exam->subject->name ?? '-',
                'answered' => $answeredCount,
                'total_questions' => $exam->questions->count(),
                'finished_at' => $finishedAt->format('H:i:s'),
                'total_score' => $totalScore,
                'max_score' => $exam->questions->sum('score'),
                'has_essay' => $hasEssay,
            ]);
    }
}
