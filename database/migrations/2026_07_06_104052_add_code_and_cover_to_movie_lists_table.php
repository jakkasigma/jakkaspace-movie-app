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
        Schema::table('movie_lists', function (Blueprint $table): void {
            $table->string('code', 10)->unique()->nullable()->after('is_public');
            $table->string('cover_photo')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('movie_lists', function (Blueprint $table): void {
            $table->dropColumn(['code', 'cover_photo']);
        });
    }
};
