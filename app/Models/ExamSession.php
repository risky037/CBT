<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'finished_at',
        'total_score',
        'status',
        'violation_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function violations()
    {
        return $this->hasMany(ExamViolation::class);
    }

    public function proctorLogs()
    {
        return $this->hasMany(ExamProctorLog::class, 'user_id', 'student_id')
                    ->where('exam_id', $this->exam_id);
    }
}