<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Force authorization for all environments
        Horizon::auth(function ($request) {
            $this->gate();
            return Gate::allows('viewHorizon');
        });
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            // Allow access in local environment
            if (app()->environment('local')) {
                return true;
            }

            $secretKey = request()->query('secret');
            $ip = request()->ip();

            // Check if secret matches env and cache it for 2 hours
            if ($secretKey === env('HORIZON_SECRET')) {
                cache()->put("{$ip}-horizon", $secretKey, 2 * 60 * 60);
                return true;
            }

            // Check if IP is already authorized in cache
            if ($secret = cache()->get("{$ip}-horizon", false)) {
                return $secret === env('HORIZON_SECRET');
            }

            return false;
        });
    }
}
