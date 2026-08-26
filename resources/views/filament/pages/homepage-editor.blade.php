<x-filament-panels::page>
    @php
        $allLanguages = \App\Models\Language::getAll();
        $currentLang = \App\Models\Language::resolve($activeLocale) ?? \App\Models\Language::getDefault();
        $currentDraftKey = \App\Models\Language::draftKeyFor($currentLang->code);
        $currentPublishedKey = \App\Models\Language::publishedKeyFor($currentLang->code);
        $currentHasDraft = \App\Models\Setting::getJson($currentDraftKey) !== null;
        $currentHasPublished = \App\Models\Setting::getJson($currentPublishedKey) !== null;
    @endphp
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Bahasa Konten:</span>
            <div class="inline-flex flex-wrap gap-1 rounded-lg p-1 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700" data-testid="editor-language-selector">
                @foreach($allLanguages as $lang)
                    @php
                        $dKey = \App\Models\Language::draftKeyFor($lang->code);
                        $pKey = \App\Models\Language::publishedKeyFor($lang->code);
                        $hasD = \App\Models\Setting::getJson($dKey) !== null;
                        $hasP = \App\Models\Setting::getJson($pKey) !== null;
                    @endphp
                    <button type="button"
                        wire:click="switchLanguage('{{ $lang->code }}')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all inline-flex items-center gap-1.5 {{ $activeLocale === $lang->code ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm ring-1 ring-gray-300 dark:ring-gray-600' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                        data-testid="editor-lang-btn-{{ $lang->code }}"
                        data-is-active="{{ $lang->is_active ? 'true' : 'false' }}">
                        <span>{{ $lang->native_name }} ({{ strtoupper($lang->code) }})</span>
                        @if($lang->is_default)
                            <span class="text-[10px] bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 px-1 py-0.2 rounded font-normal" data-testid="badge-default-{{ $lang->code }}">Default</span>
                        @endif
                        @if(!$lang->is_active)
                            <span class="text-[10px] bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 px-1 py-0.2 rounded font-normal" data-testid="badge-inactive-{{ $lang->code }}">Nonaktif</span>
                        @endif
                        @if($hasD)
                            <span class="text-[10px] bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 px-1 py-0.2 rounded font-normal" data-testid="badge-draft-{{ $lang->code }}">Draft</span>
                        @elseif($hasP)
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 px-1 py-0.2 rounded font-normal" data-testid="badge-published-{{ $lang->code }}">Published</span>
                        @else
                            <span class="text-[10px] bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400 px-1 py-0.2 rounded font-normal" data-testid="badge-empty-{{ $lang->code }}">Kosong</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center flex-wrap gap-1.5">
            <span>Sedang mengedit: <strong class="text-primary-600 dark:text-primary-400">{{ $currentLang->native_name }} ({{ strtoupper($currentLang->code) }})</strong></span>
            @if(!$currentLang->is_active)
                <span class="bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 px-2 py-0.5 rounded font-semibold text-[11px]" data-testid="editor-current-inactive-notice">Nonaktif (Belum Tampil di Publik)</span>
            @endif
            @if($currentHasDraft)
                <span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 px-1.5 py-0.5 rounded text-[11px]" data-testid="editor-current-draft-notice">Draft Aktif</span>
            @endif
        </div>
    </div>

    <form wire:submit="save">
        {{ $this->form }}
    </form>
</x-filament-panels::page>
