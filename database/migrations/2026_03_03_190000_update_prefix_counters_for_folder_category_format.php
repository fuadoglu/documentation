<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        // MySQL can bind FK checks to the old unique index via leftmost prefix.
        // Ensure a standalone index exists before dropping that unique key.
        if (! $this->hasLeadingIndexOnCategoryId()) {
            Schema::table('prefix_counters', function (Blueprint $table) {
                $table->index('category_id');
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
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return Schema::hasIndex($table, $indexName);
    }

    private function hasLeadingIndexOnCategoryId(): bool
    {
        foreach (Schema::getIndexes('prefix_counters') as $index) {
            $columns = $index['columns'] ?? [];
            if (($columns[0] ?? null) === 'category_id') {
                return true;
            }
        }

        return false;
    }
};
