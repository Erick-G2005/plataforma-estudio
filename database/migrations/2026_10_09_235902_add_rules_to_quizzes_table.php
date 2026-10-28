<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('type')->default('topic')->after('id'); // topic | final
            $table->foreignId('subject_id')->nullable()->after('topic_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('pass_score')->default(70);            // nota mínima (%)
            $table->unsignedSmallInteger('max_attempts')->nullable();          // null = ilimitados
            $table->unsignedSmallInteger('cooldown_hours')->default(24);       // espera al agotar intentos
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();    // null = sin límite
            $table->unsignedSmallInteger('questions_per_attempt')->nullable(); // null = todas las propias
            $table->unsignedSmallInteger('drawn_questions_count')->default(0); // solo examen final
            $table->string('show_answers')->default('after_attempt');          // never | after_pass | after_attempt
        });

        // El examen final pertenece a una materia, no a un tema.
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id')->nullable()->change();
        });

        // Un quiz por tema y un examen final por materia.
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unique('topic_id');
            $table->unique('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropUnique(['topic_id']);
            $table->dropUnique(['subject_id']);
        });

        // topic_id no se vuelve a NOT NULL: fallaría si ya existen exámenes finales.
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
            $table->dropColumn([
                'type', 'pass_score', 'max_attempts', 'cooldown_hours',
                'time_limit_minutes', 'questions_per_attempt',
                'drawn_questions_count', 'show_answers',
            ]);
        });
    }
};
