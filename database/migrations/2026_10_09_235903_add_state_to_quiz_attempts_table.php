<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->string('status')->default('submitted')->after('quiz_id'); // in_progress | submitted | expired
            $table->timestamp('started_at')->nullable()->after('total_questions');
            $table->timestamp('expires_at')->nullable()->after('started_at');
            $table->decimal('percentage', 5, 2)->default(0)->after('expires_at');
            $table->boolean('passed')->default(false)->after('percentage');
        });

        // Un intento en curso todavía no tiene fecha de finalización.
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->change();
        });

        // Intentos anteriores a las reglas nuevas: se calculan con la nota mínima por defecto (70%).
        DB::table('quiz_attempts')->update([
            'started_at' => DB::raw('completed_at'),
            'percentage' => DB::raw('CASE WHEN total_questions > 0 THEN ROUND(score * 100 / total_questions, 2) ELSE 0 END'),
            'passed' => DB::raw('CASE WHEN total_questions > 0 AND score * 100 / total_questions >= 70 THEN 1 ELSE 0 END'),
        ]);

        // Las respuestas se guardan mientras el estudiante avanza, antes de calificar.
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->boolean('is_correct')->nullable()->change();
        });

        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->unique(['quiz_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropUnique(['quiz_attempt_id', 'question_id']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['status', 'started_at', 'expires_at', 'percentage', 'passed']);
        });
    }
};
