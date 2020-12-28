<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAJAX
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
		$user = Auth::check();
		if(empty($user)){
			
		}
//        if (Auth::guard($guard)->guest()) {
//            // return json mat session
//        }
		
        return $next($request);
    }
}
