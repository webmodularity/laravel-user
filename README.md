# laravel-user

## Config
### General
Publish the configuration file with:
```php
php artisan vendor:publish --provider="WebModularity\LaravelUser\UserServiceProvider" --tag=config
```

Modify the providers array in the `config/auth.php` file:
Change
```php
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],
```
to this:
```php
    'providers' => [
        'users' => [
            'driver' => 'wm-eloquent',
            'model' => WebModularity\LaravelUser\User::class,
        ],
```

### Social Logins
Add routes to `routes/web.php`
```php
Route::get('social/{userSocialProvider}', "Auth\LoginController@redirectSocialUser");
Route::get('social/handle/{userSocialProvider}', "Auth\LoginController@loginSocialUser");
```

In the `config/services.php` add the services you intend to use for Social Logins

```php
    'google' => [
        'client_id' => env('GOOGLE_ID'),
        'client_secret' => env('GOOGLE_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT')
    ],
    
    'github' => [
        'client_id' => env('GITHUB_ID'),
        'client_secret' => env('GITHUB_SECRET'),
        'redirect' => env('GITHUB_REDIRECT')
    ],
    
    'facebook' => [
        'client_id' => env('FACEBOOK_ID'),
        'client_secret' => env('FACEBOOK_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT')
    ],
    
    'twitter' => [
        'client_id' => env('TWITTER_ID'),
        'client_secret' => env('TWITTER_SECRET'),
        'redirect' => env('TWITTER_REDIRECT')
    ],
    
    'linkedin' => [
        'client_id' => env('LINKEDIN_ID'),
        'client_secret' => env('LINKEDIN_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT')
    ],
    
    'bitbucket' => [
        'client_id' => env('BITBUCKET_ID'),
        'client_secret' => env('BITBUCKET_SECRET'),
        'redirect' => env('BITBUCKET_REDIRECT')
    ],
```

Include the needed credentials in the `.env` file for each Social Login provider used. For example:
```php
GOOGLE_ID=***YOUR GOOGLE ID***
GOOGLE_SECRET=***YOUR GOOGLE SECRET***
GOOGLE_REDIRECT=http://www.yourdomain.com/social/handle/google
```