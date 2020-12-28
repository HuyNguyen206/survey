<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CheckList extends Model {

    protected $table = 'checklist';
    public $timestamps = true;
    protected $fillable = [
        'i_objid', 'i_type', 'final_status', 'final_status_id', 's_create_by', 'i_lnit_status', 's_description', 'i_modem_type',
        'bit_sub_assign', 'bit_cl_electric', 'bit_upgrade', 'supporter', 'sub_supporter',
        'dept_id', 'request_from', 'section_id', 'id_checklist_isc', 'section_survey_id', 'section_code', 'section_contract_num'];

//    /*
//     * lấy thông tin khách hàng từ database survey
//     */
//    public function getAccountInfoByContractNum( $Contractnum ){
//    	return DB::table('outbound_accounts')->where('contract_num','=',$Contractnum)->first();
//    
//    }
    //Lưu CheckList
    public function saveCL($infoSave) {
//        try {
//            if(!empty($infoSave['ContractNum'])){
//                $temp = explode('/ ',$infoSave['ContractNum']);
//                $contract = $temp[0];
//                $createContract = $temp[1];
//            }
        $result = CheckList::create(
                        [
                            'i_objid' => isset($infoSave['ObjId']) ? $infoSave['ObjId'] : '',
                            'i_type' => isset($infoSave['Type']) ? $infoSave['Type'] : '',
                            's_create_by' => isset($infoSave['CreateBy']) ? $infoSave['CreateBy'] : '',
                            'i_lnit_status' => isset($infoSave['Init_Status']) ? $infoSave['Init_Status'] : '',
                            's_description' => isset($infoSave['Description']) ? $infoSave['Description'] : '',
                            'i_modem_type' => isset($infoSave['ModemType']) ? $infoSave['ModemType'] : '', //'ten cong ty',
                            'bit_sub_assign' => isset($infoSave['SubAssign']) ? $infoSave['SubAssign'] : '', // 'số giấy đăng ký kinh doanh của KH đại lý (KH đăng ký gói Public)',
                            'bit_cl_electric' => isset($infoSave['CLElectric']) ? $infoSave['CLElectric'] : '',
                            'bit_upgrade' => isset($infoSave['Upgrade']) ? $infoSave['Upgrade'] : '',
                            'supporter' => isset($infoSave['Supporter']) ? $infoSave['Supporter'] : '',
                            'sub_supporter' => isset($infoSave['SubSupporter']) ? $infoSave['SubSupporter'] : '',
                            'dept_id' => isset($infoSave['DeptID']) ? $infoSave['DeptID'] : '',
                            'request_from' => isset($infoSave['RequestFrom']) ? $infoSave['RequestFrom'] : '',                           
                            'id_checklist_isc' => isset($infoSave['idChecklistIsc']) ? $infoSave['idChecklistIsc'] : null,
                            'section_survey_id' => isset($infoSave['typeSurvey']) ? $infoSave['typeSurvey'] : null,
                            'section_code' => isset($infoSave['codedm']) ? $infoSave['codedm'] : null,
                            'section_contract_num' => isset($infoSave['contractNum']) ? $infoSave['contractNum'] : null,
                            'final_status' => isset($infoSave['final_status']) ? $infoSave['final_status'] : null,
                            'final_status_id' => isset($infoSave['final_status_id']) ? $infoSave['final_status_id'] : null,                            
        ]);
        $res['code'] = 200;
        $res['msg'] = 'Successful';
        $res['data'] = $result;
        $res['idCL'] = $result->attributes['id'];
        return $res;
//        } catch (\Exception $ex) {
//            $res['code'] = 400;
//            $res['msg'] = $ex->getMessage();
//            $res['data'] = '';
//            return $res;
//        }
    }

    public function getAccountInfoByContract($contarct) {
        $result = OutboundAccount::select()->where('so_hd', "=", $contarct)->first();
        if (isset($result->id))
            return $result;
        return NULL;
    }

}
