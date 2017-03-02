<?php

namespace WebModularity\LaravelUser\Http\Middleware;

use Closure;

class LocalUsersAllowed
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
        if (!config('wm.user.modes.local', false)) {
            abort(404);
        }

        return $next($request);
    }
}
