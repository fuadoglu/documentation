<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->json('name_translations')->nullable()->after('name');
        });

        Schema::table('folders', function (Blueprint $table): void {
            $table->json('name_translations')->nullable()->after('name');
        });

        Schema::table('branding_settings', function (Blueprint $table): void {
            $table->string('favicon_path')->nullable()->after('logo_path');
        });

        $defaultLocale = config('app.locale', 'az');

        DB::table('categories')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($defaultLocale): void {
                foreach ($rows as $row) {
                    DB::table('categories')
                        ->where('id', $row->id)
                        ->update([
                            'name_translations' => json_encode([$defaultLocale => $row->name], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                }
            });

        DB::table('folders')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($defaultLocale): void {
                foreach ($rows as $row) {
                    DB::table('folders')
                        ->where('id', $row->id)
                        ->update([
                            'name_translations' => json_encode([$defaultLocale => $row->name], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branding_settings', function (Blueprint $table): void {
            $table->dropColumn('favicon_path');
        });

        Schema::table('folders', function (Blueprint $table): void {
            $table->dropColumn('name_translations');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('name_translations');
        });
    }
};
