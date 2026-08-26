<?php

namespace App\Http\Controllers;

use App\Filament\Pages\HomepageEditor;
use App\Models\Language;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $defaultLang = Language::getDefault();

        return $this->renderHomepage($defaultLang);
    }

    public function localizedHome(string $locale): View|RedirectResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = $user instanceof \App\Models\User && $user->isAdmin();
        $isPreview = request('preview') === 'true' && $isAdmin;

        $lang = $isPreview ? Language::resolve($locale) : Language::resolveActive($locale);

        if (! $lang) {
            abort(404);
        }

        if (! $lang->is_active && ! $isPreview) {
            abort(404);
        }

        if ($lang->is_default && ! $isPreview) {
            return redirect('/', 302);
        }

        return $this->renderHomepage($lang);
    }

    public function indexEn(): View|RedirectResponse
    {
        return $this->localizedHome('en');
    }

    protected function renderHomepage(Language $language): View
    {
        $locale = $language->code;
        app()->setLocale($locale);

        $user = \Illuminate\Support\Facades\Auth::user();
        $isPreview = request('preview') === 'true' && $user instanceof \App\Models\User && $user->isAdmin();

        $sections = Setting::getHomepageSections($locale, $isPreview);
        $seo         = Setting::getJson('seo', []);
        $contactInfo = Setting::getJson('contact_info', []);
        $socialMedia = Setting::getJson('social_media', []);
        $branding    = Setting::getJson('branding', []);
        $whatsapp    = Setting::getJson('whatsapp_button', ['enabled' => true, 'number' => '']);
        $navItems    = HomepageEditor::getNavigation($isPreview, $locale);

        // Inject dynamic data into auto-pull sections
        foreach ($sections as &$section) {
            match ($section['type'] ?? '') {
                'products' => $section['products'] = Product::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(),
                'artikel' => $section['posts'] = Post::where('is_published', true)
                    ->where('content_language', $locale)
                    ->when(!empty($section['data']['category_filter']), function ($query) use ($section) {
                        return $query->where('placement', $section['data']['category_filter']);
                    })
                    ->orderByDesc('published_at')
                    ->take((int) ($section['data']['posts_count'] ?? 3))
                    ->get(),
                'contact' => $section['contact'] = $contactInfo,
                'about' => $section['page'] = $isPreview
                    ? \App\Models\Page::find($section['data']['page_id'] ?? null)
                    : \App\Models\Page::where('is_published', true)->find($section['data']['page_id'] ?? null),
                default => null,
            };
        }
        unset($section);

        $showLanguageSwitcher = true;

        return view('home', compact(
            'sections',
            'seo',
            'contactInfo',
            'socialMedia',
            'branding',
            'whatsapp',
            'navItems',
            'isPreview',
            'locale',
            'language',
            'showLanguageSwitcher'
        ));
    }

    public function artikel()
    {
        app()->setLocale('id');
        $locale = 'id';
        $showLanguageSwitcher = false;

        $posts = Post::where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('artikel', compact('posts', 'locale', 'showLanguageSwitcher'));
    }

    public function post(Post $post)
    {
        abort_if(! $post->is_published, 404);

        app()->setLocale('id');
        $locale = 'id';
        $showLanguageSwitcher = false;

        return view('post', compact('post', 'locale', 'showLanguageSwitcher'));
    }

    public function product(Product $product)
    {
        abort_if(! $product->is_active, 404);

        app()->setLocale('id');
        $locale = 'id';
        $showLanguageSwitcher = false;

        return view('product', compact('product', 'locale', 'showLanguageSwitcher'));
    }

    public function page(\App\Models\Page $page)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = $user instanceof \App\Models\User && $user->isAdmin();
        $isPreview = request('preview') === 'true' && $isAdmin;

        if (! $page->is_published && ! $isPreview) {
            abort(404);
        }

        app()->setLocale('id');
        $locale = 'id';
        $showLanguageSwitcher = false;

        return view('page', compact('page', 'locale', 'showLanguageSwitcher', 'isPreview'));
    }
}
