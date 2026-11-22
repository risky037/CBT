<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('token');
            }
            if (!Schema::hasColumn('exams', 'randomize_questions')) {
                $table->boolean('randomize_questions')->default(true)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'randomize_questions']);
        });
    }
};