<?php

namespace App\Http\Middleware;

use Closure;

class RemoveAutoregisterCookieMiddlaware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(\Auth::check()) {
            \Cookie::queue(\Cookie::forget('autoregister_fake'));
            \Cookie::queue(\Cookie::forget('autoregister'));
        }
        
        return $next($request);
    }
}
