<?php 
namespace App\Models;

use Zizaco\Entrust\EntrustRole;
use Illuminate\Support\Facades\DB;

class Role extends EntrustRole
{
	protected $fillable = [
		'name',
		'display_name',
		'description',
		'level',
	];
	
	public static function getAllRoleById($id){
		$res = DB::table('role_user')
			->join('users', 'role_user.user_id', '=', 'users.id')
			->join('roles','role_user.role_id','=','roles.id')
			->where('role_user.user_id', '=', $id)
            ->select('roles.display_name', 'level')
            ->get();
    	return $res;
	}
}