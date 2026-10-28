<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
{
    Schema::create('quiz_attempt_answers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
        $table->foreignId('question_id')->constrained()->cascadeOnDelete();
        $table->json('selected_option_ids')->nullable(); // para choice/multiple/true_false
        $table->string('answer_text')->nullable();        // para short_answer
        $table->boolean('is_correct');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};
