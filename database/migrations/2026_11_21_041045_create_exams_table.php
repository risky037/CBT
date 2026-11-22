<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('exams', function (Blueprint $table) {
        $table->id();
        $table->foreignId('subject_id')->constrained()->onDelete('cascade');
        $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
        $table->string('title');
        $table->string('kelas');
        $table->dateTime('start_time');
        $table->dateTime('end_time');
        $table->integer('duration');
        $table->string('token', 6)->nullable();
        $table->integer('max_violations')->default(3);
        $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
        $table->timestamps();
    });

    Schema::create('exam_question', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_id')->constrained()->onDelete('cascade');
        $table->foreignId('question_id')->constrained()->onDelete('cascade');
        $table->integer('order')->default(0);
    });
}
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
