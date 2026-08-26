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
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('summary');
            $table->timestamp('published_at')->nullable()->after('is_published');
        });

        // Safely backfill existing Page records as published so they preserve public availability.
        // Uses existing created_at (or updated_at / current timestamp) as deterministic published_at.
        DB::table('pages')->update([
            'is_published' => true,
            'published_at' => DB::raw('COALESCE(created_at, updated_at, CURRENT_TIMESTAMP)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'published_at']);
        });
    }
};
