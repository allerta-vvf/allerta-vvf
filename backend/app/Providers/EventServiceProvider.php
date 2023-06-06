<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(
            \Lab404\Impersonate\Events\TakeImpersonation::class,
            function (\Lab404\Impersonate\Events\TakeImpersonation $event) {
                session()->put('password_hash_sanctum', $event->impersonated->getAuthPassword());
            }
        );
        Event::listen(
            \Lab404\Impersonate\Events\LeaveImpersonation::class,
            function (\Lab404\Impersonate\Events\LeaveImpersonation $event) {
                session()->put('password_hash_sanctum', $event->impersonator->getAuthPassword());
            }
        );
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
