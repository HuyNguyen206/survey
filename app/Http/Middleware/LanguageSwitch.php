<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Permission;
use Illuminate\Support\Facades\App;

class LanguageSwitch {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $language = 'en';
        if (Session::has('languageLocale')) {
            $language = Session::get('languageLocale');
        }
        App::setLocale($language);
        return $next($request);
    }

}
