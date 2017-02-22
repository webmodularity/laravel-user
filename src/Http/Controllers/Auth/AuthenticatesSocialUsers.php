<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use WebModularity\LaravelUser\UserSocialProfile;
use WebModularity\LaravelProviders\SocialProvider;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Http\Request;

trait AuthenticatesSocialUsers
{
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

        $socialUser = $socialite->driver($socialProvider->getSlug())->user();
        $userSocialProfile = UserSocialProfile::firstOrCreateFromSocialUser($socialProvider, $socialUser);

        if (!is_null($userSocialProfile)) {
            $this->socialUserGuard()->login($userSocialProfile->user, false);
            $this->sendLoginResponseSocialUser($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedSocialUserLoginResponse($request);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponseSocialUser($request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        return redirect()->intended($this->redirectPath());
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

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function socialUserGuard()
    {
        return $this->guard();
    }
}
