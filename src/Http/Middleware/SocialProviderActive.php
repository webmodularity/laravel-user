<?php

namespace WebModularity\LaravelUser\Http\Middleware;

use Closure;

class SocialProviderActive
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
        $socialProvider = $request->route('socialProvider');

        if (empty($socialProvider) || !$socialProvider->authIsActive()) {
            abort(404, 'Social Provider Not Found.');
        }

        return $next($request);
    }
}
