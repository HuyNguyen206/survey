<?php

namespace App\Http\Controllers\Checklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Psy\Util\Json;
use Illuminate\Support\Facades\Auth;
use App\Models\Apiisc;
use App\Models\User;
use App\Models\FowardDepartment;
use Exception;
use DB;

class ForwardDepartmentController extends Controller {

    var $code = 400;
    var $msg = array();
    var $data = array();

    /**
     * Chuyển phòng ban khác
     */
    public function forwardDepartment(Request $request) {
        try {
//        print_r($request->all());die;
            if ($request->isMethod('post') && isset($request->datapost)) {
                $idSurvey = isset($request->datapost[0]['idSurvey']) ? $request->datapost[0]['idSurvey'] : NULL;
                $arrayDepartInfo = [1 => 'IBB', 2 => 'TIN/PNC', 3 => 'Telesale', 4 => 'CUS', 5 => 'CS chi nhánh', 6 => 'CSHO', 7 => 'KDDA', 8 => 'NVTC'];
                $arrayResultMsg = '';
                $success = false;
                $modelFwd = new FowardDepartment();
                $modelISC = new Apiisc();
                $correct = true;
                $count = 0;
                foreach ($request->datapost[0]['arrayValidDepart'] as $key => $value) {
                    $idDepartment = (int) $value['check'];
                    $idReason = $value['reason'];
                    $description = str_replace(";", " ", $value['description']);

                    $data = array('ObjID' => $request->datapost[0]['ObjID'],
                        'TableID' => $request->datapost[0]['TableID'],
                        'DepartmentTransfer' => $request->datapost[0]['Department'],
                        'LogonUser' => Auth::user()->name,
                        'Department' => $idDepartment,
                        'Reason' => $idReason,
                        'Description' => $description,
                        'typeSurvey' => $request->datapost[0]['typeSurvey'],
                        'codedm' => $request->datapost[0]['codedm'],
                        'contractNum' => $request->datapost[0]['contractNum'],
                    );
                    $result = json_decode($modelISC->forwardDepartment($data), 1);
                    $data['foward_id'] = $result[0]['Result'];

                    $result[0]['Result'] = 1;
                    if ($result[0]['Result'] > 0) {
                        $arrayResultMsg .= "\n Chuyển phòng ban " . $arrayDepartInfo[$idDepartment] . " thành công";
                        $resultFwd = $modelFwd->saveFWD($data, $idSurvey);
                        $success = true;
                    } else {
                        $arrayResultMsg .= "\n Chuyển phòng ban " . $arrayDepartInfo[$idDepartment] . " thất bại";
                    }
                    $idDepartment = '';
                    $idReason = '';
                    $description = '';
                }
                if ($success)
                    return json_encode(['code' => 200, 'msg' => $arrayResultMsg, 'idFwd' => $resultFwd['idFWD']]);
                else
                    return json_encode(['code' => 200, 'msg' => $arrayResultMsg]);
            }
        } catch (Exception $ex) {
            return (['msg' => $ex->getMessage()]);
        }
    }

}
