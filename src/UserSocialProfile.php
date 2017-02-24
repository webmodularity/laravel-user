<?php

namespace WebModularity\LaravelUser;

use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use WebModularity\LaravelUser\Events\UserInvitationClaimed;
use WebModularity\LaravelContact\Person;
use WebModularity\LaravelProviders\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialUser;

/**
 * WebModularity\LaravelUser\UserSocialProfile
 *
 * @property int $id
 * @property int $user_id
 * @property int $social_provider_id
 * @property string $uid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read SocialProvider $socialProvider
 * @property-read User $user
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
        return $this->belongsTo(User::class);
    }

    /**
     * Get the social provider that owns this UserSocialProfile.
     */
    public function socialProvider()
    {
        return $this->belongsTo(SocialProvider::class);
    }

    /**
     * Attempt to create new UserSocialProfile and User from SocialUser data
     * @param SocialProvider $socialProvider
     * @param SocialUser $socialUser
     * @return Model
     */

    public static function createFromSocialUser(SocialProvider $socialProvider, SocialUser $socialUser)
    {

            $user = User::where('person_id', $person->id)->first();
            if (is_null($user)) {
            }

            if (!is_null($user)) {
                event(new UserInvitationClaimed($invitation, $user));
                event(new Registered($user));
                return static::create([
                    'user_id' => $user->id,
                    'social_provider_id' => $socialProvider->id,
                    'uid' => $socialUser->id
                ]);
            }
        }

        return null;
    }
}
