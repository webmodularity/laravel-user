<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\UserInvitation;
use WebModularity\LaravelUser\UserSocialProfile;
use WebModularity\LaravelProviders\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Contracts\Factory as Socialite;

trait AuthenticatesAndRegistersSocialUsers
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

        // Attempt to log in the user associated to this social provider
        $socialUser = $socialite->driver($socialProvider->getSlug())->user();
        $userSocialProfile = UserSocialProfile::where(
            [
                ['uid', $socialUser->id],
                ['social_provider_id', $socialProvider->id]
            ]
        )
            ->with('user')
            ->first();

        if (!is_null($userSocialProfile)) {
            $this->socialUserGuard()->login($userSocialProfile->user, false);
            $this->sendLoginResponseSocialUser($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        // Attempt to register user
        $this->registerSocialUser($request);

        return $this->sendFailedSocialUserLoginResponse($request);
    }

    /**
     * Handle a registration request for provided social user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function registerSocialUser(Request $request)
    {
        $socialProvider = $request->socialProvider;
        $socialUser = $request->socialUser;
        $person = $this->getPersonFromSocialUser($socialProvider, $socialUser);
        $invitations = UserInvitation::findInvitations($person, $socialProvider);

        // Register New User via Social
        $user = User::firstOrCreate(
            ['person_id' => $person->id],
            [
                'role_id' => $invitation->role_id,
                'avatar_url' => static::getAvatarFromSocial($socialProvider, $socialUser),
                'status' => $invitation->status
            ]
        );

        $this->validator($request->all())->validate();
        event(new Registered($user = $this->create($request->all())));
        $this->guard()->login($user);
        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    protected function getPersonFromSocialUser(SocialProvider $socialProvider, SocialUser $socialUser)
    {
        $person = Person::where('email', $socialUser->email)->first();
        $personName = !is_null($socialProvider->getPersonNameFromSocialUser($socialUser))
            ? $socialProvider->getPersonNameFromSocialUser($socialUser)
            : Person::splitFullName($socialUser->getName());

        if (is_null($person)) {
            return Person::create(
                [
                    'email' => $socialUser->email,
                    'first_name' => $personName['firstName'],
                    'last_name' => $personName['lastName']
                ]
            );
        } else {
            // Update name on person model if null
            return $person->updateIfNull('first_name', $personName['firstName'])
                ->updateIfNull('last_name', $personName['lastName']);
        }
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
        return Auth::guard();
    }
}
