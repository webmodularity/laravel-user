<?php

namespace WebModularity\LaravelUser\Observers;

use WebModularity\LaravelUser\User;

class UserObserver
{
    /**
     * Listen to the User restored event.
     *
     * @param  User  $user
     * @return void
     */
    public function restored(User $user)
    {
        $user->person->restore();
    }

    /**
     * Listen to the User deleting event.
     *
     * @param  User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        if ($user->isForceDeleting()) {
            $user->person->forceDelete();
        } else {
            $user->person->delete();
        }
    }
}