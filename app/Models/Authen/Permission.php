<?php

namespace App\Models\Authen;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model {

    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public static function getAllPermission(){
        $res = DB::table('permissions as p')
                ->leftjoin('grouppermission_permission as gp', 'p.id', '=', 'gp.permission_id')
                ->leftjoin('grouppermissions as g', 'g.id', '=', 'gp.grouppermission_id')
                ->select('p.name', 'p.description', 
                        'p.display_name', 'g.name as groupName', 
                        'g.description as groupDescription', 'g.display_name as groupDisplayName')
                ->get();
        return $res;
    }
    
    public static function getAllPermissionByRoleId($roleId){
        $res = DB::table('permission_role')
                ->where('role_id', '=', $roleId)
                ->select('*')
                ->get();
        return $res;
    }
//    public static function getAllPermissionById($id) {
//        $all = DB::table('permission_role')
//                ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
//                ->join('role_user', 'role_user.role_id', '=', 'permission_role.role_id')
//                ->where('role_user.user_id', '=', $id)
//                ->select('permission_role.role_id', 'permissions.name')
//                ->get();
//        $res = [];
//        foreach ($all as $role) {
//            array_push($res, $role->name);
//        }
//        return $res;
//    }

    public static function getAllPermissionByName($name) {
        $res = DB::table('permissions')
                ->select('id', 'name', 'display_name', 'description')
                ->where('name', '=', $name)
                ->get();
        return $res;
    }
    
    public function insertPermissionGetId($input) {
        $model = new Permission();
        $model->name = $input['name'];
        $model->display_name = $input['display_name'];
        $model->description = $input['description'];
        $model->save();
        return $model->id;
    }

    public function updatePermissionGetId($input) {
        $model = Permission::find($input['id']);
        $model->name = $input['name'];
        $model->display_name = $input['display_name'];
        $model->description = $input['description'];
        $model->save();
        return $model->id;
    }

    public function getPermissionGroupPermissionByPermissionId($id) {
        $res = DB::table($this->table . ' as p')
                ->leftjoin('grouppermission_permission as gp', 'p.id', '=', 'gp.permission_id')
                ->leftjoin('grouppermissions as g', 'g.id', '=', 'gp.grouppermission_id')
                ->select('p.name as permissionName', 'p.description as permissionDescription', 
                        'p.display_name as permissionDisplayName', 'g.name as groupName', 
                        'g.description as groupDescription', 'g.display_name as groupDisplayName')
                ->where('p.id', $id)
                ->first();
        return $res;
    }
    
    public function addPermissionToUser($userId, $permissionId){
        $res = DB::table('user_permission')
                ->insert(['user_id' => $userId, 'permission_id' => $permissionId]);
        return $res;
    }
    
    public function removePermissionFromUser($userId){
        $res = DB::table('user_permission')
                ->where('user_id',$userId)
                ->delete();
        return $res;
    }
    
    public function addPermissionToRole($roleId, $permissionId){
        $res = DB::table('permission_role')
                ->insert(['role_id' => $roleId, 'permission_id' => $permissionId]);
        return $res;
    }
    
    public function removePermissionFromRole($roleId){
        $res = DB::table('permission_role')
                ->where('role_id',$roleId)
                ->delete();
        return $res;
    }
}
