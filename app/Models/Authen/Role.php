<?php

namespace App\Models\Authen;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    protected $table = 'roles';
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'created_at',
        'updated_at'
    ];

    public static function getRoleById($id) {
        $res = DB::table('roles')
            ->where('id',$id)
            ->select('*')
            ->orderBy('level', 'asc')
            ->first();
        return $res;
    }
    
    public function getAllRole() {
        $res = DB::table($this->table)
                ->select('*')
                ->orderBy('level', 'asc')
                ->get();
        return $res;
    }

    public function getAllRoleByLevel($level) {
        $res = DB::table($this->table)
                ->where('level', '>', $level)
                ->select('*')
                ->orderBy('level', 'asc')
                ->get();
        return $res;
    }

    public function insertRoleGetId($input) {
        $model = new Role();
        $model->name = $input['name'];
        $model->display_name = $input['display_name'];
        $model->description = $input['description'];
        $model->level = $input['level'];
        $model->save();
        return $model->id;
    }

    public function updateRoleGetId($input) {
        $model = Role::find($input['id']);
        $model->name = $input['name'];
        $model->display_name = $input['display_name'];
        $model->description = $input['description'];
        $model->level = $input['level'];
        $model->save();
        return $model->id;
    }

    public function getRoleGroupRoleByRoleId($id) {
        $res = DB::table($this->table . ' as r')
                ->leftjoin('grouprole_role as gr', 'r.id', '=', 'gr.role_id')
                ->leftjoin('grouproles as g', 'g.id', '=', 'gr.grouprole_id')
                ->select('r.name as roleName', 'r.description as roleDescription', 'r.display_name as roleDisplayName', 'r.level as roleLevel', 'g.name as groupName', 'g.description as groupDescription', 'g.display_name as groupDisplayName'
                )
                ->where('r.id', $id)
                ->get();

        $need = ['group' => []];

        foreach ($res as $val) {
            $need['roleName'] = $val->roleName;
            $need['roleDescription'] = $val->roleDescription;
            $need['roleDisplayName'] = $val->roleDisplayName;
            $need['roleLevel'] = $val->roleLevel;
            if (!empty($val->groupName)) {
                array_push($need['group'], $val->groupName);
            }
        }
        return $need;
    }

    public function getPermissionByRole() {
        $perrolTB = 'permission_role';
        $res = DB::table($perrolTB)
                ->select('role_id', 'permission_id')
                ->get();
        return $res;
    }
    
    public static function getAllRoleByUserId($id) {
        $res = DB::table('role_user')
                ->join('users', 'role_user.user_id', '=', 'users.id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', '=', $id)
                ->select('roles.display_name', 'roles.level', 'roles.id')
                ->first();
        return $res;
    }
    
    public static function changeRolebyUserId($userId, $roleId){
        $res = DB::table('role_user')
                ->where('user_id', '=', $userId)
                ->update(['role_id' => $roleId]);
        return $res;
    }
    
    public static function insertRoleByUserId($userId, $roleId){
        $res = DB::table('role_user')
                ->insert(['user_id' => $userId, 'role_id' => $roleId]);
        return $res;
    }
}
