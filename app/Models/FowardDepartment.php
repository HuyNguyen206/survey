<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class FowardDepartment extends Model {

    protected $table = 'foward_department';
    public $timestamps = true;
    protected $fillable = [
        'obj_id', 'table_id', 'department_transfer', 'logon_user', 'department', 'reason',
        'description', 'time_create', 'section_id', 'section_survey_id', 'section_code', 'section_contract_num', 'foward_id'];

    //Lưu CheckList
    public function saveFWD($infoSave, $idSurvey) {
        $result = FowardDepartment::create(
                        [
                            'obj_id' => isset($infoSave['ObjID']) ? $infoSave['ObjID'] : '',
                            'table_id' => isset($infoSave['TableID']) ? $infoSave['TableID'] : '',
                            'department_transfer' => isset($infoSave['DepartmentTransfer']) ? $infoSave['DepartmentTransfer'] : '',
                            'logon_user' => isset($infoSave['LogonUser']) ? $infoSave['LogonUser'] : '',
                            'department' => isset($infoSave['Department']) ? $infoSave['Department'] : '',
                            'reason' => isset($infoSave['Reason']) ? $infoSave['Reason'] : '',
                            'description' => isset($infoSave['Description']) ? $infoSave['Description'] : '',
                            'section_survey_id' => isset($infoSave['typeSurvey']) ? $infoSave['typeSurvey'] : null,
                            'section_code' => isset($infoSave['codedm']) ? $infoSave['codedm'] : null,
                            'section_contract_num' => isset($infoSave['contractNum']) ? $infoSave['contractNum'] : null,
                            'section_id' => $idSurvey,
                            'foward_id' => isset($infoSave['foward_id']) ? $infoSave['foward_id'] : null,
        ]);
        $res['code'] = 200;
        $res['msg'] = 'Successful';
        $res['data'] = $result;
        $res['idFWD'] = $result->attributes['id'];
        return $res;
    }

}
