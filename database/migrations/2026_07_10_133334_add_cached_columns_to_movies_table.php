<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->text('overview')->nullable()->after('backdrop_path');
            $table->string('genres')->nullable()->after('overview');
            $table->decimal('rating', 3, 1)->nullable()->after('genres');
            $table->string('poster_url')->nullable()->after('rating');
            $table->year('release_year')->nullable()->after('poster_url');
            $table->timestamp('cached_at')->nullable()->after('release_year');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->dropColumn(['overview', 'genres', 'rating', 'poster_url', 'release_year', 'cached_at']);
        });
    }
};
