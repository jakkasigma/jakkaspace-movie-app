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
        Schema::table('diary_entries', function (Blueprint $table): void {
            $table->string('movie_title', 255)->nullable()->after('tmdb_id');
        });
    }

    public function down(): void
    {
        Schema::table('diary_entries', function (Blueprint $table): void {
            $table->dropColumn('movie_title');
        });
    }
};
