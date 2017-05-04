<?php

namespace WebModularity\LaravelUser\Listeners;

use Illuminate\Http\Request;
use WebModularity\LaravelUser\Events\UserSocialProviderLinked;
use WebModularity\LaravelUser\LogUser;
use WebModularity\LaravelLog\LogRequest;
use WebModularity\LaravelUser\LogUserAction;

/**
 * Class LogUserSocialProviderLinked
 * @package WebModularity\LaravelUser\Listeners
 */
class LogUserSocialProviderLinked
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
     * Log social provider link to LogUser
     * @param UserSocialProviderLinked $event
     */
    public function handle(UserSocialProviderLinked $event)
    {
        $logRequest = LogRequest::createFromRequest($this->request);

        LogUser::create([
            'log_request_id' => $logRequest->id,
            'user_id' => $event->user->user_id,
            'user_action_id' => LogUserAction::where('slug', 'link-social')->first()->id,
            'social_provider_id' => $event->socialProvider->id
        ]);
    }
}
