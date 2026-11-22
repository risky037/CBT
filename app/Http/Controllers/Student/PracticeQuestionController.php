<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;

class PracticeQuestionController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user();
        $query = Question::with('subject')->latest();

        // Filter otomatis sesuai kelas siswa jika profil siswa memiliki data kelas
        if (!empty($student->kelas)) {
            $query->where('kelas', $student->kelas);
        }

        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $questions = $query->paginate(10);
        $subjects = Subject::all();

        return view('student.questions.index', compact('questions', 'subjects'));
    }

    public function show(Question $question)
    {
        $question->load('subject');
        return view('student.questions.show', compact('question'));
    }
}