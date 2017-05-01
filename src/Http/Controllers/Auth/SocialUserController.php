<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use WebModularity\LaravelUser\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Factory as Socialite;
use WebModularity\LaravelUser\User;
use WebModularity\LaravelUser\UserSocialProfile;
use WebModularity\LaravelUser\UserSocialProvider as SocialProvider;

class SocialUserController extends Controller
{
    use AuthenticatesUsers, RedirectUsersTo;

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth.social_users_allowed');
    }

    public function redirectSocialUser(SocialProvider $socialProvider, Socialite $socialite)
    {
        return $socialite->driver($socialProvider->slug)->redirect();
    }

    /**
     * Obtain the user information from specified SocialProvider.
     *
     * @param SocialProvider $socialProvider SocialProvider Model filled from route
     * @param Socialite $socialite injected from ioc
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleSocialUser(SocialProvider $socialProvider, Socialite $socialite, Request $request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Attempt to log in the user associated to this social provider
        $socialUser = $socialite->driver($socialProvider->slug)->user();
        $userSocialProfile = UserSocialProfile::findFromSocialUser($socialUser, $socialProvider->id);

        if (!is_null($userSocialProfile)) {
            $this->guard()->login($userSocialProfile->user, false);
            $this->sendLoginResponse($request);
        }

        if (config('wm.user.register', false)) {
            // Attempt to create new user
            $user = User::createFromSocialUser($socialUser, $socialProvider);
            if (!is_null($user)) {
                $this->guard()->login($user, false);
                $this->sendLoginResponse($request);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedSocialUserLoginResponse($request);
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
        return redirect()->route('login')->with('warning', 'No social user found.');
    }
}