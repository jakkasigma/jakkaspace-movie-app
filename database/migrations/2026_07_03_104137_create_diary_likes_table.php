<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diary_entry_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'diary_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_likes');
    }
};
