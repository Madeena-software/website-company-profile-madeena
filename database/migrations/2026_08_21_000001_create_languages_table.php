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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('native_name', 100);
            $table->json('ui_labels')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Backfill default initial languages (id and en) safely
        $now = now();
        DB::table('languages')->insert([
            [
                'code' => 'id',
                'name' => 'Indonesian',
                'native_name' => 'Bahasa Indonesia',
                'ui_labels' => json_encode([
                    'navigation' => 'Navigasi',
                    'contact' => 'Kontak',
                    'all_rights_reserved' => 'Seluruh hak dilindungi.',
                    'preview_mode_notice' => 'Anda sedang melihat Mode Pratinjau (Draft).',
                    'back' => 'Kembali',
                    'language' => 'Bahasa',
                    'read' => 'Baca',
                    'view_all' => 'Lihat Semua :title',
                    'articles' => 'Artikel',
                    'manage_website_in_admin' => 'Kelola Website di Admin',
                ]),
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'ui_labels' => json_encode([
                    'navigation' => 'Navigation',
                    'contact' => 'Contact',
                    'all_rights_reserved' => 'All rights reserved.',
                    'preview_mode_notice' => 'You are viewing Preview Mode (Draft).',
                    'back' => 'Back',
                    'language' => 'Language',
                    'read' => 'Read',
                    'view_all' => 'View All :title',
                    'articles' => 'Articles',
                    'manage_website_in_admin' => 'Manage Website in Admin',
                ]),
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
