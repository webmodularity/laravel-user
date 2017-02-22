<?php

namespace WebModularity\LaravelUser\Http\Middleware;

use Closure;

class SocialLoginOnly
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
        if (config('wm.auth.social.social_login_only', false)) {
            abort(404);
        }

        return $next($request);
    }
}
