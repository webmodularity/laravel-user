<?php

namespace WebModularity\LaravelUser\Listeners;

use Illuminate\Http\Request;
use WebModularity\LaravelUser\Events\UserSocialProfileLinked;
use WebModularity\LaravelUser\LogUser;
use WebModularity\LaravelLog\LogRequest;

class LogUserSocialProfileLinked
{
    protected $request;

    /**
     * Create the event listener.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Log social profile linking to LogUser
     * @param UserSocialProfileLinked $event
     */
    public function handle(UserSocialProfileLinked $event)
    {
        $userSocialProfile = $event->userSocialProfile;

        LogUser::create([
            'log_request_id' => LogRequest::createFromRequest($this->request)->id,
            'user_id' => $userSocialProfile->user_id,
            'user_action' => LogUser::ACTION_LINK_SOCIAL,
            'social_provider_id' => $userSocialProfile->social_provider_id
        ]);
    }
}
