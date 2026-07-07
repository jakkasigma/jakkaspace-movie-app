<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_promo_user', function (Blueprint $table): void {
            $table->foreignId('subscription_promo_id')->constrained('subscription_promos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->unsignedInteger('original_price');
            $table->unsignedInteger('discounted_price');
            $table->string('code_used', 32)->nullable();
            $table->timestamp('applied_at');

            $table->unique(['subscription_promo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_promo_user');
    }
};
