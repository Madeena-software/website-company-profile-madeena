<!DOCTYPE html>
<html lang="{{ isset($locale) ? \App\Models\Setting::normalizeLocale($locale) : 'id' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::getJson('seo')['meta_title'] ?? 'PT Madeena Karya Indonesia - Digital Radiography')</title>
    <meta name="description" content="@yield('description', \App\Models\Setting::getJson('seo')['meta_description'] ?? 'PT Madeena Karya Indonesia — produsen alat Digital Direct Radiography buatan Indonesia.')">
    @php $branding = $branding ?? \App\Models\Setting::getJson('branding', []); @endphp
    @if(!empty($branding['logo']))
    <link rel="icon" type="image/png" href="{{ route('storage.public', ['path' => $branding['logo']]) }}">
    @else
    <link rel="icon" type="image/png" href="{{ asset('images/logo-current.png') }}">
    @endif
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @if(!empty($branding['font_family']))
    @php
    $fontFamilyUrl = str_replace(' ', '+', $branding['font_family']);
    $fontFamilyCss = "'" . $branding['font_family'] . "', sans-serif";
    $customFontStyle = "<" . "style" . ">\nbody { font-family: {$fontFamilyCss}; }\n</" . "style" . ">" ;
        @endphp
        <link href="https://fonts.googleapis.com/css2?family={{ $fontFamilyUrl }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        {!! $customFontStyle !!}
        @endif

        @if(!empty($branding['primary_color']) || !empty($branding['secondary_color']))
        @php
        $primaryVar = !empty($branding['primary_color']) ? "--color-madeena-blue: {$branding['primary_color']};" : "";
        $secondaryVar = !empty($branding['secondary_color']) ? "--color-madeena-teal: {$branding['secondary_color']};" : "";
        $customColorStyle = "<" . "style" . ">
                :root {
                    {$primaryVar}
                    {$secondaryVar}
                }
                .bg-madeena-blue { background-color: var(--color-madeena-blue, #1a365d); }
                .text-madeena-blue { color: var(--color-madeena-blue, #1a365d); }
                .border-madeena-blue { border-color: var(--color-madeena-blue, #1a365d); }
                .bg-madeena-teal { background-color: var(--color-madeena-teal, #2dd4bf); }
                .text-madeena-teal { color: var(--color-madeena-teal, #2dd4bf); }
                .border-madeena-teal { border-color: var(--color-madeena-teal, #2dd4bf); }
            </" . "style" . ">" ;
            @endphp
            {!! $customColorStyle !!}
            @endif
            @stack('head')
            </head>

<body class="font-sans bg-white text-gray-900 antialiased">
    @php
    $currentCode = isset($locale) ? \App\Models\Language::normalizeCode($locale) : 'id';
    $currentLanguage = \App\Models\Language::resolve($currentCode) ?? \App\Models\Language::getDefault();
    $activeLanguages = \App\Models\Language::getActive();
    $navItems = $navItems ?? \App\Filament\Pages\HomepageEditor::getNavigation(false, $currentCode);
    $contactInfo = $contactInfo ?? \App\Models\Setting::getJson('contact_info', []);
    $socialMedia = $socialMedia ?? \App\Models\Setting::getJson('social_media', []);
    $whatsappBtn = $whatsapp ?? \App\Models\Setting::getJson('whatsapp_button', ['enabled' => true, 'number' => '']);
    $homeBaseUrl = $currentLanguage->homepageUrl();
    $previewQuery = request('preview') === 'true' ? '?preview=true' : '';
    $showLanguageSwitcher = $showLanguageSwitcher ?? false;
    @endphp

    <header class="fixed top-0 left-0 right-0 z-50 bg-madeena-blue/95 backdrop-blur-sm shadow-lg" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <a href="{{ $currentLanguage->homepageUrl() }}" class="flex items-center gap-3">
                    @if(!empty($branding['logo']))
                    <img src="{{ route('storage.public', ['path' => $branding['logo']]) }}" alt="Logo" class="h-10 w-auto">
                    @else
                    <img src="{{ asset('images/logo-current.png') }}" alt="Logo Madeena" class="h-10 w-auto">
                    <span class="text-white font-bold text-xl hidden sm:block">Madeena</span>
                    @endif
                </a>

                <nav class="hidden md:flex items-center gap-6">
                    @foreach($navItems as $item)
                    @if($item['is_external'] ?? false)
                    <a href="{{ $item['url'] }}" target="_blank" class="text-white/90 hover:text-white font-medium transition-colors">{{ $item['label'] }}</a>
                    @else
                    <a href="{{ $homeBaseUrl }}{{ $item['anchor'] ?? '' }}" class="text-white/90 hover:text-white font-medium transition-colors">{{ $item['label'] }}</a>
                    @endif
                    @endforeach

                    @if(!empty($showLanguageSwitcher) && $activeLanguages->count() > 1)
                        @if($activeLanguages->count() <= 3)
                        {{-- Compact Inline Dynamic Language Selector (<= 3 languages) --}}
                        <div class="flex items-center text-xs font-semibold border border-white/30 rounded-full px-2.5 py-1 text-white gap-1.5 ml-2" data-testid="language-switcher-desktop" data-layout="inline">
                            @foreach($activeLanguages as $idx => $l)
                                @if($idx > 0)
                                    <span class="text-white/40">|</span>
                                @endif
                                @if($l->code === $currentCode)
                                    <span class="text-white bg-white/20 px-1.5 py-0.5 rounded font-bold" title="{{ $l->native_name }}">{{ strtoupper($l->code) }}</span>
                                @else
                                    <a href="{{ $l->homepageUrl($previewQuery !== '') }}" class="text-white/70 hover:text-white transition-colors" title="{{ $l->native_name }}">{{ strtoupper($l->code) }}</a>
                                @endif
                            @endforeach
                        </div>
                        @else
                        {{-- Scalable Dropdown Language Selector (> 3 languages) --}}
                        <div class="relative ml-2" x-data="{ langOpen: false }" @click.outside="langOpen = false" data-testid="language-switcher-desktop" data-layout="dropdown">
                            <button type="button" @click="langOpen = !langOpen" class="flex items-center gap-1.5 text-xs font-semibold border border-white/30 rounded-full px-3 py-1 text-white hover:bg-white/10 transition-colors" aria-expanded="false" data-testid="language-dropdown-toggle">
                                <i class="fas fa-globe text-white/80"></i>
                                <span>{{ strtoupper($currentCode) }}</span>
                                <i class="fas fa-chevron-down text-[10px] text-white/70 transition-transform duration-200" :class="{ 'rotate-180': langOpen }"></i>
                            </button>
                            <div x-show="langOpen" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1.5 z-50 overflow-hidden" style="display: none;" data-testid="language-dropdown-menu">
                                @foreach($activeLanguages as $l)
                                    <a href="{{ $l->homepageUrl($previewQuery !== '') }}" class="flex items-center justify-between px-4 py-2 text-xs font-medium transition-colors {{ $l->code === $currentCode ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700' }}" data-testid="lang-link-{{ $l->code }}">
                                        <span>{{ $l->native_name }}</span>
                                        <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded {{ $l->code === $currentCode ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">{{ $l->code }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endif
                </nav>

                <button @click="open = !open" class="md:hidden text-white p-2">
                    <i class="fas fa-bars text-xl" x-show="!open"></i>
                    <i class="fas fa-times text-xl" x-show="open"></i>
                </button>
            </div>

            <div x-show="open" x-transition class="md:hidden pb-4 border-t border-white/20 mt-2 pt-4 space-y-2">
                @foreach($navItems as $item)
                @if($item['is_external'] ?? false)
                <a href="{{ $item['url'] }}" target="_blank" class="block text-white/90 hover:text-white font-medium py-2 transition-colors">{{ $item['label'] }}</a>
                @else
                <a href="{{ $homeBaseUrl }}{{ $item['anchor'] ?? '' }}" class="block text-white/90 hover:text-white font-medium py-2 transition-colors">{{ $item['label'] }}</a>
                @endif
                @endforeach

                @if(!empty($showLanguageSwitcher) && $activeLanguages->count() > 1)
                    @if($activeLanguages->count() <= 3)
                    {{-- Mobile Inline Dynamic Language Selector (<= 3 languages) --}}
                    <div class="pt-2 border-t border-white/10 flex items-center justify-between px-1" data-testid="language-switcher-mobile" data-layout="inline">
                        <span class="text-xs text-white/70 font-medium">{{ $currentLanguage->getUiLabel('language', 'Bahasa') }}:</span>
                        <div class="inline-flex items-center text-xs font-semibold border border-white/30 rounded-full px-2.5 py-1 text-white gap-1.5">
                            @foreach($activeLanguages as $idx => $l)
                                @if($idx > 0)
                                    <span class="text-white/40">|</span>
                                @endif
                                @if($l->code === $currentCode)
                                    <span class="text-white bg-white/20 px-1.5 py-0.5 rounded font-bold" title="{{ $l->native_name }}">{{ strtoupper($l->code) }}</span>
                                @else
                                    <a href="{{ $l->homepageUrl($previewQuery !== '') }}" class="text-white/70 hover:text-white transition-colors" title="{{ $l->native_name }}">{{ strtoupper($l->code) }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @else
                    {{-- Mobile Scalable Dropdown/Grid Selector (> 3 languages) --}}
                    <div class="pt-2 border-t border-white/10 space-y-2 px-1" data-testid="language-switcher-mobile" data-layout="dropdown" x-data="{ mobileLangOpen: false }">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-white/70 font-medium">{{ $currentLanguage->getUiLabel('language', 'Bahasa') }}:</span>
                            <button type="button" @click="mobileLangOpen = !mobileLangOpen" class="inline-flex items-center gap-1.5 text-xs font-semibold border border-white/30 rounded-full px-3 py-1 text-white bg-white/10" data-testid="mobile-language-dropdown-toggle">
                                <i class="fas fa-globe text-white/80"></i>
                                <span>{{ $currentLanguage->native_name }} ({{ strtoupper($currentCode) }})</span>
                                <i class="fas fa-chevron-down text-[10px] text-white/70 transition-transform duration-200" :class="{ 'rotate-180': mobileLangOpen }"></i>
                            </button>
                        </div>
                        <div x-show="mobileLangOpen" x-transition class="grid grid-cols-2 gap-1.5 pt-2 bg-black/20 p-2 rounded-xl border border-white/10" style="display: none;" data-testid="mobile-language-menu">
                            @foreach($activeLanguages as $l)
                                <a href="{{ $l->homepageUrl($previewQuery !== '') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs {{ $l->code === $currentCode ? 'bg-white text-madeena-blue font-bold shadow-sm' : 'text-white/80 hover:bg-white/10' }}" data-testid="mobile-lang-link-{{ $l->code }}">
                                    <span class="truncate">{{ $l->native_name }}</span>
                                    <span class="text-[10px] font-mono ml-1 opacity-70">{{ strtoupper($l->code) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-madeena-blue text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        @if(!empty($branding['logo']))
                        <img src="{{ route('storage.public', ['path' => $branding['logo']]) }}" alt="Logo" class="h-10 w-auto">
                        @else
                        <img src="{{ asset('images/logo-current.png') }}" alt="Logo Madeena" class="h-10 w-auto">
                        <span class="font-bold text-xl">Madeena</span>
                        @endif
                    </div>
                    <p class="text-white/70 text-sm leading-relaxed">
                        @php
                        $seoDesc = \App\Models\Setting::getJson('seo')['meta_description'] ?? 'PT Madeena Karya Indonesia — Produsen alat Digital Radiography buatan Indonesia.';
                        @endphp
                        {{ $seoDesc }}
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold text-lg mb-4">{{ $currentLanguage->getUiLabel('navigation', 'Navigasi') }}</h4>
                    <ul class="space-y-2 text-white/70">
                        @foreach($navItems as $item)
                        @if($item['is_external'] ?? false)
                        <li><a href="{{ $item['url'] }}" target="_blank" class="hover:text-white transition-colors">{{ $item['label'] }}</a></li>
                        @else
                        <li><a href="{{ $homeBaseUrl }}{{ $item['anchor'] ?? '' }}" class="hover:text-white transition-colors">{{ $item['label'] }}</a></li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-lg mb-4">{{ $currentLanguage->getUiLabel('contact', 'Kontak') }}</h4>
                    <ul class="space-y-2 text-white/70 text-sm">
                        @if(!empty($contactInfo['email']))
                        <li class="flex items-start gap-2">
                            <i class="fas fa-envelope mt-1"></i>
                            <a href="mailto:{{ $contactInfo['email'] }}" class="hover:text-white transition-colors">
                                {{ $contactInfo['email'] }}
                            </a>
                        </li>
                        @endif
                        @if(!empty($contactInfo['phone']))
                        <li class="flex items-start gap-2">
                            <i class="fas fa-phone mt-1"></i>
                            <a href="tel:{{ preg_replace('/\s/', '', $contactInfo['phone']) }}" class="hover:text-white transition-colors">
                                {{ $contactInfo['phone'] }}
                            </a>
                        </li>
                        @endif
                        @if(!empty($contactInfo['whatsapp']))
                        <li class="flex items-start gap-2">
                            <i class="fab fa-whatsapp mt-1"></i>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $contactInfo['whatsapp']) }}" target="_blank" class="hover:text-white transition-colors">
                                {{ $contactInfo['whatsapp'] }}
                            </a>
                        </li>
                        @endif
                        @if(!empty($contactInfo['address']))
                        <li class="flex items-start gap-2">
                            <i class="fas fa-map-marker-alt mt-1 flex-shrink-0"></i>
                            <span>{{ $contactInfo['address'] }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/20 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex gap-4">
                    @if(!empty($socialMedia['instagram']))
                    <a href="{{ $socialMedia['instagram'] }}" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    @endif
                    @if(!empty($socialMedia['linkedin']))
                    <a href="{{ $socialMedia['linkedin'] }}" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    @endif
                    @if(!empty($socialMedia['youtube']))
                    <a href="{{ $socialMedia['youtube'] }}" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fab fa-youtube text-xl"></i>
                    </a>
                    @endif
                    @if(!empty($contactInfo['whatsapp']))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $contactInfo['whatsapp']) }}" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </a>
                    @endif
                    @if(!empty($contactInfo['email']))
                    <a href="mailto:{{ $contactInfo['email'] }}" class="text-white/70 hover:text-white transition-colors">
                        <i class="fas fa-envelope text-xl"></i>
                    </a>
                    @endif
                </div>
                <p class="text-white/50 text-sm">v{{ config('app.version', '1.0') }} &copy; {{ date('Y') }} PT Madeena Karya Indonesia. {{ $currentLanguage->getUiLabel('all_rights_reserved', 'Seluruh hak dilindungi.') }}</p>
            </div>
        </div>
    </footer>

    @if(!empty($whatsappBtn['enabled']) && !empty($whatsappBtn['number']))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsappBtn['number']) }}"
        target="_blank" rel="noopener" aria-label="Chat WhatsApp"
        class="fixed bottom-6 right-6 z-50 bg-green-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform duration-200 @if(!empty($isPreview)) mb-14 @endif">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
    @endif

    @if(!empty($isPreview))
    <div style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999; background-color: #ea580c; color: white; text-align: center; padding: 12px 16px; box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 12px;">
        <i class="fas fa-info-circle" style="font-size: 1.25rem;"></i>
        <span style="font-weight: 500; font-size: 0.875rem;">{{ $currentLanguage->getUiLabel('preview_mode_notice', 'Anda sedang melihat Mode Pratinjau (Draft).') }}</span>
        <a href="{{ url('/admin/homepage-editor') }}" style="margin-left: 16px; padding: 6px 16px; background-color: white; color: #ea580c; border-radius: 6px; font-weight: bold; font-size: 0.875rem; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">{{ $currentLanguage->getUiLabel('back', 'Kembali') }}</a>
    </div>
    @endif

    @stack('scripts')
</body>

</html>