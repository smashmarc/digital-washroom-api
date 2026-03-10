<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class EntraAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Auth::extend('entra', function ($app, $name, array $config) {
            return new class($app['request']) implements \Illuminate\Contracts\Auth\Guard {
                protected $request;
                protected $user;

                public function __construct($request)
                {
                    $this->request = $request;
                }

                public function check()
                {
                    return $this->user() !== null;
                }

                public function guest()
                {
                    return $this->user() === null;
                }

                public function user()
                {
                    return $this->user ?? $this->request->user();
                }

                public function id()
                {
                    return $this->user() ? $this->user()->getAuthIdentifier() : null;
                }

                public function validate(array $credentials = [])
                {
                    return false; // middleware already validated
                }

                public function setUser($user)
                {
                    $this->user = $user;
                    return $this;
                }

                public function hasUser(): bool
                {
                    return $this->user() !== null;
                }
            };
        });
    }
}
