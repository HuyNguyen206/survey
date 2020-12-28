<?php 
namespace App\Models;

use Zizaco\Entrust\EntrustPermission;
use Illuminate\Support\Facades\DB;
class Permission extends EntrustPermission
{
	protected $fillable = [
		'name',
		'display_name',
		'description',
	];
	
	public static function getAllPermissionById($id){
		$all = DB::table('permission_role')
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
			->join('role_user', 'role_user.role_id', '=', 'permission_role.role_id')
			->where('role_user.user_id', '=', $id)
            ->select('permission_role.role_id', 'permissions.name')
            ->get();
		$res = [];
		foreach($all as $role){
			array_push($res, $role->name);
		}
    	return $res;
	}
}