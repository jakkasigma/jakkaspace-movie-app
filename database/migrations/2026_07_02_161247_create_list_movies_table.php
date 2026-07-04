<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_movies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('movie_list_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tmdb_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['movie_list_id', 'tmdb_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_movies');
    }
};
