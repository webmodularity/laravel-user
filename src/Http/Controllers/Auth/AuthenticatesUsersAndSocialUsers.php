<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Factory as Socialite;
use WebModularity\LaravelUser\UserSocialProfile;
use WebModularity\LaravelProviders\SocialProvider;

/**
 * Class AuthenticatesUsersAndSocialUsers
 * @package WebModularity\LaravelUser\Http\Controllers\Auth
 */
trait AuthenticatesUsersAndSocialUsers
{
    use AuthenticatesUsers, RegistersSocialUsers;

    /**
     * Obtain the user information from specified SocialProvider.
     *
     * @param SocialProvider $socialProvider SocialProvider Model filled from route
     * @param Socialite $socialite injected from ioc
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginSocialUser(SocialProvider $socialProvider, Socialite $socialite, Request $request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Attempt to log in the user associated to this social provider
        $socialUser = $socialite->driver($socialProvider->getSlug())->user();
        $userSocialProfile = UserSocialProfile::findBySocialUser($socialUser, $socialProvider);

        if (!is_null($userSocialProfile)) {
            $this->guard()->login($userSocialProfile->user, false);
            $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        // Try and register new user before sending failed response.
        return $this->registerSocialUser($request) ?: $this->sendFailedSocialUserLoginResponse($request);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedSocialUserLoginResponse(Request $request)
    {
        $errors = ['socialUser' => 'No social user found.'];
        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }
        return redirect()->back()->with('social_login_error', 'No social user found.');
    }
}