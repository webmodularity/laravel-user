<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Contracts\User as SocialUser;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelUser\Events\UserInvitationClaimed;
use WebModularity\LaravelUser\Events\UserSocialProfileLinked;
use WebModularity\LaravelUser\User;
use WebModularity\LaravelUser\UserInvitation;
use WebModularity\LaravelUser\UserSocialProfile;
use WebModularity\LaravelUser\UserSocialProvider;

/**
 * Class RegistersSocialUsers
 * @package WebModularity\LaravelUser\Http\Controllers\Auth
 */
trait RegistersSocialUsers
{
    /**
     * Method called from (failed) social user login attempt
     * @param SocialUser $socialUser
     * @param UserSocialProvider $socialProvider
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|null
     */
    protected function registerSocialUser(SocialUser $socialUser, UserSocialProvider $socialProvider, Request $request)
    {
        if (is_null($socialUser) || is_null($socialProvider)) {
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
        // Link User to SocialProvider
        $userSocialProfile = UserSocialProfile::create([
            'user_id' => $user->id,
            'social_provider_id' => $socialProvider->id,
            'uid' => $socialUser->getId()
        ]);
        event(new UserSocialProfileLinked($userSocialProfile));

        // Login
        $this->socialUserGuard()->login($user);
        return $this->registered($request, $user) ?: redirect($this->redirectPath());
    }


    /**
     * @param SocialUser $socialUser
     * @param UserSocialProvider $socialProvider
     * @return null
     */
    protected function getUserInvitationFromSocialUser(SocialUser $socialUser, UserSocialProvider $socialProvider)
    {
        $person = Person::where('email', $socialUser->getEmail())->first();
        $invitations = UserInvitation::findInvitations($person, $socialProvider);

        if ($invitations->count() == 1) {
            return $invitations->first();
        } elseif ($invitations->count() > 1) {
            $index = $invitations->search(function ($item, $key) {
                return $item->person_id > 0;
            });
            return $invitations[$index] ?: null;
        }

        return null;
    }

    /**
     * @param SocialUser $socialUser
     * @param UserSocialProvider $socialProvider
     * @param UserInvitation $invitation
     * @return mixed
     */
    protected function getUserFromInvitation(
        SocialUser $socialUser,
        UserSocialProvider $socialProvider,
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
     * @param UserSocialProvider $socialProvider
     * @return mixed
     */
    protected function getPersonFromSocialUser(SocialUser $socialUser, UserSocialProvider $socialProvider)
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
