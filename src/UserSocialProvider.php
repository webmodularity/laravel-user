<?php

namespace WebModularity\LaravelUser;

use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Contracts\User as SocialUser;

/**
 * WebModularity\LaravelUser\UserSocialProvider
 *
 * @property int $id
 * @property string $slug
 * @property bool $status
 */

class UserSocialProvider extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function getName()
    {
        if ($this->slug == 'github') {
            return 'GitHub';
        }

        return ucfirst($this->slug);
    }

    /**
     * Get a more accurate first and last name from some social providers.
     * @param $socialUser
     * @return array|null [] keyed by firstName, lastName
     */

    public function getPersonNameFromSocialUser(SocialUser $socialUser)
    {
        // Extract person name based on SocialProvider
        if ($this->slug == 'google') {
            return [
                'firstName' => $socialUser->user['name']['givenName'],
                'lastName' => $socialUser->user['name']['familyName']
            ];
        }

        return null;
    }

    /**
     * Returns the url of the social user avatar if available.
     * @param $socialUser
     * @return string|null Avatar url or null
     */
    public function getAvatarFromSocial(SocialUser $socialUser)
    {
        if (empty($socialUser->getAvatar())) {
            return null;
        }

        if ($this->slug == 'google') {
            // Change default size to 160
            return preg_replace('/\?sz=\d+$/', '?sz=160', $socialUser->getAvatar(), 1);
        }

        return $socialUser->getAvatar();
    }
}
