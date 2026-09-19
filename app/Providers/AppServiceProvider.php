<?php

namespace App\Providers;

use App\Models\User;
use App\Services\WebsiteAnalytics;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Login::class => 'login', Logout::class => 'logout',
            Failed::class => 'failed_login'] as $event => $type) {
            Event::listen($event, function ($event) use ($type) {
                if ($event->guard === 'web') {
                    app(WebsiteAnalytics::class)->record(request(), $type,
                        request()->routeIs('auth.google*') ? 'Google sign-in' : 'Account access',
                        user: $type === 'failed_login' ? null : $event->user);
                }
            });
        }

        // Centralized Super Admin bypass and unified permission resolution
        Gate::before(function (User $user, string $ability) {
            if ($user->role === 'super_admin') {
                return true;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null; // Fall through to standard model policies if ability is not a permission name
        });
    }
}
