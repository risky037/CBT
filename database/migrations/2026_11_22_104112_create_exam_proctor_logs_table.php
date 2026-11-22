<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('exam_proctor_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID Siswa
        $table->foreignId('exam_id')->constrained()->onDelete('cascade'); // ID Ujian
        $table->string('image_path'); // Path foto
        $table->string('violation_type')->nullable(); // Jika ada pelanggaran (misal: pindah tab)
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('exam_proctor_logs');
    }
};
