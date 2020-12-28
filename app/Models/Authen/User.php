<?php

namespace App\Models\Authen;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password', 'login_partner',
        'user_zone', 'user_brand', 'last_login'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getActiveUser() {
        $res = DB::table('role_user')
                ->join('users', 'role_user.user_id', '=', 'users.id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('users.status', '=', 0)
                ->select('users.*', 'roles.level', 'roles.display_name', 'role_user.*')
                ->orderBy('users.created_at', 'DESC')
                ->get();
        return $res;
    }

    public static function getRole($userID) {
        $roleID = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('user_id', '=', $userID)
                ->select('roles.id', 'roles.display_name', 'roles.level')
                ->first();
        return (array) $roleID;
    }

    public function getUserByName($name) {
        $res = DB::table('users')
                ->where('users.name', '=', $name)
                ->select('*')
                ->first();
        return $res;
    }

    public function getUserWithZoneRole() {
        $allUser = DB::table('users')
                ->join('role_user', 'role_user.user_id', '=', 'users.id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->select('users.name', 'users.email', 'users.last_login', 'users.user_zone', 'users.user_brand', 'users.user_brand_plus', 'roles.level', 'roles.display_name')
                ->where('roles.display_name', '<>', 'Member')
                ->orderBy('roles.level', 'ASC')
                ->get();

        $allBrand = DB::table('location as l')
                ->leftjoin('location_branches as lb', 'lb.location_id', '=', 'l.id')
                ->select('l.region', 'l.id', 'l.name', 'lb.name as chinhanh', 'lb.id as chinhanhid')
                ->get();

        $resultUser = [];
        foreach ($allUser as $user) {
            $brand = null;
            $result = [];
            if (!empty($user->user_brand)) {
                $brands = json_decode($user->user_brand);
                foreach ($brands as $brand) {
                    foreach ($allBrand as $val) {
                        if ($val->id == $brand && $brand != '4' && $brand != '8') {
                            $expZone = explode(' - ', $val->name);
                            $expRegion = explode(' ', $val->region);
                            if (isset($result[$expRegion[1]])) {
                                $result[$expRegion[1]] .= ', ' . $expZone[1];
                            } else {
                                $result[$expRegion[1]] = $expZone[1];
                            }
                        }
                    }
                }
            }
            $brand_plus = null;
            if (!empty($user->user_brand_plus)) {
                $brand_plus = json_decode($user->user_brand_plus);
                foreach ($brand_plus as $plus) {
                    foreach ($allBrand as $val) {
                        if ($val->chinhanhid == $plus) {
                            $expZone = explode(' - ', $val->name);
                            $expRegion = explode(' ', $val->region);
                            if (isset($result[$expRegion[1]])) {
                                $result[$expRegion[1]] .= ', ' . $val->chinhanh;
                            } else {
                                $result[$expRegion[1]] = $expZone[1] . ': ' . $val->chinhanh;
                            }
                        }
                    }
                }
            }

            $temp = [
                'name' => $user->name,
                'email' => $user->email,
                'last_login' => $user->last_login,
                'user_zone' => $result,
                'role' => $user->display_name,
            ];

            array_push($resultUser, $temp);
        }

        return $resultUser;
    }

    public static function getAllPermissionByUserId($id) {
        $first = DB::table('users as u')
                ->leftjoin('role_user as ru', 'ru.user_id', '=', 'u.id')
                ->leftjoin('permission_role as pr', 'pr.role_id', '=', 'ru.role_id')
                ->leftjoin('permissions as p', 'p.id', '=', 'pr.permission_id')
                ->select('p.id as permissionId', 'p.name as permissionName', 'p.description as permissionDescription', 'p.display_name as permissionDisplayName',
                           'u.id','u.name'
                        )
                ->whereRaw('(not FIND_IN_SET(p.id, u.user_except_permission) or u.user_except_permission is null)')
                ->where('u.id', $id);
                
        
        $second = DB::table('users as u')
                ->join('user_permission as up', 'up.user_id', '=', 'u.id')
                ->join('permissions as p', 'p.id', '=', 'up.permission_id')
                ->select('p.id as permissionId', 'p.name as permissionName', 'p.description as permissionDescription', 'p.display_name as permissionDisplayName',
                           'u.id','u.name'
                        )
                ->whereRaw('(not FIND_IN_SET(p.id, u.user_except_permission) or u.user_except_permission is null)')
                ->where('u.id', $id)
                ->union($first);
        $all = $second->get();

        $res = [];
        foreach ($all as $per) {
            if(!isset($res['id'])){
                $res['id'] = $per->id;
                $res['name'] = $per->name;
                $res['permission'] = [];
                
                $temp['id'] = $per->permissionId;
                $temp['name'] = $per->permissionName;
                $temp['description'] = $per->permissionDescription;
                $temp['displayName'] = $per->permissionDisplayName;
                array_push($res['permission'], $temp);
            }else{
                if(!empty($per->permissionId)){
                    $temp['id'] = $per->permissionId;
                    $temp['name'] = $per->permissionName;
                    $temp['description'] = $per->permissionDescription;
                    $temp['displayName'] = $per->permissionDisplayName;
                    array_push($res['permission'], $temp);
                }
            }
        }
        return $res;
    }

    public static function getAllPermissionByParam($param) {
        $first = DB::table('users as u')
            ->leftjoin('role_user as ru', 'ru.user_id', '=', 'u.id')
            ->leftjoin('permission_role as pr', 'pr.role_id', '=', 'ru.role_id')
            ->leftjoin('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->select('p.id as permissionId', 'p.name as permissionName', 'p.description as permissionDescription', 'p.display_name as permissionDisplayName',
                'u.id','u.name'
            )
            ->whereRaw('(not FIND_IN_SET(p.id, u.user_except_permission) or u.user_except_permission is null)')
            ->where(function($query) use ($param) {
                if (!empty($param['name'])) {
                    $query->where('u.name', '=', $param['name']);
                }
            })
        ;

        $second = DB::table('users as u')
            ->join('user_permission as up', 'up.user_id', '=', 'u.id')
            ->join('permissions as p', 'p.id', '=', 'up.permission_id')
            ->select('p.id as permissionId', 'p.name as permissionName', 'p.description as permissionDescription', 'p.display_name as permissionDisplayName',
                'u.id','u.name'
            )
            ->whereRaw('(not FIND_IN_SET(p.id, u.user_except_permission) or u.user_except_permission is null)')
            ->where(function($query) use ($param) {
                if (!empty($param['name'])) {
                    $query->where('u.name', '=', $param['name']);
                }
            })
            ->union($first);
        $all = $second->get();

        $res = [];
        foreach ($all as $per) {
            if(!isset($res['id'])){
                $res['id'] = $per->id;
                $res['name'] = $per->name;
                $res['permission'] = [];

                $temp['id'] = $per->permissionId;
                $temp['name'] = $per->permissionName;
                $temp['description'] = $per->permissionDescription;
                $temp['displayName'] = $per->permissionDisplayName;
                array_push($res['permission'], $temp);
            }else{
                if(!empty($per->permissionId)){
                    $temp['id'] = $per->permissionId;
                    $temp['name'] = $per->permissionName;
                    $temp['description'] = $per->permissionDescription;
                    $temp['displayName'] = $per->permissionDisplayName;
                    array_push($res['permission'], $temp);
                }
            }
        }
        return $res;
    }

    public static function getAllPermissionByUser() {
        $first = DB::table('users as u')
                ->leftjoin('role_user as ru', 'ru.user_id', '=', 'u.id')
                ->leftjoin('permission_role as pr', 'pr.role_id', '=', 'ru.role_id')
                ->leftjoin('permissions as p', 'p.id', '=', 'pr.permission_id')
                ->select('p.id as permissionId', 'p.name as permissionName', 'p.description as permissionDescription', 'p.display_name as permissionDisplayName',
                           'u.id','u.name'
                        )
                ->whereRaw('(not FIND_IN_SET(p.id, u.user_except_permission) or u.user_except_permission is null)');

        
        $second = DB::table('users as u')
                ->join('user_permission as up', 'up.user_id', '=', 'u.id')
                ->join('permissions as p', 'p.id', '=', 'up.permission_id')
                ->select('p.id as permissionId', 'p.name as permissionName', 'p.description as permissionDescription', 'p.display_name as permissionDisplayName',
                           'u.id','u.name'
                        )
                ->whereRaw('(not FIND_IN_SET(p.id, u.user_except_permission) or u.user_except_permission is null)')
                ->union($first);
        $all = $second->get();
        
        $res = [];
        foreach ($all as $val) {
            $check = array_search($val->id, array_column($res, 'id'));
            if($check === false){
                $tempUser['id'] = $val->id;
                $tempUser['name'] = $val->name;
                $tempUser['permission'] = [];
                
                if(!empty($val->permissionId)){
                    $tempPermission['id'] = $val->permissionId;
                    $tempPermission['name'] = $val->permissionName;
                    $tempPermission['description'] = $val->permissionDescription;
                    $tempPermission['display_name'] = $val->permissionDisplayName;
                    array_push($tempUser['permission'], $tempPermission);
                }
                array_push($res, $tempUser);
            }else{
                if(!empty($val->permissionId)){
                    $tempPermission['id'] = $val->permissionId;
                    $tempPermission['name'] = $val->permissionName;
                    $tempPermission['description'] = $val->permissionDescription;
                    $tempPermission['display_name'] = $val->permissionDisplayName;
                    array_push($res[$check]['permission'], $tempPermission);
                }
            }
        }
        return $res;
    }
    
    public function getUserWithFullBrandPlus(){
        $userTB = 'users';
        $roleTB = 'roles';
        $useroleTB = 'role_user';
        $res = DB::table($useroleTB)
                ->join($userTB, $useroleTB . '.user_id', '=', $userTB . '.id')
                ->join($roleTB, $useroleTB . '.role_id', '=', $roleTB . '.id')
                ->where($userTB . '.is_active', '=', 0)
                ->where($userTB . '.status', '=', 0)
                ->select($userTB . '.id', $userTB . '.name', $userTB . '.email', $userTB . '.user_zone', $userTB . '.user_brand', $userTB . '.user_brand_plus', $roleTB . '.id as role_id', $roleTB . '.display_name', $roleTB . '.description')
                ->get();
        return $res;
    }
    
    public function saveUserBrand($param) {
        $user = User::find($param['id']);
        $user->user_zone = json_encode($param['user_zone']);
        $user->user_brand = json_encode($param['user_brand']);
        $user->user_brand_plus = json_encode($param['user_brand_plus']);
        $res = $user->save();
        return $res;
    }
    
    public function saveUserExceptPermission($param) {
        $user = User::find($param['id']);
        $user->user_except_permission = $param['user_except_permission'];
        $res = $user->save();
        return $res;
    }
}
