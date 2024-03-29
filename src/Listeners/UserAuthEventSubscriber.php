<?php

namespace WebModularity\LaravelUser\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use WebModularity\LaravelLog\LogRequest;
use WebModularity\LaravelUser\LogUser;
use WebModularity\LaravelUser\LogUserAction;

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

    /**
     * Handle user login events
     * @param Login $event
     * @return void
     */
    public function onUserLogin(Login $event)
    {
        LogUser::create([
            'log_request_id' => LogRequest::createFromRequest($this->request)->id,
            'user_id' => $event->user->id,
            'user_action_id' => LogUserAction::where('slug', 'login')->first()->id,
            'social_provider_id' => $this->getSocialProviderId()
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
            'log_request_id' => LogRequest::createFromRequest($this->request)->id,
            'user_id' => $event->user->id,
            'user_action_id' => LogUserAction::where('slug', 'logout')->first()->id,
        ]);
    }

    /**
     * Handle user register events
     * @param Registered $event
     * @return void
     */
    public function onUserRegister(Registered $event)
    {
        LogUser::create([
            'log_request_id' => LogRequest::createFromRequest($this->request)->id,
            'user_id' => $event->user->id,
            'user_action_id' => LogUserAction::where('slug', 'register')->first()->id,
            'social_provider_id' => $this->getSocialProviderId()
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
            'Illuminate\Auth\Events\Registered',
            'WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber@onUserRegister'
        );

        $events->listen(
            'Illuminate\Auth\Events\Login',
            'WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber@onUserLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'WebModularity\LaravelUser\Listeners\UserAuthEventSubscriber@onUserLogout'
        );
    }

    protected function getSocialProviderId()
    {
        return isset($this->request->socialProvider) && !empty($this->request->socialProvider)
            ? $this->request->socialProvider->id
            : null;
    }
}
