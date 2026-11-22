<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exam_sessions', 'violation_count')) {
            Schema::table('exam_sessions', function (Blueprint $table) {
                $table->unsignedInteger('violation_count')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exam_sessions', 'violation_count')) {
            Schema::table('exam_sessions', function (Blueprint $table) {
                $table->dropColumn('violation_count');
            });
        }
    }
};
