<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\DB;
use \App\Models\Zone;
use \App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Component\ExtraFunction;


class Authens extends Controller
{
	protected function viewSurvey(){
		return view('outbound');
	}
	
	protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }
	
	public function getRolePermission(){
		$roles = role::all();
		$permissions = Permission::all();
		$permissionsTB = 'permissions';
		$perrolTB = 'permission_role';
		$perrol = DB::table($perrolTB)
            ->join($permissionsTB, $permissionsTB.'.id', '=', $perrolTB.'.permission_id')
            ->select($permissionsTB.'.name', $perrolTB.'.role_id')
            ->get();
		return view("authens/indexRP", ['roles' => $roles, 
			'permissions' => $permissions,
			'perrol' => $perrol]);
	}
	
	public function saveRolePermission(Request $request){
		$input = $request->all();
		if(empty($input['Base'])){
			$request->session()->flash('alert', 'authens.If none permission is chosen, you should delete this role. Not modified it to none permission');
		}
		else{
			DB::beginTransaction();
			try{
				$baserole = Role::find($input['baserole']);
				$resCheck = ExtraFunction::checkCanAction($baserole->level);
				if(!$resCheck){
					$request->session()->flash('alert', 'Bạn không có quyền thay đổi quyền hạn của vai trò có cấp độ ngang hoặc cao hơn cấp độ của bạn');
					return redirect(main_prefix.'/authens');
				}
				
				$baserole->perms()->sync([]);
				foreach($input['Base'] as $val){
					$baserole->attachPermission($val);
				}
				$request->session()->flash('status', true);
				$request->session()->flash('oldbaserole', $input['baserole']);
				
				$user = Auth::user();
				$all_role = Permission::getAllPermissionById($user->id);
				Session::put('all_role'.$user->id, $all_role);
				
				DB::commit();
			}catch(Exception $e){
				$request->session()->flash('status', false);
				DB::rollback();
			}
		}
		return redirect(main_prefix.'/authens');
	}
	
	public function getRoleUser(){
		$roles = role::all();
		$res = User::getActiveUser();
		return view("authens/indexRU", ['roles' => $roles, 
			'userrole' => $res]);
	}
	
	public function saveRoleUser(Request $request){
		$input = $request->all();
		DB::beginTransaction();
		try{
			foreach($input as $key => $val){
				$temp = explode('_', $key);
				if(count($temp) == 2){
					if($temp[0] == 'baserole'){
						$userid = $temp[1];
						$roleid = $val;
						$newRole = Role::find($roleid);
						$oldRole = Role::getAllRoleById($userid);
						if($newRole->display_name != $oldRole['0']->display_name){
							$resNewCheck = ExtraFunction::checkCanAction($newRole->level);
							if(!$resNewCheck){
								$request->session()->flash('alert', 'Bạn không có quyền thay đổi vai trò của thành viên có cấp độ thấp lên cấp độ ngang hoặc cao hơn cấp độ của bạn');
								return redirect(main_prefix.'/authens/view-role-user');
							}
							$resOldCheck = ExtraFunction::checkCanAction($oldRole['0']->level);
							if(!$resOldCheck){
								$request->session()->flash('alert', 'Bạn không có quyền thay đổi vai trò của thành viên có cấp độ ngang hoặc cao hơn cấp độ của bạn');
								return redirect(main_prefix.'/authens/view-role-user');
							}
						}
						
						$user = User::find($userid);
						$user->roles()->sync([]);
						$user->roles()->attach($roleid);
					}
				}
			}
			$request->session()->flash('status', true);
			
			$user = Auth::user();
			$all_role = Permission::getAllPermissionById($user->id);
			Session::put('all_role'.$user->id, $all_role);
			
			DB::commit();
		}catch(Exception $e){
			$request->session()->flash('status', false);
			DB::rollback();
		}
		return redirect(main_prefix.'/authens/view-role-user');
	}
	
	public function getUserPermission(){
		$roles = role::all()->toArray();
		$zone = Zone::all()->toArray();
		$brand = Brand::all()->toArray();
		
		$userTB = 'users';
		$roleTB = 'roles';
		$useroleTB = 'role_user';
		$res = DB::table($useroleTB)
            ->join($userTB, $useroleTB.'.user_id', '=', $userTB.'.id')
			->join($roleTB, $useroleTB.'.role_id', '=', $roleTB.'.id')
			->where($userTB.'.is_active', '=', 0)
			->where($userTB.'.status', '=', 0)
            ->select($userTB.'.id', $userTB.'.name', $userTB.'.email', $userTB.'.user_zone', $userTB.'.user_brand',
					$roleTB.'.id as role_id', $roleTB.'.display_name', $roleTB.'.description')
            ->get();
//	var_dump($res);die;
		return view("authens/indexUP", [
			'roles' => $roles, 
			'zone' => $zone,
			'brand'=>$brand,
			'userrole' => $res]);
	}
	
	public function saveUserPermission(Request $request){
		$input = $request->all();
		DB::beginTransaction();
		try{
			$baseuser = Role::getAllRoleById($input['baseuser']);
			if(empty($baseuser)){
				$request->session()->flash('alert', 'Không tìm thấy người quản trị');
				return redirect(main_prefix.'/authens/view-user-permission');
			}
			$resCheck = ExtraFunction::checkCanAction($baseuser['0']->level);
			if(!$resCheck){
				$request->session()->flash('alert', 'Bạn không có quyền thay đổi quyền hạn của người quản trị có cấp độ ngang hoặc cao hơn cấp độ của bạn');
				return redirect(main_prefix.'/authens/view-user-permission');
			}
			
			$user = User::find($input['baseuser']);
			$user_zone = [];
			$user_brand = [];
			foreach($input as $key => $val){
				$temp = explode('_', $key);
				if(count($temp) == 2){
					if($temp[0] == 'zone'){
						$zone = $temp[1];
						array_push($user_zone, $zone);
						$user_brand = array_merge($user_brand, $val);
					}
				}
			}
			
			$user->user_zone = json_encode($user_zone);
			$user->user_brand = json_encode($user_brand);
			$user->save();
			$request->session()->flash('status', true);
			DB::commit();
		}catch(Exception $e){
			$request->session()->flash('status', false);
			DB::rollback();
		}
		return redirect(main_prefix.'/authens/view-user-permission');
	}
}
