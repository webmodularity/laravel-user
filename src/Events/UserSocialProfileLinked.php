<?php

namespace WebModularity\LaravelUser\Events;

use WebModularity\LaravelUser\UserSocialProfile;

/**
 * Class UserSocialProfileLinked
 * @package WebModularity\LaravelUser\Events
 */
class UserSocialProfileLinked
{
    public $userSocialProfile;

    /**
     * Create a new event instance.
     *
     * @param  UserSocialProfile  $userSocialProfile
     */
    public function __construct(UserSocialProfile $userSocialProfile)
    {
        $this->userSocialProfile = $userSocialProfile;
    }
}
