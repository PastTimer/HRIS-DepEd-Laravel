<?php

namespace App\Providers;

use App\Models\Personnel;
use App\Models\School;
use App\Models\User;
use App\Observers\PersonnelObserver;
use App\Observers\SchoolObserver;
use App\Observers\UserObserver;
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
        School::observe(SchoolObserver::class);
        Personnel::observe(PersonnelObserver::class);
        User::observe(UserObserver::class);
    }
}
