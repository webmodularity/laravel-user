<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Auth;

trait RedirectPendingUsersTo
{
    protected function redirectTo()
    {
        if (Auth::user()->isPending()) {
            $this->guard()->logout();
            session()->flush();
            session()->regenerate();
            session()->flash('info', 'This user account is pending approval.');
            return 'login';
        }

        return '/';
    }
}