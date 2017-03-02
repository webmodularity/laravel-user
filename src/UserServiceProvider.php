<?php

namespace WebModularity\LaravelUser;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use View;

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
            'WebModularity\LaravelUser\Listeners\LogUserInvitationClaimed'
        );

        // Config
        $this->publishes([__DIR__ . '/../config/user.php' => config_path('wm/user.php')], 'config');

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // UserProvider
        $this->app->make('auth')->provider('wm-eloquent', function ($app, array $config) {
            return new UserProvider($app->make('hash'), $config['model']);
        });

        // Social Logins
        $socialProviders = UserSocialProvider::all();
        if (count($socialProviders) > 0) {
            $this->loadSocialLogins($socialProviders, $events);
        }
    }

    protected function loadSocialLogins($socialProviders, Dispatcher $events)
    {
        // Events
        $events->listen(
            'WebModularity\LaravelUser\Events\UserSocialProfileLinked',
            'WebModularity\LaravelUser\Listeners\LogUserSocialProfileLinked'
        );

        View::composer('auth.login', function ($view) use ($socialProviders) {
            $view->with('socialProviders', $socialProviders);
        });

        // Social Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/social.php');
        $this->app->make('router')->bind('socialProvider', function ($value) {
            return UserSocialProvider::where('slug', $value)->first();
        });
        $this->app->make('router')->aliasMiddleware(
            'auth.social_provider',
            'WebModularity\LaravelUser\Http\Middleware\SocialProviderActive'
        );
        $this->app->make('router')->aliasMiddleware(
            'auth.social_login_only',
            'WebModularity\LaravelUser\Http\Middleware\SocialLoginOnly'
        );
    }
}
