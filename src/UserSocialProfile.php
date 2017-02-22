<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Contracts\User as SocialUser;
use WebModularity\LaravelUser\Events\UserInvitationClaimed;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelProviders\SocialProvider;

/**
 * WebModularity\LaravelUser\UserSocialProfile
 *
 * @property int $id
 * @property int $user_id
 * @property int $social_provider_id
 * @property string $uid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \WebModularity\LaravelProviders\SocialProvider $socialProvider
 * @property-read \WebModularity\LaravelUser\User $user
 */

class UserSocialProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'social_provider_id', 'uid'
    ];

    /**
     * Get the user that owns this UserSocialProfile.
     */
    public function user()
    {
        return $this->belongsTo('WebModularity\LaravelUser\User');
    }

    /**
     * Get the social provider that owns this UserSocialProfile.
     */
    public function socialProvider()
    {
        return $this->belongsTo('WebModularity\LaravelProviders\SocialProvider');
    }

    /**
     *
     * @param SocialProvider $socialProvider
     * @param SocialUser $socialUser
     * @return Model|null
     */

    public static function firstOrCreateFromSocialUser(SocialProvider $socialProvider, SocialUser $socialUser)
    {
        if (!is_null($socialUser) && !is_null($socialProvider) && $socialProvider->authIsActive()) {
            $userSocialProfile = static::where(
                [
                    ['uid', $socialUser->id],
                    ['social_provider_id', $socialProvider->id]
                ]
            )
                ->with('user')
                ->first();
            return !is_null($userSocialProfile)
                ? $userSocialProfile
                : static::createFromSocialUser($socialProvider, $socialUser);
        }

        return null;
    }

    /**
     * Attempt to create new UserSocialProfile and User from SocialUser data
     * @param SocialProvider $socialProvider
     * @param SocialUser $socialUser
     * @return Model
     */

    public static function createFromSocialUser(SocialProvider $socialProvider, SocialUser $socialUser)
    {
        if (!is_null($socialProvider) && !is_null($socialUser)) {
            $invitation = UserInvitation::firstFromSocial($socialProvider, $socialUser->email);

            if (is_null($invitation)) {
                return null;
            }

            $person = static::firstOrCreatePersonFromSocialUser($socialProvider, $socialUser);
            $user = User::firstOrCreate(
                ['person_id' => $person->id],
                [
                    'role_id' => $invitation->role_id,
                    'avatar_url' => static::getAvatarFromSocial($socialProvider, $socialUser),
                    'status' => $invitation->status
                ]
            );

            if (!is_null($user)) {
                event(new UserInvitationClaimed($invitation, $user));
                return static::create([
                    'user_id' => $user->id,
                    'social_provider_id' => $socialProvider->id,
                    'uid' => $socialUser->id
                ]);
            }
        }

        return null;
    }

    /**
     *
     * @param SocialProvider $socialProvider
     * @param SocialUser $socialUser
     * @return Person
     */

    public static function firstOrCreatePersonFromSocialUser(SocialProvider $socialProvider, SocialUser $socialUser)
    {
        $firstName = $lastName = '';
        // Extract person name based on SocialProvider
        if ($socialProvider->getSlug() == 'google') {
            $firstName = $socialUser->user['name']['givenName'];
            $lastName = $socialUser->user['name']['familyName'];
        } else {
            // Default to a best guess from the full name
            extract(Person::splitFullName($socialUser->getName()));
        }

        $person = Person::firstOrCreate(
            ['email' => $socialUser->email],
            ['first_name' => $firstName, 'last_name' => $lastName]
        );

        return $person->updateIfNull('first_name', $firstName)->updateIfNull('last_name', $lastName);
    }

    public static function getAvatarFromSocial($socialProvider, $socialUser)
    {
        $avatarUrl = !empty($socialUser->avatar)
            ? $socialUser->avatar
            : null;

        if (!is_null($avatarUrl)) {
            if ($socialProvider->getSlug() == 'google') {
                // Change default size to 160
                $avatarUrl = preg_replace('/\?sz=\d+$/', '?sz=160', $avatarUrl, 1);
            }
        }

        return $avatarUrl;
    }
}
