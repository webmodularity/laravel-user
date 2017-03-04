<?php

namespace WebModularity\LaravelUser;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use View;
use Route;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register LaravelSession Service Provider
        $this->app->register('WebModularity\LaravelSession\SessionServiceProvider');

        // Register LaravelContact Service Provider
        $this->app->register('WebModularity\LaravelContact\ContactServiceProvider');

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

        // Middleware
        $this->app->make('router')->aliasMiddleware(
            'auth.local_users_allowed',
            'WebModularity\LaravelUser\Http\Middleware\LocalUsersAllowed'
        );
        $this->app->make('router')->aliasMiddleware(
            'auth.social_users_allowed',
            'WebModularity\LaravelUser\Http\Middleware\SocialUsersAllowed'
        );

        // Social Logins
        if (config('wm.user.modes.social', false)) {
            $this->loadSocialLogins($events);
        }
    }

    protected function loadSocialLogins(Dispatcher $events)
    {
        // Events
        $events->listen(
            'WebModularity\LaravelUser\Events\UserSocialProfileLinked',
            'WebModularity\LaravelUser\Listeners\LogUserSocialProfileLinked'
        );

        // View Composers
        View::composer('auth.login', function ($view) {
            $view->with('socialProviders', UserSocialProvider::all());
        });

        Route::bind('socialProvider', function ($value) {
            return UserSocialProvider::where(
                [
                    ['slug', $value],
                    ['status', true]
                ]
            )->first();
        });
    }
}
