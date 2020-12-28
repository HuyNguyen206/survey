<?php

namespace App\Models\Authen;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class GroupPermission extends Model {

    protected $table = 'grouppermissions';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function getAllGroupPermission() {
        $res = DB::table($this->table)
                ->select('*')
                ->get();
        return $res;
    }

    public function getGroupPermissionByName($name) {
        $res = DB::table($this->table)
                ->select('*')
                ->where('name', $name)
                ->first();
        return $res;
    }

    public function addPermissionToGroup($idGroup, $idPermission) {
        $res = DB::table('grouppermission_permission')
                ->insert(['grouppermission_id' => $idGroup, 'permission_id' => $idPermission]);
        return $res;
    }

    public function insertGroupPermissionGetId($input) {
        $model = new GroupPermission();
        $model->name = $input['name'];
        $model->display_name = $input['display_name'];
        $model->description = $input['description'];
        $model->save();
        return $model->id;
    }

    public function updateGroupPermissionGetId($input) {
        $model = GroupPermission::find($input['id']);
        $model->description = $input['description'];
        $model->save();
        return $model->id;
    }

    public function changePermissionToGroup($idGroup, $idPermission) {
        $res = DB::table('grouppermission_permission')
                ->where('permission_id', $idPermission)
                ->update(['grouppermission_id' => $idGroup]);
        return $res;
    }

    public function checkPermissionBelongToGroup($idPermission) {
        $res = DB::table('grouppermission_permission')
                ->where('permission_id', $idPermission)
                ->select('permission_id', 'grouppermission_id')
                ->first();
        return $res;
    }

    public static function getAllGroupPermissionHavePermission() {
        $all = DB::table('permissions as p')
                ->leftjoin('grouppermission_permission as gp', 'p.id', '=', 'gp.permission_id')
                ->leftjoin('grouppermissions as g', 'g.id', '=', 'gp.grouppermission_id')
                ->select('p.id','p.name', 'p.description', 
                        'p.display_name', 'g.name as groupName', 'g.id as groupId',
                        'g.description as groupDescription', 'g.display_name as groupDisplayName')
                ->get();
        $res = [];
        foreach($all as $val){
            $check = array_search($val->groupName, array_column($res, 'groupName'));
            if($check === false){
                $tempGroup['groupId'] = $val->groupId;
                $tempGroup['groupName'] = $val->groupName;
                $tempGroup['groupDescription'] = $val->groupDescription;
                $tempGroup['groupDisplayName'] = $val->groupDisplayName;
                $tempGroup['permission'] = [];
                
                $tempPermission['id'] = $val->id;
                $tempPermission['name'] = $val->name;
                $tempPermission['description'] = $val->description;
                $tempPermission['display_name'] = $val->display_name;
                array_push($tempGroup['permission'], $tempPermission);
                array_push($res, $tempGroup);
            }else{
                $tempPermission['id'] = $val->id;
                $tempPermission['name'] = $val->name;
                $tempPermission['description'] = $val->description;
                $tempPermission['display_name'] = $val->display_name;
                array_push($res[$check]['permission'], $tempPermission);
            }
        }
        return $res;
    }

}
