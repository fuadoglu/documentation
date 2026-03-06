<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORY_HELPER_INDEX = 'prefix_counters_category_id_index';

    public function up(): void
    {
        if (! Schema::hasColumn('prefix_counters', 'folder_id')) {
            Schema::table('prefix_counters', function (Blueprint $table) {
                $table->foreignId('folder_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('folders')
                    ->nullOnDelete();
            });
        }

        // MySQL may bind category_id FK to the legacy unique key
        // (category_id, year). Create a stable standalone index first.
        if (! $this->hasIndex('prefix_counters', self::CATEGORY_HELPER_INDEX)) {
            Schema::table('prefix_counters', function (Blueprint $table) {
                $table->index('category_id', self::CATEGORY_HELPER_INDEX);
            });
        }

        Schema::table('prefix_counters', function (Blueprint $table) {
            if ($this->hasIndex('prefix_counters', 'prefix_counters_category_id_year_unique')) {
                $table->dropUnique(['category_id', 'year']);
            }

            if (! $this->hasIndex('prefix_counters', 'prefix_counters_folder_id_category_id_year_unique')) {
                $table->unique(['folder_id', 'category_id', 'year']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('prefix_counters', function (Blueprint $table) {
            if ($this->hasIndex('prefix_counters', 'prefix_counters_folder_id_category_id_year_unique')) {
                $table->dropUnique(['folder_id', 'category_id', 'year']);
            }
        });

        if (Schema::hasColumn('prefix_counters', 'folder_id')) {
            Schema::table('prefix_counters', function (Blueprint $table) {
                $table->dropConstrainedForeignId('folder_id');
            });
        }

        Schema::table('prefix_counters', function (Blueprint $table) {
            if (! $this->hasIndex('prefix_counters', 'prefix_counters_category_id_year_unique')) {
                $table->unique(['category_id', 'year']);
            }
        });

        Schema::table('prefix_counters', function (Blueprint $table) {
            if ($this->hasIndex('prefix_counters', self::CATEGORY_HELPER_INDEX)) {
                $table->dropIndex(self::CATEGORY_HELPER_INDEX);
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return Schema::hasIndex($table, $indexName);
    }
};
