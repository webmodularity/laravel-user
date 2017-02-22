<?php

namespace WebModularity\LaravelUser\Events;

use Illuminate\Queue\SerializesModels;
use WebModularity\LaravelUser\UserInvitation;

class UserInvitationClaimed
{
    use SerializesModels;

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
