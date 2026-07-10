<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'reviews', 'watch_histories', 'favorites', 'watchlists',
        'diary_entries', 'list_movies', 'pinned_movies',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            $indexName = "{$tableName}_tmdb_id_index";

            if (! Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, fn (Blueprint $table) => $table->index('tmdb_id'));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            $indexName = "{$tableName}_tmdb_id_index";

            if (Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropIndex(['tmdb_id']));
            }
        }
    }
};
