<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
    'subject_id', 'teacher_id', 'kelas',
    'difficulty', 'type', 'content', 'options', 'correct_answer', 'score'
];

protected function casts(): array
{
    return [
        'options' => 'array',
        'correct_answer' => 'array',
    ];
}

public function subject()
{
    return $this->belongsTo(Subject::class);
}

public function teacher()
{
    return $this->belongsTo(User::class, 'teacher_id');
}
}
