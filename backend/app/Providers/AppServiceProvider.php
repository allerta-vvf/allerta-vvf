<?php

namespace App\Providers;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

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
        $prefix = config('app.prefix_path', '/');
        URL::macro('r', function ($name, $parameters = [], $absolute = true) use ($prefix) {
            $url = URL::route($name, $parameters, $absolute);
            if ($prefix === '/') {
                return $url;
            } else {
                return $prefix . parse_url($url, PHP_URL_PATH);
            }
        });

        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });
        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'access_token')
            );
        });
    }
}
