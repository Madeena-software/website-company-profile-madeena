{{-- sections/cta.blade.php --}}
@php
    $bg = match($data['background_style'] ?? 'gradient') {
        'light'    => 'bg-madeena-light',
        'white'    => 'bg-white',
        'dark'     => 'bg-madeena-blue text-white',
        default    => 'bg-gradient-to-br from-madeena-blue to-teal-800 text-white',
    };
    $isDark = in_array($data['background_style'] ?? 'gradient', ['dark', 'gradient']);
@endphp
<section id="{{ $data['section_id'] ?? 'cta' }}" class="py-20 {{ $bg }} relative overflow-hidden">
    @if($isDark)
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style='background-image: url("data:image/svg+xml,%3Csvg width=&apos;60&apos; height=&apos;60&apos; viewBox=&apos;0 0 60 60&apos; xmlns=&apos;http://www.w3.org/2000/svg&apos;%3E%3Cg fill=&apos;none&apos; fill-rule=&apos;evenodd&apos;%3E%3Cg fill=&apos;%23ffffff&apos; fill-opacity=&apos;0.4&apos;%3E%3Cpath d=&apos;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&apos;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")'></div>
    </div>
    @endif
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h2 class="text-3xl md:text-5xl font-bold {{ $isDark ? 'text-white' : 'text-madeena-blue' }} mb-6">
            {{ $data['title'] ?? 'Siap Membangun Kemitraan?' }}
        </h2>
        @if(!empty($data['subtitle']))
        <div class="text-lg md:text-xl {{ $isDark ? 'text-white/80 [&_strong]:text-white' : 'text-gray-600 [&_strong]:text-gray-900' }} mb-10 leading-relaxed space-y-4 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-3 [&_ul]:inline-block [&_ul]:text-left [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-3 [&_ol]:inline-block [&_ol]:text-left [&_li]:mb-1 {{ $isDark ? '[&_a]:text-madeena-teal hover:[&_a]:text-teal-300' : '[&_a]:text-madeena-blue hover:[&_a]:text-madeena-teal' }} [&_a]:underline [&_strong]:font-semibold">
            {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($data['subtitle'])->toHtml() !!}
        </div>
        @endif
        @if(!empty($data['button_text']) && !empty($data['button_url']))
        <a href="{{ $data['button_url'] }}" class="inline-block bg-madeena-teal hover:bg-teal-500 text-white font-bold text-lg px-10 py-4 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            {{ $data['button_text'] }}
        </a>
        @endif
    </div>
</section>
