<?php

namespace WebModularity\LaravelUser;

use View;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use WebModularity\LaravelUser\Http\Middleware\SocialProviderActive;
use WebModularity\LaravelUser\Http\Middleware\SocialLoginOnly;
use WebModularity\LaravelProviders\SocialProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register LaravelSession Service Provider
        $this->app->register('WebModularity\LaravelSession\SessionServiceProvider');

        // Register LaravelContact Service Provider
        $this->app->register('WebModularity\LaravelContact\ContactServiceProvider');

        // Register LaravelProviders Service Provider
        $this->app->register('WebModularity\LaravelProviders\ProvidersServiceProvider');

        // Register LaravelLog Service Provider
        $this->app->register('WebModularity\LaravelLog\LogServiceProvider');

        // Register Socialite Service Provider
        $this->app->register('Laravel\Socialite\SocialiteServiceProvider');

        // Config
        $this->mergeConfigFrom(__DIR__ . '/../config/user.php', 'wm.user');
    }

    public function boot(Dispatcher $events)
    {
        // User Auth Event Listener
        $events->subscribe('WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber');
        $events->listen(
            'WebModularity\LaravelUser\Events\UserInvitationClaimed',
            'WebModularity\LaravelUser\Listeners\UserInvitationSetClaimedAt'
        );

        // Config
        $this->publishes([__DIR__ . '/../config/auth.php' => config_path('wm/auth.php')], 'config');

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // UserProvider
        $this->app->make('auth')->provider('wm-eloquent', function (Application $app, array $config) {
            return new UserProvider($app->make('hash'), $config['model']);
        });

        // Social Logins
        $this->loadSocialLogins();
    }

    protected function loadSocialLogins()
    {
        if (count(config('wm.auth.social.providers', [])) > 0) {
            // Social Routes
            $this->loadRoutesFrom(__DIR__ . '/../routes/social.php');
            $this->app->make('router')->bind('socialProvider', function ($value) {
                return SocialProvider::whereHas('provider', function ($query) use ($value) {
                    $query->where('slug', $value);
                })->first();
            });
            $this->app->make('router')->aliasMiddleware('auth.social_provider', SocialProviderActive::class);
            $this->app->make('router')->aliasMiddleware('auth.social_login_only', SocialLoginOnly::class);

            View::composer('auth.login', function ($view) {
                $view->with('socialProviders', SocialProvider::whereHas('provider', function ($query) {
                    $query->whereIn('slug', config('wm.auth.social.providers', []));
                })->get());
            });
        }
    }
}
