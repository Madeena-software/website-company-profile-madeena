<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Post;
use App\Models\Setting;
use App\Policies\PostPolicy;
use App\Services\EventFeedbackRateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\LaravelPassport\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
            LogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('laravelpassport', Provider::class);
        });

        Gate::policy(Post::class, PostPolicy::class);

        View::composer(['layouts.app', 'home', 'product', 'post'], function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $settings = Setting::all()->pluck('value', 'key');
                } else {
                    $settings = collect();
                }
            } catch (\Exception $e) {
                $settings = collect();
            }

            $view->with('settings', $settings);
        });

        RateLimiter::for('event-feedback-submission', function (Request $request) {
            $event = EventFeedbackRateLimiter::resolveEvent($request);
            if ($event !== null && ! $event->is_active) {
                return Limit::none();
            }

            $eventKey = $event !== null ? (string) $event->id : (string) ($request->route('event') ?? 'unknown');
            $ip = $request->ip() ?? '127.0.0.1';

            $limits = [
                Limit::perMinute(30)->by(EventFeedbackRateLimiter::ipKey($eventKey, $ip)),
            ];

            $contactFingerprint = EventFeedbackRateLimiter::fingerprintContact($request);
            if ($contactFingerprint !== null) {
                $limits[] = Limit::perMinutes(10, 3)->by(
                    EventFeedbackRateLimiter::contactKey($eventKey, $contactFingerprint)
                );
            }

            return $limits;
        });

        RateLimiter::for('event-feedback-csrf', function (Request $request) {
            $event = EventFeedbackRateLimiter::resolveEvent($request);
            if ($event !== null && ! $event->is_active) {
                return Limit::none();
            }

            $eventKey = $event !== null ? (string) $event->id : (string) ($request->route('event') ?? 'unknown');
            $ip = $request->ip() ?? '127.0.0.1';

            return Limit::perMinute(60)->by(EventFeedbackRateLimiter::csrfKey($eventKey, $ip));
        });
    }
}
