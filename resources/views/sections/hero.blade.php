{{-- sections/hero.blade.php --}}
@php $banners = $data['banners'] ?? []; @endphp
<section id="{{ $data['section_id'] ?? 'banner' }}" class="relative min-h-screen flex items-center bg-gradient-to-br from-madeena-blue via-madeena-blue to-teal-800 pt-20">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style='background-image: url("data:image/svg+xml,%3Csvg width=&apos;60&apos; height=&apos;60&apos; viewBox=&apos;0 0 60 60&apos; xmlns=&apos;http://www.w3.org/2000/svg&apos;%3E%3Cg fill=&apos;none&apos; fill-rule=&apos;evenodd&apos;%3E%3Cg fill=&apos;%23ffffff&apos; fill-opacity=&apos;0.4&apos;%3E%3Cpath d=&apos;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&apos;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")'></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
        @if(count($banners) > 0)
            @php $hero = $banners[0]; @endphp
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-7 xl:col-span-8">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-5xl xl:text-6xl font-extrabold text-white leading-tight mb-6 break-words">
                        {{ $hero['title'] ?? '' }}
                    </h1>
                    @if(!empty($hero['subtitle']))
                    <p class="text-xl md:text-2xl font-medium text-madeena-teal mb-6">{{ $hero['subtitle'] }}</p>
                    @endif
                    @if(!empty($hero['description']))
                    <div class="text-white/80 text-lg leading-relaxed mb-8 space-y-4 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-3 [&_li]:mb-1 [&_a]:text-madeena-teal [&_a]:underline hover:[&_a]:text-teal-300 [&_strong]:text-white [&_strong]:font-semibold">
                        {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($hero['description'])->toHtml() !!}
                    </div>
                    @endif
                    @if(!empty($hero['cta_text']) && !empty($hero['cta_url']))
                    <a href="{{ $hero['cta_url'] }}" class="btn-primary text-lg">{{ $hero['cta_text'] }}</a>
                    @endif
                </div>
                <div class="lg:col-span-5 xl:col-span-4 flex justify-center lg:justify-end">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-madeena-teal/20 rounded-full blur-2xl"></div>
                        @if(!empty($hero['image_path']))
                        <img src="{{ route('storage.public', ['path' => $hero['image_path']]) }}"
                             alt="{{ $hero['title'] ?? '' }}"
                             class="relative w-64 h-64 md:w-80 md:h-80 object-cover rounded-2xl drop-shadow-2xl">
                        @else
                        <img src="{{ asset('images/logo-current.png') }}"
                             alt="Logo PT Madeena Karya Indonesia"
                             class="relative w-64 h-64 md:w-80 md:h-80 object-contain drop-shadow-2xl">
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <a href="#" class="text-white/60 hover:text-white transition-colors">
            <i class="fas fa-chevron-down text-2xl"></i>
        </a>
    </div>
</section>
