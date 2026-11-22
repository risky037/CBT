<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('kelas');
            $table->string('bab')->nullable();
            $table->enum('difficulty', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->enum('type', ['pilihan_ganda', 'menjodohkan', 'isian_singkat', 'uraian']);
            $table->longText('content');
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->integer('score')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};