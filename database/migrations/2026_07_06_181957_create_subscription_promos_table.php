<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_promos', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 32)->unique()->nullable();
            $table->string('type', 10);
            $table->unsignedSmallInteger('value');
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->unsignedSmallInteger('max_uses')->default(0);
            $table->unsignedSmallInteger('used_count')->default(0);
            $table->datetime('starts_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('show_popup')->default(false);
            $table->string('popup_title', 100)->nullable();
            $table->text('popup_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_promos');
    }
};
