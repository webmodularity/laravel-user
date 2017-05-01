<?php

namespace WebModularity\LaravelUser\Http\Controllers\Auth;

use Auth;
use Route;

trait RedirectUsersTo
{
    protected function redirectTo()
    {
        if (Auth::user()->isPending()) {
            if (Route::current()->getActionMethod() == 'register') {
                session()->flash('pending-user-success', 'New user account created successfully. We will contact you
                once the approval process is complete and your credentials are active.');
            } elseif (Route::current()->getActionMethod() == 'reset') {
                session()->flash('pending-user-success', 'Password reset successfully.');
            }
        }

        return '/';
    }
}