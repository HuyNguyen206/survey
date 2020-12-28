<?php

/*
 * Controlers kết nối tới API của ISC
 * 
 */

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiHelper;
use App\Models\Api\ApiQGD;
use App\Models\Api\ApiTele;
use App\Models\ListEmailQGD;
use App\Models\PushNotification;
use App\Models\SurveySectionsMG;
use App\Models\Apiisc;
use DB;
use Exception;
use App\Component\ExtraFunction;
use App\Models\SummaryCsat;
use App\Models\SummaryTime;
use Illuminate\Support\Facades\Mail;
use App\Component\HelpProvider;
use App\Models\Api\ApiMobi;
use App\Models\Api\ApiSale;
use App\Models\SummaryBranches;
use App\Models\SummaryNps;
use App\Models\SurveySections;
use App\Models\Surveys;
use App\Models\SurveyResult;
//use Illuminate\Support\Facades\DB;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Models\OutboundAccount;
use App\Models\ContactProfile;
use App\Models\SurveySectionsEmail;
use App\Models\ApiTransactionLog;
use App\Models\OutboundQuestions;
use App\Models\AccountProfiles;
use App\Models\PrecheckList;
use App\Models\CheckList;
use Illuminate\Http\Request;
use App\Models\ListInvalidSurveyCase;

class TestController extends Controller {

    var $link_API = 'http://cemcc.fpt.net/';
    var $input;

    public function test() {
        $help = new ApiHelper();

        $param['sectionId'] = 16104;
        $result = $help->checkSendMail($param);
        dump($result);
//        die;
        if($result['status']){
            $help->prepareSendMail($param, $result);
        }
    }

    private function isSendAPI($param) {
        $model_notification = new PushNotification();
        $out = $model_notification->getPushNotificationToCheckDuplicate($param);
        if (count($out) < 1) {
            $send = false;
        } else {
            $send = true;
            if ($out->confirm_code != $param['confirm_code']) {
                $send = false;
            }
        }
        return $send;
    }

    private function sendAPI($type) {
        $sale = new ApiSale();
        $mobi = new ApiMobi();
        $tele = new ApiTele();
        $modelListEmailQGD = new ListEmailQGD();
        $modelHelper = new HelpProvider();
        $date = date('Y-m-d H:i:s');

        //Gọi api của sale, net tùy trường hợp
        switch ($type) {
            case 'sale':
                $res = $sale->pushNotificationToSale($this->input[$type]);
                break;
            case 'net':
                $res = $mobi->pushNotificationToNet($this->input[$type]);
                $param['push_notification_subjects'] = $this->input['paramMail']['subject'];
                break;
            case 'tele':
                $res = $tele->pushNotificationToISCGetEmailList($this->input[$type]);
                if (!$res['error']) {
                    $tempRes = json_decode($res['output'], 1);
                    try {
                        $modelHelper->sendMail($this->input, $tempRes['msg']['data'][0]['EmailLeader']);
                    } catch (Exception $ex) {
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'cl':
                $res = $mobi->pushNotificationToISCGetEmailList($this->input[$type]);
                break;
            case 'qgd':
                $paramQGD['location_id'] = $this->input['paramMail']['location_id'];
                $paramQGD['branch_code'] = $this->input['paramMail']['branch_code'];
                $resQGD = $modelListEmailQGD->getListEmail($paramQGD);
                $res['error'] = false;
                $res['output'] = json_encode($resQGD);
                $res['msg'] = 'Có list mail';
                if (empty($resQGD)) {
                    $res['error'] = true;
                    $res['msg'] = 'Không có list mail';
                }
                break;
            default:
                return;
        }

        if ($res['error']) {
            $status = 0;
        } else {
            $status = 1;
        }

        $param['confirm_code'] = $this->input['paramMail']['confirm_code'];
        $param['api_status'] = $status;
        $param['api_created_at'] = $date;
        $param['api_last_sent_at'] = $date;
        $param['api_output'] = $res['output'];
        $param['api_message'] = $res['msg'];
        $param['api_send_count'] = 1;


        if ($status) {
            $temp = json_decode($param['api_output'], 1);

            //Bổ sung thông tin mail đối với trường hợp gọi api net thành công
            if (isset($temp['msg']['email'])) {
                $param['push_notification_send_to'] = $temp['msg']['email'];
            }
            if (isset($temp['msg']['ccemail'])) {
                $param['push_notification_send_cc'] = $temp['msg']['ccemail'];
            }
            if (isset($temp['msg']['accountinside'])) {
                $param['push_notification_inside_confirm'] = strtolower($temp['msg']['accountinside']);
            }

            // Bổ sung thông tin mail đối với trường hợp send mail telesale
            if (isset($temp['msg']['data'][0]['EmailLeader'])) {
                $param['push_notification_send_to'] = $temp['msg']['data'][0]['EmailLeader'];
            }
            if (isset($temp['msg']['data'][0]['AccountInsideLeader'])) {
                $param['push_notification_inside_confirm'] = strtolower($temp['msg']['data'][0]['AccountInsideLeader']);
            }

            //Bổ sung thông tin đối với trường hợp send mail qgd
            if (isset($temp['email_list'])) {
                $param['push_notification_send_to'] = $temp['email_list'];
            }
        }

        switch ($type) {
            case 'sale':
                break;
            case 'net':
                break;
            case 'tele':
                if (!$res['error']) {
                    $tempRes = json_decode($res['output'], 1);
                    try {
                        $modelHelper->sendMail($this->input, $tempRes['msg']['data'][0]['EmailLeader']);
                    } catch (Exception $ex) {
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'cl':
                if (!$res['error']) {
                    $type = 'CL';
                    $tempRes = json_decode($res['output'], 1);
                    $mail = $tempRes['msg']['email'];
                    $cc = $tempRes['msg']['ccemail'];
                    $realCc = [];
                    $temp = explode(';', $cc);
                    foreach ($temp as $val) {
                        if (!empty($val)) {
                            array_push($realCc, $val);
                        }
                    }
                    try {
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    } catch (Exception $ex) {
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'qgd':
                if (!$res['error']) {
                    $type = 'QGD';
                    $tempRes = json_decode($res['output'], 1);
                    $mail = $tempRes['email_list'];
                    $realCc = [
                        'ThuanNT@fpt.com.vn',
                        'Dungth8@fpt.com.vn',
                        'Nhungpt@fpt.com.vn',
                    ];
                    try {
                        $modelHelper->sendMailTest($this->input, $mail, $type, $realCc);
                    } catch (Exception $ex) {
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            default:
                return;
        }
    }

    public function testInfo($contract, $type, $code) {
        $apiISC = new Apiisc();
        $infoAcc = array('ObjID' => 0,
            'Contract' => $contract,
            'IDSupportlist' => $code,
            'Type' => $type
        );
        $uri = 'http://parapi.fpt.vn/api/RadAPI/spCEM_ObjectGetByObjID/?';
        $uri .= http_build_query($infoAcc);
        dd(json_decode($apiISC->getAPI($uri)));
    }

    public function testInfoChecklist($listIdC) {
        $apiISC = new Apiisc();
        $listCL = array('ChecklistID' => $listIdC
        );
        $uri = 'http://parapi.fpt.vn/api/RadAPI/SupportListGetByCLID/?';
        $uri .= http_build_query($listCL);
        var_dump(json_decode($apiISC->getAPI($uri)));
        dd(json_decode($apiISC->getAPI($uri)));
    }

    public function testInfoPreChecklist($listIdPC) {
        $apiISC = new Apiisc();
        $listPCL = array('IDPreCheckList' => $listIdPC
        );
        $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?';
        $uri .= http_build_query($listPCL);
        dd(json_decode($apiISC->getAPI($uri)));
    }

    public function testInfoFD($listIdFD) {
        $apiISC = new Apiisc();
        $listIdFD = array('DiscussionID' => $listIdFD
        );
        $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetCuscareDiscussionByID?';
        $uri .= http_build_query($listIdFD);
        dd(json_decode($apiISC->getAPI($uri)));
    }

    public function getCsatSummary($fromDay, $toDay) {
        set_time_limit(0);
//        $day = $this->argument('day');
//        if( empty($day ) ){
//            $dayNow = date('Y-m-d',time());
//        }
//        $day='2017-07-14';
        //while ( $day != $dayNow) {
        // lấy danh sách các điểm tiếp xúc
        $poc = $this->getPoc();
        foreach ($poc as $k => $v) {
            $timeFrom = strtotime($fromDay . " 00:00:00");
            $timeTo = strtotime($toDay . " 23:59:59");
            $questionList = (array) $v;
            $result = DB::table('outbound_survey_sections AS s')
                    ->join('outbound_survey_result AS r', 's.section_id', '=', 'r.survey_result_section_id')
                    ->join('outbound_questions AS q', 'r.survey_result_question_id', '=', 'q.question_id')
                    ->join('outbound_answers AS a', 'r.survey_result_answer_id', '=', 'a.answer_id')
                    ->where('s.section_time_completed_int', '>', $timeFrom)
                    ->where('s.section_time_completed_int', '<', $timeTo)
                    ->where('s.section_connected', '=', '4')
                    ->where('s.section_survey_id', '=', $k)
                    ->whereIn('q.question_id', $questionList)
                    ->groupBy('s.section_sub_parent_desc', 's.section_survey_id', 's.section_location_id', 's.section_branch_code', 's.section_record_channel', 'q.question_id', 'a.answers_point')
                    ->select(DB::raw('s.section_survey_id ,s.section_record_channel ,s.section_sub_parent_desc, s.section_location_id, s.section_branch_code, 
                        q.question_id, a.answers_point,q.question_id,
                        SUM(case when a.answers_point = 0 then 1 else 0 end) as csat_0,
                        SUM(case when a.answers_point = 1 then 1 else 0 end) as csat_1,
                        SUM(case when a.answers_point = 2 then 1 else 0 end) as csat_2,
                        SUM(case when a.answers_point = 3 then 1 else 0 end) as csat_3,
                        SUM(case when a.answers_point = 4 then 1 else 0 end) as csat_4,
                        SUM(case when a.answers_point = 5 then 1 else 0 end) as csat_5'))
                    ->get();

            foreach ($result as $row) {
                $branch = new SummaryBranches();
                $branchID = $branch->getBranchId($row->section_location_id, $row->section_branch_code);
//                                var_dump($branchID);die;
                if ($branchID > 0) { // nhiều trường họp isc trả location_id = 0
                    $summaryCsat = new SummaryCsat();
                    $summaryTime = new SummaryTime();
                    $summaryCsat->time_id = $summaryTime->getTimeIdByDay($day);
                    $summaryCsat->object_id = $this->mapQuestionToObjects($row->question_id);
                    $summaryCsat->branch_id = $branchID;
                    $summaryCsat->channel_id = $row->section_record_channel;
                    $summaryCsat->question_id = $row->question_id;
                    $summaryCsat->answer_point = $row->answers_point;
                    $summaryCsat->poc_id = $k;
                    $summaryCsat->csat_1 = $row->csat_1;
                    $summaryCsat->csat_2 = $row->csat_2;
                    $summaryCsat->csat_3 = $row->csat_3;
                    $summaryCsat->csat_4 = $row->csat_4;
                    $summaryCsat->csat_5 = $row->csat_5;
                    $summaryCsat->csat_0 = $row->csat_0;
                    // var_dump($summaryCsat );
                    // die();
                    $summaryCsat->save();
                }
            }
        }
        echo 'done-' . $day . "\n";
        //    $day = date( "Y-m-d", strtotime( $day ." +1 day" ) );
        //}
    }

    protected function getPoc() {
        return array(
            '1' => array(1, 2, 10, 11), // sau triển khai
            '2' => array(4, 12, 13), // bảo trì
            '3' => array(14, 15), // mobipay
            //'4' => array(),// tại quầy
            // '5' => array(),// hifpt
            '6' => array(20, 21, 22, 23), // TLS
        );
    }

    public function getNpsSummary($fromDay, $toDay) {
        set_time_limit(0);
//        $day = $this->argument('day');
//        if( empty($day ) ){
//            $dayNow = date('Y-m-d',time());
//        }
//        $day='2017-07-14';
        //while ( $day != $dayNow) {
        // lấy danh sách các điểm tiếp xúc
        $poc = $this->getPocNps();
        foreach ($poc as $k => $v) {
            $timeFrom = strtotime($fromDay . " 00:00:00");
            $timeTo = strtotime($toDay . " 23:59:59");
            $questionList = (array) $v;
//            DB::connection()->enableQueryLog();
            $result = DB::table('outbound_survey_sections AS s')
                    ->join('outbound_survey_result AS r', 's.section_id', '=', 'r.survey_result_section_id')
                    ->join('outbound_questions AS q', 'r.survey_result_question_id', '=', 'q.question_id')
                    ->join('outbound_answers AS a', 'r.survey_result_answer_id', '=', 'a.answer_id')
                    ->where('s.section_time_completed_int', '>', $timeFrom)
                    ->where('s.section_time_completed_int', '<', $timeTo)
                    ->where('s.section_connected', '=', '4')
                    ->where('s.section_survey_id', '=', $k)
                    ->where('r.survey_result_answer_id', '<>', -1)
                    ->whereIn('q.question_id', $questionList)
                    ->groupBy('s.section_sub_parent_desc', 's.section_survey_id', 's.section_location_id', 's.section_branch_code', 's.section_record_channel', 'q.question_id', 'a.answers_point')
                    ->select(DB::raw('s.section_survey_id ,s.section_record_channel ,s.section_sub_parent_desc, s.section_location_id, s.section_branch_code, 
                        q.question_id, a.answers_point,
                        SUM(case when a.answers_point = 0 then 1 else 0 end) as nps_0,
                        SUM(case when a.answers_point = 1 then 1 else 0 end) as nps_1,
                        SUM(case when a.answers_point = 2 then 1 else 0 end) as nps_2,
                        SUM(case when a.answers_point = 3 then 1 else 0 end) as nps_3,
                        SUM(case when a.answers_point = 4 then 1 else 0 end) as nps_4,
                        SUM(case when a.answers_point = 5 then 1 else 0 end) as nps_5,
                        SUM(case when a.answers_point = 6 then 1 else 0 end) as nps_6,
                        SUM(case when a.answers_point = 7 then 1 else 0 end) as nps_7,
                        SUM(case when a.answers_point = 8 then 1 else 0 end) as nps_8,
                        SUM(case when a.answers_point = 9 then 1 else 0 end) as nps_9,
                        SUM(case when a.answers_point = 10 then 1 else 0 end) as nps_10'))
//                                   ->tosql();
//                dd($result);die;
                    ->get();
//            $queries = DB::getQueryLog();
//            dd($queries);die;
            foreach ($result as $row) {
                $branch = new SummaryBranches();
                $branchID = $branch->getBranchId($row->section_location_id, $row->section_branch_code);

//                                var_dump($branchID);die;
                if ($branchID > 0) { // nhiều trường họp isc trả location_id = 0
                    $summaryNps = new SummaryNps();
                    $summaryTime = new SummaryTime();

                    $summaryNps->time_id = $summaryTime->getTimeIdByDay($day);
//                    $summaryNps->object_id = $this->mapQuestionToObjects($row->question_id);
                    $summaryNps->object_id = 0;
                    $summaryNps->branch_id = $branchID;
                    $summaryNps->channel_id = $row->section_record_channel;
                    $summaryNps->question_id = $row->question_id;
                    $summaryNps->poc_id = $k;
                    $summaryNps->nps_0 = $row->nps_0;
                    $summaryNps->nps_1 = $row->nps_1;
                    $summaryNps->nps_2 = $row->nps_2;
                    $summaryNps->nps_3 = $row->nps_3;
                    $summaryNps->nps_4 = $row->nps_4;
                    $summaryNps->nps_5 = $row->nps_5;
                    $summaryNps->nps_6 = $row->nps_6;
                    $summaryNps->nps_7 = $row->nps_7;
                    $summaryNps->nps_8 = $row->nps_8;
                    $summaryNps->nps_9 = $row->nps_9;
                    $summaryNps->nps_10 = $row->nps_10;
                    // var_dump($summaryCsat );
                    // die();
                    $summaryNps->save();
                }
            }
        }
        echo 'done-' . $day . "\n";
        //    $day = date( "Y-m-d", strtotime( $day ." +1 day" ) );
        //}
    }

    protected function getPocNps() {
        return array(
            '1' => array(6), // sau triển khai
            '2' => array(8), // bảo trì
            '3' => array(16), // mobipay
            //'4' => array(),// tại quầy
            // '5' => array(),// hifpt
            '6' => array(24), // TLS
        );
    }

    protected function mapQuestionToObjects($questionID) {
        $questionList = array(
            '1' => '1',
            '2' => '3',
            '4' => '4',
            '10' => '5',
            '11' => '6',
            '12' => '5',
            '13' => '6',
            '14' => '5',
            '15' => '6',
            '18' => '', // hifpt
            '20' => '5',
            '21' => '6',
            '22' => '3',
            '23' => '2',
            '26' => '4'
        );
        if (isset($questionList[$questionID]))
            return $questionList[$questionID];
        return 0;
    }

    //Test API Transaction
    //Lấy thông tin giao dịch, hợp đồng này có NPS hay không, trả về màn hình khảo sát của FPT.vn
    public function getInfoContractQGD(Request $request) {
        try {
            $data = $request->input();

            //Giả định mảng tham số bắt buộc phải gửi qua
            $arrayValidate = ['ContractId', 'TransactionCode', 'Type'];
            $arrayError = [];
            foreach ($arrayValidate as $key => $value) {
                //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                if (!isset($data[$value]) || $data[$value] == '')
                    array_push($arrayError, $value);
//                $error.=$value . ', ';
            }
            //Đầy đủ dữ liệu , không có lỗi
            if (empty($arrayError)) {
                //Gọi Api ISC
                $api = new Apiisc();
                $arraySentToISC = array(
                    'contractId' => $data['ContractId'],
                    'transactionId' => $data['TransactionCode'],
                    'key' => $data['Type']
                );
//                $arraySentToISC = array(
//                    'contractId' => 1003280733,
//                    'transactionId' => 1570832,
//                    'key' => 1
//                );

                $timeStartCall = date('Y-m-d H:i:s');
                $resultReturn = $api->GetInforContractQGDApi($arraySentToISC);
                $timeEndCall = date('Y-m-d H:i:s');
                // Ghi log gọi API ISC
                $logger = new Logger('my_logger');
                $logger->pushHandler(new StreamHandler(storage_path() . '/logs/API_ISC_QGD.log', Logger::INFO));
//                $logger->pushHandler(new FirePHPHandler());
                $logger->addInfo('Log Call API', array('TimeStartCall' => $timeStartCall, 'TimeEndCall' => $timeEndCall, 'input' => $arraySentToISC, 'output' => $resultReturn));

                //Gọi qua ISC thất bại
                if ($resultReturn['success'] == false) {
                    $dataApi = [
                        'id' => 'fail',
                        'status' => '500',
                        'detail' => $resultReturn['result'],
                    ];
                    $status = 500;
                    return response()->json($dataApi, $status);
                }
                $returnDataIsc = json_decode($resultReturn['result'])->data;
                $data['TransactionInfo'] = $returnDataIsc;
//                var_dump($data['TransactionInfo']);die;
                unset($data['ContractNum']);
                unset($data['SecureCode']);
                $type = $data['Type'] == 4 ? 7 : 4;
                $dataApi = [
                    'id' => 'success',
                    'status' => '200',
                    'detail' => ($data),
                    'ques_ans' => $this->getQuesAns($type),
                    'nps' => $this->checkNPS($data['TransactionInfo']->ContractNumber),
                ];
                $status = 200;

                return response()->json($dataApi, $status);
            } else {
                $dataApi = [
                    'id' => 'fail',
                    'status' => '503',
                    'detail' => 'Truong ' . implode(',', $arrayError) . ' bi thieu hoac khong co du lieu',
                ];
                $status = 500;
                return response()->json($dataApi, $status);
            }
        } catch (Exception $e) {
            $dataApi = [
                'id' => 'fail',
                'status' => '500',
                'detail' => $e->getMessage(),
            ];
            $status = 500;
            return response()->json($dataApi, $status);
        }
    }

    //Lưu thông tin khảo sát và giao dịch đẩy qua từ FPT.vn
    public function saveInfoTransaction(Request $request) {
        try {
            $allData = $request->input();
            $input = json_encode($allData['data']);
            //Lưu log gọi api
            $source = 'ApiTransactionController/saveInfoTransaction';
            $apiLog = new ApiTransactionLog();
            $apiLog->survey_id = isset($data['contract']['Type']) ? ($data['contract']['Type'] == 4 ? 7 : 4) : null;
            $apiLog->source = $source;
            $apiLog->input = $input;
            $apiLog->save();
            $modelOutboundAccount = new OutboundAccount();
            $messageValidatePerTransaction = [];
            $messageErrorUpdate = [];
            $messageSuccessUpdate = [];
            if (!isset($allData['data']) || $allData['data'] == '') {
                $dataApi = [
                    'id' => 'fail',
                    'status' => '503',
                    'detail' => 'Thiếu data đầu vào',
                ];
                $status = 500;
                return response()->json($dataApi, $status);
            }
            foreach ($allData['data'] as $key => $data) {
                if (!empty($data) && isset($data['ques_ans']) && !empty($data['ques_ans']) && isset($data['contract']) && !empty($data['contract'])) {
                    $validateArray = ['questionID', 'answerID', 'note'];
                    $ques_ans = $data['ques_ans'];
                    $arrayError = [];
                    foreach ($ques_ans as $key2 => $value2) {
                        foreach ($validateArray as $key3 => $value3) {
                            //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                            if ((!isset($value2[$value3]) || $value2[$value3] == '')) {
                                if (!in_array($value3, $arrayError))
                                    array_push($arrayError, $value3);
                            }
                        }
                    }
                    //Validate thành công
                    if (empty($arrayError)) {
                        DB::beginTransaction();
                        $surveySection = new SurveySections();
                        $dataQGD = $data['contract'];
                        $dataTransactionInfo = $dataQGD['TransactionInfo'];
                        $type = in_array($dataQGD['Type'], [1, 2, 3]) ? 4 : 7;
                        $resultCodes = $surveySection->checkExistCodes($dataQGD['TransactionCode'], $type, $dataTransactionInfo['ContractNumber']);
                        //Đã lưu thông tin rồi thì cập nhập                      
                        if (!empty($resultCodes)) {
                            //Xóa dữ liệu cũ
                            DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $resultCodes[0]->section_id)->delete();
                            foreach ($ques_ans as $key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $resultCodes[0]->section_id;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                //Rất ko hài lòng, ko hài lòng
                                // if ($value['answerID'] == 1 || $value['answerID'] == 2) {
                                //     $surveyResult->survey_result_answer_extra_id = isset($value['answerExtraID']) ? $value['answerExtraID'] : null;
                                //     $surveyResult->survey_result_note = isset($value['note']) ? $value['note'] : null;
                                //  }
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
//                                    array_push($arrayErrorUpdate, ['ContractNum' => $dataQGD['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'SecureCode' => $dataQGD['SecureCode']]);
                                    array_push($messageErrorUpdate, ['ContractNum' => $dataTransactionInfo['ContractNumber'], 'TransactionCode' => $dataQGD['TransactionCode']]);
//                                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
//                                    $flagError = true;
                                    DB::rollback();
                                    break;
                                }
                            }
                            array_push($messageSuccessUpdate, ['ContractNum' => $dataTransactionInfo['ContractNumber'], 'TransactionCode' => $dataQGD['TransactionCode']]);
                            DB::commit();
                            continue;
                        }
//                            $dataApi = [
//                                'id' => 'fail',
//                                'status' => '503',
//                                'detail' => 'Khảo sát '.$dataTransactionInfo['ContractNumber'].'/4/'.$dataQGD['TransactionCode'].' đã lưu thông tin',
//                            ];
//                            $status = 500;
//                            return response()->json($dataApi, $status);
//                            exit;
//                        }
                        $flagSuccess = true;

                        // lấy thông tin khách hàng trong database survey
                        $accountInfo = $outboundAccount->getAccountInfoByContractNum($dataTransactionInfo['ContractNumber']);
                        // update hoặc insert thông tin khách hàng
                        $dataTransactionInfo['ContractNum'] = $dataTransactionInfo['ContractNumber'];
                        unset($dataTransactionInfo['ContractNumber']);
                        $resultOutboundAccount = $outboundAccount->saveAccount($dataTransactionInfo);
//                          $accountInfoISC = (array) $responseAccountInfo[0];
                        if (empty($accountInfo->contract_num)) { // nếu chưa có thông tin khách hàng
                            $this->saveAccountProfiles($dataTransactionInfo);
                        } else {
                            // nếu tìm thấy thông tin khách hàng kiểm tra các thông tin
                            // Họ và tên, Ngày tháng năm sinh, giới tính, địa chỉ trên CMND, địa chỉ lắp đặt, địa chỉ thanh toán
                            // nếu các thông tin trên đã được lưu trong database survey và các thông tin này giống với thông tin đã gọi API của lần trước thì lấy thông tin trong database survey
                            // nếu thông tin này là khác với thông tin đã gọi API lần trước thì lưu mới thông tin khách hàng = thông tin API mới
//                            $accountInfoCurrent = (array) $responseAccountInfo[0];
                            $this->saveAccountProfiles($dataTransactionInfo, $accountInfo);

                            // kiểm tra đã lưu thông tin tiếng việt chưa
                            // nếu có thì load lên load đè API trả về
                            $AccountProfiles = new AccountProfiles;
                            $AccountProfilesVN = $AccountProfiles->getAccountProfilesByContract($request->sohd);
                            //var_dump( $AccountProfilesVN );
                            if (isset($AccountProfilesVN->ap_contract)) {
                                $responseAccountInfo[0]->CustomerName = $AccountProfilesVN->ap_fullname;
                                $responseAccountInfo[0]->Address = $AccountProfilesVN->ap_address_id;
                                $responseAccountInfo[0]->BillTo = $AccountProfilesVN->ap_address_bill;
                                $responseAccountInfo[0]->ObjAddress = $AccountProfilesVN->ap_address_setup;
                                $responseAccountInfo[0]->Sex = $AccountProfilesVN->ap_sex;
                                $responseAccountInfo[0]->Birthday = $AccountProfilesVN->ap_birthday;
                            }
                            // end tiếng việt
                        }
                        $surveySection->section_code = $dataQGD['TransactionCode'];
                        $surveySection->section_account_id = $resultOutboundAccount['data']->id;
                        $surveySection->section_contract_num = $dataTransactionInfo['ContractNum'];
                        $surveySection->section_customer_name = $dataTransactionInfo['CustomerName'];
                        $surveySection->section_survey_id = $type;
                        $surveySection->section_record_channel = 2;
                        $surveySection->sale_center_id = 3;
                        $surveySection->section_phone = $dataTransactionInfo['Phone'];
                        //$surveySection->section_note = $dataQGD['note'];
                        $surveySection->section_objAddress = $dataTransactionInfo['Address'];
                        $surveySection->section_sub_parent_desc = $dataTransactionInfo['SubParentDesc'];
                        $surveySection->section_location = $dataTransactionInfo['ChiNhanh'];
                        $surveySection->section_fee_local_type = $dataTransactionInfo['ContractTypeName'];
                        $surveySection->section_time_start = $dataQGD['SectionTimeStart'];
                        $surveySection->section_time_completed = $dataQGD['SectionTimeCompleted'];
                        $surveySection->section_time_completed_int = strtotime(date('Y-m-d H:i:s'));
                        $surveySection->section_connected = 4;
                        $surveySection->section_action = 1;
                        $surveySection->section_region = $dataTransactionInfo['Region'];
                        $surveySection->section_location_id = $dataTransactionInfo['LocationID'];
                        $surveySection->section_branch_code = $dataTransactionInfo['BranchCode'];
                        $surveySection->section_package_sal = $dataTransactionInfo['PackageSal'];
                        $surveySection->section_payment_type = $dataTransactionInfo['PaymentType'];
                        $surveySection->section_account_payment = $dataTransactionInfo['AccountPayment'];
                        $surveySection->section_use_service = $dataTransactionInfo['UseService'];
//                        $accountInfo = $modelOutboundAccount->getAccountInfoByContract($dataTransactionInfo['ContractNumber']);
//                        $surveySection->section_account_id = ($accountInfo == NULL) ? 0 : $accountInfo->id;
                        //Lưu thành công thông tin giao dịch
                        if ($surveySection->save()) {
                            $idDetail = $surveySection->section_id;
                            foreach ($ques_ans as $key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $idDetail;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                //Rất ko hài lòng, ko hài lòng
                                // if ($value['answerID'] == 1 || $value['answerID'] == 2) {
                                //     $surveyResult->survey_result_answer_extra_id = isset($value['answerExtraID']) ? $value['answerExtraID'] : null;
                                //     $surveyResult->survey_result_note = isset($value['note']) ? $value['note'] : null;
                                //  }
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
//                                    array_push($arrayErrorUpdate, ['ContractNum' => $dataQGD['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'SecureCode' => $dataQGD['SecureCode']]);
                                    array_push($messageErrorUpdate, ['ContractNum' => $dataTransactionInfo['ContractNumber'], 'TransactionCode' => $dataQGD['TransactionCode']]);
//                                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
//                                    $flagError = true;
                                    DB::rollback();
                                    break;
                                }
                            }
                            $surveySectionEmail = new SurveySectionsEmail();
                            $surveySectionEmail->section_id = $idDetail;
                            $surveySectionEmail->section_time_start_transaction = $dataTransactionInfo['ThoiGianGiaoDich'];
                            $surveySectionEmail->section_user_create_transaction = $dataTransactionInfo['NguoiTaoGD'];
                            if ($surveySectionEmail->save()) {
                                //Lưu thành công cả 2 bảng
                                array_push($messageSuccessUpdate, ['ContractNum' => $dataTransactionInfo['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'Type' => $dataQGD['Type']]);

//                            $messageSuccessUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thành công; ';
                                DB::commit();
                            } else {
                                array_push($messageErrorUpdate, ['ContractNum' => $dataTransactionInfo['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'Type' => $dataQGD['Type']]);
//                                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
//                                    $flagError = true;
                                DB::rollback();
                                break;
                            }
                        } else {
//                            array_push($arrayErrorUpdate, ['ContractNum' => $dataQGD['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'SecureCode' => $dataQGD['SecureCode']]);
//                            $flagError = true;
                            array_push($messageErrorUpdate, ['ContractNum' => $dataTransactionInfo['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'Type' => $dataQGD['Type']]);

//                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
                            DB::rollback();
                            break;
                        }
                    } else {
                        (isset($dataTransactionInfo['ContractNum']) || isset($data['contract']['TransactionCode'])) ?
                                        array_push($messageValidatePerTransaction, ['ContractNum' => isset($dataTransactionInfo['ContractNum']) ? $dataTransactionInfo['ContractNum'] : '', 'TransactionCode' => isset($data['contract']['TransactionCode']) ? $data['contract']['TransactionCode'] : '', 'Type' => isset($dataQGD['Type']) ? $dataQGD['Type'] : null
                                        ]) :
                                        array_push($messageValidatePerTransaction, 'Missing data ' . implode(',', $arrayError));
//                        array_push($messageValidatePerTransaction, 'Missing data ' . implode(',', $arrayError));
//                        $messageValidatePerTransaction.= 'Missing data ' . implode(',', $arrayError);
                    }
                } else {
                    (isset($dataTransactionInfo['ContractNum']) || isset($data['contract']['TransactionCode'])) ?
                                    array_push($messageValidatePerTransaction, ['ContractNum' => isset($dataTransactionInfo['ContractNum']) ? $dataTransactionInfo['ContractNum'] : '', 'TransactionCode' => isset($data['contract']['TransactionCode']) ? $data['contract']['TransactionCode'] : '', 'Type' => isset($dataQGD['Type']) ? $dataQGD['Type'] : null
                                    ]) :
                                    array_push($messageValidatePerTransaction, 'Missing ques_ans, contract  data ');

//                    $messageValidatePerTransaction.= 'Missing ques_ans, contract  data ';
                }
            }
            $dataApi = [
                'id' => 'success',
                'status' => '200',
                'detail' => ['ErrorRecord:' => ($messageValidatePerTransaction != '') ? $messageValidatePerTransaction : 'None',
                    'SucessRecord' => ($messageSuccessUpdate != '') ? $messageSuccessUpdate : 'None',
                    'FailRecord' => ($messageErrorUpdate != '') ? $messageErrorUpdate : 'None',
                ]
            ];
            $status = 200;
            return response()->json($dataApi, $status);
//            }
            //Không có dữ liệu gửi qua, hoặc gửi không đủ dữ liệu
            //ques_ans:Bộ câu hỏi, trả lời khảo sát, dạng mảng
            //contract:Thông tin giao dịch trả về từ api đầu tiên
        } catch (Exception $ex) {
            //Lưu dữ liệu bị lỗi
//            if ($flagError == true) {
//                DB::rollback();
//            }
            DB::rollback();
            $dataApi = [
                'id' => 'fail',
                'status' => '500',
                'detail' => $ex->getMessage(),
            ];
            $status = 500;
            return response()->json($dataApi, $status);
        }
    }

    //Hàm kiểm tra hợp đồng này có cần khảo sát câu NPS hay không
    //True:có
    //False:không
    public function checkNPS($contractNum) {
        $hasNPS = FALSE;
//        $contractModel = DB::table('outbound_accounts')->select('id')->where('contract_num', '=', $contractNum)->get();
//        if (isset($contractModel[0]->id)) {
        $SurveySections = new SurveySections();

        $accountInfoFromSurvey = $SurveySections->getAllSurveyInfoOfAccountQGD($contractNum);
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
                $flag = NULL;

                if ($d->question_id != $flag) {
                    $flag = $d->question_id;
                    $content .= '<b>' . $d->question_title_short . ': </b>';
                }
                $content .= $d->answers_title . ", ";
                if ($d->question_is_nps == 1) {
                    if ($i->section_time_completed > $dateSurveyTemp) {
                        $dateSurveyTemp = $i->section_time_completed;
                    }
                }
            }
            $i->content = $content;
            $historyOutboundSurvey[] = (array) $i;
        }
        if ($dateSurveyTemp != FALSE) {

            $currentDate = new \DateTime();
            $lastest_survey_nps_time = new \DateTime($dateSurveyTemp);
            $interval = $lastest_survey_nps_time->diff($currentDate)->format("%a");
            if ($interval < 90) {
                $hasNPS = TRUE;
            }
        }
//        }
        return $hasNPS;
    }

    //Api lấy thông tin người liên hệ
    public function getContact(Request $request) {
        try {
            if (!isset($request->contractNum)) {
                return json_encode(['code' => 406, 'msg' => 'Missing input contractNum']);
            } else {
                if ($request->contractNum == '') {
                    return json_encode(['code' => 406, 'msg' => 'Empty input contractNum ']);
                } else {
                    $timeStartCall = date('Y-m-d H:i:s');
                    $contract = $request->contractNum;
                    $modelOutboundAccount = new OutboundAccount();
                    $accountInfo = $modelOutboundAccount->getAccountInfoByContract($contract);
                    $modelContactProfile = new ContactProfile();
                    $contractNumOrAccId = $accountInfo == NULL ? $request->contractNum : $accountInfo->id;
                    $response['data'] = $modelContactProfile->getContactApi($contractNumOrAccId);

                    // Ghi log gọi API từ ISC
                    $logger = new Logger('my_logger');
                    $logger->pushHandler(new StreamHandler(storage_path() . '/logs/API_ISC_GetContact.log', Logger::INFO));
//                $logger->pushHandler(new FirePHPHandler());
                    $returnData = json_encode(['code' => 200, 'msg' => 'Success', 'data' => $response['data']]);
                    $timeEndCall = date('Y-m-d H:i:s');
                    $logger->addInfo('Log Call API', array('TimeStartCall' => $timeStartCall, 'TimeEndCall' => $timeEndCall, 'input' => $request->input(), 'output' => $returnData, 'ip' => $request->ip()));
                    return $returnData;
                }
            }
        } catch (Exception $ex) {
            return json_encode(['code' => 500, 'msg' => 'Internal Server Error']);
        }
    }

    //Api thêm thông tin người liên hệ
    public function addContact(Request $request) {
        try {
            $arrayValidate = [];
            $arrayInput = ['contractNum', 'dataContact', 'createrName'];
            $arrayInputContact = ['name', 'phone', 'relationship'];
            $input = $request->input();
            foreach ($arrayInput as $key => $value) {
                if (!isset($input[$value]) || $input[$value] == '')
                    array_push($arrayValidate, $value);
            }
            if (!empty($arrayValidate)) {
                return json_encode(['code' => 406, 'msg' => 'Missing or empty input ' . implode(',', $arrayValidate)]);
            } else {
                $dataContactInput = $request->dataContact;
                foreach ($arrayInputContact as $key2 => $value2) {
                    if (!isset($dataContactInput[$value2]) || $dataContactInput[$value2] == '')
                        array_push($arrayValidate, $value2);
                }
                if (!empty($arrayValidate))
                    return json_encode(['code' => 406, 'msg' => 'Missing or empty input ' . implode(',', $arrayValidate)]);
                else {
                    $timeStartCall = date('Y-m-d H:i:s');
                    $info = $request->dataContact;
                    $modelOutboundAccount = new OutboundAccount();
                    $accountInfo = $modelOutboundAccount->getAccountInfoByContract($request->contractNum);
                    $contractNum = $request->contractNum;
                    $accountID = ($accountInfo == NULL) ? NULL : $accountInfo->id;
                    $userCreatedName = $request->createrName;
                    ;
                    $modelContactProfile = new ContactProfile();
                    $response = $modelContactProfile->saveContactProfile($info, $accountID, NULL, $userCreatedName, $contractNum);
                    if ($response['code'] == 200) {
                        $result = array('code' => 200, 'msg' => 'Success');
                    } else {
                        $result = array('code' => 500, 'msg' => 'Update fail');
                    }
                    $returnData = json_encode($result);
                    // Ghi log gọi API ISC
                    $logger = new Logger('my_logger');
                    $logger->pushHandler(new StreamHandler(storage_path() . '/logs/API_ISC_AddContact.log', Logger::INFO));
                    $timeEndCall = date('Y-m-d H:i:s');
//                $logger->pushHandler(new FirePHPHandler());
                    $logger->addInfo('Log Call API', array('TimeStartCall' => $timeStartCall, 'TimeEndCall' => $timeEndCall, 'input' => $request->input(), 'output' => $returnData, 'ip' => $request->ip()));
                    return $returnData;
                }
            }
        } catch (Exception $ex) {
            return json_encode(['code' => 500, 'msg' => 'Internal Server Error']);
        }
    }

    public function saveInfoTransactionCounter(Request $request) {
        try {
            $allData = $request->input();
            $input = json_encode($allData['data']);
            //Lưu log gọi api
            $source = 'ApiTransactionController/saveInfoTransactionCounter';
            $apiLog = new ApiTransactionLog();
            $apiLog->survey_id = 8;
            $apiLog->source = $source;
            $apiLog->input = $input;
            $apiLog->save();
            $modelOutboundAccount = new OutboundAccount();
            $messageValidatePerTransaction = [];
            $messageErrorUpdate = [];
            $messageSuccessUpdate = [];
            if (!isset($allData['data']) || $allData['data'] == '') {
                $dataApi = [
                    'id' => 'fail',
                    'status' => '503',
                    'detail' => 'Thiếu data đầu vào',
                ];
                $status = 500;
                return response()->json($dataApi, $status);
            }

            foreach ($allData['data'] as $key => $data) {
//                $flagSuccess = false;
//                if (empty($data) || !isset($data['ques_ans']) || empty($data['ques_ans']) || !isset($data['contract']) || empty($data['contract'])) {
//                    $dataApi = [
//                        'id' => 'fail',
//                        'status' => '503',
//                        'detail' => 'Du lieu gui qua khong du',
//                    ];
//                    $status = 500;
//                    return response()->json($dataApi, $status);
//                } 
                if (isset($data['ques_ans']) && !empty($data['ques_ans']) && isset($data['survey_info']) && !empty($data['survey_info'])) {
                    $validateArray = ['questionID', 'answerID', 'note'];
                    $ques_ans = $data['ques_ans'];
                    $arrayError = [];

                    foreach ($ques_ans as $key2 => $value2) {
                        foreach ($validateArray as $key3 => $value3) {
                            //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                            if ((!isset($value2[$value3]) || $value2[$value3] == '')) {
                                if (!in_array($value3, $arrayError))
                                    array_push($arrayError, $value3);
                            }
                        }
                    }
                    //Validate thành công
//                    if (isset($ques_ans['questionID']) && $ques_ans['questionID'] != '' && isset($ques_ans['answerID']) && $ques_ans['answerID'] != '' && isset($ques_ans['note']) && $ques_ans['note'] != '') {
                    if (empty($arrayError)) {
                        DB::beginTransaction();
                        $surveySection = new SurveySections();
                        $resultCodes = $surveySection->checkExistCodes($data['survey_info']['SectionCode'], 8, $data['survey_info']['ContractNumber']);

                        //Đã lưu thông tin rồi nên bỏ qua
                        if (!empty($resultCodes)) {

                            //Xóa dữ liệu cũ
                            DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $resultCodes[0]->section_id)->delete();
//                             var_dump($resultCodes);die;
                            foreach ($ques_ans as $key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $resultCodes[0]->section_id;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                //Rất ko hài lòng, ko hài lòng
                                // if ($value['answerID'] == 1 || $value['answerID'] == 2) {
                                //     $surveyResult->survey_result_answer_extra_id = isset($value['answerExtraID']) ? $value['answerExtraID'] : null;
                                //     $surveyResult->survey_result_note = isset($value['note']) ? $value['note'] : null;
                                //  }
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
//                                    array_push($arrayErrorUpdate, ['ContractNum' => $dataQGD['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'SecureCode' => $dataQGD['SecureCode']]);
                                    array_push($messageErrorUpdate, ['ContractNum' => $dataTransactionInfo['ContractNumber'], 'TransactionCode' => $dataQGD['TransactionCode']]);
//                                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
//                                    $flagError = true;
                                    DB::rollback();
                                    break;
                                }
                            }
                            array_push($messageSuccessUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'], 'SectionCode' => $data['survey_info']['SectionCode'], 'Type' => $data['survey_info']['Type']]);
                            DB::commit();
                            continue;
                        }
                        $infoAcc = array('ObjID' => 0,
                            'Contract' => $data['survey_info']['ContractNumber'],
                            'IDSupportlist' => $data['survey_info']['SectionCode'],
                            'Type' => $data['survey_info']['Type']
                        );

                        /*
                         * Lấy thông tin khách hàng
                         */
                        $apiIsc = new Apiisc();

                        $responseAccountInfo = $apiIsc->GetFullAccountInfo($infoAcc);

                        $responseAccountInfo = $responseAccountInfo['result']['data'];


                        $outboundAccount = new OutboundAccount();
                        $accountInfoISC = (array) $responseAccountInfo[0];
                        // lấy thông tin khách hàng trong database survey
                        $accountInfo = $outboundAccount->getAccountInfoByContractNum($data['survey_info']['ContractNumber']);
                        // update hoặc insert thông tin khách hàng
                        $outboundAccount->saveAccount($accountInfoISC);
                        if (empty($accountInfo->contract_num)) { // nếu chưa có thông tin khách hàng
                            $this->saveAccountProfiles($accountInfoISC);
                        } else {
                            // nếu tìm thấy thông tin khách hàng kiểm tra các thông tin
                            // Họ và tên, Ngày tháng năm sinh, giới tính, địa chỉ trên CMND, địa chỉ lắp đặt, địa chỉ thanh toán
                            // nếu các thông tin trên đã được lưu trong database survey và các thông tin này giống với thông tin đã gọi API của lần trước thì lấy thông tin trong database survey
                            // nếu thông tin này là khác với thông tin đã gọi API lần trước thì lưu mới thông tin khách hàng = thông tin API mới
                            $accountInfoCurrent = (array) $responseAccountInfo[0];
                            $this->saveAccountProfiles($accountInfoISC, $accountInfo);

                            // kiểm tra đã lưu thông tin tiếng việt chưa
                            // nếu có thì load lên load đè API trả về
                            $AccountProfiles = new AccountProfiles;
                            $AccountProfilesVN = $AccountProfiles->getAccountProfilesByContract($request->sohd);
                            //var_dump( $AccountProfilesVN );
                            if (isset($AccountProfilesVN->ap_contract)) {
                                $responseAccountInfo[0]->CustomerName = $AccountProfilesVN->ap_fullname;
                                $responseAccountInfo[0]->Address = $AccountProfilesVN->ap_address_id;
                                $responseAccountInfo[0]->BillTo = $AccountProfilesVN->ap_address_bill;
                                $responseAccountInfo[0]->ObjAddress = $AccountProfilesVN->ap_address_setup;
                                $responseAccountInfo[0]->Sex = $AccountProfilesVN->ap_sex;
                                $responseAccountInfo[0]->Birthday = $AccountProfilesVN->ap_birthday;
                            }
                            // end tiếng việt
                        }

                        $dataTransactionInfo = (array) $this->processDataFromISC($responseAccountInfo[0]);
                        //Insert dư lieu isc tra ve vao survey_section                      
//                        $surveySection->section_contract_id = $dataQGD['ContractNum'];
                        $surveySection->section_code = $data['survey_info']['SectionCode'];
                        $surveySection->section_contract_num = $data['survey_info']['ContractNumber'];
                        $surveySection->section_account_id = $outboundAccount->id;
                        $surveySection->section_customer_name = isset($dataTransactionInfo['CustomerName']) ? $dataTransactionInfo['CustomerName'] : null;
                        $surveySection->section_survey_id = 8;
                        $surveySection->section_record_channel = 5;
//                        $surveySection->sale_center_id = 3;
                        $surveySection->section_phone = isset($dataTransactionInfo['Phone']) ? $dataTransactionInfo['Phone'] : null;
                        //$surveySection->section_note = $dataQGD['note'];
                        $surveySection->section_objAddress = isset($dataTransactionInfo['Address']) ? $dataTransactionInfo['Address'] : null;
                        $surveySection->section_sub_parent_desc = isset($dataTransactionInfo['SubParentDesc']) ? $dataTransactionInfo['SubParentDesc'] : null;
                        $surveySection->section_location = isset($dataTransactionInfo['Location']) ? $dataTransactionInfo['Location'] : null;
                        $surveySection->section_fee_local_type = isset($dataTransactionInfo['ContractTypeName']) ? $dataTransactionInfo['ContractTypeName'] : null;
                        $surveySection->section_time_start = $data['survey_info']['SectionTimeStart'];
                        $surveySection->section_time_completed = $data['survey_info']['SectionTimeCompleted'];
                        $surveySection->section_time_completed_int = strtotime($data['survey_info']['SectionTimeCompleted']);
                        $surveySection->section_connected = 4;
                        $surveySection->section_action = 1;
                        $surveySection->section_region = isset($dataTransactionInfo['Region']) ? $dataTransactionInfo['Region'] : null;
                        $surveySection->section_location_id = isset($dataTransactionInfo['LocationID']) ? $dataTransactionInfo['LocationID'] : null;
                        $surveySection->section_branch_code = isset($dataTransactionInfo['BranchCode']) ? $dataTransactionInfo['BranchCode'] : null;
                        $surveySection->section_supporter = isset($dataTransactionInfo['Supporter']) ? $dataTransactionInfo['Supporter'] : null;
                        $surveySection->section_subsupporter = isset($dataTransactionInfo['SubSupporter']) ? $dataTransactionInfo['SubSupporter'] : null;
                        $surveySection->section_finish_date_list = isset($dataTransactionInfo['FinishDateList']) ? $dataTransactionInfo['FinishDateList'] : null;
                        $surveySection->section_finish_date_inf = isset($dataTransactionInfo['FinishDateINF']) ? $dataTransactionInfo['FinishDateINF'] : null;
                        if (isset($dataTransactionInfo['BranchCodeSale'])) {
                            //Vùng 1 hoặc 5
                            if ($surveySection->section_location_id == 4 || $surveySection->section_location_id == 8) {
                                //Trả dữ liệu sai
                                if ($dataTransactionInfo['BranchCodeSale'] == 0 || empty($dataTransactionInfo['BranchCodeSale']))
                                    $brancodeSale = $surveySection->section_branch_code;
                                else
                                    $brancodeSale = $dataTransactionInfo['BranchCodeSale'];
                            }
                            else {
                                $brancodeSale = $dataTransactionInfo['BranchCodeSale'];
                            }
                        } else
                            $brancodeSale = $surveySection->section_branch_code;
                        $surveySection->section_sale_branch_code = $brancodeSale;
                        $surveySection->section_package_sal = isset($dataTransactionInfo['PackageSal']) ? $dataTransactionInfo['PackageSal'] : null;
                        $surveySection->section_payment_type = isset($dataTransactionInfo['PaymentType']) ? $dataTransactionInfo['PaymentType'] : null;
                        $surveySection->section_account_payment = isset($dataTransactionInfo['AccountPayment']) ? $dataTransactionInfo['AccountPayment'] : null;
                        $surveySection->section_use_service = isset($dataTransactionInfo['UseService']) ? $dataTransactionInfo['UseService'] : null;
                        $surveySection->section_acc_sale = isset($dataTransactionInfo['AccountSale']) ? $dataTransactionInfo['AccountSale'] : null;
                        $surveySection->section_account_list = isset($dataTransactionInfo['AccountList']) ? $dataTransactionInfo['AccountList'] : null;
                        $surveySection->section_account_inf = isset($dataTransactionInfo['AccountINF']) ? $dataTransactionInfo['AccountINF'] : null;
                        $accountInfo = $modelOutboundAccount->getAccountInfoByContract($dataTransactionInfo['ContractNum']);
                        $surveySection->section_account_id = ($accountInfo == NULL) ? 0 : $accountInfo->id;
                        //Lưu thành công thông tin giao dịch
                        if ($surveySection->save()) {
                            $idDetail = $surveySection->section_id;
                            $flagSuccess = true;
//                        foreach ($ques_ans as $key => $value) {
                            foreach ($ques_ans as $key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $idDetail;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                if (!$surveyResult->save()) {
//                                    array_push($arrayErrorUpdate, ['ContractNum' => $dataQGD['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'SecureCode' => $dataQGD['SecureCode']]);
//                                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
                                    $flagSuccess = false;
                                    array_push($messageErrorUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'], 'SectionCode' => $data['survey_info']['SectionCode'], 'Type' => $data['survey_info']['Type']]);
                                    DB::rollback();
                                    break;
                                }
                            }
                            if ($flagSuccess) {
                                array_push($messageSuccessUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'], 'SectionCode' => $data['survey_info']['SectionCode'], 'Type' => $data['survey_info']['Type']]);
                                DB::commit();
                            }
                            //Rất ko hài lòng, ko hài lòng
                            // if ($value['answerID'] == 1 || $value['answerID'] == 2) {
                            //     $surveyResult->survey_result_answer_extra_id = isset($value['answerExtraID']) ? $value['answerExtraID'] : null;
                            //     $surveyResult->survey_result_note = isset($value['note']) ? $value['note'] : null;
                            //  }
                            //Lưu thất bại chi tiết khảo sát giao dịch đó
//                        }
                        } else {
//                            array_push($arrayErrorUpdate, ['ContractNum' => $dataQGD['ContractNum'], 'TransactionCode' => $dataQGD['TransactionCode'], 'SecureCode' => $dataQGD['SecureCode']]);
//                            $flagError = true;
                            array_push($messageErrorUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'], 'SectionCode' => $data['survey_info']['SectionCode'], 'Type' => $data['survey_info']['Type']]);
//                            $messageErrorUpdate.='Bộ dữ liệu khảo sát có ContractNum: ' . $dataQGD['ContractNum'] . ',TransactionCode:' . $dataQGD['TransactionCode'] . ', SecureCode:' . $dataQGD['SecureCode'] . ' cập nhập thất bại; ';
                            DB::rollback();
                            break;
                        }
                    } else {
                        array_push($messageValidatePerTransaction, ['ContractNum' => $data['survey_info']['ContractNumber'], 'SectionCode' => $data['survey_info']['SectionCode'], 'Type' => $data['survey_info']['Type']]);
                    }
                } else {
                    $message = '';
                    if (!isset($data['ques_ans']) || empty($data['ques_ans']))
                        $message.='Thiếu ques_ans truyền vào hoặc truyền vào bằng rỗng /n';
                    else
                        $message.='Thiếu survey_info truyền vào hoặc truyền vào bằng rỗng';
                    array_push($messageValidatePerTransaction, $message);
                }
            }

            $dataApi = [
                'id' => 'success',
                'status' => '200',
                'detail' => ['ErrorRecord:' => ($messageValidatePerTransaction != '') ? $messageValidatePerTransaction : 'None',
                    'SucessRecord' => ($messageSuccessUpdate != '') ? $messageSuccessUpdate : 'None',
                    'FailRecord' => ($messageErrorUpdate != '') ? $messageErrorUpdate : 'None',
                ]
            ];
            $status = 200;
            return response()->json($dataApi, $status);
//            }
            //Không có dữ liệu gửi qua, hoặc gửi không đủ dữ liệu
            //ques_ans:Bộ câu hỏi, trả lời khảo sát, dạng mảng
            //contract:Thông tin giao dịch trả về từ api đầu tiên
        } catch (Exception $ex) {
            //Lưu dữ liệu bị lỗi
//            if ($flagError == true) {
//                DB::rollback();
//            }
            DB::rollback();
            $dataApi = [
                'id' => 'fail',
                'status' => '500',
                'detail' => $ex->getMessage(),
            ];
            $status = 500;
            return response()->json($dataApi, $status);
        }
    }

    private function processDataFromISC($data) {
        $dateFormat = 'Y-m-d h:i:s'; //config('app.datetime_format');
        if (!empty($data->ContractDate)) {
            $data->ContractDate = date($dateFormat, strtotime($data->ContractDate));
        }

        // kiểm tra là bảo trì hay triển khai.
        // nếu $data->FinishDateList == null => triển khai
        // Ngày thi công $data->FinishDateINF > ngày bảo trì $data->FinishDateList => triển khai
//    	$data->isCheckList = 1; // mặc định là bảo trì
//    	if ( empty($data->FinishDateList)){
//    		$data->isCheckList = 0; // triển khai
//    	}else if ( $data->FinishDateINF > $data->FinishDateList){
//    		$data->isCheckList = 0;
//    	}
        // end kiểm tra triển khai, bảo trì	
        if (!empty($data->FinishDateINF)) {
            $data->FinishDateINF = date($dateFormat, strtotime($data->FinishDateINF));
        }
        if (!empty($data->FinishDateList)) {
            $data->FinishDateList = date($dateFormat, strtotime($data->FinishDateList));
        }
        return $data;
    }

    private function saveAccountProfiles($accountCurrent, $accountStored = NULL) {
        $AccountProfiles = new AccountProfiles;
        //$accountCurrent = (array)$accountCurrent;
        $accountStored = (array) $accountStored;
        if (empty($accountStored['contract_num'])) {
            $accountProfiles = array(
                "ap_contract" => isset($accountCurrent['ContractNum']) ? $accountCurrent['ContractNum'] : null,
                "ap_fullname" => isset($accountCurrent["CustomerName"]) ? $accountCurrent['CustomerName'] : null,
                "ap_birthday" => isset($accountCurrent["Birthday"]) ? $accountCurrent['Birthday'] : null,
                "ap_sex" => isset($accountCurrent["Sex"]) ? $accountCurrent['Sex'] : 1,
                "ap_address_id" => isset($accountCurrent["Address"]) ? $accountCurrent['Address'] : null,
                "ap_address_bill" => isset($accountCurrent["BillTo"]) ? $accountCurrent['BillTo'] : null,
                "ap_address_setup" => isset($accountCurrent["ObjAddress"]) ? $accountCurrent['ObjAddress'] : null,
//                "ap_user_update" => Auth::user()->id
            );
            $AccountProfiles->insertAccountProfiles($accountProfiles);
        } else {
            $accountProfiles = array();
            //var_dump($accountStored);
            if ($accountCurrent["CustomerName"] != $accountStored['customer_name']) {
                $accountProfiles['ap_fullname'] = $accountCurrent["CustomerName"];
            }
            if ($accountCurrent["Birthday"] != $accountStored['birthday']) {
                $accountProfiles['ap_birthday'] = $accountCurrent["Birthday"];
            }
//            if ($accountCurrent["Sex"] != $accountStored['sex']) {
//                $accountProfiles['ap_sex'] = $accountCurrent["Sex"];
//            }
            if ($accountCurrent["Address"] != $accountStored['address']) {
                $accountProfiles['ap_address_id'] = $accountCurrent["Address"];
            }
            if ($accountCurrent["BillTo"] != $accountStored['address_bill_to']) {
                $accountProfiles['ap_address_bill'] = $accountCurrent["BillTo"];
            }
            if ($accountCurrent["ObjAddress"] != $accountStored['obj_address']) {
                $accountProfiles['ap_address_setup'] = $accountCurrent["ObjAddress"];
            }


            $AccountProfiles->updateAccountProfiles($accountCurrent['ContractNum'], $accountProfiles);
        }
    }

    public function UpdateDateCL() {
        for ($i = 1; $i <= 300; $i++) {
            $listPCL = DB::table('prechecklist')
                    ->select('id_prechecklist_isc')
                    ->where(function($query) {
                        $query->whereNotNull('id_prechecklist_isc');
                        $query->where('id_prechecklist_isc', '<>', 0);
                        $query->whereNull('update_date');
                    })
                    ->orderBy('created_at', 'ASC')
                    ->limit(20)
//                    ->tosql();
                    ->get();
            $arrayPCL = [];
            foreach ($listPCL as $value) {
                array_push($arrayPCL, $value->id_prechecklist_isc);
            }
            $listPCLIDString = implode(',', $arrayPCL);
            $listPCLIDString = array('IDPreCheckList' => $listPCLIDString
            );
            $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?';
            $uri .= http_build_query($listPCLIDString);
//             var_dump($listPCLIDString);die;
            $apiISC = new Apiisc();
            $resultSetPClData = json_decode($apiISC->getAPI($uri));
//            var_dump($resultSetPClData);die;
            $PrechecklistUpdate = new PrecheckList();
            $listSupIDPartner = [];
            foreach ($resultSetPClData as $key => $value) {
                $PrechecklistUpdate = PrecheckList::where('id_prechecklist_isc', '=', $value->ID)->first();
//                if ($PrechecklistUpdate->sup_status_id != 3) {
//            var_dump($value);die;
                $PrechecklistUpdate->update_date = isset($value->UpdateDate) ? $value->UpdateDate : NULL;
                $PrechecklistUpdate->save();
//                }
            }
        }
        $dataApi = [
            'id' => 'success',
            'status' => '500',
        ];
        $status = 500;
        return response()->json($dataApi, $status);
    }

    public function UpdateAppointmentTimerPCL() {
        for ($i = 1; $i <= 600; $i++) {
            $listPCL = DB::table('prechecklist')
                    ->select('id_prechecklist_isc')
                    ->where(function($query) {
                        $query->whereNotNull('id_prechecklist_isc');
                        $query->where('id_prechecklist_isc', '<>', 0);
                        $query->whereNull('appointment_timer');
                    })
                    ->orderBy('created_at', 'ASC')
                    ->limit(20)
//                    ->tosql();
                    ->get();
            $arrayPCL = [];
            foreach ($listPCL as $value) {
                array_push($arrayPCL, $value->id_prechecklist_isc);
            }
            $listPCLIDString = implode(',', $arrayPCL);
            $listPCLIDString = array('IDPreCheckList' => $listPCLIDString
            );
            $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?';
            $uri .= http_build_query($listPCLIDString);
//             var_dump($listPCLIDString);die;
            $apiISC = new Apiisc();
            $resultSetPClData = json_decode($apiISC->getAPI($uri));
//            var_dump($resultSetPClData);die;
            $PrechecklistUpdate = new PrecheckList();
            $listSupIDPartner = [];
            foreach ($resultSetPClData as $key => $value) {
                $PrechecklistUpdate = PrecheckList::where('id_prechecklist_isc', '=', $value->ID)->first();
//                if ($PrechecklistUpdate->sup_status_id != 3) {
//            var_dump($value);die;
                $PrechecklistUpdate->appointment_timer = isset($value->AppointmentTimer) ? $value->AppointmentTimer : NULL;
                $PrechecklistUpdate->save();
//                }
            }
        }
        $dataApi = [
            'id' => 'success',
            'status' => '500',
        ];
        $status = 500;
        return response()->json($dataApi, $status);
    }

    public function UpdateActionProcessPCL() {
        for ($i = 1; $i <= 1000; $i++) {
            $listPCL = DB::table('prechecklist')
                    ->select('id_prechecklist_isc')
                    ->where(function($query) {
                        $query->whereNotNull('id_prechecklist_isc');
                        $query->where('id_prechecklist_isc', '<>', 0);
                        $query->where('action_process', 2);
                        $query->whereNull('sup_id_partner');
                    })
                    ->orderBy('created_at', 'ASC')
                    ->limit(20)
//                    ->tosql();
                    ->get();
            $arrayPCL = [];
            foreach ($listPCL as $value) {
                array_push($arrayPCL, $value->id_prechecklist_isc);
            }
            $listPCLIDString = implode(',', $arrayPCL);
            $listPCLIDString = array('IDPreCheckList' => $listPCLIDString
            );
            $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?';
            $uri .= http_build_query($listPCLIDString);
//             var_dump($listPCLIDString);die;
            $apiISC = new Apiisc();
            $resultSetPClData = json_decode($apiISC->getAPI($uri));
//            var_dump($resultSetPClData);die;
            $PrechecklistUpdate = new PrecheckList();
            $listSupIDPartner = [];
            foreach ($resultSetPClData as $key => $value) {
                $PrechecklistUpdate = PrecheckList::where('id_prechecklist_isc', '=', $value->ID)->first();
//                if ($PrechecklistUpdate->sup_status_id != 3) {
//            var_dump($value);die;
                $PrechecklistUpdate->action_process = isset($value->ActionProcess) ? $value->ActionProcess : NULL;
                $PrechecklistUpdate->sup_id_partner = isset($value->SupIDPartner) ? $value->SupIDPartner : NULL;
                if (isset($value->SupIDPartner) && $value->SupIDPartner != null)
                    array_push($listSupIDPartner, $value->SupIDPartner);
                $PrechecklistUpdate->save();
//                }
            }
        }
        $arrayCLID = [];

        foreach ($listSupIDPartner as $key1 => $value1) {
            array_push($arrayCLID, $value1->sup_id_partner);
        }

        $listCLIDString = implode(',', $arrayCLID);
        $this->insertCLData($listCLIDString);
        $dataApi = [
            'id' => 'success',
            'status' => '500',
        ];
        $status = 500;
        return response()->json($dataApi, $status);
    }

    public function UpdateInputTimeCL() {
        for ($i = 1; $i <= 100; $i++) {
            $listCLID = DB::table('checklist')
                    ->select('id_checklist_isc')
                    ->where(function($query) {
                        $query->whereNotNull('id_checklist_isc');
                        $query->where('id_checklist_isc', '<>', 0);
                        $query->where('finish_date', 'like', '%-%');
//                     $query->whereNull('input_time');
//                     $query->where('created_at','>=','2017-06-13 00:00:00');
                    })
//                ->orderBy('created_at', 'ASC')
                    ->limit(20)
//                    ->tosql();
                    ->get();
//                dump($listCLID);die;
//        dump($listCLID);die;
            $arrayCLID = [];
            foreach ($listCLID as $value) {
                array_push($arrayCLID, $value->id_checklist_isc);
            }
            $listCLIDString = implode(',', $arrayCLID);
            $listCLIDString = array('ChecklistID' => $listCLIDString
            );
//              $listCLIDString = array('ChecklistID' => '1098971762'
//            );
            $uri = 'http://parapi.fpt.vn/api/RadAPI/SupportListGetByCLID/?';
            $uri .= http_build_query($listCLIDString);
            $apiISC = new Apiisc();
            $resultSetClData = json_decode($apiISC->getAPI($uri));
//            var_dump($listCLIDString);die;
//            dump($resultSetClData);die;
//            if ($resultSetClData->statusCode == 200) {
//                  dump($resultSetClData);die;
            foreach ($resultSetClData->data as $key => $value) {
                $checklistUpdate = CheckList::where('id_checklist_isc', '=', $value->Id)->get();
                foreach ($checklistUpdate as $value2) {
                    $checklistUpdatePart = new CheckList();
                    $checklistUpdatePart = CheckList::where('id', '=', $value2->id)->first();

//                          if($checklistUpdatePart->input_time == NULL)
//                               dump($checklistUpdatePart);
//                            $checklistUpdatePart->input_time = isset($value->ThoiGianNhap) ? $value->ThoiGianNhap : NULL;
//                        $checklistUpdate->assign = isset($value->Phancong) ? $value->Phancong : NULL;
//                        $checklistUpdate->store_time = isset($value->ThoiGianTon) ? $value->ThoiGianTon : NULL;
//                        $checklistUpdate->error_position = isset($value->ViTriLoi) ? $value->ViTriLoi : NULL;
//                        $checklistUpdate->error_description = isset($value->MotaLoi) ? $value->MotaLoi : NULL;
//                        $checklistUpdate->reason_description = isset($value->NguyenNhan) ? $value->NguyenNhan : NULL;
//                        $checklistUpdate->way_solving = isset($value->HuongXuLy) ? $value->HuongXuLy : NULL;
//                        $checklistUpdate->checklist_type = isset($value->LoaiCl) ? $value->LoaiCl : NULL;
//                        $checklistUpdate->repeat_checklist = isset($value->CLlap) ? $value->CLlap : NULL;
                    $checklistUpdatePart->finish_date = isset($value->FinishDate) ? $value->FinishDate : NULL;
//                        dump($checklistUpdate);die;
                    $checklistUpdatePart->save();
                }
//                    dump($checklistUpdate);die;
//                    if ($checklistUpdate->final_status_id != 1 && $checklistUpdate->final_status_id != 97) {
//                        $checklistUpdate->final_status = isset($value->Final_Status) ? $value->Final_Status : NULL;
//                        $checklistUpdate->final_status_id = isset($value->Final_Status_Id) ? $value->Final_Status_Id : NULL;
//                        $checklistUpdate->total_minute = isset($value->TongSoPhut) ? $value->TongSoPhut : NULL;
//                      dump($checklistUpdate);
//                        dump($checklistUpdate->save());die;
//                        dump('sdsd');die;
            }
//                    die;
        }
//            }
        $dataApi = [
            'id' => 'success',
            'status' => '500',
        ];
        $status = 500;
        return response()->json($dataApi, $status);
    }

    public function insertCLData($listIdCl) {
        try {
//            $listCLID = DB::table('prechecklist')
//                    ->select('sup_id_partner')
//                    ->where(function($query) {
//                        $query->whereNotNull('sup_id_partner');
////                        $query->where('created_at', '>=', '2017-06-20 00:00:00');
////                        $query->where('created_at', '<=', '2017-06-21 23:59:59');
//                    })
////                    ->tosql();
//                    ->get();
//            $arrayCLID = [];
//            foreach ($listCLID as $key1 => $value1) {
//                array_push($arrayCLID, $value1->sup_id_partner);
//            }
//            $listCLIDString = implode(',', $arrayCLID);
//          $listCLIDString=  '1102310522,1102310642,1102310732,1102311292,1102311332,1102311772,1102313142,1102313232,1102313382,1102314092,1102314532,1102315272,1102315422,1102315632,1102315642,1102316002,1102316452,1102317782,1102318542,1102318702,1102319112,1102319892,1102329032,1102332582,1102332712,1102333942,1102334612,1102334802,1102345962,1105144142,1105144932,1105145922';

            $listCLIDString = array('ChecklistID' => $listIdCl
            );
            $uri = 'http://parapi.fpt.vn/api/RadAPI/SupportListGetByCLID/?';
            $uri .= http_build_query($listCLIDString);
            $apiISC = new Apiisc();
            $resultSetClData = json_decode($apiISC->getAPI($uri));
            if ($resultSetClData->statusCode == 200) {
                foreach ($resultSetClData->data as $key => $value) {
                    $checklist = Checklist::where('id_checklist_isc', '=', $value->Id)->first();
                    //Nếu chưa tồn tạo thì tạo mới
                    if ($checklist == NULL)
                        $checklist = new CheckList();
                    $contractTypeSection = DB::table('prechecklist')->select('section_survey_id', 'section_code', 'section_contract_num')
                                    ->where('sup_id_partner', '=', $value->Id)->get();
                    $checklist->id_checklist_isc = $value->Id;
                    $checklist->section_survey_id = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_survey_id : null;
                    $checklist->section_code = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_code : null;
                    $checklist->section_contract_num = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_contract_num : null;
                    $checklist->final_status = isset($value->Final_Status) ? $value->Final_Status : NULL;
                    $checklist->final_status_id = isset($value->Final_Status_Id) ? $value->Final_Status_Id : NULL;
                    $checklist->total_minute = isset($value->TongSoPhut) ? $value->TongSoPhut : NULL;
                    $checklist->input_time = isset($value->ThoiGianNhap) ? $value->ThoiGianNhap : NULL;
                    $checklist->assign = isset($value->Phancong) ? $value->Phancong : NULL;
                    $checklist->store_time = isset($value->ThoiGianTon) ? $value->ThoiGianTon : NULL;
                    $checklist->error_position = isset($value->ViTriLoi) ? $value->ViTriLoi : NULL;
                    $checklist->error_description = isset($value->MotaLoi) ? $value->MotaLoi : NULL;
                    $checklist->reason_description = isset($value->NguyenNhan) ? $value->NguyenNhan : NULL;
                    $checklist->way_solving = isset($value->HuongXuLy) ? $value->HuongXuLy : NULL;
                    $checklist->checklist_type = isset($value->LoaiCl) ? $value->LoaiCl : NULL;
                    $checklist->repeat_checklist = isset($value->CLlap) ? $value->CLlap : NULL;

                    $checklist->finish_date = isset($value->FinishDate) ? $value->FinishDate : NULL;
                    $checklist->save();
                }
            }
            return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Dữ liệu Checklist đã được cập nhập đầy đủ ']);
        } catch (Exception $e) {
            return json_encode(['code' => 500, 'status' => 'Lỗi', 'msg' => $e->getMessage()]);
        }
    }

    public function updateObijId() {
//          $listContract = DB::table('outbound_survey_sections')
//                ->select('section_contract_num','section_code','section_survey_id')
//                ->where('section_account_id',1671173)
//                 ->whereIn('section_survey_id',[1,2,6])
//                 ->orderBy('section_id','DESC')
//                 ->first();
//          dump($listContract);die;


        $listObijID = DB::table('outbound_accounts')
                ->select('id')
                ->where('objid', '')
                ->whereIn('contract_num', ['DND032633', 'SGD223744', 'HNH242327', 'TGD011410', 'SGD238050', 'TID008949', 'SGD126053', 'SGD524189', 'SGDF00592', 'SGD035062', 'HND189261', 'SGD527982', 'SGFD51704', 'SGD516049', 'SGD247837', 'SGD248761', 'SGD093558', 'HNH103709', 'SGAD01826'])
                ->offset(11)
                ->take(5)
                ->get();
        foreach ($listObijID as $key => $value) {
            $contract = DB::table('outbound_survey_sections')
                    ->select('section_contract_num', 'section_code', 'section_survey_id')
                    ->where('section_account_id', $value->id)
                    ->whereIn('section_survey_id', [1, 2, 6])
                    ->orderBy('section_id', 'DESC')
                    ->first();
            if ($contract != null && $contract->section_contract_num != '' && $contract->section_code != '' && $contract->section_survey_id != '') {
                $apiISC = new Apiisc();
                $infoAcc = array('ObjID' => 0,
                    'Contract' => $contract->section_contract_num,
                    'IDSupportlist' => $contract->section_code,
                    'Type' => $contract->section_survey_id
                );
                $uri = 'http://parapi.fpt.vn/api/RadAPI/spCEM_ObjectGetByObjID/?';
                $uri .= http_build_query($infoAcc);
                $result = json_decode($apiISC->getAPI($uri));
                $outboundAcoount = OutboundAccount::where('id', $value->id)->first();
                $outboundAcoount->objid = $result->data[0]->ObjID;
                $outboundAcoount->save();
            }
        }
        return 'Thanh cong';
    }

    public function updateAllDate() {
//         $surveySection=OutboundAccount::where('contract_num','BND029485')->get();
//          $surveySection2=SurveySections::find(1);
//         dump($surveySection[0]);
//           dump($surveySection2);die;
          $dateFormat = 'Y-m-d H:i:s';
        $apiIsc = new Apiisc();
        $result = DB::table('outbound_survey_sections')
                ->select('section_id', 'section_contract_num', 'section_survey_id', 'section_code')
//                ->where('section_finish_date_list', 'like', '%1970%')
                  ->whereNull('section_supporter')
                  ->whereNull('section_subsupporter')
                ->where('section_time_completed_int','>=',  strtotime('2018-01-01 00:00:00'))
                 ->whereIn('section_survey_id',[1,2,6,9,10])
//                ->limit(5)
                ->get();
        foreach ($result as $key => $value) {
            $infoAcc = array('ObjID' => 0,
                'Contract' => $value->section_contract_num,
                'IDSupportlist' => $value->section_code,
                'Type' => $value->section_survey_id
            );

            /*
             * Lấy thông tin khách hàng
             */

            $responseAccountInfo = $apiIsc->GetFullAccountInfo($infoAcc);
            $responseAccountInfo = json_decode($responseAccountInfo['result'])->data;
//            dump($value,$responseAccountInfo[0]);
//            $surveySection = new SurveySections();
//            $outboundAcount = new OutboundAccount();
            $surveySection=SurveySections::find($value->section_id);
//              $outboundAcount=OutboundAccount::where('contract_num',$value->section_contract_num)->get();
//               $outboundAcount[0]->contract_date=date($dateFormat, time($responseAccountInfo[0]->ContractDate));
//            $surveySection->section_finish_date_list=  $outboundAcount[0]->finish_date_list= isset($responseAccountInfo[0]->FinishDateList) ? date($dateFormat, time($responseAccountInfo[0]->FinishDateList)) : '';
//              $surveySection->section_finish_date_inf= $outboundAcount[0]->finish_date_inf=isset($responseAccountInfo[0]->FinishDateINF) ? date($dateFormat, time($responseAccountInfo[0]->FinishDateINF)) : ''; 
             $surveySection->section_supporter=  isset($responseAccountInfo[0]->Supporter) ? $responseAccountInfo[0]->Supporter : null;
              $surveySection->section_subsupporter=isset($responseAccountInfo[0]->SubSupporter) ? $responseAccountInfo[0]->SubSupporter : null; 
            $surveySection->save();
//              $outboundAcount[0]->save();
            
           
//             =date($dateFormat, time($responseAccountInfo[0]->FinishDateList));
//                 date($dateFormat, time($responseAccountInfo[0]->FinishDateINF));
           
//             dump($responseAccountInfo[0]);
//            die;
        }
        return 'Thanh cong';
    }
    
   public function testUpdateInvalidCase() {
        $errorBrachCode = [];
        $errorUserName = [];
        $result = DB::table('list_invalid_survey_case')
                ->select('*')
                ->get();
        foreach ($result as $key => $value) {
            $arrayError = explode(',', $value->type_error);
             $caseToDelete = ListInvalidSurveyCase::find($value->id);
            if (in_array(1, $arrayError)) {
                $infoAcc = array('ObjID' => 0,
                    'Contract' => $value->contract_number,
                    'IDSupportlist' => $value->section_code,
                    'Type' => $value->survey_id
//                 'Contract' => 'BND029485',
//                'IDSupportlist' => '1114583322',
//                'Type' => 9
                );
//            DB::beginTransaction();
                /*
                 * Lấy thông tin khách hàng
                 */
                try {                  
                    $url = 'RPDeployment/spCEM_ObjectGetByObjID';
                    $result = json_decode($this->postAPI($infoAcc, $url), true);
                    $responseAccountInfo = $result['data'];
                    $testData = ['Supporter', 'SubSupporter', 'LocationID', 'BranchCode'];
                    $validData = true;
                    foreach ($testData as $key => $value2) {
                        if (!isset($responseAccountInfo[0][$value2]))
                            $validData = false;
                    }
                    //Có đủ dữ liệu trả về
                    if ($validData) {
                        $surveySection = SurveySections::find($value->section_id);
                        $surveySection->section_supporter = isset($responseAccountInfo[0]['Supporter']) ? $responseAccountInfo[0]['Supporter'] : null;
                        $surveySection->section_subsupporter = isset($responseAccountInfo[0]['SubSupporter']) ? $responseAccountInfo[0]['SubSupporter'] : null;
                        $surveySection->section_location_id = isset($responseAccountInfo[0]['LocationID']) ? $responseAccountInfo[0]['LocationID'] : null;
                        $surveySection->section_branch_code = isset($responseAccountInfo[0]['BranchCode']) ? $responseAccountInfo[0]['BranchCode'] : null;
                        if ($surveySection->save()) {
                            DB::commit();
                        } else {
                            DB::rollback();
                            $caseToDelete->updated_date_on_survey = date('Y-m-d H:i:s');
                            $caseToDelete->save();
                        }
                    } else {
                        DB::rollback();
                        $caseToDelete->updated_date_on_survey = date('Y-m-d H:i:s');
                        $caseToDelete->save();
                    }
                } catch (Exception $ex) {
                    echo 'loi';
                    echo $ex->getMessage();
                    DB::rollback();
                }
            }
            if (in_array(2, $arrayError)) {
                array_push($errorBrachCode, $value->section_id);
            }
            if (in_array(3, $arrayError)) {
                array_push($errorUserName, $value->section_id);
            }
            $caseToDelete->delete();
        }
        if(!empty($errorBrachCode))
        {
             Mail::send('emails.listInvalidCase', ['info' => $errorBrachCode,'title'=>'Thông tin các case bị sai thông tin vùng miền, chi nhánh'], function ($message) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to('huydp2@fpt.com.vn');
//                    $message->cc($cc);
                    $message->subject('Thông tin các case bị sai thông tin vùng miền, chi nhánh');
                });
        }
         if(!empty($errorUserName))
        {
             Mail::send('emails.listInvalidCase', ['info' => $errorUserName,'title'=>'Thông tin các case bị thiếu thông tin người đăng nhập'], function ($message) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to('huynl2@fpt.com.vn');
//                    $message->cc($cc);
                    $message->subject('Thông tin các case bị thiếu thông tin người đăng nhập');
                });
        }
        echo 'Thanh cong';
    }

    private function postAPI($data, $url) {
        $str_data = json_encode($data);
        $uri = 'http://parapiora.fpt.vn/api/' . $url;
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_PROXY, "");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result = curl_exec($ch);
//        if (FALSE === $result) {
//            throw new Exception(curl_error($ch), curl_errno($ch));
//            var_dump(curl_error($ch));
//            var_dump(curl_errno($ch));
//            die;
//            return curl_error($ch);
//        }
        // close the connection, release resources used
        curl_close($ch);
        return $result;

//          $resultCurlExt = Curl::to($uri)
//                ->withData($data)
//                ->returnResponseObject()
//                ->post();
//        if (isset($resultCurlExt->error))
//            return $resultCurlExt->error;
//        else
//            return $resultCurlExt->content;
    }

}
