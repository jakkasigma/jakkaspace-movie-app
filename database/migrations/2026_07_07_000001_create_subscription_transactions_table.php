<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_transactions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('tier', 20);
            $table->string('action', 20); // subscribe / renew / upgrade / cancel / admin_grant / admin_extend / redeem
            $table->unsignedInteger('price')->default(0);
            $table->string('payment_method', 20)->nullable();
            $table->foreignId('promo_id')->nullable()->constrained('subscription_promos')->nullOnDelete();
            $table->string('promo_code', 32)->nullable();
            $table->unsignedSmallInteger('period_days')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
    }
};
