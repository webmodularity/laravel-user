<?php

namespace WebModularity\LaravelUser\Http\Middleware;

use Closure;
use Auth;

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
            $pendingUserSuccess = session('pending-user-success', null);
            Auth::guard()->logout();
            session()->flush();
            session()->regenerate();
            if (!empty($pendingUserSuccess)) {
                session()->flash('success', $pendingUserSuccess);
            }
            session()->flash('warning', 'This user account is pending approval.');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
