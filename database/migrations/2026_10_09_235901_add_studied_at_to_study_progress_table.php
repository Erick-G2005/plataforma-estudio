<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_progress', function (Blueprint $table) {
            $table->timestamp('studied_at')->nullable()->after('completed_at');
        });

        // Los temas ya completados cuentan como estudiados: nada se vuelve a bloquear.
        DB::table('study_progress')
            ->where('completed', true)
            ->update(['studied_at' => DB::raw('COALESCE(completed_at, updated_at)')]);
    }

    public function down(): void
    {
        Schema::table('study_progress', function (Blueprint $table) {
            $table->dropColumn('studied_at');
        });
    }
};
