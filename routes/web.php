<?php

use App\Http\Controllers\Event\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicStorageController;
use App\Http\Controllers\SsoController;
use App\Livewire\EventDisplay;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();

        return response()->json(['status' => 'ok', 'db' => 'connected', 'timestamp' => now()]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/en', [HomeController::class, 'indexEn'])->name('home.en');
Route::get('/artikel', [HomeController::class, 'artikel'])->name('artikel.index');
Route::get('/produk/{product:slug}', [HomeController::class, 'product'])->name('product.show');
Route::get('/artikel/{post:slug}', [HomeController::class, 'post'])->name('post.show');
Route::get('/halaman/{page:slug}', [HomeController::class, 'page'])->name('page.show');
Route::get('/storage/{path}', PublicStorageController::class)
    ->where('path', '.*')
    ->name('storage.public');

Route::prefix('events/{event:slug}')
    ->name('events.')
    ->group(function (): void {
        Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
        Route::get('/feedback/csrf-token', [FeedbackController::class, 'csrfToken'])
            ->middleware('throttle:event-feedback-csrf')
            ->name('feedback.csrf-token');
        Route::post('/feedback', [FeedbackController::class, 'store'])
            ->middleware('throttle:event-feedback-submission')
            ->name('feedback.store');
        Route::get('/display', EventDisplay::class)->name('display');
    });

Route::redirect('/inabuyer2026/feedback', '/events/inabuyer-2026/feedback');
Route::redirect('/inabuyer2026/display', '/events/inabuyer-2026/display');

Route::prefix('sso')->group(function (): void {
    Route::get('/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
    Route::get('/silent', [SsoController::class, 'silentRedirect'])->name('sso.silent');
    Route::get('/callback', [SsoController::class, 'callback'])->name('sso.callback');
});
if (app()->environment(['local', 'testing'])) {
    Route::get('/test-support/login', function () {
        $email = config('auth.filament_admin_email', 'admin@madeena.local');

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'admin')
            ->first();

        if (! $user) {
            abort(404, 'Test user not found.');
        }

        Auth::login($user);

        return redirect('/admin');
    })->name('test-support.login');
}

Route::get('/{locale}', [HomeController::class, 'localizedHome'])
    ->where('locale', '[a-zA-Z]{2,3}(-[a-zA-Z0-9]{2,4})?')
    ->name('home.locale');
