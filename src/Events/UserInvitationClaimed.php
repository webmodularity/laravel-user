<?php

namespace WebModularity\LaravelUser\Events;

use WebModularity\LaravelUser\UserInvitation;

class UserInvitationClaimed
{
    public $userInvitation;

    /**
     * Create a new event instance.
     *
     * @param  UserInvitation  $userInvitation
     */
    public function __construct(UserInvitation $userInvitation)
    {
        $this->userInvitation = $userInvitation;
    }
}
