<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['subject', 'questions'])->latest()->get();
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('admin.exams.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'kelas' => 'required|string',
            'duration' => 'required|integer|min:5',
            'max_violations' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $exam = Exam::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'teacher_id' => auth()->id(),
            'kelas' => $request->kelas,
            'duration' => $request->duration,
            'max_violations' => $request->max_violations,
            'token' => strtoupper(Str::random(6)), // Generate 6 karakter token otomatis
            'is_active' => $request->has('is_active'),
            'randomize_questions' => $request->has('randomize_questions'),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('admin.exams.show', $exam->id)
            ->with('success', 'Jadwal ujian berhasil dibuat. Silakan pilih soal dari Bank Soal.');
    }

    public function show(Exam $exam)
    {
        $exam->load('questions');
        // Ambil soal dari Bank Soal yang sesuai dengan mapel ujian ini
        $availableQuestions = Question::where('subject_id', $exam->subject_id)->latest()->get();
        $selectedQuestionIds = $exam->questions->pluck('id')->toArray();

        return view('admin.exams.show', compact('exam', 'availableQuestions', 'selectedQuestionIds'));
    }

    public function syncQuestions(Request $request, Exam $exam)
    {
        $exam->questions()->sync($request->question_ids ?? []);
        return back()->with('success', 'Daftar soal pada ujian ini berhasil diperbarui.');
    }

    public function refreshToken(Exam $exam)
    {
        $exam->update(['token' => strtoupper(Str::random(6))]);
        return back()->with('success', 'Token ujian berhasil diperbarui menjadi: ' . $exam->token);
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dihapus.');
    }
}