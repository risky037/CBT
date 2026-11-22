<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('exam_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_id')->constrained()->onDelete('cascade');
        $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
        $table->dateTime('started_at');
        $table->dateTime('finished_at')->nullable();
        $table->decimal('total_score', 5, 2)->default(0);
        $table->enum('status', ['ongoing', 'finished', 'blocked'])->default('ongoing');
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
