<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
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
     * @param User $user
     * @param SocialProvider $socialProvider
     * @param SocialUser $socialUser
     * @return mixed
     */
    public static function linkSocialProfile(User $user, SocialProvider $socialProvider, SocialUser $socialUser)
    {
        $userSocialProfile = static::create([
            'user_id' => $user->id,
            'social_provider_id' => $socialProvider->id,
            'uid' => $socialUser->id
        ]);
        // Trigger event
        return $userSocialProfile;
    }

    public static function findBySocialUser($socialUser, SocialProvider $socialProvider)
    {
        return static::where(
            [
                ['uid', $socialUser->id],
                ['social_provider_id', $socialProvider->id]
            ]
        )
            ->with('user')
            ->first();
    }
}
