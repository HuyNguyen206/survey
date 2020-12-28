<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Authen\User;

class BeforeAction {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = Auth::user();
        if (!empty($user)) {
            $userRole = Session::get('userRole');
            if (!isset($userRole) || empty($userRole)) {
                $userRole = User::getRole($user->id);
                Session::put('userRole', $userRole);
            }

            return $next($request);
        } else {
            if ($request->ajax()) {
                $data = array_merge([
                    'id' => 'session_expire',
                    'code' => 800,
                    'status' => '401'
                        ], config('errors.session_expire'));

                $status = 401;
                return response()->json($data, $status);
            } else {
                return redirect()->guest('login');
            }
        }
    }

}
