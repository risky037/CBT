<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\MonitorController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SettingController; 
use App\Http\Controllers\Student\ExamSessionController;
use App\Http\Controllers\Api\ProctorController; // Import ProctorController

Route::get('/', fn() => redirect()->route('login'));

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Area Admin & Guru
Route::middleware(['auth', 'role:admin,guru'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('admin.questions.index'))->name('dashboard');
    Route::resource('questions', QuestionController::class);
    Route::post('/questions/generate-ai', [QuestionController::class, 'generateAi'])->name('questions.generate-ai');

    Route::resource('exams', ExamController::class);
    Route::post('/exams/{exam}/refresh-token', [ExamController::class, 'refreshToken'])->name('exams.refresh-token');
    Route::post('/exams/{exam}/sync-questions', [ExamController::class, 'syncQuestions'])->name('exams.sync-questions');

    // Menu Pantau Ujian (Live Monitoring)
    Route::get('/monitor', [MonitorController::class, 'index'])->name('monitor.index');
    Route::post('/monitor/{session}/unlock', [MonitorController::class, 'unlock'])->name('monitor.unlock');
    Route::post('/monitor/{session}/force-finish', [MonitorController::class, 'forceFinish'])->name('monitor.force-finish');

    // Menu Hasil Ujian & Koreksi Uraian
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{session}', [ResultController::class, 'show'])->name('results.show');
    Route::post('/results/{session}/grade', [ResultController::class, 'grade'])->name('results.grade');
    Route::get('/results/export/{exam}', [ResultController::class, 'exportCsv'])->name('results.export');

    // Menu Siswa & Kelas
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::get('/students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
    Route::post('/students/import', [StudentController::class, 'importCsv'])->name('students.import');

    // Menu Pengaturan (Ganti Password & Aplikasi)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/password/reset', [SettingController::class, 'resetPassword'])->name('settings.password.reset');
    Route::post('/settings/app', [SettingController::class, 'updateApp'])->name('settings.app');
    Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
});

// Area Siswa
Route::middleware(['auth', 'role:siswa'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [ExamSessionController::class, 'index'])->name('dashboard');
    Route::post('/exams/{exam}/start', [ExamSessionController::class, 'start'])->name('exams.start');
    Route::get('/sessions/{session}/take', [ExamSessionController::class, 'take'])->name('sessions.take');
    Route::post('/sessions/{session}/answer', [ExamSessionController::class, 'saveAnswer'])->name('sessions.answer');
    Route::post('/sessions/{session}/violation', [ExamSessionController::class, 'logViolation'])->name('sessions.violation');
    Route::post('/sessions/{session}/finish', [ExamSessionController::class, 'finish'])->name('sessions.finish');
});

// Endpoint Tambahan (Dilindungi Auth agar fungsi auth()->id() terbaca)
Route::middleware('auth')->group(function () {
    Route::post('/proctor/upload', [ProctorController::class, 'uploadSnapshot'])->name('proctor.upload');
});