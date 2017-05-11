<?php

namespace WebModularity\LaravelUser;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use View;
use Route;
use Validator;

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
        // Translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'user-trans');

        // User Auth Event Listener
        $events->subscribe('WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber');

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
            'auth.redirect_pending_users',
            'WebModularity\LaravelUser\Http\Middleware\RedirectPendingUsers'
        );
        $this->app->make('router')->aliasMiddleware(
            'auth.social_users_allowed',
            'WebModularity\LaravelUser\Http\Middleware\SocialUsersAllowed'
        );
        $this->app->make('router')->aliasMiddleware(
            'auth.register_users_allowed',
            'WebModularity\LaravelUser\Http\Middleware\RegisterUsersAllowed'
        );

        // Validators
        // UserPersonUnique
        Validator::extend(
            'userPersonUnique',
            '\WebModularity\LaravelUser\Validators\UserPersonUniqueValidator@validate',
            $this->app->translator->trans('user-trans::validation.user-person-unique')
        );

        // Social Logins
        if (config('wm.user.social', false)) {
            $this->loadSocialLogins($events);
        }
    }

    protected function loadSocialLogins(Dispatcher $events)
    {
        // Events
        $events->listen(
            'WebModularity\LaravelUser\Events\UserSocialProviderLinked',
            'WebModularity\LaravelUser\Listeners\LogUserSocialProviderLinked'
        );
        $events->listen(
            'WebModularity\LaravelUser\Events\UserSocialProviderUnlinked',
            'WebModularity\LaravelUser\Listeners\LogUserSocialProviderUnlinked'
        );

        // View Composers
        View::composer(['auth.login', 'auth.register', 'users.edit'], function ($view) {
            $view->with('socialProviders', UserSocialProvider::isActive()->get());
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
