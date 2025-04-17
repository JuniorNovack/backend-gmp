<?php

namespace App\Providers;

use App\Services\UserService;
use Illuminate\Support\ServiceProvider;
use App\Services\Company\CompanyService;
use App\Services\Contracts\IUserService;
use App\Services\Company\CompanyContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IUserService::class, UserService::class);
        $this->app->singleton(CompanyContract::class, CompanyService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
