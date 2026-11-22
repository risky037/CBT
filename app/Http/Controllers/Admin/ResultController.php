<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with('subject')->latest()->get();
        $selectedExamId = $request->get('exam_id', $exams->first()?->id);

        $sessions = collect();
        $selectedExam = null;

        if ($selectedExamId) {
            $selectedExam = Exam::with(['subject', 'questions'])->find($selectedExamId);
            $sessions = ExamSession::with(['student', 'answers.question'])
                ->where('exam_id', $selectedExamId)
                ->orderByDesc('total_score')
                ->get();
        }

        return view('admin.results.index', compact('exams', 'selectedExam', 'sessions'));
    }

    public function show(ExamSession $session)
    {
        $session->load(['student', 'exam.subject', 'exam.questions', 'answers.question', 'violations']);
        return view('admin.results.show', compact('session'));
    }

    public function grade(Request $request, ExamSession $session)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'numeric|min:0',
        ]);

        foreach ($request->scores as $answerId => $scoreValue) {
            $answer = StudentAnswer::where('exam_session_id', $session->id)->find($answerId);
            if ($answer) {
                $maxScore = $answer->question->score ?? 10;
                $finalScore = min((float) $scoreValue, (float) $maxScore);
                $answer->update([
                    'score_obtained' => $finalScore,
                    'is_correct' => $finalScore > 0,
                ]);
            }
        }

        $totalScore = $session->answers()->sum('score_obtained');
        $session->update(['total_score' => $totalScore]);

        return back()->with('success', "Penilaian berhasil disimpan. Total skor siswa: {$totalScore}");
    }

    public function exportCsv(Exam $exam)
    {
        $sessions = ExamSession::with('student')
            ->where('exam_id', $exam->id)
            ->orderByDesc('total_score')
            ->get();

        $filename = 'Hasil_Ujian_' . str_replace(' ', '_', $exam->title) . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Siswa', 'Kelas', 'Email', 'Status', 'Pelanggaran', 'Waktu Mulai', 'Waktu Selesai', 'Total Skor']);

            foreach ($sessions as $index => $session) {
                fputcsv($file, [
                    $index + 1,
                    $session->student->name ?? '-',
                    $session->student->kelas ?? '-',
                    $session->student->email ?? '-',
                    strtoupper($session->status),
                    $session->violation_count . 'x',
                    $session->started_at ?? '-',
                    $session->finished_at ?? '-',
                    $session->total_score ?? 0,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}