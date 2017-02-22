<?php

namespace WebModularity\LaravelUser\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use WebModularity\LaravelLog\LogRequest;

class UserAuthEventSubscriber
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

    public function getLogRequestId()
    {
        $logRequest = LogRequest::createFromRequest($this->request);
        return !is_null($logRequest) ? $logRequest->id : null;
    }

    /**
     * Handle user login events
     * @param Login $event
     * @return void
     */
    public function onUserLogin(Login $event)
    {
        LogUser::create([
            'log_request_id' => $this->getLogRequestId(),
            'user_id' => $event->user->id,
            'user_action' => LogUser::ACTION_LOGIN
        ]);
    }

    /**
     * Handle user logout events
     * @param Logout $event
     * @return void
     */
    public function onUserLogout(Logout $event)
    {
        LogUser::create([
            'log_request_id' => $this->getLogRequestId(),
            'user_id' => $event->user->id,
            'user_action' => LogUser::ACTION_LOGOUT
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber@onUserLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber@onUserLogout'
        );
    }
}