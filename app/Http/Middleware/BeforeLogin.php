<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\SoapController;
use App\Models\Authen\User;
use App\Models\Authen\Role;
use Validator;

class BeforeLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $maintain = false;

    public function handle($request, Closure $next) {
        $currentAction = app()->router->getCurrentRoute()->getActionName();
        list($controller, $action) = explode('@', $currentAction);
        $controllerName = preg_replace('/.*\\\/', '', $controller);

        if($this->maintain){
            return redirect();
        }

        if ($controllerName === 'AuthController' && $action === 'login' && $request->getMethod() === 'POST') {
            $info = $request->all();

            //Bổ sung luật cho thông tin đăng nhập
            $rules = [
                'name' => 'required',
                'password' => 'required',
            ];
            foreach ($rules as $key => $val) {
                $input[$key] = $info[$key];
            }
            $validator = Validator::make($input, $rules);

            $isEmail = strpos($info['name'], '@');
            if ($isEmail === false && trim($info['name']) !== '') {///đăng nhập bằng tk inside
                $controller = new SoapController;
                $result = $controller->LogOnInsideWithOTP($request);
                if ($result) {
                    $user = User::where('name', '=', $info['name'])->first(); //ktra dữ liệu tồn tại trong db
                    if (empty($user)) {
                        //insert vào dữ liệu để authen
                        $create = User::create([
                            'name' => trim($info['name']),
                            'email' => trim($info['name']) . '@fpt.com.vn',
                            'password' => bcrypt($request['password']),
                            'login_partner' => 2, //tài khoản inside
                        ]);
                        if (empty($create['wasRecentlyCreated']) || $create['wasRecentlyCreated'] === false) {
                            return redirect()->guest('login'); //nếu không tạo được user
                        }

                        $modelRole = new Role();
                        $modelRole->insertRoleByUserId($create->id, 6); //thêm quyền Member cho các tài khoản inside đăng nhập
                    } else {
                        if (!password_verify($request['password'], $user->password)) {
                            //cập nhật password cho riêng tk inside
                            $user->password = bcrypt($request['password']);
                            $user->save();
                        }
                    }
                    $request->merge(['email' => $info['name'] . '@fpt.com.vn']);
                    return $next($request);
                }
                //nếu không đăng nhập được do inside trả về False
                $validator->errors()->add('password', trans('login.valid password'));
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput($input);
            } elseif ($isEmail !== false) {
                //Ngoại trừ một số email của dev
                $exception = [
                    'huydp2@fpt.com.vn',
                    'huynl@fpt.com.vn',
                    'dev@fpt.com.vn',
                ];

                $arrayName = [
                    'huydp2@fpt.com.vn' => 'huydp2',
                    'huynl@fpt.com.vn' => 'huynl',
                    'dev@fpt.com.vn' => 'dev',
                ];

                $except = array_search($info['name'], $exception);
                if ($except !== false) {
                    $request->merge(['email' => $info['name'] . '@fpt.com.vn']);
                    $request->merge(['name' => $arrayName[$info['name']]]);
                    return $next($request);
                }

                //Ngăn đăng nhập bằng email
                $validator->errors()->add('name', trans('login.valid account inside'));
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput($input);
            }
        }
        return $next($request);
    }
}
