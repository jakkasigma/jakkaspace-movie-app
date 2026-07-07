<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('subscription_promos_temp', function ($table) {
                $table->id();
                $table->string('name', 100);
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

            DB::statement('INSERT INTO subscription_promos_temp SELECT id, name, type, value, plan_id, max_uses, used_count, starts_at, expires_at, show_popup, popup_title, popup_message, is_active, created_by, created_at, updated_at FROM subscription_promos');
            DB::statement('DROP TABLE subscription_promos');
            DB::statement('ALTER TABLE subscription_promos_temp RENAME TO subscription_promos');
        } else {
            Schema::table('subscription_promos', function ($table) {
                $table->dropColumn('code');
            });
        }
    }

    public function down(): void
    {
        Schema::table('subscription_promos', function ($table) {
            $table->string('code', 32)->unique()->nullable()->after('name');
        });
    }
};
