<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
	use EntrustUserTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'login_partner',
		'user_zone', 'user_brand','last_login'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
	
	public static function getActiveUser(){
		$res = DB::table('role_user')
			->join('users', 'role_user.user_id', '=', 'users.id')
			->join('roles','role_user.role_id','=','roles.id')
			->where('users.status', '=', 0)
			->select('users.*', 'roles.level','roles.display_name','role_user.*')
			->orderBy('users.created_at','DESC')
            ->get();
    	return $res;
	}
    public static function getRole($userID)
    {
        $roleID = DB::table('role_user')
			->where('user_id', '=', $userID)
			->select('role_id')
            ->get();
    	return $roleID[0]->role_id;
    }
}
