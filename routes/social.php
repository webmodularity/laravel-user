<?php

use WebModularity\LaravelProviders\SocialProvider;
use Laravel\Socialite\Contracts\Factory as Socialite;

Route::group(['middleware' => ['web', 'auth.social_provider']], function () {
    Route::get('social/{socialProvider}', function (SocialProvider $socialProvider, Socialite $socialite) {
        return $socialite->driver($socialProvider->getSlug())->redirect();
    });
    Route::get('social/handle/{socialProvider}', "Auth\LoginController@loginSocialUser");
});
