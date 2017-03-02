<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(UserSocialProvider::class);
    }
}
