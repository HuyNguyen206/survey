<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Validator;
use App\Http\Requests\CheckUsersRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;

use App\Component\ExtraFunction;
use Illuminate\Support\Facades\Hash;

class Users extends Controller
{
	public function index(){
		$users = user::all();
		return view("users/index",[
			'users' => $users,
		]);
	}
	
	public function create(){
		$roles = role::all();
		return view("users/create",[
			'roles' => $roles,
		]);
	}
	
	public function process(CheckUsersRequest $request){
		$input = $request->all();
		foreach($input as $key => $val){
			$input[$key] = trim($val);
		}
		
		$role = Role::find($input['role']);
		if(empty($role)){
			$request->session()->flash('alert', 'Không tìm thấy vai trò');
			return redirect(main_prefix.'/users/create');
		}

		$resCheck = ExtraFunction::checkCanAction($role->level);
		if(!$resCheck){
			$request->session()->flash('alert', 'Bạn không có quyền tạo thành viên có cấp độ ngang hoặc cao hơn cấp độ của bạn');
			return redirect(main_prefix.'/users/create');
		}
		
		DB::beginTransaction();
		try{
			$create = User::create([
				'name' => $input['name'],
				'email' => $input['email'],
				'password' => bcrypt($input['password']),
			]);
			if(!$create['wasRecentlyCreated']){
				$request->session()->flash('status', false);
				DB::rollback();
			}else{
				$create->attachRole($input['role']);
				$request->session()->flash('status', true);
			}
			DB::commit();
		}catch(Exception $e){
			$request->session()->flash('status', false);
			DB::rollback();
		}
		return redirect(main_prefix.'/users/create');
	}
	
	public function edit($id){
		$role = role::findOrFail($id);
		var_dump($role);die;
	    return view('roles/edit',compact('role'));
	}
	
	public function update($id){
		
	}
    
    public function falseInside($name,Request $request){
        $input['name'] = $name;
        $request->merge(['email' => $input['name']]);
        $validator = Validator::make($input,['inside_require' => 'required']);
        $validator->errors()->add('email', trans('permissions.invalid inside'));
        $this->throwValidationException(
            $request, $validator
        );
    }
}
