<?php

namespace WebModularity\LaravelUser\Listeners;

use Illuminate\Http\Request;
use WebModularity\LaravelUser\Events\UserSocialProviderUnlinked;
use WebModularity\LaravelUser\LogUser;
use WebModularity\LaravelLog\LogRequest;
use WebModularity\LaravelUser\LogUserAction;

/**
 * Class LogUserSocialProviderUnlinked
 * @package WebModularity\LaravelUser\Listeners
 */
class LogUserSocialProviderUnlinked
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
     * Log social provider unlink to LogUser
     * @param UserSocialProviderUnlinked $event
     */
    public function handle(UserSocialProviderUnlinked $event)
    {
        $logRequest = LogRequest::createFromRequest($this->request);

        LogUser::create([
            'log_request_id' => $logRequest->id,
            'user_id' => $event->user->user_id,
            'user_action_id' => LogUserAction::where('slug', 'unlink-social')->first()->id,
            'social_provider_id' => $event->socialProvider->id
        ]);
    }
}
