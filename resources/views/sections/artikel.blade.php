@php
    $posts = $section['posts'] ?? collect();
    $currentLang = $language ?? \App\Models\Language::resolve($locale ?? null) ?? \App\Models\Language::getDefault();
    $articlesTitle = $data['section_title'] ?? $currentLang->getUiLabel('articles', 'Artikel');
@endphp
@if($posts->isNotEmpty())
<section id="{{ $data['section_id'] ?? 'blog' }}" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="section-title">{{ $data['section_title'] ?? 'Artikel Terbaru' }}</h2>
            @if(!empty($data['section_subtitle']))
            <p class="text-lg text-gray-600 mt-4">{{ $data['section_subtitle'] }}</p>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <article class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 group">
                @if($post->cover_image)
                <div class="aspect-video overflow-hidden">
                    <img src="{{ route('storage.public', ['path' => $post->cover_image]) }}"
                         alt="{{ $post->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                @endif
                <div class="p-6">
                    @if($post->category)
                    <span class="inline-block text-xs font-semibold text-madeena-teal bg-madeena-teal/10 px-2 py-1 rounded mb-3">{{ $post->category }}</span>
                    @endif
                    <h3 class="text-lg font-bold text-madeena-blue mb-2 group-hover:text-madeena-teal transition-colors">
                        <a href="{{ route('post.show', ['post' => $post->slug ?: $post->id]) }}">{{ $post->title }}</a>
                    </h3>
                    @if($post->excerpt)
                    <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">{{ $post->excerpt }}</p>
                    @endif
                    <div class="mt-4 flex items-center justify-between">
                        @if($post->published_at)
                        <span class="text-gray-400 text-xs">{{ $post->published_at->format('d M Y') }}</span>
                        @endif
                        <a href="{{ route('post.show', ['post' => $post->slug ?: $post->id]) }}"
                           class="text-madeena-teal font-semibold text-sm hover:text-madeena-blue transition-colors">
                            {{ $currentLang->getUiLabel('read', 'Baca') }} <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('artikel.index') }}" class="btn-secondary">{{ $currentLang->getUiLabel('view_all', ['title' => $articlesTitle]) }}</a>
        </div>
    </div>
</section>
@endif
