<?php

namespace WebModularity\LaravelUser\Events;

use Illuminate\Queue\SerializesModels;
use WebModularity\LaravelUser\User;
use WebModularity\LaravelUser\UserSocialProvider;

/**
 * Class UserSocialProviderLinked
 * @package WebModularity\LaravelUser\Events
 */
class UserSocialProviderLinked
{
    use SerializesModels;

    public $user;
    public $socialProvider;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     * @param  UserSocialProvider $socialProvider
     */
    public function __construct(User $user, UserSocialProvider $socialProvider)
    {
        $this->user = $user;
        $this->socialProvider = $socialProvider;
    }
}
