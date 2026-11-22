<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'nis_nip', 'role', 'kelas', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi untuk Guru: Daftar ujian yang dibuat oleh guru tersebut.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    /**
     * Relasi untuk Guru: Daftar soal di Bank Soal yang dibuat oleh guru tersebut.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'teacher_id');
    }

    /**
     * Relasi untuk Siswa: Riwayat sesi ujian yang diikuti oleh siswa tersebut.
     */
    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'student_id');
    }
}