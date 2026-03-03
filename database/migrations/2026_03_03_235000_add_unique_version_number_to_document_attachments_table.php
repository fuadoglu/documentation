<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $documentIds = DB::table('document_attachments')
            ->select('document_id')
            ->distinct()
            ->pluck('document_id');

        foreach ($documentIds as $documentId) {
            $attachments = DB::table('document_attachments')
                ->where('document_id', $documentId)
                ->orderBy('version_number')
                ->orderBy('id')
                ->get(['id']);

            $version = 1;

            foreach ($attachments as $attachment) {
                DB::table('document_attachments')
                    ->where('id', $attachment->id)
                    ->update(['version_number' => $version]);

                $version++;
            }
        }

        try {
            Schema::table('document_attachments', function (Blueprint $table): void {
                $table->dropIndex('document_attachments_document_id_version_number_index');
            });
        } catch (\Throwable) {
            // Index may not exist in some deployments.
        }

        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->unique(['document_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->dropUnique('document_attachments_document_id_version_number_unique');
            $table->index(['document_id', 'version_number']);
        });
    }
};
