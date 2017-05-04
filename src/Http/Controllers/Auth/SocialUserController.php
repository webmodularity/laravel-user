<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Factory as Socialite;
use WebModularity\LaravelUser\User;
use Auth;
use WebModularity\LaravelUser\UserSocialProvider as SocialProvider;

class SocialUserController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

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
        if (Auth::check()) {
            // Link social account
            // @TODO: See User::linkFromSocial() method
            dd('link social account needs work.');
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Attempt to log in the user associated to this social provider
        $socialUser = $socialite->driver($socialProvider->slug)->user();
        $user = User::findFromSocialUser($socialUser, $socialProvider);
        if (is_null($user) && config('wm.user.register', false)) {
            dd(Person::where('email', $socialUser->getEmail())->has('user')->get());
            if (!is_null(Person::where('email', $socialUser->getEmail())->has('user')->get())) {
                // If the register attempt was unsuccessful we will increment the number of attempts
                // to login and redirect the user back to the login form. Of course, when this
                // user surpasses their maximum number of attempts they will get locked out.
                $this->incrementLoginAttempts($request);

                return $this->sendFailedSocialUserRegisterResponse($request);
            }
            $user = User::createFromSocialUser($socialUser, $socialProvider);
            if ($user->isPending()) {
                $request->session()->flash('success', 'New user account created successfully. We will contact you
                once the approval process is complete and your credentials are active.');
            }
        }

        if (!is_null($user)) {
            if ($user->isPending()) {
                return redirect()->route('login')->with('warning', 'This user account is pending approval.');
            }
            $this->guard()->login($user, false);
            $this->sendLoginResponse($request);
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
        return redirect()->route('login')->with('danger', $errors['socialUser']);
    }

    /**
     * Get the failed register response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedSocialUserRegisterResponse(Request $request)
    {
        $errors = ['socialUser' => 'Unable to register new user account with credentials provided. There may
        be an existing user account with that email address. If you are trying to link a social provider to an existing
        user account you will need to log in and access your account settings.'];
        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }
        return redirect()->route('login')->with('danger', $errors['socialUser']);
    }
}