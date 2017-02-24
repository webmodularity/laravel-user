<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\Events\UserInvitationClaimed;
use WebModularity\LaravelUser\User;
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
        $userSocialProfile = UserSocialProfile::findBySocialUser($socialUser, $socialProvider);

        if (!is_null($userSocialProfile)) {
            $this->socialUserGuard()->login($userSocialProfile->user, false);
            $this->sendLoginResponseSocialUser($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        // Attempt to register user
        return $this->registerSocialUser($request);
    }

    /**
     * Handle a registration request for provided social user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function registerSocialUser(Request $request)
    {
        $socialUser = $request->socialUser;
        $socialProvider = $request->socialProvider;
        // User Invitation
        $invitation = $this->getUserInvitationFromSocialUser($socialUser, $socialProvider);
        if (is_null($invitation)) {
            // No invitations found to register this user
            return $this->sendFailedSocialUserLoginResponse($request);
        }
        // User
        $user = $this->getUserFromInvitation($socialUser, $socialProvider, $invitation);
        $userSocialProfile = UserSocialProfile::linkSocialProfile($user, $socialProvider, $socialUser);
        // trigger link-social event

        // Login
        $this->socialUserGuard()->login($user);
        return $this->socialUserRegistered($request, $user)
            ?: redirect($this->redirectPath());
    }

    protected function getUserFromInvitation(
        SocialUser $socialUser,
        SocialProvider $socialProvider,
        UserInvitation $invitation
    ) {
        // Person
        $person = $this->getPersonFromSocialUser($socialUser, $socialProvider);

        $user = User::where('person_id', $person->id)->first();
        if (is_null($user)) {
            $user = User::create(
                [
                    'person_id' => $person->id,
                    'role_id' => $invitation->role_id,
                    'avatar_url' => $socialProvider->getAvatarFromSocial($socialUser),
                    'status' => $invitation->status
                ]
            );
            event(new Registered($user));
        }
        return $user;
    }

    protected function getPersonFromSocialUser(SocialUser $socialUser, SocialProvider $socialProvider)
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

    protected function getUserInvitationFromSocialUser($socialUser, $socialProvider)
    {
        $invitations = UserInvitation::findInvitations($socialUser->email, $socialProvider);
        dd($invitations);
        // pick best invitation
        //event(new UserInvitationClaimed($invitation));

        return null;
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

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function socialUserRegistered(Request $request, $user)
    {
        //
    }
}
