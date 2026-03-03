<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->string('sha256', 64)->nullable()->after('file_size');
        });

        DB::table('document_attachments')
            ->select(['id', 'disk', 'file_path'])
            ->whereNull('sha256')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    try {
                        if (! $row->disk || ! $row->file_path) {
                            continue;
                        }

                        $disk = Storage::disk((string) $row->disk);

                        if (! $disk->exists((string) $row->file_path)) {
                            continue;
                        }

                        $checksum = $disk->checksum((string) $row->file_path);

                        if (! is_string($checksum) || $checksum === '') {
                            continue;
                        }

                        DB::table('document_attachments')
                            ->where('id', $row->id)
                            ->update(['sha256' => strtolower($checksum)]);
                    } catch (\Throwable) {
                        continue;
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->dropColumn('sha256');
        });
    }
};
