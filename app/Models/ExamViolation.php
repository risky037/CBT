<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamViolation extends Model
{
    protected $fillable = ['exam_session_id', 'violation_type', 'description'];

public function session()
{
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
}
}
