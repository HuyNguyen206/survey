<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Http\Requests\CheckPermissionsRequest;
use Illuminate\Support\Facades\Validator;

class Permissions extends Controller
{
	public function index(){
		$permissions = Permission::all();
		return view("permissions/index")->with("data", $permissions);
	}
	
	public function create(){
		return view("permissions/create");
	}
	
	public function store(CheckPermissionsRequest $request){
		$input = $request->all();
		foreach($input as $key => $val){
			$input[$key] = trim($val);
		}
		$temp = explode('-', trim($input['name']));
		if(count($temp) != 2){
			$validator = Validator::make($input,['name' => 'required']);
			$validator->errors()->add('name', trans('permissions.invalid name'));
			$this->throwValidationException(
				$request, $validator
			);
		}
		$input['name'] = strtolower($input['name']);//chuyển ký tự hoa thành ký tự thường
		$create = Permission::create($input);
		if(!$create['wasRecentlyCreated']){
			$request->session()->flash('status', false);
		}else{
			$request->session()->flash('status', true);
		}
		return redirect(main_prefix.'/permissions/create');
	}
	
	public function edit($id){
		$role = role::findOrFail($id);
		var_dump($role);die;
	    return view('permissions/edit',compact('permissions'));
	}
	
	public function update($id){
		
	}
}
