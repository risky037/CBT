<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    // Mengizinkan kolom ini untuk diisi (Mass Assignment)
    protected $fillable = [
        'id',
        'app_name',
        'school_name',
        'logo'
    ];
}