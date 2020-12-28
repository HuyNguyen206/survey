<?php

namespace App\Http\Controllers\Checklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Psy\Util\Json;
use Illuminate\Support\Facades\Auth;
use App\Models\Apiisc;
use App\Models\User;
use App\Models\CheckList;
use App\Models\PrecheckList;
use App\Helpers\Helper;
use App\Models\Api\ApiHelper;
use DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

//use Exception;

class CheckListController extends Controller {

    var $code = 400;
    var $msg = array();
    var $data = array();
    var $lang;
    
    public function __construct()
    {
        $this->lang = Session::has('languageLocale') ? Session::get('languageLocale') : 'en';
    }
    public function getNameUser(Request $request) {
        try {
            $name = User::find(Auth::user()->id)->name;
            $apiIsc = new Apiisc();
                $input = array('ObjID' => $request->ObjID, 'Grouppoints' => $request->Grouppoints, 'POP' => $request->POP, 'Type' => $request->Type, 'InitStatus' => $request->InitStatus);
                $result = json_decode($apiIsc->GetSubTeamID($input), true);
                $responseSubID = $result['data'];
            return (['responseSubID' => $responseSubID, 'name'=>$name, 'code' => 200]);
        } catch (Exception $ex) {
            return (['msg' => $ex->getMessage(), 'code' => 500]);
        }
    }

    public function getDateInfo(Request $request) {
        try {
            $apiIsc = new Apiisc();
            $data = $request->datapost;
            $data['AppointmentDate'] = date("Y/m/d", strtotime($data['AppointmentDate']));
            $reponseDateInfo = json_decode($apiIsc->PartnerTimezoneAbility_List($data), true);
            return (['code' => 200, 'reponseDateInfo' => $reponseDateInfo['data']]);
        } catch (Exception $ex) {
            return (['msg' => $ex->getMessage()]);
        }
    }

    public function createPCL(Request $request) {
        try {
           
            $listViEnResult=[0=>['vi'=>'So hop dong khong dung', 'en'=>'Contract Number is incorrect'],
                1=>['vi'=>'Thanh cong', 'en'=>'Success'], 2=>['vi'=>'Co PreChecklist moi tao hoac dang xu ly', 'en'=>'PreCheckList being processed, cannot create'],
                3=>['vi'=>'Khach hang nay da tao Checklist', 'en'=>'Customer have been created CheckList'],
                4=>['vi'=>'Tinh trang HD hien tai khong duoc tao PreCL', 'en'=>'Current status of Contract can not create PreCL'], 
                5=>['vi'=>'Phong ban khong co quyen tao Prechecklist', 'en'=>'Department does not have permission to create PreCL']];
            $Prechecklist = new PrecheckList();
            $modalISC = new Apiisc();
            $contracNumber = isset($request->contractNum) ? $request->contractNum : null;
            $typeSurvey = isset($request->typeSurvey) ? $request->typeSurvey : null;
            $codeSurvey = isset($request->codedm) ? $request->codedm : null;
            $arrayDataPCL = $request->input;
//                $arrayDataPCL['Timezone'] = '';
            $arrayDataPCL['Description'] = Helper::convert_vi_to_en($arrayDataPCL['Description']);
            //Lấy dữ liệu từ ISC, server live và test
            $resultPCLIsc = json_decode($modalISC->CreatePreChecklist($arrayDataPCL));
            $data = $resultPCLIsc->data->Table;
            $resultCode = $data[0]->ResultID;
            $IDPreCL = $data[0]->IDPreCL;           
            if(isset($listViEnResult[$resultCode]))
            {
                $msg=$listViEnResult[$resultCode][$this->lang];
            }
            else
            {
                $msg = $data[0]->ResultText;
            }
            //Tạo PreCL thành công
            if ($resultCode == 1) {
                $arrayDataPCL['typeSurvey'] = $typeSurvey;
                $arrayDataPCL['codedm'] = $codeSurvey;
                $arrayDataPCL['contractNum'] = $contracNumber;
                $arrayDataPCL['idPreChecklistIsc'] = $IDPreCL;
                $resultCL = $Prechecklist->savePCL($arrayDataPCL);
//                            $this->storeActionSurvey($uniqueCase, '', $resultCL['idPCL'], $state);
                $returnData = ['msg' => $msg, 'idPCL' => $resultCL['idPCL']];
            } else {
                $returnData = ['msg' => $msg];
            }
            return $returnData;
        } catch (Exception $ex) {
            return (['msg' => $ex->getMessage()]);
        }
    }
    
    public function createCL(Request $request)
    {
         try {
            // dump($request->input());die;
             $modalISC = new Apiisc();
            $getData = $request->datapost;
            $contracNumber = isset($getData['contractNum']) ? $getData['contractNum'] : null;
            $typeSurvey = isset($getData['typeSurvey']) ? $getData['typeSurvey'] : null;
            $codeSurvey = isset($getData['codedm']) ? $getData['codedm'] : null;
            unset($getData['contractNum']);
            unset($getData['typeSurvey']);
            unset($getData['codedm']);
                $checklist = new CheckList();
                $arrayDataCL = $getData;
                $arrayDataCL['Description'] = Helper::convert_vi_to_en($arrayDataCL['Description']);
                //Tạo checklist bên ISC
                $resultCLIsc = json_decode($modalISC->SupportListDSLCreate($arrayDataCL), true);
                $idCL=$resultCLIsc['data'][0]['ReturnValue'];
                $arrayDataCL['idChecklistIsc'] = $idCL;
                $arrayDataCL['final_status_id'] = $idCL != 0 ? 0 : NULL;
                $arrayDataCL['final_status'] = $idCL != 0 ? "Da phan cong" : NULL;
                //Tạo checklist thất bại
                if ($idCL <= 0) {
                    return (['msg' => ($this->lang == 'vi') ? 'Tạo CheckList thất bại' : 'Fail to create checklist']);
                }
                //Tạo checklist thành công
                else {
                    $arrayDataCL['typeSurvey'] = $typeSurvey;
                    $arrayDataCL['codedm'] = $codeSurvey;
                    $arrayDataCL['contractNum'] = $contracNumber;
                    $resultCL = $checklist->saveCL($arrayDataCL);
                    $arrayData = $request->datadate;
                    //Có gán múi giờ phân công
                    if (isset($arrayData['Timezone']))
                    {
                        $arrayData['SupID'] = $idCL;
                    //Gán múi giờ phân công cho checklist vừa tạo
                    $arrayData['AppointmentDate'] = date("Y-m-d", strtotime($arrayData['AppointmentDate']));
                    $resultAssignDate = json_decode($modalISC->SupportList_Assign_Insert($arrayData), true);
                    if ($resultAssignDate['data'] === 0) {
                        return (['msg' => ($this->lang == 'vi') ? 'Tạo CheckList và gán ngày phân công thành công' : 'Create checklist and assign date success ', 'idCL' => $resultCL['idCL']]);
                    } else {
                        return (['msg' => ($this->lang == 'vi') ? 'Tạo CheckList thành công, gán ngày phân công thất bại' : 'Create checklist success, assign date fail']);
                    }
                }
                else
                {
                    return (['msg' => ($this->lang == 'vi') ? 'Tạo CheckList thành công' : 'Create checklist success ', 'idCL' => $resultCL['idCL']]);
                }
       
                }
                 } catch (Exception $ex) {
            return (['msg' => $ex->getMessage()]);
        }
    }

    //Hàm xem CheckList, không có thì trả về mảng rỗng
    public function getCheckList(Request $request) {
        try {
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->SupportListGetByObjID(['ObjID' => $request->ObjID]), true);
            //Không có dữ liệu checklist trả về->tạo checklist mới
            if (empty($result['data'])) {
                return (['code' => 200]);
            } else {
                //Danh sách id các trạng thái hoàn thành checklist
                $resultEva = array();
                $listFinalStatus = array(1, 3, 4, 97, 98, 99, 100);
                $check = 0;
                foreach ($result['data'] as $key => $value) {

                    if (in_array($value['Final_Status'], $listFinalStatus) == false) {
                        $check++;
                        $value['onCom'] = false;
                        $value = (object) $value;
                    } else {
                        $value['onCom'] = true;
                        $value = (object) $value;
                    }
                    array_push($resultEva, $value);
                }
                //CheckList đã hoàn thành->tạo checklist mới
                if ($check == 0) {
                    return (['code' => 400, 'data' => $resultEva]);
                }
                //Tồn tại checklist chưa hoàn thành
                else {
                    return (['code' => 600, 'data' => $resultEva]);
                }
            }
        } catch (Exception $ex) {
            return (['msg' => $ex->getMessage()]);
        }
    }

    //Hàm xem CheckList, không có thì trả về mảng rỗng
    public function checkRemainPTC(Request $request) {
        try {
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->checkRemainPTCApi(['iObjId' => $request->ObjID]), true);
            return (['data' => $result['data']['0']['Statusinf']]);
        } catch (Exception $ex) {
            return (['data' => $ex->getMessage()]);
        }
    }

    //Hàm xem CheckList, không có thì trả về mảng rỗng
    public function SupportListCheck(Request $request) {
        try {
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->SupportListCheckApi(['iObjId' => $request->ObjID]), true);
            $hasClStore = !empty($result['data']) ? true : false;
            return (['hasClStore' => $hasClStore, 'code' => 200]);
        } catch (Exception $ex) {
            return (['data' => $ex->getMessage(), 'code' => 500]);
        }
    }

    //Hàm xem hợp đồng có rule vật lý hay ko
    public function checkRulePhysical(Request $request) {
        try {
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->checkRulePhysicalApi(['Contract' => $request->contract]), true);
            return (['data' => $result['Data'][0]]);
        } catch (Exception $ex) {
            return (['data' => $ex->getMessage()]);
        }
    }

    //Hàm xem hợp đồng có rule vật lý hay ko, có thì lấy thông tin đối tác, tổ con
    public function checkCreateCl(Request $request) {
        try {
            $modalISC = new Apiisc();
            $request->iInit_Status = 1;
            $init_status = ($request->iInit_Status == '1' ? 4 : ( $request->iInit_Status == '2' ? 50 : 104 ));
            $result = json_decode($modalISC->checkCreateClApi(['iInit_Status' => $init_status, 'iObjID' => $request->iObjID]), true);
            return (['data' => $result['data'][0]]);
        } catch (Exception $ex) {
            return (['data' => $ex->getMessage()]);
        }
    }

//Hàm xem PreCheckList, không có thì trả về mảng rỗng
    public function getPreCheckList(Request $request) {
        try {
            $modalISC = new Apiisc();
            $result = $modalISC->GetPreChecklistByObjID(['Objid' => $request->ObjID]);
            $result = json_decode($result)->data->Table;
            //Không có dữ liệu checklist trả về
            //Không có dữ liệu Prechecklist trả về->tạo Prechecklist mới
            if (empty($result)) {
                return (['code' => 200]);
            } else {
                $resultEva = array();
                $check = 0;
                foreach ($result as $key => $value) {
                    if ($value->SupStatus != 3) {
                        $value = (array) $value;
                        $check++;
                        $value['onCom'] = false;
                        $value = (object) $value;
                    } else {
                        $value = (array) $value;
                        $value['onCom'] = true;
                        $value = (object) $value;
                    }
                    array_push($resultEva, $value);
//                    $check++;
                }
                //PreCheckList đã hoàn thành->tạo Prechecklist mới
                if ($check == 0) {
                    return (['code' => 400, 'data' => $resultEva]);
                }
                //Tồn tại Prechecklist chưa hoàn thành
                else {
                    return (['code' => 600, 'data' => $resultEva]);
                }
            }
        } catch (Exception $ex) {
//            dump(1232);
            return (['msg' => $ex->getMessage()]);
        }
    }

    public function GetFirstStatusName() {
        try {
            $listStatusApiUpdate=[];
            $listFirstStatus=[1=>['vi'=>'Mất kết nối','en'=>'Disconnect'], 2=>['vi'=>'Mạng chậm','en'=>'Slow Network'], 3=>['vi'=>'Mạng không ổn định','en'=>'Network connection is not stable']
                ,4=>['vi'=>'Tình trạng khác','en'=>'Other Status'],5=>['vi'=>'Yêu cầu nhập Checklist','en'=>'Request Enter Checklist'],6=>['vi'=>'TV OTT','en'=>'TV OTT'],
                7=>['vi'=>'Wifi','en'=>'Wifi']];
            $name = User::find(Auth::user()->id)->name;
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->GetFirstStatusNameApi(), true);          
            $listStatusApi=$result['data']['Table'];
//              dump($listStatusApi);
            foreach ($listStatusApi as $key => $value) {
                if(isset($listFirstStatus[$value['ID']]))
                {
                    array_push($listStatusApiUpdate, ['ID' => $value['ID'], 'Name' =>$listFirstStatus[$value['ID']][$this->lang]]);
                }
                else
                {
                    array_push($listStatusApiUpdate, $value);
                }
            }
            return (['name' => $name, 'listFirtStatus' => $listStatusApiUpdate, 'code' => 200]);
        } catch (Exception $ex) {
            return (['code' => 500, 'msg' => $ex->getMessage()]);
        }
    }

    public function supportListRemainCheck(Request $request) {
        try {
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->supportListRemainCheckApi(['iObjID' => $request->ObjID]), true);
            if ($result['StatusCode'] == 200 && isset($result['data'][0]['StatusCL'])) {
                return (['StatusCL' => $result['data'][0]['StatusCL'], 'code' => 200, 'msg' => 'Gọi qua thành công']);
            } else {
                return (['code' => 500, 'msg' => 'Dữ liệu trả về từ api ISC không hợp lệ']);
            }
        } catch (Exception $ex) {
            return (['code' => 500, 'msg' => $ex->getMessage()]);
        }
    }
    
    public function getGroupPoint(Request $request)
    {
         try {
            $modalISC = new Apiisc();
            $result = json_decode($modalISC->getGroupPointApi(['Contract' => $request->ContractNum]), true);
            if ($result['StatusCode'] == 200 ) {
                if(!empty( $groupPoint=$result['data']))
                {
                $groupPoint=$result['data'][0]['TDName'];
                $pop= explode('.', $groupPoint)[0];
                }
                else
                {
                    $groupPoint=$pop='';
                }
                return (['groupPoint' => $groupPoint,'pop' => $pop, 'code' => 200 ]);
            } else {
                return (['code' => 500, 'msg' => 'Có lỗi xảy ra']);
            }
        } catch (Exception $ex) {
            return (['code' => 500, 'msg' => $ex->getMessage()]);
        }
    }

}
