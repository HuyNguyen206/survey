<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CreateBreadcrumb
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
		$currentAction = app()->router->getCurrentRoute()->getActionName();
		list($controller, $action) = explode('@', $currentAction);
		$controllerName = preg_replace('/.*\\\/', '', $controller);
		
		if(strpos($currentAction, 'get') !== false){
			session()->put('sub_breadcrumb', 'get');
		}
		return $next($request);
    }
}
