@extends('layouts.app')

@section('title', $seo['meta_title'] ?? 'PT Madeena Karya Indonesia - Digital Radiography Indonesia')

@section('description', $seo['meta_description'] ?? 'PT Madeena Karya Indonesia — produsen alat Digital Direct Radiography buatan Indonesia.')

@section('content')

@php
    $currentLang = $language ?? \App\Models\Language::resolve($locale ?? null) ?? \App\Models\Language::getDefault();
@endphp

@if(!empty($sections))
    @foreach($sections as $index => $section)
        @if(View::exists('sections.' . $section['type']))
            @include('sections.' . $section['type'], [
                'data'     => $section['data'] ?? [],
                'section'  => $section,
                'index'    => $index,
                'language' => $currentLang,
            ])
        @endif
    @endforeach
@else
    {{-- Fallback: minimal static page when no sections are configured --}}
    <section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-madeena-blue to-teal-800 text-white pt-20">
        <div class="text-center px-4">
            <img src="{{ asset('images/logo-current.png') }}" alt="Logo Madeena" class="w-32 h-32 mx-auto mb-6 object-contain">
            <h1 class="text-4xl font-bold mb-4">PT Madeena Karya Indonesia</h1>
            <p class="text-xl text-white/80 mb-8">Know Sciences, Learn Engineering, Create Technology, Develop Business.</p>
            <a href="/admin" class="btn-primary">{{ $currentLang->getUiLabel('manage_website_in_admin', 'Kelola Website di Admin') }}</a>
        </div>
    </section>
@endif

@endsection

@push('scripts')
<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>
@endpush