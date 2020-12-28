<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class PrecheckList extends Model {

    protected $table = 'prechecklist';
    public $timestamps = true;
    protected $fillable = [
        'obj_id', 'location_name', 'first_status', 'location_phone', 'description',
        'create_by', 'status', 'id_prechecklist_isc', 'section_survey_id', 'section_code', 'section_contract_num'];

    //Lưu CheckList
    public function savePCL($infoSave) {
        $result = PrecheckList::create(
                        [
                            'section_survey_id' => isset($infoSave['typeSurvey']) ? $infoSave['typeSurvey'] : null,
                            'section_code' => isset($infoSave['codedm']) ? $infoSave['codedm'] : null,
                            'section_contract_num' => isset($infoSave['contractNum']) ? $infoSave['contractNum'] : null,                            
                            'obj_id' => isset($infoSave['ObjID']) ? $infoSave['ObjID'] : '',                            
                            'location_name' => isset($infoSave['Location_Name']) ? $infoSave['Location_Name'] : '',
                            'location_phone' => isset($infoSave['Location_Phone']) ? $infoSave['Location_Phone'] : '',
                            'first_status' => isset($infoSave['FirstStatus']) ? $infoSave['FirstStatus'] : '',                                                        
                            'description' => isset($infoSave['Description']) ? $infoSave['Description'] : '',
                            'create_by' => isset($infoSave['CreateBy']) ? $infoSave['CreateBy'] : '',                            
                            'id_prechecklist_isc' => isset($infoSave['idPreChecklistIsc']) ? $infoSave['idPreChecklistIsc'] : NULL,
         
        ]);
        $res['code'] = 200;
        $res['msg'] = 'Successful';
        $res['data'] = $result;
        $res['idPCL'] = $result->attributes['id'];
        return $res;
    }

}
