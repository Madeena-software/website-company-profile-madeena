<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy flat keys (kept for backward compatibility with HomeController)
        $flat = [
            ['key' => 'company_name', 'value' => 'PT Madeena Karya Indonesia', 'group' => 'general'],
            ['key' => 'tagline', 'value' => 'Know Sciences, Learn Engineering, Create Technology, Develop Business.', 'group' => 'general'],
        ];

        foreach ($flat as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // New JSON group format used by SiteSettings page and HomepageEditor
        Setting::setJson('contact_info', [
            'email'     => 'madeenajog@gmail.com',
            'phone'     => '+62 821 3811 4011',
            'whatsapp'  => '+62 857 2830 4141',
            'address'   => 'Jl. Lowanu No. 68-72, Sorosutan, Umbulharjo, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55162',
        ]);

        Setting::setJson('social_media', [
            'instagram' => '',
            'linkedin'  => '',
            'youtube'   => '',
        ]);

        Setting::setJson('seo', [
            'meta_title'       => 'PT Madeena Karya Indonesia - Digital Radiography Indonesia',
            'meta_description' => 'PT Madeena Karya Indonesia — produsen alat Digital Direct Radiography (DDR) berbasis teknologi Camera Coupled X-Ray Detector (CCXD) buatan Indonesia. TKDN 57,62%, Izin Edar Kemenkes RI AKD 21501220581.',
        ]);

        Setting::setJson('branding', [
            'logo'            => null,
            'primary_color'   => '#1a365d',
            'secondary_color' => '#2dd4bf',
            'font_family'     => 'Inter',
        ]);

        Setting::setJson('whatsapp_button', [
            'enabled' => true,
            'number'  => '+62 857 2830 4141',
        ]);

        Setting::setJson('nav_custom_links', []);

        // Seed a minimal starter homepage if none exists yet
        if (! Setting::where('key', 'homepage_sections')->exists()) {
            Setting::setJson('homepage_sections', [
                ['type' => 'hero', 'data' => [
                    'section_id'  => 'sec-hero',
                    'show_in_nav' => false,
                    'nav_label'   => 'Beranda',
                    'banners'     => [
                        [
                            'title'       => 'PT MADEENA Karya Indonesia',
                            'subtitle'    => 'Know Sciences, Learn Engineering, Create Technology, Develop Business.',
                            'description' => 'Produsen alat Digital Direct Radiography (DDR) berbasis teknologi Camera Coupled X-Ray Detector (CCXD) buatan Indonesia. TKDN 57,62%, Izin Edar Kemenkes RI AKD 21501220581.',
                            'cta_text'    => 'Lihat Produk Kami',
                            'cta_url'     => '#produk',
                            'image_path'  => null,
                        ],
                    ],
                ]],
                ['type' => 'about', 'data' => [
                    'section_id'      => 'tentang',
                    'show_in_nav'     => true,
                    'nav_label'       => 'Tentang Kami',
                    'background_style' => 'light',
                    'page_id'         => \App\Models\Page::where('slug', 'tentang')->value('id') ?? 1,
                ]],
                ['type' => 'products', 'data' => [
                    'section_id'      => 'produk',
                    'show_in_nav'     => true,
                    'nav_label'       => 'Produk',
                    'section_title'   => 'Produk Inovasi Teknologi Kesehatan',
                    'section_subtitle' => 'Berstandar Nasional, Izin Edar Kemenkes RI',
                ]],
                ['type' => 'artikel', 'data' => [
                    'section_id'    => 'insight',
                    'show_in_nav'   => true,
                    'nav_label'     => 'Insight',
                    'section_title' => 'Insight & Berita Terbaru',
                    'posts_count'   => 3,
                ]],
                ['type' => 'cta', 'data' => [
                    'section_id'      => 'cta',
                    'show_in_nav'     => false,
                    'background_style' => 'gradient',
                    'title'           => 'Siap Mengupgrade Fasilitas Radiologi Anda?',
                    'subtitle'        => 'Dukung kemandirian alat kesehatan nasional dengan menggunakan produk buatan Indonesia.',
                    'button_text'     => 'Hubungi Kami Sekarang',
                    'button_url'      => '#kontak',
                ]],
                ['type' => 'contact', 'data' => [
                    'section_id'     => 'kontak',
                    'show_in_nav'    => true,
                    'nav_label'      => 'Kontak',
                    'section_title'  => 'Hubungi Kami',
                    'section_subtitle' => 'Untuk informasi lebih lanjut mengenai produk dan layanan PT Madeena Karya Indonesia, silakan menghubungi kami',
                ]],
            ]);
        }
    }
}
