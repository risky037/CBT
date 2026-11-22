<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamProctorLog;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with('subject')->latest()->get();
        $selectedExamId = $request->get('exam_id', $exams->first()?->id);

        $sessions = collect();
        $selectedExam = null;

        if ($selectedExamId) {
            $selectedExam = Exam::with(['subject', 'questions'])->find($selectedExamId);
            $sessions = ExamSession::with(['student', 'violations'])
                ->withCount(['answers' => function ($q) {
                    $q->whereNotNull('answer');
                }])
                ->where('exam_id', $selectedExamId)
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($session) use ($selectedExamId) {
                    // Ambil 1 foto kamera terbaru siswa pada ujian ini
                    $session->latest_photo = ExamProctorLog::where('exam_id', $selectedExamId)
                        ->where('user_id', $session->student_id)
                        ->latest()
                        ->first();

                    // Ambil seluruh riwayat foto kamera untuk bukti pelanggaran
                    $session->all_photos = ExamProctorLog::where('exam_id', $selectedExamId)
                        ->where('user_id', $session->student_id)
                        ->latest()
                        ->get();

                    return $session;
                });
        }

        return view('admin.monitor.index', compact('exams', 'selectedExam', 'sessions'));
    }

    public function unlock(ExamSession $session)
    {
        $session->update([
            'status' => 'ongoing',
            'violation_count' => 0,
            'finished_at' => null,
        ]);

        $namaSiswa = $session->student->name ?? 'Siswa';
        return back()->with('success', "Akses ujian untuk {$namaSiswa} berhasil dibuka kembali dan pelanggaran di-reset.");
    }

    public function forceFinish(ExamSession $session)
    {
        $totalScore = $session->answers()->sum('score_obtained');

        $session->update([
            'status' => 'finished', // Disamakan dengan ExamSessionController agar terbaca di menu Hasil
            'finished_at' => now(),
            'total_score' => $totalScore,
        ]);

        $namaSiswa = $session->student->name ?? 'Siswa';
        return back()->with('success', "Ujian {$namaSiswa} telah diselesaikan secara paksa.");
    }
}