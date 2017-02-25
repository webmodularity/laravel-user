<?php

namespace WebModularity\LaravelUser\Events;

use Illuminate\Queue\SerializesModels;
use WebModularity\LaravelUser\UserSocialProfile;

class UserSocialProfileLinked
{
    use SerializesModels;

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
