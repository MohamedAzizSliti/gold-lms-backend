<?php

namespace App\Providers;
use Carbon\Carbon;

use App\Facades\AppMethods;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App',function(){
            return new AppMethods();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Utiliser la même locale que Laravel pour Carbon
        Carbon::setLocale(config('app.locale'));
    }
}
