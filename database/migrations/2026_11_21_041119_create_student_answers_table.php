<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('student_answers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_session_id')->constrained()->onDelete('cascade');
        $table->foreignId('question_id')->constrained()->onDelete('cascade');
        $table->json('answer')->nullable();
        $table->boolean('is_correct')->nullable();
        $table->decimal('score_obtained', 5, 2)->default(0);
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
