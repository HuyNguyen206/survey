<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Session\TokenMismatchException;
use Psy\Util\Json;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e) {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e) {
        if ($e instanceof TokenMismatchException) {
            $user = Auth::user();
            if (empty($user)) {
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
        if ($e instanceOf NotFoundHttpException) {
            $data = array_merge([
                'id' => 'not_found',
                'status' => '404'
                    ], config('errors.not_found'));

            $status = 404;
        }

        if ($e instanceOf MethodNotAllowedHttpException) {
            $data = array_merge([
                'id' => 'method_not_allowed',
                'status' => '405'
                    ], config('errors.method_not_allowed'));

            $status = 405;
        }
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        return response()->json($data, $status);
    }

}
