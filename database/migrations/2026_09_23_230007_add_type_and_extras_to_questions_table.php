<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('questions', function (Blueprint $table) {
        $table->string('type')->default('single_choice')->after('statement');
        // single_choice | multiple_choice | true_false | short_answer
        $table->string('image_path')->nullable()->after('type');
        $table->text('explanation')->nullable()->after('image_path');
        $table->string('correct_answer_text')->nullable()->after('explanation');
        // usado solo cuando type = short_answer
    });
}

public function down(): void
{
    Schema::table('questions', function (Blueprint $table) {
        $table->dropColumn(['type', 'image_path', 'explanation', 'correct_answer_text']);
    });
}
};
