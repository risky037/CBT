<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
    'exam_session_id', 'question_id', 'answer', 'is_correct', 'score_obtained'
];

protected function casts(): array
{
    return [
        'answer' => 'array',
        'is_correct' => 'boolean',
    ];
}

public function question()
{
    return $this->belongsTo(Question::class);
}
}
