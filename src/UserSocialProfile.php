<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use WebModularity\LaravelUser\Events\UserSocialProfileLinked;

/**
 * WebModularity\LaravelUser\UserSocialProfile
 *
 * @property int $id
 * @property int $user_id
 * @property int $social_provider_id
 * @property string $uid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read UserSocialProvider $socialProvider
 * @property-read User $user
 */

class UserSocialProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'social_provider_id', 'uid'];

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
        return $this->belongsTo(UserSocialProvider::class);
    }

    public static function findFromSocialUser($socialUser, $socialProviderId)
    {
        $userSocialProfile = UserSocialProfile::where(
            [
                ['uid', $socialUser->getId()],
                ['social_provider_id', $socialProviderId]
            ]
        )
            ->with('user')
            ->first();

        if (!is_null($userSocialProfile)) {
            return $userSocialProfile;
        }

        // Search for user with matching email address
        $user = User::whereHas('person', function ($query) use ($socialUser) {
            $query->where('email', $socialUser->getEmail());
        })->first();
        if (!is_null($user)) {
            // Link social profile
            $userSocialProfile = UserSocialProfile::create([
                'user_id' => $user->id,
                'social_provider_id' => $socialProviderId,
                'uid' => $socialUser->getId()
            ]);
            event(new UserSocialProfileLinked($userSocialProfile));
            $userSocialProfile->load('user');
            return $userSocialProfile;
        }

        return null;
    }
}
