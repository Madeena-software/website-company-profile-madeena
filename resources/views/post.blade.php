@extends('layouts.app')

@section('title', $post->title . ' - PT Madeena Karya Indonesia')

@push('styles')
    @vite('resources/css/academic-article.css')
@endpush

@push('scripts')
    @vite('resources/js/katex-render.js')
@endpush

@section('content')
<div class="pt-24 pb-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('artikel.index') }}" class="inline-flex items-center gap-2 text-madeena-teal hover:text-madeena-blue transition-colors mb-8">
            <i class="fas fa-arrow-left"></i> Kembali ke Artikel
        </a>

        <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
            @if($post->cover_image)
            <div class="aspect-video overflow-hidden">
                <img src="{{ route('storage.public', ['path' => $post->cover_image]) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
            @endif
            <div class="p-8">
                @if($post->category)
                <span class="inline-block text-xs font-semibold text-madeena-teal bg-madeena-teal/10 px-3 py-1 rounded-full mb-4">{{ $post->category }}</span>
                @endif
                <h1 class="text-3xl font-bold text-madeena-blue mb-4">{{ $post->title }}</h1>
                
                @if ($post->authors_info)
                    <div class="academic-authors mb-4 text-gray-600">
                        @foreach ($post->authors_info as $author)
                            <span class="font-semibold">{{ $author['name'] }}</span>
                            @if (!empty($author['affiliation']))
                                <span class="affiliation italic">({{ $author['affiliation'] }})</span>
                            @endif
                            @if (!$loop->last) &bull; @endif
                        @endforeach
                    </div>
                @endif

                @if($post->published_at)
                <p class="text-gray-400 text-sm mb-6">{{ $post->published_at->format('d F Y') }}</p>
                @endif
                
                @if ($post->abstract)
                    <div class="academic-abstract bg-gray-50 border-l-4 border-blue-500 p-4 my-6 italic text-gray-700">
                        <strong>{{ $post->content_language === 'en' ? 'Abstract' : 'Abstrak' }}:</strong>
                        {{ $post->abstract }}
                    </div>
                @endif

                @if ($post->keywords)
                    <div class="academic-keywords my-4">
                        <strong>{{ $post->content_language === 'en' ? 'Keywords' : 'Kata Kunci' }}:</strong>
                        @foreach ($post->keywords as $keyword)
                            <span class="keyword inline-block bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm mr-2 mb-2">{{ $keyword }}</span>
                        @endforeach
                    </div>
                @endif

                @if($post->content_json)
                <div class="mt-8">
                    <x-academic-content
                        :content="$post->content_json"
                        :language="$post->content_language"
                        :enableAutoNumbering="$post->enable_auto_numbering"
                    />
                </div>
                @endif
            </div>
        </article>
    </div>
</div>
@endsection