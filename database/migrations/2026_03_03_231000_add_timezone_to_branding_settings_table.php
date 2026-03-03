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
        Schema::table('branding_settings', function (Blueprint $table): void {
            $table->string('timezone', 64)->nullable()->after('secondary_color');
        });

        DB::table('branding_settings')
            ->whereNull('timezone')
            ->update(['timezone' => config('app.timezone', 'UTC')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branding_settings', function (Blueprint $table): void {
            $table->dropColumn('timezone');
        });
    }
};
