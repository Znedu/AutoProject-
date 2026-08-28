<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\BookingBillingPolicy;
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
        Gate::define('permission', function (User $user, string $permission): bool {
            return $user->hasPermission($permission);
        });

        Gate::define('role', function (User $user, ...$roles): bool {
            return $user->hasRole(collect($roles)->flatten()->all());
        });

        Gate::define('billing.view', [BookingBillingPolicy::class, 'view']);
        Gate::define('billing.manage', [BookingBillingPolicy::class, 'manage']);
        Gate::define('billing.finalize', [BookingBillingPolicy::class, 'finalize']);
        Gate::define('billing.record-payment', [BookingBillingPolicy::class, 'recordPayment']);
    }
}
