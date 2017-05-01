<?php

namespace WebModularity\LaravelUser\Http\Middleware;

use Closure;
use Auth;
use Route;

class RedirectPendingUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user()->isPending()) {
            Auth::guard()->logout();
            session()->flush();
            session()->regenerate();
            if (Route::current()->getActionMethod() == 'register') {
                session()->flash('success', 'New user account created successfully. We will contact you once the
                approval process is complete and your credentials are active.');
            } elseif (Route::current()->getActionMethod() == 'reset') {
                session()->flash('success', 'Password reset successfully.');
            }
            session()->flash('warning', 'This user account is pending approval.');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
