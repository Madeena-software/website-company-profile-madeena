<?php

namespace App\Filament;

use App\Filament\RichEditorBlocks\EquationBlock;
use App\Filament\RichEditorBlocks\FigureBlock;
use App\Filament\RichEditorBlocks\ReferenceListBlock;
use App\Filament\RichEditorBlocks\TableBlock;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class BuilderBlocks
{
    public static function get(): array
    {
        return [
            self::heroBlock(),
            self::productsBlock(),
            self::featuresBlock(),
            self::aboutBlock(),
            self::legalitiesBlock(),
            self::contactBlock(),
            self::artikelBlock(),
            self::galleryBlock(),
            self::testimonialBlock(),
            self::videoBlock(),
            self::freeTextBlock(),
            self::teamBlock(),
            self::faqBlock(),
            self::timelineBlock(),
            self::partnersBlock(),
            self::statsBlock(),
            self::ctaBlock(),
            self::mapBlock(),
            self::pricingBlock(),
            self::currentProjectsBlock(),
            self::projectInvestmentBlock(),
        ];
    }

    public static function getProductBlocks(): array
    {
        return [
            self::freeTextBlock(),
            self::galleryBlock(),
            self::videoBlock(),
            self::featuresBlock(),
            self::testimonialBlock(),
        ];
    }

    private static function getIconOptions(): array
    {
        return [
            'fa-building' => '🏢 Gedung',
            'fa-certificate' => '📜 Sertifikat',
            'fa-award' => '🏆 Penghargaan',
            'fa-shield-alt' => '🛡️ Keamanan',
            'fa-university' => '🏛️ Universitas',
            'fa-file-contract' => '📋 Kontrak',
            'fa-brain' => '🧠 Otak / AI',
            'fa-network-wired' => '🌐 Jaringan',
            'fa-hospital' => '🏥 Rumah Sakit',
            'fa-x-ray' => '🩻 X-Ray',
            'fa-beaker' => '🧪 Laboratorium',
            'fa-microscope' => '🔬 Mikroskop',
            'fa-heartbeat' => '💓 Kesehatan',
            'fa-stethoscope' => '🩺 Stetoskop',
            'fa-handshake' => '🤝 Kemitraan',
            'fa-globe' => '🌍 Global',
            'fa-chart-line' => '📈 Grafik',
            'fa-cogs' => '⚙️ Teknologi',
            'fa-users' => '👥 Tim',
            'fa-phone' => '📞 Telepon',
            'fa-envelope' => '📧 Email',
            'fa-map-marker-alt' => '📍 Lokasi',
            'fa-star' => '⭐ Bintang',
            'fa-check-circle' => '✅ Centang',
            'fa-lightbulb' => '💡 Ide',
            'fa-rocket' => '🚀 Roket',
            'fa-wrench' => '🔧 Alat',
            'fa-camera' => '📷 Kamera',
            'fa-book' => '📖 Buku',
            'fa-graduation-cap' => '🎓 Pendidikan',
        ];
    }

    private static function getNavFields($defaultShow = false, $defaultLabel = ''): array
    {
        return [
            TextInput::make('section_id')
                ->label('ID Bagian (Anchor)')
                ->default(fn() => uniqid('sec-'))
                ->helperText('Otomatis dibuat, tapi bisa diganti untuk link navigasi (#id).'),
            Toggle::make('show_in_nav')->label('Tampilkan di Navigasi')->default($defaultShow),
            TextInput::make('nav_label')->label('Label Navigasi')->default($defaultLabel),
        ];
    }

    private static function heroBlock(): Block
    {
        return Block::make('hero')
            ->label('🖼️ Hero Banner')
            ->icon('heroicon-o-photo')
            ->schema(array_merge(self::getNavFields(false, 'Beranda'), [
                Repeater::make('banners')
                    ->label('Slide Banner')
                    ->schema([
                        TextInput::make('title')->label('Judul')->required(),
                        TextInput::make('subtitle')->label('Subjudul'),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->columnSpanFull(),
                        FileUpload::make('image_path')->label('Gambar Banner')->image()->disk('public')->directory('banners'),
                        TextInput::make('cta_text')->label('Teks Tombol'),
                        TextInput::make('cta_url')->label('URL Tombol'),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->addActionLabel('+ Tambah Slide')
                    ->helperText('Tambahkan beberapa slide untuk carousel banner.'),
            ]));
    }

    private static function productsBlock(): Block
    {
        return Block::make('products')
            ->label('📦 Produk')
            ->icon('heroicon-o-beaker')
            ->schema(array_merge(self::getNavFields(true, 'Produk'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Produk Inovasi Teknologi Kesehatan'),
                TextInput::make('section_subtitle')->label('Subjudul')->default('Berstandar Nasional, Izin Edar Kemenkes RI'),
            ]))
        ;
    }

    private static function aboutBlock(): Block
    {
        return Block::make('about')
            ->label('🏢 Tentang Kami')
            ->icon('heroicon-o-building-office')
            ->schema(array_merge(self::getNavFields(true, 'Tentang'), [
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('white'),
                Select::make('page_id')
                    ->label('Pilih Halaman')
                    ->options(\App\Models\Page::pluck('title', 'id'))
                    ->searchable()
                    ->helperText('Pilih halaman yang berisi profil perusahaan lengkap.'),
            ]));
    }

    private static function featuresBlock(): Block
    {
        return Block::make('features')
            ->label('⭐ Keunggulan')
            ->icon('heroicon-o-star')
            ->schema(array_merge(self::getNavFields(false, 'Keunggulan'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Keunggulan Teknologi'),
                Repeater::make('items')
                    ->label('Kartu Keunggulan')
                    ->schema([
                        Select::make('icon')
                            ->label('Ikon')
                            ->options(self::getIconOptions())
                            ->searchable()
                            ->helperText('Pilih ikon yang sesuai.'),
                        TextInput::make('title')->label('Judul')->required(),
                        Textarea::make('description')->label('Deskripsi')->rows(2),
                    ])
                    ->columns(1)
                    ->reorderable()
                    ->addActionLabel('+ Tambah Keunggulan'),
            ]));
    }

    private static function legalitiesBlock(): Block
    {
        return Block::make('legalities')
            ->label('📜 Sertifikasi')
            ->icon('heroicon-o-shield-check')
            ->schema(array_merge(self::getNavFields(true, 'Legalitas'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Legalitas Formal'),
                TextInput::make('section_subtitle')->label('Subjudul'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('dark'),
                Repeater::make('certificates')
                    ->label('Daftar Sertifikasi')
                    ->schema([
                        Select::make('icon')
                            ->label('Ikon')
                            ->options(self::getIconOptions())
                            ->searchable(),
                        TextInput::make('title')->label('Nama Sertifikasi')->required(),
                        TextInput::make('detail')->label('Nomor / Detail')->required(),
                    ])
                    ->columns(1)
                    ->reorderable()
                    ->addActionLabel('+ Tambah Sertifikasi'),
            ]));
    }

    private static function contactBlock(): Block
    {
        return Block::make('contact')
            ->label('📞 Kontak')
            ->icon('heroicon-o-phone')
            ->schema(array_merge(self::getNavFields(true, 'Kontak'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Hubungi Kami'),
                TextInput::make('section_subtitle')->label('Subjudul'),
            ]))
        ;
    }

    private static function artikelBlock(): Block
    {
        return Block::make('artikel')
            ->label('📝 Artikel')
            ->icon('heroicon-o-newspaper')
            ->schema(array_merge(self::getNavFields(false, 'Artikel'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Artikel Terbaru'),
                TextInput::make('section_subtitle')->label('Subjudul Bagian')->default('Artikel terbaru tentang inovasi teknologi kesehatan dan perkembangan industri medis'),
                TextInput::make('category_filter')->label('Filter Penempatan (Seksi)')
                    ->placeholder('Misal: Insight, Current Project, dsb')
                    ->helperText('Ketik nama penempatan. Nama ini akan otomatis menjadi pilihan "Penempatan di Halaman Utama" saat Anda membuat postingan baru.'),
                TextInput::make('posts_count')->label('Jumlah Artikel Ditampilkan')->numeric()->default(3)
                    ->helperText('Berapa artikel terbaru yang ditampilkan.'),
            ]));
    }

    private static function galleryBlock(): Block
    {
        return Block::make('gallery')
            ->label('🖼️ Galeri Foto')
            ->icon('heroicon-o-photo')
            ->schema(array_merge(self::getNavFields(false, 'Galeri'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Galeri'),
                Repeater::make('images')
                    ->label('Foto')
                    ->schema([
                        FileUpload::make('image')->label('Gambar')->image()->disk('public')->directory('gallery')->required(),
                        TextInput::make('caption')->label('Keterangan'),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Foto'),
            ]));
    }

    private static function testimonialBlock(): Block
    {
        return Block::make('testimonial')
            ->label('💬 Testimoni')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->schema(array_merge(self::getNavFields(false, 'Testimoni'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Testimoni'),
                Repeater::make('testimonials')
                    ->label('Daftar Testimoni')
                    ->schema([
                        FileUpload::make('photo')->label('Foto')->image()->disk('public')->directory('testimonials'),
                        TextInput::make('name')->label('Nama')->required(),
                        TextInput::make('role')->label('Jabatan / Instansi'),
                        Textarea::make('quote')->label('Testimoni')->rows(3)->required(),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Testimoni'),
            ]));
    }

    private static function videoBlock(): Block
    {
        return Block::make('video')
            ->label('📹 Video')
            ->icon('heroicon-o-video-camera')
            ->schema(array_merge(self::getNavFields(false, 'Video'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Video'),
                TextInput::make('video_url')->label('URL Video (YouTube/Vimeo)')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText('Tempel URL video dari YouTube atau Vimeo.')
                    ->required(),
                TextInput::make('video_caption')->label('Keterangan Video'),
            ]));
    }

    private static function freeTextBlock(): Block
    {
        return Block::make('free_text')
            ->label('📄 Teks Bebas')
            ->icon('heroicon-o-document-text')
            ->schema(array_merge(self::getNavFields(false, 'Info'), [
                TextInput::make('section_title')->label('Judul Bagian'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('white'),
                RichEditor::make('content')
                    ->label('Konten')
                    ->json()
                    ->customBlocks([
                        FigureBlock::class,
                        TableBlock::class,
                        EquationBlock::class,
                        ReferenceListBlock::class,
                    ])
                    ->columnSpanFull(),
            ]));
    }

    private static function teamBlock(): Block
    {
        return Block::make('team')
            ->label('👥 Tim Kami')
            ->icon('heroicon-o-users')
            ->schema(array_merge(self::getNavFields(false, 'Tim'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Struktur Organisasi'),
                TextInput::make('section_subtitle')->label('Subjudul'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('light'),
                Repeater::make('members')
                    ->label('Anggota Tim')
                    ->schema([
                        FileUpload::make('photo')->label('Foto')->image()->disk('public')->directory('team'),
                        TextInput::make('name')->label('Nama')->required(),
                        TextInput::make('role')->label('Jabatan')->required(),
                        Textarea::make('bio')->label('Bio Singkat')->rows(2),
                        TextInput::make('linkedin')->label('URL LinkedIn')->url(),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Anggota'),
            ]));
    }

    private static function faqBlock(): Block
    {
        return Block::make('faq')
            ->label('❓ FAQ')
            ->icon('heroicon-o-question-mark-circle')
            ->schema(array_merge(self::getNavFields(false, 'FAQ'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Pertanyaan Umum'),
                TextInput::make('section_subtitle')->label('Subjudul'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('white'),
                Repeater::make('faqs')
                    ->label('Daftar Pertanyaan')
                    ->schema([
                        TextInput::make('question')->label('Pertanyaan')->required(),
                        Textarea::make('answer')->label('Jawaban')->rows(3)->required(),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah FAQ'),
            ]));
    }

    private static function timelineBlock(): Block
    {
        return Block::make('timeline')
            ->label('⏳ Timeline Sejarah')
            ->icon('heroicon-o-clock')
            ->schema(array_merge(self::getNavFields(false, 'Sejarah'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Perjalanan Kami'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('light'),
                Repeater::make('milestones')
                    ->label('Milestone')
                    ->schema([
                        TextInput::make('year')->label('Tahun / Waktu')->required(),
                        TextInput::make('title')->label('Judul Pencapaian')->required(),
                        Textarea::make('description')->label('Deskripsi')->rows(2),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Milestone'),
            ]));
    }

    private static function partnersBlock(): Block
    {
        return Block::make('partners')
            ->label('🤝 Mitra')
            ->icon('heroicon-o-user-group')
            ->schema(array_merge(self::getNavFields(false, 'Mitra'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Mitra Kami'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('white'),
                Repeater::make('partners')
                    ->label('Mitra & Klien')
                    ->schema([
                        FileUpload::make('logo')->label('Logo Mitra')->image()->disk('public')->directory('partners')->required(),
                        TextInput::make('name')->label('Nama Mitra'),
                        TextInput::make('url')->label('URL Website')->url(),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Mitra'),
            ]));
    }

    private static function statsBlock(): Block
    {
        return Block::make('stats')
            ->label('📊 Statistik Pencapaian')
            ->icon('heroicon-o-chart-bar')
            ->schema(array_merge(self::getNavFields(false, 'Statistik'), [
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('dark'),
                Repeater::make('stats')
                    ->label('Angka Statistik')
                    ->schema([
                        TextInput::make('number')->label('Angka (misal: 100+)')->required(),
                        TextInput::make('label')->label('Label (misal: Klien)')->required(),
                        Select::make('icon')->label('Ikon')->options(self::getIconOptions())->searchable(),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Statistik')
                    ->maxItems(4),
            ]));
    }

    private static function ctaBlock(): Block
    {
        return Block::make('cta')
            ->label('📢 Call to Action')
            ->icon('heroicon-o-megaphone')
            ->schema(array_merge(self::getNavFields(false, 'CTA'), [
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('gradient'),
                TextInput::make('title')->label('Judul Ajakan')->required(),
                RichEditor::make('subtitle')
                    ->label('Subjudul Deskripsi')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ]),
                TextInput::make('button_text')->label('Teks Tombol')->required(),
                TextInput::make('button_url')->label('URL Tombol')->required(),
            ]));
    }

    private static function mapBlock(): Block
    {
        return Block::make('map')
            ->label('📍 Lokasi Peta')
            ->icon('heroicon-o-map')
            ->schema(array_merge(self::getNavFields(false, 'Lokasi'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Temukan Kami'),
                TextInput::make('address')->label('Alamat Lengkap (Teks)'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('white'),
                Textarea::make('embed_url')
                    ->label('URL Embed Google Maps (src attribute)')
                    ->helperText('Contoh: https://www.google.com/maps/embed?pb=...')
                    ->rows(3)
                    ->required(),
            ]));
    }

    private static function pricingBlock(): Block
    {
        return Block::make('pricing')
            ->label('💳 Daftar Harga')
            ->icon('heroicon-o-currency-dollar')
            ->schema(array_merge(self::getNavFields(false, 'Harga'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Daftar Harga & Paket'),
                TextInput::make('section_subtitle')->label('Subjudul'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('light'),
                Repeater::make('plans')
                    ->label('Paket / Produk')
                    ->schema([
                        TextInput::make('name')->label('Nama Paket')->required(),
                        TextInput::make('price')->label('Harga (Teks)')->required()->default('Hubungi Kami'),
                        Toggle::make('is_featured')->label('Jadikan Paket Unggulan (Highlight)'),
                        Repeater::make('features')
                            ->label('Fitur / Spesifikasi')
                            ->simple(TextInput::make('item')->label('Fitur')->required())
                            ->addActionLabel('+ Tambah Fitur'),
                        TextInput::make('button_text')->label('Teks Tombol')->default('Pilih Paket'),
                        TextInput::make('button_url')->label('URL Tombol')->default('#kontak'),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Paket'),
            ]));
    }

    private static function currentProjectsBlock(): Block
    {
        return Block::make('current_projects')
            ->label('🚧 Proyek Berjalan (Desain Lama)')
            ->icon('heroicon-o-clipboard-document-list')
            ->schema(array_merge(self::getNavFields(false, 'Proyek'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Proyek Berjalan'),
                TextInput::make('section_subtitle')->label('Subjudul'),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('light'),
                Repeater::make('projects')
                    ->label('Daftar Proyek')
                    ->schema([
                        FileUpload::make('image')->label('Gambar Proyek')->image()->disk('public')->directory('projects'),
                        TextInput::make('title')->label('Nama Proyek')->required(),
                        Textarea::make('description')->label('Deskripsi Singkat')->rows(2),
                        TextInput::make('progress')->label('Progress (%)')->numeric()->minValue(0)->maxValue(100),
                    ])
                    ->reorderable()
                    ->addActionLabel('+ Tambah Proyek'),
            ]));
    }

    private static function projectInvestmentBlock(): Block
    {
        return Block::make('project_investment')
            ->label('💼 Peluang Investasi (Desain Lama)')
            ->icon('heroicon-o-banknotes')
            ->schema(array_merge(self::getNavFields(false, 'Investasi'), [
                TextInput::make('section_title')->label('Judul Bagian')->default('Peluang Investasi Proyek'),
                Textarea::make('description')->label('Deskripsi Peluang')->rows(3),
                Select::make('background_style')->label('Gaya Latar')->options([
                    'white' => 'Putih',
                    'light' => 'Abu-Abu Muda',
                    'dark' => 'Gelap',
                    'gradient' => 'Gradien',
                ])->default('dark'),
                TextInput::make('target_funding')->label('Target Pendanaan (Rp)'),
                TextInput::make('roi')->label('Estimasi ROI / Keuntungan'),
                Repeater::make('highlights')
                    ->label('Highlight / Keuntungan Investor')
                    ->simple(TextInput::make('item')->label('Poin Highlight')->required())
                    ->addActionLabel('+ Tambah Poin'),
                TextInput::make('button_text')->label('Teks Tombol (Pitch Deck)'),
                TextInput::make('button_url')->label('URL File Pitch Deck'),
            ]));
    }
}
