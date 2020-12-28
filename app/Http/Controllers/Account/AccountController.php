<?php

namespace App\Http\Controllers\Account;

use DB;
use Session;
use Redis;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Psy\Util\Json;
use Illuminate\Support\Facades\Auth;
use App\SQLServer;
use App\Models\OutboundAccount;
use App\Models\Apiisc;
use App\Models\SurveySections;
use App\Models\ContactProfile;
use App\Models\App\Models;
use App\Models\AccountProfiles;
use App\Models\User;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Component\HelpProvider;
use Illuminate\Support\Facades\App;

class AccountController extends Controller {

    /**
     * hiển thị danh sách surver cho nhân viên chăm sóc khách hàng lựa chọn cho phù hợp với đối tượng KH
     * @return Response
     */
    public function index(Request $request) {
        return $this->search($request);
    }

     /*
      Tìm khách hàng có số hợp đồng
     */
    public function search($contractNum, $type, $code) {
//         App:: setLocale('en');
        $surveySec = new SurveySections();
        $resultCodes = $surveySec->checkExistCodes($code, $type, $contractNum);
        //Đã khảo sát trước đó
        if (!empty($resultCodes)) {
            //Gặp người sử dụng, cho chỉnh sửa
            if ($resultCodes[0]->section_connected == 4) {
                $roleID = User::getRole(Auth::user()->id);
                $timeLimit = ($roleID == 36) ? 'P30D' : 'PT5M';
                $messageEdit = ($roleID == 36) ? 'Khảo sát này đã vượt quá 30 ngày để sửa' : 'Khảo sát này đã vượt quá 5 phút để sửa';
                $currentDate = new \DateTime();
                $time_complete = new \DateTime($resultCodes[0]->section_time_completed);
                $time_complete->add(new \DateInterval($timeLimit));
                if ($time_complete < $currentDate) {
                    $result = array('code' => 650, 'msg' => $messageEdit, 'idSur' => $resultCodes[0]->section_id);
                    return Json::encode($result);
                } else {
                    $result = array('code' => 600, 'idSur' => $resultCodes[0]->section_id);
                    return Json::encode($result);
                }
            }
            //Không gặp được KH, cho thử lại
            else {
                $result = array('code' => 700, 'idSur' => $resultCodes[0]->section_id);
                return Json::encode($result);
            }
        }
        //Chưa khảo sát
        else {
            if (empty($contractNum)) {
                $result['code'] = 400; //không có dữ liệu
                $result['message'] = 'Bad Request';
                return $result;
            }
            $infoAcc = array('ObjID' => 0,
                'Contract' => $contractNum,
                'IDSupportlist' => $code,
                'Type' => $type
            );

            /*
             * Lấy thông tin khách hàng
             */
            $apiIsc = new Apiisc();
            $responseAccountInfo = $apiIsc->GetFullAccountInfo($infoAcc);  
//            dump($responseAccountInfo);
            $responseAccountInfo = json_decode($responseAccountInfo);
            if ($responseAccountInfo->statusCode != 200) {              
                return ['resultStatus' => $responseAccountInfo->data,'code'=>$responseAccountInfo->statusCode];
                exit();
            } 
//            else if ($responseAccountInfo['resultCode'] == 3) {
//                return ['resultStatus' => 'Yêu cầu không hợp lệ'];
//                exit();
//            } 
//            else
                $responseAccountInfo = $responseAccountInfo->data;
            // end lấy thông tin khách hàng
            // nếu không lấy được thông tin khách hàng return false
            if (!isset($responseAccountInfo[0]->ObjID)) {
                $result['code'] = 400; //không có dữ liệu
                $result['msg'] = 'Bad Request';
                return Json::encode($result);
            }         
            $outboundAccount = new OutboundAccount();
               $helpProvider= new HelpProvider();
            $responseAccountInfo[0] = $helpProvider->processDataFromISC($responseAccountInfo[0]);
            $accountInfoISC = (array) $responseAccountInfo[0];
            // lấy thông tin khách hàng trong database survey
            $accountInfo = $outboundAccount->getAccountInfoByContractNum($contractNum);
            // update hoặc insert thông tin khách hàng
            $outboundAccount->saveAccount($accountInfoISC);
            $startDate = $responseAccountInfo[0]->ContractDate;
            $responseAccountInfo[0]->ContractDate = $startDate; //date("d-m-Y", $startDate );
            $responseInfo['data_cusinfo'] = $responseAccountInfo;
            //Thông tin chủ hợp đồng
            $infoContact = ['phone' => $responseAccountInfo[0]->Phone, 'name' => $responseAccountInfo[0]->CustomerName, 'relationship' => 4];
            $responseInfo['infoContact'] = $infoContact;

            $responseInfo['msg'] = 'Success';
            $responseInfo['code'] = '200';
            // nếu có thông tin khách hàng thì lấy thông tin lịch sử hỗ trợ
            $ObjID = $responseAccountInfo[0]->ObjID;
            //Thong tin bang thong
            $paramHistory = array('ObjID' => $ObjID, 'RecordCount' => 3);
            $historyData = json_decode($apiIsc->getCallerHistoryByObjID($paramHistory));
            $responseInfo['data_history'] = $historyData;
            /*
             * kiểm tra thông tin khách hàng đã lưu trên Mo hay chưa
             * Nếu lưu rồi lấy thông tin lịch sử survey
             */
            $responseInfo['accountInfoFromSurvey'] = $outboundAccount->getAccountInfoByContract($infoAcc['Contract']);
            $hasNPS = FALSE;
            if (isset($responseInfo['accountInfoFromSurvey']->id)) {
                $SurveySections = new SurveySections();
                $accountInfoFromSurvey = $SurveySections->getAllSurveyInfoOfAccount($responseInfo['accountInfoFromSurvey']->id);
                $historyOutboundSurvey = array();
                $dateSurveyTemp = FALSE;
                foreach ($accountInfoFromSurvey as $i) {
                    // chi tiết từng khảo sát
                    $i->resultDetail = $SurveySections->getAllDetailSurveyInfo($i->section_id);
                    // kiểm tra câu hỏi NPS
                    // nếu câu hỏi có NPS lấy thời gian hoàn so sánh với thời gian hoàn thanh NPS của các câu khảo sát khác.

                    $content = '';
                    $temp = $i->resultDetail;
                    foreach ($i->resultDetail as $d) {
//        			var_dump($i->resultDetail);die;
                        $flag = NULL;
                       if ($d->question_id != $flag) {
                            $flag = $d->question_id;
                            $content .= '<b>' . $d->question_title_short . ': </b>';
                        }
                        if($d->answers_title == 'Chưa trả lời')
                        {
                             $content .= $d->answers_extra_title . " => ".$d->answers_title . ", ";
                        }
                        else
                        {
                        $content .= $d->answers_title . ", ";
                        }
//        			if(($d->question_is_nps==1) && isset($d->question_is_nps)){
                        if ($d->question_is_nps == 1) {
                            if ($i->section_time_completed > $dateSurveyTemp) {
                                $dateSurveyTemp = $i->section_time_completed;
                            }
                        }
                    }
                    $i->content = $content;
                    $historyOutboundSurvey[] = (array) $i;
                }
                $responseInfo['last_nps_time'] = $dateSurveyTemp;
                if ($dateSurveyTemp != FALSE) {

                    $currentDate = new \DateTime();
                    $lastest_survey_nps_time = new \DateTime($dateSurveyTemp);
                    $interval = $lastest_survey_nps_time->diff($currentDate)->format("%a");
                    if ($interval < 90) {
                        $hasNPS = TRUE;
                    }
                    $responseInfo['interval'] = $interval;
                }
                $responseInfo['outbound_history'] = $historyOutboundSurvey;
            }

            $responseInfo['NPS'] = $hasNPS;
            $responseInfo['section_time_start'] = date('Y-m-d H:i:s');
           
//            $accountInfo=(array)$responseInfo['data_cusinfo'][0];
//             dump($accountInfo);die;
//            return $responseInfo;
//             dump($responseInfo);die;
            $dataReturn=['responseInfo'=>$responseInfo];
            switch($type)
            {
                case 1:
                {
                   return view('surveys.createDeployEmpty', $dataReturn);
                   break;
                }
                case 2:
                {
                   return view('surveys.createMaitain', $dataReturn);
                   break;
                }
            }
            
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request) {
        $account = new Account;
    }

    /*
     * Lấy thông tin khách hàng
     * step1: gọi store ISC lấy thông tin
     * step2: Nếu không có thì truy cấp database mo lấy dự liệu
     */

    //store goi lay thong tin khach hang
    public function getAccountInfo($info) {

        $account = new Account;
        //store gọi lấy thông tin khách hàng, output ít thông tin cơ bản: ObjID: Mã HĐ, Contract: Số HĐ, FullName: Tên đầy đủ, Status: Tình trạng, Passport: CMND, Address: Địa chỉ KH
        $fullInfo = '';
        $response = array('code' => 404);
        $resultAccountFromISC = $this->getAccountInfoFromISC($info); // gọi store lấy từ ISC	
        return $resultAccountFromISC;

        if ($resultAccountFromISC['code'] == 200) {
            return $resultAccountFromISC;
        }
        return $response;
        /*
         * không có dữ liệu từ ISC truy cập database Mo lấy dữ liệu khách hàng
         * tạm thời không truy cập vào database mo nêu không tìm thấy dữ liệu từ ISC
         */
        // return $this->getAccountInfoFromMo($info);
    }

    private function getHistorySupport($iObjid) {
        $account = new Account;
        $result = $account->StoreGetHistorySup($iObjid); //Store lấy lịch sử hỗ trợ

        if (!empty($result)) {
            return $result;
        }
        return false;
        /*
         * không có dữ liệu từ ISC truy cập database Mo lấy dữ liệu khách hàng
         * tạm thời không truy cập vào database mo nêu không tìm thấy dữ liệu từ ISC
         */
    }

    public function save(Request $request) {
        $AccountProfiles = new AccountProfiles;
        $accountInfo = $request->datapost;
        if (!empty($accountInfo['ContractNum'])) { // có truyền thông tin số hợp đồng
            $AccountProfilesVN = $AccountProfiles->getAccountProfilesByContract($accountInfo['ContractNum']);
            $accountProfilesStore = array(
                "ap_fullname" => $accountInfo['CustomerName'],
                "ap_sex" => $accountInfo['Sex'],
                "ap_address_id" => $accountInfo['Address'],
                "ap_address_bill" => $accountInfo['BillTo'],
                "ap_address_setup" => $accountInfo['ObjAddress'],
                "ap_user_update" => Auth::user()->id
            );
            if (!empty($accountInfo['Birthday'])) {
                $accountProfilesStore["ap_birthday"] = date('Y-m-d', strtotime($accountInfo['Birthday']));
            }
            if (isset($AccountProfilesVN->ap_contract)) {
                if ($AccountProfiles->updateAccountProfiles($request->datapost['ContractNum'], $accountProfilesStore)) {
                    return json_encode(['code' => 200]);
                }
            } else {
                $accountProfilesStore["ap_contract"] = $request->datapost['ContractNum'];
                if ($AccountProfiles->insertAccountProfiles($accountProfilesStore)) {
                    return json_encode(['code' => 200]);
                }
            }
        }
        return json_encode(['code' => 404]);
    }

    /*
     * nếu chưa có account_profiles thì lưu mới 
     * nếu có rồi thì kiểm tra xem thông tin ISC trả về lần gấn nhất và hiện tại có khác nhau không
     * khác thì update nếu giống thì return lại - không làm gì
     */

    private function saveAccountProfiles($accountCurrent, $accountStored = NULL) {
        $AccountProfiles = new AccountProfiles;
        //$accountCurrent = (array)$accountCurrent;
        $accountStored = (array) $accountStored;
        if (empty($accountStored['contract_num'])) {
            $accountProfiles = array(
                "ap_contract" => $accountCurrent['ContractNum'],
                "ap_fullname" => $accountCurrent["CustomerName"],
                "ap_birthday" => $accountCurrent["Birthday"],
                "ap_sex" => $accountCurrent["Sex"],
                "ap_address_id" => $accountCurrent["Address"],
                "ap_address_bill" => $accountCurrent["BillTo"],
                "ap_address_setup" => $accountCurrent["ObjAddress"],
                "ap_user_update" => Auth::user()->id
            );
            $AccountProfiles->insertAccountProfiles($accountProfiles);
        } else {
            $accountProfiles = array();
            //var_dump($accountStored);
//            if ($accountCurrent["CustomerName"] != $accountStored['customer_name']) {
                $accountProfiles['ap_fullname'] = $accountCurrent["CustomerName"];
//            }
//            if ($accountCurrent["Birthday"] != $accountStored['birthday']) {
                $accountProfiles['ap_birthday'] = $accountCurrent["Birthday"];
//            }
//            if ($accountCurrent["Sex"] != $accountStored['sex']) {
                $accountProfiles['ap_sex'] = $accountCurrent["Sex"];
//            }
//            if ($accountCurrent["Address"] != $accountStored['address']) {
                $accountProfiles['ap_address_id'] = $accountCurrent["Address"];
//            }
//            if ($accountCurrent["BillTo"] != $accountStored['address_bill_to']) {
                $accountProfiles['ap_address_bill'] = $accountCurrent["BillTo"];
//            }
//            if ($accountCurrent["ObjAddress"] != $accountStored['obj_address']) {
                $accountProfiles['ap_address_setup'] = $accountCurrent["ObjAddress"];
//            }


            $AccountProfiles->updateAccountProfiles($accountCurrent['ContractNum'], $accountProfiles);
        }
    }

    /*
     * So sánh dữ liệu nếu mới khác cũ thì lấy mới
     */

    private function getfieldAfterCompare($accountCurrent, $accountStored) {
        if ($accountCurrent != $accountStored)
            return TRUE;
        return FALSE;
    }

}
