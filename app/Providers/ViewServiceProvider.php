<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Share favicon with all views
        View::share('favicon', asset('img/hospital_logo.png'));

        // Create a blade directive for the favicon
        Blade::directive('favicon', function () {
            return "<?php echo '<link rel=\"icon\" type=\"image/png\" href=\"'.asset('img/hospital_logo.png').'\">'; ?>";
        });

        // Add response middleware to inject favicon into all HTML responses
        $this->app->singleton('favicon.middleware', function ($app) {
            return new \App\Http\Middleware\FaviconMiddleware();
        });
    }
}