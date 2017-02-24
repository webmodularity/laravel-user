<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\Events\UserInvitationClaimed;
use WebModularity\LaravelUser\User;
use WebModularity\LaravelUser\UserInvitation;
use WebModularity\LaravelUser\UserSocialProfile;
use WebModularity\LaravelProviders\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialUser;
use Auth;
use DebugBar;

/**
 * Class RegistersSocialUsers
 * @package WebModularity\LaravelUser\Http\Controllers\Auth
 */
trait RegistersSocialUsers
{
    /**
     * Handle a registration request for provided social user.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|null Returns null on failure
     */
    protected function registerSocialUser(Request $request)
    {
        DebugBar::info("Registered social user!");
        $socialUser = isset($request->socialUser) ? $request->socialUser : null;
        $socialProvider = isset($request->socialProvider) ? $request->socialProvider : null;
        DebugBar::info($socialUser);
        DebugBar::info($socialProvider);
        if (empty($socialUser) || empty($socialProvider)) {
            return null;
        }
        // User Invitation
        $invitation = $this->getUserInvitationFromSocialUser($socialUser, $socialProvider);
        if (is_null($invitation)) {
            // No invitations found to register this user
            return null;
        }
        event(new UserInvitationClaimed($invitation));
        // User
        $user = $this->getUserFromInvitation($socialUser, $socialProvider, $invitation);
        $userSocialProfile = UserSocialProfile::linkSocialProfile($user, $socialProvider, $socialUser);
        // trigger link-social event

        // Login
        $this->socialUserGuard()->login($user);
        return $this->registered($request, $user) ?: redirect($this->redirectPath());
    }


    /**
     * @param SocialUser $socialUser
     * @param SocialProvider $socialProvider
     * @return null
     */
    protected function getUserInvitationFromSocialUser(SocialUser $socialUser, SocialProvider $socialProvider)
    {
        $invitations = UserInvitation::findInvitations($socialUser->getEmail(), $socialProvider);
        Debugbar::info($invitations);
        // pick best invitation

        return null;
    }

    /**
     * @param SocialUser $socialUser
     * @param SocialProvider $socialProvider
     * @param UserInvitation $invitation
     * @return mixed
     */
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

    /**
     * @param SocialUser $socialUser
     * @param SocialProvider $socialProvider
     * @return mixed
     */
    protected function getPersonFromSocialUser(SocialUser $socialUser, SocialProvider $socialProvider)
    {
        $person = Person::where('email', $socialUser->getEmail())->first();

        $personName = !is_null($socialProvider->getPersonNameFromSocialUser($socialUser))
            ? $socialProvider->getPersonNameFromSocialUser($socialUser)
            : Person::splitFullName($socialUser->getName());

        if (is_null($person)) {
            return Person::create(
                [
                    'email' => $socialUser->getEmail(),
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
     * Get the failed register response instance.
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
     * Get the guard to be used during registration.
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
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
