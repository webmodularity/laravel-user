<?php

namespace WebModularity\LaravelUser\Listeners;

use Carbon\Carbon;
use WebModularity\LaravelUser\Events\UserInvitationClaimed;

class LogUserInvitationClaimed
{

    /**
     * Set the claimed_at field to current time unless is a mass assignment invitation
     * @param UserInvitationClaimed $event
     */
    public function handle(UserInvitationClaimed $event)
    {
        $userInvitation = $event->userInvitation;

        if (!is_null($userInvitation) && !is_null($userInvitation->person_id)) {
            // Set claimed_at for non-mass invitations
            $userInvitation->claimed_at = Carbon::now();
            $userInvitation->save();
        }
    }
}
