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

        Schema::table('prefix_counters', function (Blueprint $table) {
            $table->dropUnique(['category_id', 'year']);
            $table->unique(['folder_id', 'category_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('prefix_counters', function (Blueprint $table) {
            $table->dropUnique(['folder_id', 'category_id', 'year']);
        });

        if (Schema::hasColumn('prefix_counters', 'folder_id')) {
            Schema::table('prefix_counters', function (Blueprint $table) {
                $table->dropConstrainedForeignId('folder_id');
            });
        }

        Schema::table('prefix_counters', function (Blueprint $table) {
            $table->unique(['category_id', 'year']);
        });
    }
};
