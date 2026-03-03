<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('document_attachments', 'version_number')) {
                $table->unsignedInteger('version_number')->default(1)->after('document_id');
            }

            if (! Schema::hasColumn('document_attachments', 'version_note')) {
                $table->string('version_note', 500)->nullable()->after('version_number');
            }

            $table->index(['document_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::table('document_attachments', function (Blueprint $table) {
            $table->dropIndex(['document_id', 'version_number']);

            if (Schema::hasColumn('document_attachments', 'version_note')) {
                $table->dropColumn('version_note');
            }

            if (Schema::hasColumn('document_attachments', 'version_number')) {
                $table->dropColumn('version_number');
            }
        });
    }
};
