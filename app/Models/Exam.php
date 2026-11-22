<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
    'subject_id', 'teacher_id', 'title', 'kelas', 
    'start_time', 'end_time', 'duration', 'token', 'max_violations', 'status'
];

protected function casts(): array
{
    return [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}

public function subject()
{
    return $this->belongsTo(Subject::class);
}

public function questions()
{
    return $this->belongsToMany(Question::class, 'exam_question')->withPivot('order');
}

public function sessions()
{
    return $this->hasMany(ExamSession::class);
}
}
