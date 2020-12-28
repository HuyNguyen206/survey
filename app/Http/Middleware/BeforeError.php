<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BeforeError
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
		$user = Auth::user();
		if(!empty($user)){
			$currentAction = app()->router->getCurrentRoute()->getActionName();
			list($controller, $action) = explode('@', $currentAction);
			$controllerName = preg_replace('/.*\\\/', '', $controller);
			
            session()->put('main_breadcrumb', ucfirst($controllerName));
			session()->put('active_breadcrumb', ucfirst($action));
            
			return $next($request);

		}
		return redirect()->guest('login');
    }
}
