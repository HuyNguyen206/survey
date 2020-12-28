<?php

namespace App\Models\Authen;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class GroupRole extends Model {

    protected $table = 'grouproles';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function getAllGroupRole() {
        $res = DB::table($this->table)
                ->select('*')
                ->get();
        return $res;
    }

    public function getGroupRoleByName($name) {
        $res = DB::table($this->table)
                ->select('*')
                ->where('name', $name)
                ->first();
        return $res;
    }
    
    public function getGroupRoleById($id) {
        $res = DB::table($this->table)
                ->select('*')
                ->where('id', $id)
                ->first();
        return $res;
    }

    public function addRoleToGroup($idGroup, $idRole) {
        $res = DB::table('grouprole_role')
                ->insert(['grouprole_id' => $idGroup, 'role_id' => $idRole]);
        return $res;
    }

    public function insertGroupRoleGetId($input) {
        $model = new GroupRole();
        $model->name = $input['name'];
        $model->display_name = $input['display_name'];
        $model->description = $input['description'];
        $model->save();
        return $model->id;
    }

    public function updateGroupRoleGetId($input) {
        $model = GroupRole::find($input['id']);
        $model->description = $input['description'];
        $model->save();
        return $model->id;
    }

    public function changeRoleToGroup($idGroup, $idRole) {
        $res = DB::table('grouprole_role')
                ->where('role_id', $idRole)
                ->update(['grouprole_id' => $idGroup]);
        return $res;
    }

    public function removeRoleFromAllGroup($idRole){
        $res = DB::table('grouprole_role')
                ->where('role_id', $idRole)
                ->delete();
        return $res;
    }
}
