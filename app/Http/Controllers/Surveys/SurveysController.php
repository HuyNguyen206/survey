<?php

namespace App\Http\Controllers\Surveys;

use App\Component\ExtraFunction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Psy\Util\Json;
use App\Models\Surveys;
use App\Models\SurveyResult;
use App\Models\SurveySections;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\OutboundAccount;
use App\Models\Apiisc;
use App\Models\ContactProfile;
use App\Models\User;
use App\Models\CheckList;
use App\Models\PrecheckList;
use App\Models\FowardDepartment;
use App\Models\OutboundQuestions;
use App\Models\ListInvalidSurveyCase;
use App\Helpers\Helper;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Component\HelpProvider;
use App\Jobs\SendNotificationEmail;
use App\Models\Api\ApiHelper;
use Exception;
use App\Jobs\NotifyNewPhoneNumberEmail;
use Illuminate\Support\Facades\App;
use DB;
//use Illuminate\Support\Facades\Validator;
//use Illuminate\Contracts\Validation\Validator;
use Validator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class SurveysController extends Controller {

    /**
     * hiển thị danh sách surver cho nhân viên chăm sóc khách hàng lựa chọn cho phù hợp với đối tượng KH
     *
     * @return Response
     */
    var $code = 400;
    var $msg = array();
    var $data = array();

    var $modelSurveySection;
    var $modelSurveyResult;
    var $extraFunction;
    public function __construct(){
        $this->modelSurveySection = new SurveySections();
        $this->modelSurveyResult = new SurveyResult();
        $this->extraFunction = new ExtraFunction();
    }

    private function checkCanViewEditSurvey($surveySection){
        $userRole = Session::get('userRole');
        $nowTime = strtotime(date('Y-m-d H:i:s'));
        $surveyTime = $surveySection->section_time_completed_int;
        $surveyConnected = $surveySection->section_connected;
        $surveyCount = $surveySection->section_count_connected;

        $canEditSurvey = false;
        $isMember = true;

        $timeSpent = $nowTime - $surveyTime;
        $timeMaxEdit = 2592000; // 30 ngày
        $timeMinEdit = 300; // 5 phút

        // Kiểm tra quyền hạn
        if($userRole['level'] != 100){
            $isMember = false;
        }

        //Kiểm tra thời gian
        if($timeMinEdit < $timeSpent && $timeSpent <= $timeMaxEdit){
            if(!$isMember){
                $canEditSurvey = true;
            }
        }elseif($timeMinEdit >=$timeSpent){
            $canEditSurvey = true;
        }

        //Kiểm tra liên lạc được hay không
        if($surveyConnected == 1 && $surveyCount < 3){
            $canEditSurvey = true;
        }

        return [
            "canEditSurvey" => $canEditSurvey,
            'isMember' => $isMember,
        ];
    }

    public function checkSurvey($so_hd, $surveyID, $sectionCode){
        //Kiểm tra thông tin hợp đồng
        $paramSurveySection = [
            'shd' => $so_hd,
            'code' => $sectionCode,
            'num_type' => $surveyID
        ];
        if(!in_array($paramSurveySection['num_type'], [1,2])){
            $message = ['message' => trans('error.SurveyTypeDoesNotExist')];
            return view('errors.error', $message);
        }

        $surveySection = $this->modelSurveySection->getSurveySections($paramSurveySection);
        if(!empty($surveySection)){
            $result = $this->checkCanViewEditSurvey($surveySection);
            if($result['canEditSurvey']){
                return $this->editSurveyView($surveySection);
            }else{
                $messageTemp = trans('warning.ExpiredTime');
                if($result['isMember']){
                    if($surveySection->section_count_connected < 3){
                        $messageTemp .= ' 5 '.trans('warning.minutes').'! ';
                    }else{
                        $messageTemp = trans('warning.CanNotContactFor3Times').'! ';
                    }
                }else{
                    $messageTemp .= ' 30 '.trans('warning.days').'! ';
                }
                $messageTemp .= trans('warning.SurveyCannotBeEdit');

                $message = [
                    'message' => $messageTemp,
                    'link' => url('/success/'.$surveySection->section_id),
                ];
                return view('errors.warning', $message);
            }
        }else{
            return $this->createSurveyView($paramSurveySection);
        }
    }

    private function createSurveyView($paramSurveySection){
        try{
            // Lấy thông tin khách hàng bằng api inside
            $infoAcc = array(
                'Contract' => $paramSurveySection['shd'],
                'IDSupportlist' =>$paramSurveySection['code'],
                'Type' => $paramSurveySection['num_type'],
            );

            // Lấy thông tin khách hàng qua api inside
            $apiIsc = new Apiisc();
            $responseAccountInfo = json_decode($apiIsc->GetFullAccountInfo($infoAcc));
            $accountInfoISC = (array) $responseAccountInfo->data[0];

            if(isset($accountInfoISC['Result'])){
                $message = ['message' => trans('error.CustomerInfoDoesNotExist')];
                return view('errors.error', $message);
            }

            // update hoặc insert thông tin khách hàng
            $outboundAccount = new OutboundAccount();
            $outboundAccount->saveAccount($accountInfoISC);
            // Lấy thông tin xuống OutboundAccount
            $accountInfo = $outboundAccount->getAccountInfoByContractZero($paramSurveySection['shd']);
            $responseInfo = [];
            // Lấy thông tin lịch sử hỗ trợ khách hàng
            $paramHistory = [
                'contract' => $paramSurveySection['shd'],
            ];
            $historyData = $apiIsc->getCallerHistoryByObjID($paramHistory);
            $historyData = $historyData->data;
            $responseInfo['data_history'] = $historyData;

            // Lấy thông tin lịch sử chăm sóc
            $accountInfoFromSurvey = $this->modelSurveySection->getAllSurveyInfoOfAccount($accountInfo->id);
            $historyOutboundSurvey = [];
            foreach($accountInfoFromSurvey as $value){
                $value = (array)$value;
                $historyOutboundSurvey[] = $value;
            }
            $responseInfo['outbound_history'] = $historyOutboundSurvey;
            $customerQA = [];
            $baseQA = $this->extraFunction->getFullQAWithKeyIsQuestionKey($paramSurveySection['num_type']);
            $baseAllAns = $this->extraFunction->getFullAnswerWithKeyIsGroupAnswer();

            $baseQAOther = $this->extraFunction->getFullQAOfQuestionOtherNPS($paramSurveySection['num_type']);

            $mapAliasWithGroupAnswer = [
                1 => 8,
                3 => 7,
                4 => 7,
                5 => 6,
                10 => 4,
            ];

            $arrayConnected = [
                4 => 'MeetUser',
                3 => 'DidntMeetUser',
                2 => 'MeetCustomerCustomerDeclinedToTakeSurvey',
                1 => 'CannotContact',
                0 => 'NoNeedContact',
            ];

            $arrayPointOfContact = [
                1 => 'AfterActive',
                2 => 'AfterChecklist',
            ];

            $arrayAction = [
                1 => [
                    'actionKey' => 'NotYetDoAnything',
                    'actionInputId' => '',
                ],
                2 => [
                    'actionKey' => 'CreateChecklist',
                    'actionInputId' => 'createCL',
                ],
                3 => [
                    'actionKey' => 'CreatePreChecklist',
                    'actionInputId' => 'createPCL',
                ],
            ];

            $data = [
                'arrayConnected' => $arrayConnected,
                'arrayPointOfContact' => $arrayPointOfContact,
                'arrayAction' => $arrayAction,
                'accountInfo' => $accountInfo,
                'responseInfo' => $responseInfo,
                'customerQA' => $customerQA,
                'baseQA' => $baseQA,
                'baseAllAns' => $baseAllAns,
                'mapAliasWithGroupAnswer' => $mapAliasWithGroupAnswer,
                'paramSurveySection' => $paramSurveySection,
                'baseQAOther' => $baseQAOther,
            ];

            return view('surveys.createSurvey', $data);
        }catch(Exception $ex){
//            $message = ['message' => trans('error.SystemError')];
            $message = ['message' => $ex->getMessage()];
            return view('errors.error', $message);
        }
    }

    public function createSurvey(Request $request){
        $input = $request->all();
        $user = Auth::user();
        $Surveys = new Surveys();
        $validateData = [
            'valid' => true,
            'error' => null,
        ];

        try{
            if(empty($input['contactPhone']) || empty($input['contactName'])){
                $validateData['valid'] = false;
                $validateData['error']['contact'] = trans('validation.Contact');
            }

            if(!$validateData['valid']){
                return redirect()->back()
                    ->withErrors($validateData['error'])
                    ->withInput($input);
            }
            DB::beginTransaction();
            $outboundAccount = new OutboundAccount();
            $dataAccount = $outboundAccount->getAccountInfoByContractZero($input['contractNum']);
            $input['ip']=$request->ip();
            //Đổ dữ liệu vào model survey
            $surveySections = $this->assignData($input, $dataAccount, $user); 
            $sectionID = $Surveys->saveSurveySections($surveySections);
            $input['sectionID']=$sectionID;
            if($input['connected'] == 4){
                // Lấy toàn bộ câu hỏi có thể có của loại khảo sát
                $baseQA = $this->extraFunction->getFullQAWithKeyIsQuestionKey($input['type']);
                $baseQAOther = $this->extraFunction->getFullQAOfQuestionOtherNPS($input['type']);
                $paramResult = [];
                // Kiểm tra input ứng với từng loại câu hỏi về nhân viên hay dịch vụ
                $questionNumber = 1;
                foreach($baseQA as $questionID => $dataQuestion){
                    $resultValidate = $this->validateAnswerQuestion($input, $questionID, $dataQuestion, $questionNumber);
                    if($resultValidate['valid']){
                        $paramResult[] = $resultValidate['data'];
                    }else{
                        $validateData['valid'] = false;
                        foreach($resultValidate['error'] as $key => $value){
                            $validateData['error'][$key] = $value;
                        }
                    }
                    $questionNumber++;
                }

                // Kiểm tra input ứng với loại câu hỏi có nhiều câu trả lời
                foreach($baseQAOther as $questionID => $dataQuestion){
                    $resultValidate = $this->validateAnswerQuestionOther($input, $questionID);
                    if($resultValidate['valid']){
                        $paramResult = array_merge($paramResult, $resultValidate['data']);
                    }
                }

                // update lại bộ câu trả lời
                if($validateData['valid']){
                    $this->modelSurveyResult->insertSurveyResult($paramResult);
                }else{
                    return redirect()->back()
                        ->withErrors($validateData['error'])
                        ->withInput($input);
                }
            }

            // Cập nhật lại thông tin khảo sát
            DB::commit();
             // đá về đây...
            return redirect(url('/success/'.$sectionID));
        }catch(Exception $ex){
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => trans('error.SystemError')])
                ->withInput($input);
        }
    }

    private function editSurveyView($surveySection){
        try{
            $surveyInfo = (array)$surveySection;
            // Lấy thông tin khách hàng
            $outboundAccount = new OutboundAccount();
            $accountInfo = $outboundAccount->getAccountInfoByContractZero($surveyInfo['section_contract_num']);

            $apiIsc = new Apiisc();
            $responseInfo = [];
            // Lấy thông tin lịch sử hỗ trợ khách hàng
            $paramHistory = [
                'contract' => $surveyInfo['section_contract_num'],
            ];
            $historyData = $apiIsc->getCallerHistoryByObjID($paramHistory);
            $historyData = $historyData->data;
            $responseInfo['data_history'] = $historyData;

            // Lấy thông tin lịch sử chăm sóc
            $accountInfoFromSurvey = $this->modelSurveySection->getAllSurveyInfoOfAccount($surveyInfo['section_account_id'], $surveyInfo['section_id']);
            $historyOutboundSurvey = [];
            foreach($accountInfoFromSurvey as $value){
                $value = (array)$value;
                $historyOutboundSurvey[] = $value;
            }
            $responseInfo['outbound_history'] = $historyOutboundSurvey;
            $customerQA = $this->extraFunction->getDetailAnswerOfCustomer($surveyInfo['section_id']);
            $baseQA = $this->extraFunction->getFullQAWithKeyIsQuestionKey($surveyInfo['section_survey_id']);
            $baseAllAns = $this->extraFunction->getFullAnswerWithKeyIsGroupAnswer();

            $baseQAOther = $this->extraFunction->getFullQAOfQuestionOtherNPS($surveyInfo['section_survey_id']);

            $mapAliasWithGroupAnswer = [
                1 => 8,
                3 => 7,
                4 => 7,
                5 => 6,
                10 => 4,
            ];

            $arrayConnected = [
                4 => 'MeetUser',
                3 => 'DidntMeetUser',
                2 => 'MeetCustomerCustomerDeclinedToTakeSurvey',
                1 => 'CannotContact',
                0 => 'NoNeedContact',
            ];

            $arrayPointOfContact = [
                1 => 'AfterActive',
                2 => 'AfterChecklist',
            ];

            $arrayAction = [
                1 => [
                    'actionKey' => 'NotYetDoAnything',
                    'actionInputId' => '',
                ],
                2 => [
                    'actionKey' => 'CreateChecklist',
                    'actionInputId' => 'createCL',
                ],
                3 => [
                    'actionKey' => 'CreatePreChecklist',
                    'actionInputId' => 'createPCL',
                ],
            ];

            $data = [
                'arrayConnected' => $arrayConnected,
                'arrayPointOfContact' => $arrayPointOfContact,
                'arrayAction' => $arrayAction,
                'surveyInfo' => $surveyInfo,
                'accountInfo' => $accountInfo,
                'responseInfo' => $responseInfo,
                'customerQA' => $customerQA,
                'baseQA' => $baseQA,
                'baseAllAns' => $baseAllAns,
                'mapAliasWithGroupAnswer' => $mapAliasWithGroupAnswer,
                'baseQAOther' => $baseQAOther,
            ];

            return view('surveys.editSurvey', $data);
        }catch(Exception $ex){
            $message = ['message' => trans('error.SystemError')];
            return view('errors.error', $message);
        }
    }

    public function editSurvey(Request $request){
        $input = $request->all();
        $user = Auth::user();
        $validateData = [
            'valid' => true,
            'error' => null,
        ];

        try{
            DB::beginTransaction();
            $sectionID = $input['sectionID'];
            // Xóa toàn bộ câu trả lời cũ
            $this->modelSurveyResult->deleteSurveyResult($sectionID);

            if($input['connected'] == 4){
                // Lấy toàn bộ câu hỏi có thể có của loại khảo sát
                $baseQA = $this->extraFunction->getFullQAWithKeyIsQuestionKey($input['type']);
                $baseQAOther = $this->extraFunction->getFullQAOfQuestionOtherNPS($input['type']);
                $paramResult = [];

                // Kiểm tra input ứng với từng loại câu hỏi về nhân viên hay dịch vụ
                $questionNumber = 1;
                foreach($baseQA as $questionID => $dataQuestion){
                    $resultValidate = $this->validateAnswerQuestion($input, $questionID, $dataQuestion, $questionNumber);
                    if($resultValidate['valid']){
                        $paramResult[] = $resultValidate['data'];
                    }else{
                        $validateData['valid'] = false;
                        foreach($resultValidate['error'] as $key => $value){
                            $validateData['error'][$key] = $value;
                        }
                    }
                    $questionNumber++;
                }

                // Kiểm tra input ứng với loại câu hỏi có nhiều câu trả lời
                foreach($baseQAOther as $questionID => $dataQuestion){
                    $resultValidate = $this->validateAnswerQuestionOther($input, $questionID);
                    if($resultValidate['valid']){
                        $paramResult = array_merge($paramResult, $resultValidate['data']);
                    }
                }

                // update lại bộ câu trả lời hoặc trả về lỗi thông tin
                if($validateData['valid']){
                    $this->modelSurveyResult->insertSurveyResult($paramResult);
                }
            }

            // Cập nhật lại thông tin khảo sát
            $paramSection = [
                'section_connected' => $input['connected'],
                'section_contact_phone' => $input['contactPhone'],
                'section_contact' => $input['contactName'],
                'section_action' => $input['action'],
                'section_user_modified' => $user->name,
                'section_date_modified' => date('Y-m-d H:i:s'),
                'section_count_connected' => $input['sectionCountConnected'] + 1,
                'section_note' => $input['note'],
            ];

            if(empty($paramSection['section_contact_phone']) || empty($paramSection['section_contact'])){
                $validateData['valid'] = false;
                $validateData['error']['contact'] = trans('validation.Contact');
            }

            if(!$validateData['valid']){
                return redirect()->back()
                    ->withErrors($validateData['error'])
                    ->withInput($input);
            }

            $result = $this->modelSurveySection->updateSurvey($sectionID, $paramSection);
            if($result){
                DB::commit();
            }else{
                DB::rollback();
                $message = ['message' => trans('error.CannotSaveSurvey')];
                return view('errors.error', $message);
            }

            // đá về đây...
            return redirect(url('/success/'.$sectionID));
        }catch(Exception $ex){
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => trans('error.SystemError')])
                ->withInput($input);
        }
    }

    public function successSurvey($sectionID){
        $modelSurveySection = new SurveySections();
        $detailResult = $modelSurveySection->getAllDetailSurveyInfo($sectionID);
        $surveySection = $modelSurveySection->getSurveySections(['sectionId' => $sectionID]);

        $apiHelp = new ApiHelper();
        $paramCheck['sectionId'] = $sectionID;
        $resCheck = $apiHelp->checkSendMail($paramCheck);
        if ($resCheck['status']) {
            Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
        }

        $data = [
            'detail' => $detailResult,
            'contract' => $surveySection->section_contract_num,
            'connected' => $surveySection->section_connected,
            'contactPhone' => $surveySection->section_contact_phone,
            'mainNote' => $surveySection->section_note,
        ];
        return view('surveys.success', $data);
    }

    private function validateAnswerQuestion($input, $questionID, $dataQuestion, $questionNumber){
        $fieldResult = [
            'survey_result_section_id' => $input['sectionID'],
            'survey_result_question_id' => $questionID,
            'survey_result_answer_id' => null,
            'survey_result_answer_extra_id' => null,
            'survey_result_error' => null,
            'survey_result_note' => null,
            'survey_result_action' => null,
        ];
        $result = [
            'valid' => true,
            'data' => $fieldResult,
            'error' => null,
        ];

        $keyTrans = [
            'notNote' => 'TheAgentMustFillNoteForBadAnswerCustomer',
            'notResolve' => 'TheAgentMustChooseResolveForBadAnswerCustomer',
            'notErrorType' => 'TheAgentMustChooseErrorTypeForBadAnswerCustomer',
            'notRate' => 'TheAgentDidNotChooseAnyAnswerYet',
        ];

        if(isset($input['extraAnswer'.$questionID])){
            $result['data']['survey_result_answer_id'] = '-1';
            $result['data']['survey_result_answer_extra_id'] = $input['extraAnswer'.$questionID];
            $result['data']['survey_result_note'] = $input['subNote'.$questionID];
        }else{
            if(isset($input['rateScore'.$questionID])){
                $result['data']['survey_result_answer_id'] = $input['rateScore'.$questionID];
                $result['data']['survey_result_note'] = $input['subNote'.$questionID];
                if(in_array($input['rateScore'.$questionID], [1,2])){
                    if(empty($input['subNote'.$questionID])){
                        $result['valid'] = false;
                        $result['error']['question'.$questionID] = trans('validation.'.$keyTrans['notNote'], ['questionNumber' => $questionNumber]);
                    }
                    if(in_array($dataQuestion['questionAlias'], [5,6])){
                        if(isset($input['extraAction'.$questionID])){
                            $result['data']['survey_result_action'] = $input['extraAction'.$questionID];
                        }else{
                            $result['valid'] = false;
                            $result['error']['question'.$questionID] = trans('validation.'.$keyTrans['notResolve'], ['questionNumber' => $questionNumber]);
                        }
                        if(isset($input['extraError'.$questionID])){
                            $result['data']['survey_result_error'] = $input['extraError'.$questionID];
                        }else{
                            $result['valid'] = false;
                            $result['error']['question'.$questionID] = trans('validation.'.$keyTrans['notErrorType'], ['questionNumber' => $questionNumber]);
                        }
                    }
                }
            }else{
                $result['valid'] = false;
                $result['error']['question'.$questionID] = trans('validation.'.$keyTrans['notRate'], ['questionNumber' => $questionNumber]);
            }
        }
        return $result;
    }

    private function validateAnswerQuestionOther($input, $questionID){
        $fieldResult = [
            'survey_result_section_id' => $input['sectionID'],
            'survey_result_question_id' => $questionID,
            'survey_result_answer_id' => null,
            'survey_result_answer_extra_id' => null,
            'survey_result_error' => null,
            'survey_result_note' => null,
            'survey_result_action' => null,
        ];
        $result = [
            'valid' => true,
            'data' => [],
            'error' => null,
        ];

        if(isset($input['rateScore'.$questionID])){
            $fieldResult['survey_result_note'] = $input['subNote'.$questionID];
            foreach($input['rateScore'.$questionID] as $answer){
                $fieldResult['survey_result_answer_id'] = $answer;
                $result['data'][] = $fieldResult;
            }
        }else{
            $result['valid'] = false;
        }
        return $result;
    }

    public function index() {
        return $this->showListSurveys();
    }

    /*
      Lấy lịch sử khảo sát ở frontend
     */

    public function getHistoryFrontend(Request $request) {
        $pageNum = 1;
        $itemPer = 10;
        if (!empty($request->pageNum)) {
            $pageNum = $request->pageNum;
        }
        if (!empty($request->itemPer)) {
            $itemPer = $request->itemPer;
        }
        $modelSurveySections = new SurveySections();
        $outQuestionModel = new OutboundQuestions();
        $allQuestions = $outQuestionModel->getAllQuestion();
        $questionNeed = [];
        foreach ($allQuestions as $question) {
            if (isset($questionNeed[$question->question_alias])) {
                array_push($questionNeed[$question->question_alias], $question->question_id);
            } else {
                $questionNeed[$question->question_alias] = [$question->question_id];
            }
        }
        $condition['allQuestion'] = $questionNeed;
        $currentUserId = Auth::user()->id;
        $recordNum = $modelSurveySections->getAllSurveyInfoUser($currentUserId, '', '', $request->filter, $request->listIdResult, $request->listTypeSurvey);
        $result = $modelSurveySections->getAllSurveyInfoUser($currentUserId, $itemPer, $pageNum, $request->filter, $request->listIdResult, $request->listTypeSurvey);
        if ($recordNum > 0) {
            $dataInfo['do'] = 1;
            $dataInfo['total_count'] = $recordNum;
            $dataInfo['data'] = $result;
        } else {
            $dataInfo['do'] = 2;
        }
        return json_encode($dataInfo);
    }

    /*
      Tìm thông tin hợp đồng
     */

    public function search($so_hd, $sectionCode, $surveyID) {
        $infoAcc = array('ObjID' => '',
            'Contract' => $so_hd,
            'ID' => $sectionCode,
            'Type' => $surveyID
        );
        /*
         * Lấy thông tin khách hàng
         */
        $apiIsc = new Apiisc();
//        $responseAccountInfo = $apiIsc->GetFullAccountInfo($infoAcc);
        $outboundAccount = new OutboundAccount();
        $responseAccountInfo = $outboundAccount->getAccountInfoByContractZero($so_hd);
        // end lấy thông tin khách hàng
        // nếu không lấy được thông tin khách hàng return false
        $responseAccountInfo = json_decode($responseAccountInfo);
        $responseInfo['orginal_dataCus'] = $responseAccountInfo;
        if (!isset($responseAccountInfo[0]->ObjID)) {
            $result['code'] = 400; //không có dữ liệu
            $result['msg'] = 'Bad Request';
            return $result;
        }
        $helpProvider = new HelpProvider();
        $responseAccountInfo[0] = $helpProvider->processDataFromISC($responseAccountInfo[0]);
        $startDate = $responseAccountInfo[0]->ContractDate;
        $responseAccountInfo[0]->ContractDate = $startDate; //date("d-m-Y", $startDate );
        $responseInfo['data_cusinfo'] = $responseAccountInfo;
        $responseInfo['msg'] = 'Success';
        $responseInfo['code'] = 200;
        // nếu có thông tin khách hàng thì lấy thông tin lịch sử hỗ trợ
        //$ObjID = 1020104442;
        $ObjID = $responseAccountInfo[0]->ObjID;

        $paramHistory = array('ObjID' => $ObjID, 'RecordCount' => 3);
        $historyData = json_decode($apiIsc->getCallerHistoryByObjID($paramHistory));
        if (count($historyData) > 0) {
            for ($i = 0; $i < count($historyData); $i++) {
                if (isset($historyData[$i]->StartDate)) {
                    $startDate = $historyData[$i]->StartDate;
                    $startDate = preg_replace('/[^0-9]/', '', $startDate);
                    $startDate = $startDate / 1000;
                    $historyData[$i]->StartDate = date("d-m-Y", $startDate);
                }
                if (isset($historyData[$i]->EndDate)) {

                    $endDate = $historyData[$i]->EndDate;
                    $endDate = preg_replace('/[^0-9]/', '', $endDate);
                    $endDate = $endDate / 1000;
                    $historyData[$i]->EndDate = date("d-m-Y", $endDate);
                }
            }
        }
        $responseInfo['data_history'] = $historyData;
        /*
         * kiểm tra thông tin khách hàng đã lưu trên Mo hay chưa
         * Nếu lưu rồi lấy thông tin lịch sử survey
         */

        $OutboundAccount = new OutboundAccount();
        $responseInfo['accountInfoFromSurvey'] = $OutboundAccount->getAccountInfoByContract($infoAcc['Contract']);
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
//         var_dump($responseInfo);die;
        return $responseInfo;
    }

    /*
      Lấy thông tin hợp đồng, chi tiết của khảo sát
     */

    public function edit_survey_frontend(Request $request) {
        $survey = new SurveySections();
        $roleID = User::getRole(Auth::user()->id);
        $resultCheck = $survey->checkSurvey(Auth::user()->id, $request->surveyID, $roleID);
        if (!is_object($resultCheck)) {
            if ($resultCheck == 1) {
                $result = array('code' => 600, 'msg' => 'Khảo sát này không tồn tại');
                return Json::encode($result);
            } else if ($resultCheck == 2) {
                $result = array('code' => 600, 'msg' => 'Bạn không có quyền chỉnh sửa khảo sát này');
                return Json::encode($result);
            }
        } else {
            $timeLimit = ($roleID == 36) ? 'P30D' : 'PT5M';
            $messageEdit = ($roleID == 36) ? 'Khảo sát này đã vượt quá 30 ngày để sửa' : 'Khảo sát này đã vượt quá 5 phút để sửa';
            if ($resultCheck->section_connected == 4) {
                $currentDate = new \DateTime();
                $time_complete = new \DateTime($resultCheck->section_time_completed);
                $time_complete->add(new \DateInterval($timeLimit));
                if ($time_complete < $currentDate) {
                    $result = array('code' => 600, 'msg' => $messageEdit);
                    return Json::encode($result);
                }
            }
        }
        $surveyResult = new SurveyResult();
        $modelSurvey = SurveySections::find($request->surveyID);
        $sectionCode = $modelSurvey->section_code;
        $surveyId = $modelSurvey->section_survey_id;
        $account = OutboundAccount::find($modelSurvey->section_account_id);
        $result = $this->search($account->so_hd, $sectionCode, $surveyId);
        //Lấy thông tin băng thông
        //Thong tin bang thong
        $apiIsc = new Apiisc();
        $infoBanwidth = array('ObjID' => $account->objid,
        );
        $arrayBandwidth = [0 => 'Không nâng băng thông', 1 => 'Chưa hoàn tất nâng băng thông', 2 => 'Hoàn tất nâng băng thông'];
        $bandwidth = json_decode($apiIsc->CheckBandwidthByObjID($infoBanwidth), true);
        $result['bandWidthInfo'] = $arrayBandwidth[$bandwidth[0]['Result']];
        $result['section_connected'] = $modelSurvey->section_connected;
        //Nếu khảo sát có tồn tại chi tiết các câu hỏi
        if (!empty($surveyResult->getDetailSurvey($modelSurvey->section_id))) {
            $resultDetail = $surveyResult->getDetailSurvey($modelSurvey->section_id);
            $arrayResult = array();
            foreach ($resultDetail as $key => $value) {

                if (in_array($value->survey_result_question_id, [5, 7, 25, 40, 44])) {
                    if ($value->survey_result_answer_id != -1) {
                        $arrayMap = [31 => 1, 27 => 2, 52 => 3, 53 => 4, 54 => 5, 55 => 6, 138 => 25, 139 => 24, 57 => 8, 58 => 9, 59 => 10, 60 => 11, 29 => 12,
                            62 => 13, 63 => 14, 64 => 15, 65 => 16, 66 => 17, 28 => 18, 68 => 19, 69 => 20, 32 => 21, 70 => 22, 71 => 23, 72 => 24,
                            73 => 25, 74 => 26, 75 => 27, 76 => 28, 77 => 29, 78 => 30, 79 => 31, 80 => 32, 81 => 33, 82 => 34, 83 => 35, 84 => 36];
//                        $array7 = [27 => 1, 28 => 2, 29 => 3, 30 => 4, 31 => 5, 32 => 6];
                        $arrayAnswer = explode(',', $value->survey_result_answer_id);
                        $count = count($arrayAnswer);
                        if ($count == 2 && $arrayAnswer[1] == '') {
                            array_pop($arrayAnswer);
                        }
                        $arrayAnswerConvert = array();
                        foreach ($arrayAnswer as $key => $value2) {
                            if (isset($arrayMap[$value2])) {
                                $arrayAnswerConvert[$arrayMap[$value2]] = $value2;
//                                }
                            }
                        }
                    } else {

                        $arrayAnswerConvert = [1 => $value->survey_result_answer_id];
                    }
                    $arrayResult['answer' . $value->survey_result_question_id] = $arrayAnswerConvert;
                } else {
                    $arrayResult["answer" . $value->survey_result_question_id] = $value->survey_result_answer_id;
                }
                $arrayResult["subnote" . $value->survey_result_question_id] = $value->survey_result_note;
                $arrayResult["extraQuestion" . $value->survey_result_question_id] = $value->survey_result_answer_extra_id;
                if (in_array($value->survey_result_question_id, [10, 11, 12, 13, 20, 21, 41, 42, 46, 47])) {
                    if ($value->survey_result_answer_id == 1 || $value->survey_result_answer_id == 2) {
                        $arrayResult["extraError" . $value->survey_result_question_id] = $value->survey_result_answer_extra_id;
                        $arrayResult["extraAction" . $value->survey_result_question_id] = $value->survey_result_action;
                        unset($arrayResult["extraQuestion" . $value->survey_result_question_id]);
                    } else if ($value->survey_result_answer_id == -1) {
                        $arrayResult["extraQuestion" . $value->survey_result_question_id] = $value->survey_result_answer_extra_id;
                    }
                }
            }

//            $result['ques_ans'] = $arrayResult;
        }
        //Lấy thêm thông tin liên hệ ở contact_profile nếu có
        $resultContact = ContactProfile::where(['contact_phone' => $modelSurvey->section_contact_phone, 'account_id' => $modelSurvey->section_account_id])->first();
        $infoContact = ['phone' => $modelSurvey->section_contact_phone, 'name' => $resultContact['contact_name'], 'relationship' => $resultContact['contact_relationship']];
        $result['infoContact'] = $infoContact;
        //
        $arrayResult["note"] = $modelSurvey->section_note;
        $arrayResult["action"] = $modelSurvey->section_action;
        $arrayResult["connected"] = $modelSurvey->section_connected;
        $result['section_survey_id'] = $modelSurvey->section_survey_id;
        $result['ques_ans'] = $arrayResult;
        $result['staffName'] = ($modelSurvey->section_user_modified == NULL) ? $modelSurvey->section_user_name : $modelSurvey->section_user_modified;
        $result['dateModified'] = ($modelSurvey->section_date_modified == NULL) ? '' : $modelSurvey->section_date_modified;
        $result['section_time_start'] = date('Y-m-d H:i:s');
        $result['linkInfo'] = array('contractNumber' => $account->so_hd, 'sectionCode' => $sectionCode, 'surveyId' => $surveyId);
        //Kiểm tra khảo sát có các câu NPS
//        if($resultCheck->section_survey_id==1){
        if ($modelSurvey->section_connected == 4) {
            //Có chọn NPS
            if (isset($arrayResult['answer24']) || isset($arrayResult['answer8']) || isset($arrayResult['answer6']) || isset($arrayResult['answer39']) || isset($arrayResult['answer45'])) {
                $result['NPS'] = false;
            } else {
//                $result['NPS'] = true;
                $result['NPS'] = true;
            }
        } else {
//            $hasNPS = $surveyResult->checkIsEva($request->surveyID, $resultCheck->section_survey_id,$modelSurvey->section_connected);
            $hasNPS = $surveyResult->checkIsEva($modelSurvey->section_account_id);
            $result['NPS'] = $hasNPS;
        }
        return $result;
    }

    /*
      lấy danh sách các chương trình khảo sát đang tốn tại
     */

    public function showListSurveys() {
        return Surveys::all();
    }

    /*
      xử lý dự liệu hoàn thành survey
     */

    public function complete(Request $request) {
        if (!isset($request->codes) || $request->codes == null)
            $error[0] = 'SectionCode';
        if (!isset($request->surveyid) || $request->surveyid == null)
            $error[1] = 'surveyid';
        if (!isset($request->contractNum) || $request->contractNum == null)
            $error[2] = 'contractNum';
//        if (isset($request->dataaccount['LocationID']) && in_array($request->dataaccount['LocationID'], [4, 8])) {
//            if ((!isset($request->dataaccount['BranchCode']) || $request->dataaccount['BranchCode'] == null) && (!isset($request->dataaccount['BranchCodeSale']) || $request->dataaccount['BranchCodeSale'] == null))
//                $error[3] = 'BranchCode-BranchCodeSale';
//        }
//        else if (!isset($request->dataaccount['LocationID'])) {
//            $error[4] = 'LocationID';
//        }
        //Dữ liệu khảo sát bị thiếu 
        if (!empty($error)) {
            $result = array('code' => 200, 'msg' => 'Dữ liệu gửi qua bị thiếu các trường ' . implode(',', $error));
            return Json::encode($result);
        }
        $surveySec = new SurveySections();
        $Surveys = new Surveys();
        $send = false;
        if ($request->isMethod('post') && isset($request->datapost) && isset($request->dataaccount)) {
            $uniqueCase = $request->contractNum . $request->surveyid . $request->codes;
            //Kiểm tra có tạo thành công các CL, PCL
//            $resultAction = $this->checkActionSurvey($request->input(), $uniqueCase);
//            if ($resultAction[0] == false) {
//                $result = array('code' => 900, 'msg' => $resultAction[1]);
//                return Json::encode($result);
//            }
            $resultCodes = $surveySec->checkExistCodes($request->codes, $request->surveyid, $request->contractNum);
            //Nếu tồn tại khảo sát này rồi
            if (!empty($resultCodes)) {
                $result = array('code' => 400, 'msg' => 'Dữ liệu khảo sát này đã tồn tại rồi, vui lòng chỉnh sửa hoặc khảo sát lại');
                return Json::encode($result);
            }
            /*
             * kiểm tra số hợp đồng đã có trong database survey hay chưa
             * nếu chưa có insert mới vào  trả về id khách hàng 
             * nếu có thì trả ra id
             */
            $dataAccount = $request->dataaccount;
            if (!isset($dataAccount['ContractNum'])) {
                $this->setmsg('Không tìm thấy mã hợp đồng');
                return $this->resultSurvey();
            }
            $OutboundAccount = new OutboundAccount();
            $accountInfo = $OutboundAccount->getAccountInfoByContract($dataAccount['ContractNum']);
            $datapost = $request->datapost;
            $surveyID = $request->surveyid;
            /*
             * tạo survey sections
             */
            // cần tạo try catch
            $user = Auth::user();
            //Đổ dữ liệu vào model survey
            $surveySections = $this->assignData($request, $datapost, $dataAccount, $user, $accountInfo, $surveyID);
            $type = $surveySections[1];
            $brandCodeOrigin = $surveySections[2];
            $surveySections = $surveySections[0];
            /* lấy dữ liệu của survey
             * nếu không có dự liệu thì trả về kết quả không tìm thấy
             */
            //Tao Transaction
            DB::beginTransaction();
            try {
                $surveySectionID = $Surveys->saveSurveySections($surveySections);
                //Case khảo sát bị lỗi dữ liệu
                if (!empty($type)) {
                    $caseInvalidInfo = new ListInvalidSurveyCase();
                    $caseInvalidInfo->section_id = $surveySectionID;
                    $caseInvalidInfo->contract_number = $surveySections['section_contract_num'];
                    $caseInvalidInfo->section_code = $surveySections['section_code'];
                    $caseInvalidInfo->survey_id = $surveySections['section_survey_id'];
                    $caseInvalidInfo->sub_parent_desc = $surveySections['section_sub_parent_desc'];
                    $caseInvalidInfo->branch_code = $brandCodeOrigin;
                    $caseInvalidInfo->location_id = $surveySections['section_location_id'];
                    $caseInvalidInfo->support = $surveySections['section_supporter'];
                    $caseInvalidInfo->sub_support = $surveySections['section_subsupporter'];
                    $caseInvalidInfo->user_name = $user;
                    $caseInvalidInfo->type_error = implode(",", $type);
                    $caseInvalidInfo->save();
                }
                //Có tạo checklist
                if (isset($request->listCl)) {
                    $modelCL = new CheckList();
                    foreach ($request->listCl as $key => $value) {
                        $modelCL = CheckList::find($value);
                        $modelCL->section_id = $surveySectionID;
                        $modelCL->save();
                    }
                }
                //Có tạo Prechecklist
                if (isset($request->listPCl)) {
                    $modelPCL = new PrecheckList();
                    foreach ($request->listPCl as $key => $value) {
                        $modelPCL = PrecheckList::find($value);
                        $modelPCL->section_id = $surveySectionID;
                        $modelPCL->save();
                    }
                }
                //Có tạo Prechecklist
                if (isset($request->listFWD)) {
                    $modelFWD = new FowardDepartment();
                    foreach ($request->listFWD as $key => $value) {
                        $modelFWD = FowardDepartment::find($value);
                        $modelFWD->section_id = $surveySectionID;
                        $modelFWD->save();
                    }
                }
                if ($datapost['connected'] == '4') {
                    // Lưu thông tin liên hệ vào contact_profile
                    $info = $request->dataContact;
                    $modelOutboundAccount = new OutboundAccount();
                    $accountInfo = $modelOutboundAccount->getAccountInfoByContract($request->contractNum);
//            $info[0]['accountID'] = !empty($accountInfo) ? $accountInfo->id : 0; //tạm thời = 0 nếu ko tìm ra được record trong account
                    $accountID = $accountInfo->id;
                    $userCreated = Auth::user()->id;
                    $userCreatedName = Auth::user()->name;
                    $modelContactProfile = new ContactProfile();
                    $response = $modelContactProfile->saveContactProfile($info, $accountID, $userCreated, $userCreatedName, $request->contractNum);
                }

                /*
                 * Nếu khách hàng đồng ý trả lời và chọn 1 nội dung khảo sát
                 * Lấy danh sách các câu hỏi của surveys
                 */

                if ($surveySections['section_connected'] == 4) { // 4 đồng ý khảo sát
                    //Nếu khảo sát thành công
//                 if ($request->cases != 0) {
                    //Lấy danh sách câu hỏi để validate ứng với từng trường hợp
                    $listValidateQues = $this->getValidateCase($request->cases);
                    //Lấy kết quả validate
                    $resultValidate = $this->checkValidate($datapost, $listValidateQues);

                    //Không trả lời đủ các câu hỏi khảo sát
                    if (is_string($resultValidate)) {
                        DB::rollback();
                        $result = array('code' => 700, 'msg' => 'Anh/Chị chưa chọn hoặc chọn rồi nhưng thiếu câu trả lời ở các câu khảo sát sau: ' . $resultValidate);
//				$result = array('code' => $e->getCode(), 'msg' => $e->getMessage());
                        return Json::encode($result);
                    }
//                 }
                    $SurveyResult = new SurveyResult();
                    //tạo mảng chứa câu hỏi và câu trả lời
                    $arrQuestion = $arrAnswer = array();
                    $QuestionList = $Surveys->getQuestionBySurvey($surveyID);
                    foreach ($QuestionList as $question) {
                        /*
                         * $answerId = '-1'; // mã cấu trả lời ' Chưa trả lời'
                         * Khi câu hỏi không được chọn câu trả lời sẽ mặc định lấy là chưa trả lời 
                         */
//                    $answerId = '-1';
                        $answerPost = 'answer' . $question->question_id;
                        $ansernotePost = 'subnote' . $question->question_id;
                        $ansExtra = 'extraQuestion' . $question->question_id;
                        $ansernote = '';
                        $anserextraActionId = NULL;
                        if (isset($datapost[$answerPost]) || isset($datapost[$ansernotePost]) || isset($datapost[$ansExtra])) { {
                                if (isset($datapost[$answerPost])) {
                                    $answerId = $datapost[$answerPost];
                                    if ($question->question_id == 5 || $question->question_id == 7 || $question->question_id == 25 || $question->question_id == 40 || $question->question_id == 44) {
                                        if (!is_array($answerId)) {
                                            $answerId = -1;
                                        }
                                    }
                                    if (is_array($answerId) && count($answerId) > 0) {
                                        $array = array();
                                        foreach ($answerId as $key => $value) {
                                            if ($value != false) {
                                                array_push($array, $value);
                                            }
                                        }
                                        $answerId = implode(",", $array);
                                    } else if ($answerId == false) {
                                        $answerId = -1;
                                    }
                                } else {
                                    $answerId = -1;
                                }

                                if (isset($datapost[$ansernotePost])) {
                                    $ansernote = $datapost[$ansernotePost];
                                } else {
                                    $ansernote = '';
                                }
                                // kiểm tra question có câu hỏi phụ không
                                // nếu có thì phải lưu thông tin
                                $anserextraQuestionId = NULL;
                                if (!empty($question->question_answer_group_extra_id) && $question->question_answer_group_extra_id > 0) {

                                    $anserextraQuestion = 'extraQuestion' . $question->question_id;

                                    if (isset($datapost[$anserextraQuestion]) && $datapost[$anserextraQuestion] != 0 && $answerId == -1) {
                                        $anserextraQuestionId = $datapost[$anserextraQuestion];
                                    }
                                }
                                //Vừa chấm điểm vừa chọn lý do 
                                if ($anserextraQuestionId != NULL) {
                                    if ($answerId != -1)
                                        $anserextraQuestionId = NULL;
                                }
                                //Kiểm tra lấy id xử lý lỗi và hướng xử lý
                                if (in_array($question->question_id, [10, 11, 12, 13, 20, 21, 41, 42, 46, 47])) {
                                    //Chọn câu trả lời
                                    if ($answerId != -1) {
                                        if ($answerId == 1 || $answerId == 2) {
                                            $anserextraQuestionId = $datapost['extraError' . $question->question_id];
                                            $anserextraActionId = $datapost['extraAction' . $question->question_id];
                                        }
                                    }
                                }
                                $anserextraActionId = isset($anserextraActionId) ? $anserextraActionId : NULL;
                                $SurveyResultItem = array(
                                    "survey_result_section_id" => $surveySectionID,
                                    "survey_result_question_id" => $question->question_id,
                                    "survey_result_answer_id" => $answerId,
                                    "survey_result_note" => $ansernote,
                                    "survey_result_answer_extra_id" => $anserextraQuestionId,
                                    "survey_result_action" => $anserextraActionId
                                );

                                $SurveyResult->saveSurveyResult($SurveyResultItem);
                                //array_push($arrQuestion,$question);
                            }
                        }
                    }
                    $send = true;
                }
                //Số điện thoại gọi điển khảo sát là mới
                $phoneContact = isset($request->phoneContact) ? $request->phoneContact : null;
                $newPhone = $this->checkNewPhone($request->contractNum, $phoneContact);
//                if ($newPhone) {
//                    $job = (new NotifyNewPhoneNumberEmail($request->dataContact, date('Y-m-d H:i:s'), $request->surveyid, $request->contractNum))->onQueue('newPhone')->delay(60);
//                    $this->dispatch($job);
//                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();

                $result = array('code' => 600, 'msg' => 'Qúa trình tạo khảo sát bị lỗi. Vui lòng khảo sát lại ');
                $result = array('code' => $e->getCode(), 'msg' => $e->getMessage());
                return Json::encode($result);
            }

            if ($send) {
                $apiHelp = new ApiHelper();
                $paramCheck['sectionId'] = $surveySectionID;
                $resCheck = $apiHelp->checkSendMail($paramCheck);
                if ($resCheck['status']) {
                    Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
                }
            }
            //Xét xong rồi xóa
            Redis::del($uniqueCase);
            $result = array('code' => 200, 'msg' => 'Tạo khảo sát thành công', 'ids' => $surveySectionID, 'shd' => $dataAccount['ContractNum']);
            return Json::encode($result);
        }
    }

    /*
      update dữ liệu survey
     */

    public function update(Request $request) {
        DB::beginTransaction();
        $send = false;
        try {
            $SurveyResult = new SurveyResult();
            $modelSurvey = SurveySections::find($request->idS);
            //Trạng thái gốc
            $section_con = $modelSurvey->section_connected;
            //Retry
            if ($section_con != 4) {
                $linkInfo = $request->linkInfo;
                $uniqueCase = $linkInfo['contractNumber'] . $linkInfo['surveyId'] . $linkInfo['sectionCode'];
                //Kiểm tra có tạo thành công các CL, PCL
//                $resultAction = $this->checkActionSurvey($request->input(), $uniqueCase);
//                if ($resultAction[0] == false) {
//                    $result = array('code' => 900, 'msg' => $resultAction[1]);
//                    return Json::encode($result);
//                }
                //Số điện thoại gọi điển khảo sát là mới
                $phoneContact = isset($request->phoneContact) ? $request->phoneContact : null;
                $newPhone = $this->checkNewPhone($request->contractNum, $phoneContact);
//                if ($newPhone) {
//                    $job = (new NotifyNewPhoneNumberEmail($request->dataContact, date('Y-m-d H:i:s'), $request->surveyid, $request->contractNum))->onQueue('newPhone')->delay(60);
//                    $this->dispatch($job);
//                }
                $modelSurvey->section_contact_phone = $phoneContact;
                $modelSurvey->section_time_completed = date('Y-m-d H:i:s');
                $modelSurvey->section_time_completed_int = time();
                $modelSurvey->section_time_start = $request->time_start;
                // nếu trạng thái trước là không khảo sát được mà trạng thái sau là gặp người sử dụng và lấy được thông tin khảo sát thì cập nhật
                //người tạo khảo sát là người gặp được khách hàng
                if ($request->input()['datapost']['connected'] == '4') {
                    $modelSurvey->section_user_name = Auth::user()->name;
                    $modelSurvey->section_user_id = Auth::user()->id;
                }
                //Khởi tạo bằng 0 để biết ko cần thêm ngày update vào redis
                $reUpdateTimeSummary = 0;
            } else {
                $dateComplete = date('Y-m-d', strtotime($modelSurvey->section_time_completed));
                //Ngày hoàn thành khảo sát ko cùng ngày với update
                if ($dateComplete != date('Y-m-d')) {
                    $reUpdateTimeSummary = $dateComplete;
                } else {
                    $reUpdateTimeSummary = 0;
                }
            }
            $modelSurvey->section_connected = $request->datapost['connected'];
            $modelSurvey->section_note = $request->datapost['note'];
            $modelSurvey->section_action = $request->datapost['action'];
            $modelSurvey->section_user_modified = Auth::user()->name;
            $modelSurvey->section_date_modified = date('Y-m-d H:i:s');
            $modelSurvey->ip_client = $request->ip();

//            if ($modelSurvey->section_connected == 1 || $modelSurvey->section_connected == 3) {
            $modelSurvey->section_count_connected = $modelSurvey->section_count_connected + 1;
//            }
            if ($modelSurvey->save()) {
                //Gap nguoi su dung thi luu
                if ($request->input()['datapost']['connected'] == '4') {
                    // Lưu thông tin liên hệ vào contact_profile
                    $info = $request->dataContact;
                    $modelOutboundAccount = new OutboundAccount();
                    $accountInfo = $modelOutboundAccount->getAccountInfoByContract($request->contractNum);
//            $info[0]['accountID'] = !empty($accountInfo) ? $accountInfo->id : 0; //tạm thời = 0 nếu ko tìm ra được record trong account
                    $accountID = $accountInfo->id;
                    $userCreated = Auth::user()->id;
                    $userCreatedName = Auth::user()->name;
                    $modelContactProfile = new ContactProfile();
                    $response = $modelContactProfile->saveContactProfile($info, $accountID, $userCreated, $userCreatedName, $request->contractNum);
                }
                //Gặp được khách hàng
                if ($request->cases != 0) {
                    $listValidateQues = $this->getValidateCase($request->cases);
                    //Lấy kết quả validate
                    $resultValidate = $this->checkValidate($request->datapost, $listValidateQues);
                    //Không trả lời đủ các câu hỏi khảo sát
                    if (is_string($resultValidate)) {
                        DB::rollback();
                        $result = array('code' => 700, 'msg' => 'Anh/Chị chưa chọn hoặc chọn rồi nhưng thiếu câu trả lời ở các câu khảo sát sau: ' . $resultValidate);
//				$result = array('code' => $e->getCode(), 'msg' => $e->getMessage());
                        return Json::encode($result);
                    }
//                    $resultBeforeUpdate=DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $request->idS)->get();
//                    DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $request->idS)->delete();
                    $SurveyResult->deleteSurveyResult($request->idS);
                    if ($SurveyResult->updateDetailSurvey($request->idS, $request->datapost, $request->surveyid, $request->arrayAnswer, $section_con) != '') {
                        $send = true;
                    }
                }
                if ($section_con != 4) {
                    //Xét xong rồi xóa
                    Redis::del($uniqueCase);
                }
                DB::commit();
                //Số điện thoại gọi điển khảo sát là mới
                if (isset($request->newNumberPhone)) {
                    $job = (new NotifyNewPhoneNumberEmail($request->dataContact, date('Y-m-d H:i:s'), $request->surveyid, $request->contractNum))->onQueue('newPhone')->delay(60);
                    $this->dispatch($job);
                }
                //Có ngày cần update các bảng summary lại
                if ($reUpdateTimeSummary != 0) {
                    Redis::rpush('day_update_summary', $reUpdateTimeSummary);
                }
            }
        } catch (Exception $e) {
            DB::rollback();
            $result = array('code' => 300, 'msg' => $e->getMessage()
            );
            return Json::encode($result);
        }

        $result = array('code' => 200, 'msg' => 'Cập nhập thành công');
        if ($send) {
            $apiHelp = new ApiHelper();
            $paramCheck['sectionId'] = $request->idS;
            $resCheck = $apiHelp->checkSendMail($paramCheck);
            if ($resCheck['status']) {
                Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
            }
        }

        return Json::encode($result);
    }

    private function setmsg($msg = '') {
        $this->msg[] = $msg;
    }

    private function setdata($data = '') {
        $this->data[] = $data;
    }

    private function resultSurvey() {
        if (count($this->data) == 1) {
            $this->data = $this->data[0];
        }
        if (count($this->msg) == 1) {
            $this->msg = $this->msg[0];
        }

        $result = array('code' => $this->code,
            "msg" => $this->msg,
            "data" => $this->data
        );
        echo Json::encode($result);
    }

    protected function viewSurvey($so_hd, $sectionCode, $surveyID) {
        $infoAcc = array('ObjID' => '',
            'Contract' => $so_hd,
            'ID' => $sectionCode,
            'Type' => $surveyID
        );
        /*
         * Lấy thông tin khách hàng
         */
        $apiIsc = new Apiisc();
//        $responseAccountInfo = $apiIsc->GetFullAccountInfo($infoAcc);
//        dump($responseAccountInfo);die;
        $outboundAccount = new OutboundAccount();
        $responseAccountInfo = $outboundAccount->getAccountInfoByContractZero($so_hd);
//        dump($responseAccountInfo);die;

        // end lấy thông tin khách hàng
        // nếu không lấy được thông tin khách hàng return false
        $responseAccountInfo = json_decode($responseAccountInfo);
        $responseInfo['orginal_dataCus'] = $responseAccountInfo;
        if (!isset($responseAccountInfo[0]->ObjID)) {
            $result['code'] = 400; //không có dữ liệu
            $result['msg'] = 'Bad Request';
            return $result;
        }
        $helpProvider = new HelpProvider();
        $responseAccountInfo[0] = $helpProvider->processDataFromISC($responseAccountInfo[0]);
        $startDate = $responseAccountInfo[0]->ContractDate;
        $responseAccountInfo[0]->ContractDate = $startDate; //date("d-m-Y", $startDate );
        $responseInfo['data_cusinfo'] = $responseAccountInfo;
        $responseInfo['msg'] = 'Success';
        $responseInfo['code'] = 200;
        // nếu có thông tin khách hàng thì lấy thông tin lịch sử hỗ trợ
        //$ObjID = 1020104442;
        $ObjID = $responseAccountInfo[0]->ObjID;

        $paramHistory = array('ObjID' => $ObjID, 'RecordCount' => 3);
        $historyData = json_decode($apiIsc->getCallerHistoryByObjID($paramHistory));
        if (count($historyData) > 0) {
            for ($i = 0; $i < count($historyData); $i++) {
                if (isset($historyData[$i]->StartDate)) {
                    $startDate = $historyData[$i]->StartDate;
                    $startDate = preg_replace('/[^0-9]/', '', $startDate);
                    $startDate = $startDate / 1000;
                    $historyData[$i]->StartDate = date("d-m-Y", $startDate);
                }
                if (isset($historyData[$i]->EndDate)) {

                    $endDate = $historyData[$i]->EndDate;
                    $endDate = preg_replace('/[^0-9]/', '', $endDate);
                    $endDate = $endDate / 1000;
                    $historyData[$i]->EndDate = date("d-m-Y", $endDate);
                }
            }
        }
        $responseInfo['data_history'] = $historyData;
        /*
         * kiểm tra thông tin khách hàng đã lưu trên Mo hay chưa
         * Nếu lưu rồi lấy thông tin lịch sử survey
         */

        $OutboundAccount = new OutboundAccount();
        $responseInfo['accountInfoFromSurvey'] = $OutboundAccount->getAccountInfoByContract($infoAcc['Contract']);
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
//         var_dump($responseInfo);die;
        return $responseInfo;
    }

    protected function isJSON($string) {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /*
      Lưu, cập nhập thông tin người liên hệ
     */

    public function addContact(Request $request) {
        if ($request->isMethod('post') && isset($request->dataContact)) {
//            var_dump($request->input());die;
            $info = $request->dataContact;
            $modelOutboundAccount = new OutboundAccount();
            $accountInfo = $modelOutboundAccount->getAccountInfoByContract($request->contractNum);
//            $info[0]['accountID'] = !empty($accountInfo) ? $accountInfo->id : 0; //tạm thời = 0 nếu ko tìm ra được record trong account
            $accountID = $accountInfo->id;
            $userCreated = Auth::user()->id;
            $userCreatedName = Auth::user()->name;
            $modelContactProfile = new ContactProfile();
            $response = $modelContactProfile->saveContactProfile($info, $accountID, $userCreated, $userCreatedName, $request->contractNum);
            if ($response['code'] == 200) {
                $result = array('code' => 200, 'msg' => 'Cập nhập liên hệ thành công');
            } else {
                $result = array('code' => 200, 'msg' => 'Cập nhập liên hệ bị lỗi. Vui lòng thử lại.');
            }
            return Json::encode($result);
//            return json_encode(['code' => $response['code']]);
        }
    }

    /*
      Lấy thông tin người liên hệ
     */

    public function getContact(Request $request) {
        if ($request->isMethod('post') && isset($request->contract)) {
            $contract = $request->contract;
            $modelOutboundAccount = new OutboundAccount();
            $accountInfo = $modelOutboundAccount->getAccountInfoByContract($contract);
            $modelContactProfile = new ContactProfile();
            $response['data'] = $modelContactProfile->getContactByID($accountInfo->id);
            if (!empty($response['data'])) {
                $response['code'] = 200;
            } else {
                $response['code'] = 400;
            }
            return json_encode(['code' => $response['code'], 'data' => $response['data']]);
        }
    }

    /*
      Lấy case tương ứng để validate
     */

    public function getValidateCase($caseCode) {
        $sauTrienKhai = [101 => [1, 2, 6, 10], 102 => [1, 2, 6, 11], 103 => [1, 2, 6, 11, 10], 104 => [1, 2, 10], 105 => [1, 2, 11], 106 => [1, 2, 11, 10]];
        $sauBaoTri = [201 => [4, 12, 8], 202 => [4, 13, 8], 203 => [4, 12, 13, 8], 204 => [4, 12], 205 => [4, 13], 206 => [4, 12, 13]];
        $sauTelesale = [601 => [20, 22, 23, 24], 602 => [21, 22, 23, 24], 603 => [20, 21, 22, 23, 24], 604 => [20, 22, 23], 605 => [21, 22, 23], 606 => [20, 21, 22, 23]];
        $sauGDTQ = [901 => [37, 38, 39, 41], 902 => [42, 37, 38, 39], 903 => [37, 38, 39, 41, 42], 904 => [37, 38, 41], 905 => [37, 38, 42], 906 => [37, 38, 41, 42]];
        $sauSwap = [1001 => [43, 45, 46], 1002 => [43, 45, 47], 1003 => [43, 45, 46, 47], 1004 => [43, 46], 1005 => [43, 47], 1006 => [43, 46, 47]];

        if (isset($sauTrienKhai[$caseCode]))
            return $sauTrienKhai[$caseCode];
        else if (isset($sauBaoTri[$caseCode]))
            return $sauBaoTri[$caseCode];
        else if (isset($sauTelesale[$caseCode]))
            return $sauTelesale[$caseCode];
        else if (isset($sauGDTQ[$caseCode]))
            return $sauGDTQ[$caseCode];
        else
            return $sauSwap[$caseCode];
    }

    /*
      Validate case tương ứng
     */

    public function checkValidate($dataPost, $listQues) {
        $check = true;
        $arrayInvalid = [];
        foreach ($listQues as $key => $value) {
            $tempAns = 'answer' . $value;
            $tempNote = 'subnote' . $value;
            $tempExtraQues = 'extraQuestion' . $value;
            $tempExtraAct = 'extraAction' . $value;
            $tempExtraErr = 'extraError' . $value;
            $QuesValidate = [10, 11, 12, 13, 20, 21, 41, 42, 46, 47];

            //Không chấm điểm, không chọn lý do
            if (((!isset($dataPost[$tempAns]) || in_array($dataPost[$tempAns], [false, -1])) && (!isset($dataPost[$tempExtraQues]) || in_array($dataPost[$tempExtraQues], [false, -1])))
                    //chọn 1,2,5 và không note
                    || (isset($dataPost[$tempAns]) && (in_array($dataPost[$tempAns], [1, 2, 5])) && (!isset($dataPost[$tempNote]) || $dataPost[$tempNote] == ''))
                    //chọn 1,2 dịch vụ nhưng ko nêu loại lỗi hoặc hành động xử lý
                    || (in_array($value, $QuesValidate) && (isset($dataPost[$tempAns]) && in_array($dataPost[$tempAns], [1, 2])) && ((!isset($dataPost[$tempExtraAct])) || (!isset($dataPost[$tempExtraErr]))))) {
                array_push($arrayInvalid, $value);
                $check = false;
            }
//            }
        }
        if ($check == true)
            return true;
        else {
            $arrayConvert = [];
            $arrayQuesMap = [1 => '3', 2 => '2', 4 => '2', 5 => '4', 6 => '4', 7 => '5', 8 => '3', 10 => '1.a', 11 => '1.b', 12 => '1.a', 13 => '1.b',
                20 => '1.a', 21 => '1.b', 22 => '2', 23 => '3', 24 => '4', 25 => '5', 37 => '3', 38 => '2', 39 => '4', 40 => '5', 41 => '1.a', 42 => '1.b',
                43 => '2', 44 => '4', 45 => '3', 46 => '1.a', 47 => '1.b'];
            foreach ($arrayInvalid as $key => $value) {
                array_push($arrayConvert, $arrayQuesMap[$value]);
            }
            return implode(',', $arrayConvert);
        }
    }

    public function getContactByPhone(Request $request) {
        $account = OutboundAccount::where('contract_num', $request->contractNum)->first();
        $result = ContactProfile::where(['contact_phone' => $request->phone, 'account_id' => $account['id']])->first();
        $arrayContactInfo = [];
        $arrayContactInfo['name'] = $result['contact_name'];
        $arrayContactInfo['phone'] = $result['contact_phone'];
        $arrayContactInfo['relationship'] = $result['contact_relationship'];
        return json_encode(['code' => 200, 'contactInfo' => $arrayContactInfo]);
    }

    /*
      Gán dữ liệu
     */

    public function assignData($datapost, $dataAccount, $user) {
        $dateTime = date('Y-m-d H:i:s');
        $surveySections['section_connected'] = isset($datapost['connected']) ? $datapost['connected'] : "";
        $surveySections['section_count_connected'] = 1;
        $surveySections['section_note'] = isset($datapost['note']) ? $datapost['note'] : "";
        $surveySections['section_action'] = isset($datapost['action']) ? $datapost['action'] : "1"; // 1 không làm gì
        $surveySections['section_contact'] = isset($datapost['contactName']) ? $datapost['contactName'] : "";
        $surveySections['section_account_id'] = $dataAccount['id'];
        $surveySections['section_user_id'] = isset($user) ? $user->id : '-1';
        $surveySections['section_user_name'] = isset($user) ? $user->name : NULL;
        $surveySections['section_time_completed'] = $dateTime;
        $surveySections['section_time_completed_int'] = strtotime($dateTime);
        $surveySections['section_time_start'] = $datapost['timeStart'];

        $surveySections['section_location_id'] = isset($dataAccount['LocationID']) ? $dataAccount['LocationID'] : "";
        $surveySections['section_location'] = isset($dataAccount['Location']) ? $dataAccount['Location'] : "";
        $surveySections['section_sub_parent_desc'] = isset($dataAccount['SubParentDesc']) ? $dataAccount['SubParentDesc'] : "";


        $surveySections['section_region'] = isset($dataAccount['Region']) ? $dataAccount['Region'] : "";
        $surveySections['section_branch_code'] = isset($dataAccount['BranchCode']) ? $dataAccount['BranchCode'] : null;
        $surveySections['section_sale_branch_code'] = isset($dataAccount['BranchCodeSale']) ? $dataAccount['BranchCodeSale'] : null;
        $surveySections['section_contact_person'] = isset($dataAccount['contactPerson']) ? $dataAccount['contactPerson'] : "";
        $surveySections['section_center_list'] = isset($dataAccount['CenterList']) ? $dataAccount['CenterList'] : NULL;
        $surveySections['section_account_inf'] = isset($dataAccount['AccountINF']) ? $dataAccount['AccountINF'] : NULL;
        $surveySections['section_acc_sale'] = isset($dataAccount['AccountSale']) ? $dataAccount['AccountSale'] : NULL;
        $surveySections['section_account_list'] = isset($dataAccount['AccountList']) ? $dataAccount['AccountList'] : NULL;
        $surveySections['section_account_payment'] = isset($dataAccount['AccountPayment']) ? $dataAccount['AccountPayment'] : NULL;

        $surveySections['section_objAddress'] = isset($dataAccount['ObjAddress']) ? $dataAccount['ObjAddress'] : NULL;
        $surveySections['section_legal_entity_name'] = isset($dataAccount['LegalEntityName']) ? $dataAccount['LegalEntityName'] : NULL;
        $surveySections['section_partner_name'] = isset($dataAccount['PartnerName']) ? $dataAccount['PartnerName'] : NULL;
        $surveySections['section_fee_local_type'] = isset($dataAccount['FeeLocalType']) ? $dataAccount['FeeLocalType'] : NULL;
        $surveySections['section_description'] = isset($dataAccount['Description']) ? $dataAccount['Description'] : NULL;
        $surveySections['section_package_sal'] = isset($dataAccount['PackageSal']) ? $dataAccount['PackageSal'] : NULL;
        $surveySections['section_finish_date_inf'] = isset($dataAccount['FinishDateINF']) ? $dataAccount['FinishDateINF'] : NULL;
        $surveySections['section_finish_date_list'] = isset($dataAccount['FinishDateList']) ? $dataAccount['FinishDateList'] : NULL;
        $surveySections['section_phone'] = isset($dataAccount['Phone']) ? $dataAccount['Phone'] : NULL;
        $surveySections['section_payment_type'] = isset($dataAccount['PaymentType']) ? $dataAccount['PaymentType'] : NULL;
        $surveySections['section_use_service'] = isset($dataAccount['UseService']) ? $dataAccount['UseService'] : NULL;
        $surveySections['section_email_inf'] = isset($dataAccount['EmailINF']) ? $dataAccount['EmailINF'] : NULL;
        $surveySections['section_email_list'] = isset($dataAccount['EmailList']) ? $dataAccount['EmailList'] : NULL;
        $surveySections['section_email_sale'] = isset($dataAccount['EmailSale']) ? $dataAccount['EmailSale'] : NULL;
        $surveySections['section_kind_deploy'] = isset($dataAccount['KindDeploy']) ? $dataAccount['KindDeploy'] : NULL;
        $surveySections['section_kind_main'] = isset($dataAccount['KindMain']) ? $dataAccount['KindMain'] : NULL;

        $surveySections['section_code'] = isset($datapost['codedm']) ? $datapost['codedm'] : NULL;
        $surveySections['section_contract_num'] = isset($dataAccount['ContractNum']) ? $dataAccount['ContractNum'] : NULL;
        $surveySections['section_survey_id'] = isset($datapost['typeSurvey']) ? $datapost['typeSurvey'] : NULL;
        $surveySections['section_customer_name'] = isset($dataAccount['CustomerName']) ? $dataAccount['CustomerName'] : NULL;
        $surveySections['section_company_name'] = isset($dataAccount['CompanyName']) ? $dataAccount['CompanyName'] : NULL;
        $surveySections['section_supporter'] = isset($dataAccount['Supporter']) ? $dataAccount['Supporter'] : NULL;
        $surveySections['section_subsupporter'] = isset($dataAccount['SubSupporter']) ? $dataAccount['SubSupporter'] : NULL;
        $surveySections['section_contact_phone'] = isset($datapost['contactPhone']) ? $datapost['contactPhone'] : NULL;
        $surveySections['ip_client'] = $datapost['ip'];
        $surveySections['sale_center_id'] = isset($dataAccount['CenterID']) ? $dataAccount['CenterID'] : NULL;
//        $type = [];
//        if ((!isset($dataAccount['Supporter']) || !isset($dataAccount['SubSupporter'])) || (empty($dataAccount['Supporter']) || empty($dataAccount['SubSupporter'])))
//            array_push($type, 1);
//        if (!isset($dataAccount['LocationID']) || !isset($dataAccount['BranchCode'])) {
//            array_push($type, 2);
//        } else if (isset($request->surveyid) && $request->surveyid != 6) {
//            if ((!in_array($dataAccount['LocationID'], [4, 8, 31, 65]) || $dataAccount['LocationID'] == null ) && $dataAccount['BranchCode'] != 0)
//                array_push($type, 2);
//            if (in_array($dataAccount['LocationID'], [4, 8]) && ($dataAccount['BranchCode'] == 0 || $dataAccount['BranchCodeSale'] == null )) {
//                array_push($type, 2);
//            }
//            if ($dataAccount['LocationID'] == null)
//                array_push($type, 4);
//        }
//        if (empty($user))
//            array_push($type, 3);
//        $brandCodeOrigin = isset($dataAccount['BranchCode']) ? $dataAccount['BranchCode'] : null;
//        return [$surveySections, $type, $brandCodeOrigin];
        return $surveySections;
    }

    /*
      Kiểm tra số điện thoại mới
     */

    public function checkNewPhone($contractNum, $phoneCheck) {
        $account = OutboundAccount::where('contract_num', $contractNum)->first();
        $phone = $account->phone;
        $listPhone = explode(',', $phone);
        //Số mới
        if (!in_array($phoneCheck, $listPhone)) {
            array_push($listPhone, $phoneCheck);
            $account->phone = implode(',', $listPhone);
            $account->save();
            return true;
        } else
            return false;
    }

    /*
      Kiểm tra tính hợp lệ của hành động xử lý
     */

    private function checkActionSurvey($inputData, $uniqueCase) {
        $dataSurvey = $inputData['datapost'];
        $dataAccount = $inputData['dataaccount'];
//        $packageSal=($firstTime == true ? $dataAccount['PackageSal'] : $dataAccount['PackageSal']);
//        dump($dataAccount);die;
//        $packageSal = $dataAccount['PackageSal'];
        $hasTv = in_array($dataAccount['UseService'], [1, 3]) ? true : false;
        $hasNet = in_array($dataAccount['UseService'], [2, 3]) ? true : false;
        switch ($inputData['surveyid']) {
            case 1: {
                    $pass = $this->checkActionSurveyDeeper(10, 11, $hasNet, $hasTv, $uniqueCase, $dataSurvey);
                    break;
                }
            case 2: {

                    $pass = $this->checkActionSurveyDeeper(12, 13, $hasNet, $hasTv, $uniqueCase, $dataSurvey);
                    break;
                }
            case 6: {
                    $pass = $this->checkActionSurveyDeeper(20, 21, $hasNet, $hasTv, $uniqueCase, $dataSurvey);
                    break;
                }
            case 9: {
                    $pass = $this->checkActionSurveyDeeper(41, 42, $hasNet, $hasTv, $uniqueCase, $dataSurvey);
                    break;
                }
            case 10: {
                    $pass = $this->checkActionSurveyDeeper(46, 47, $hasNet, $hasTv, $uniqueCase, $dataSurvey);
                    break;
                }
        }

        return $pass;
    }

    /*
      Kiểm tra sâu hơn tính hợp lệ của hành động xử lý
     */

    private function checkActionSurveyDeeper($quesitonIdNet, $questionIdTv, $hasNet, $hasTv, $uniqueCase, $dataSurvey) {
//        $message ='';
        $message = [];
        $success = true;
        if (in_array($dataSurvey['action'], ['2', '3'])) {
            array_push($message, ($dataSurvey['action'] == '2' ? 'Vui lòng hoàn tất quá trình tạo Checklist' : 'Vui lòng hoàn tất quá trình tạo PreChecklist'));
//            $message.=$dataSurvey['action'] == '2' ? 'Vui lòng hoàn tất quá trình tạo Checklist' : 'Vui lòng hoàn tất quá trình tạo PreChecklist');
            $actionData = Redis::get($uniqueCase);
            //Không có dữ liệu CL, PCL
            if ($actionData == null) {
                $success = false;
            } else {
                $actionDataDecode = json_decode($actionData);
                //Chọn tạo CL
                if ($dataSurvey['action'] == '2') {
                    if (empty($actionDataDecode->listCL)) {
                        $success = false;
                    }
                }

                //Chọn tạo PCL
                if ($dataSurvey['action'] == '3') {
                    if (empty($actionDataDecode->listPCL)) {
                        $success = false;
                    }
                }
//                $DayUpdateData = json_decode($checklist_update_later);
//                $addListCLData = array_merge($DayUpdateData->listCL, $listCL_update_later);
//                Redis::set('checklist_update_later', json_encode(['time' => date('y-m-d'), 'listCL' => $addListCLData]));
            }
        }
//        dump($success,$message );
        if ($success == true) {
            $message = [];
            $successDeeper = true;
            $net = $hasNet && isset($dataSurvey['answer' . $quesitonIdNet]) && in_array($dataSurvey['answer' . $quesitonIdNet], ['1', '2']) && isset($dataSurvey['extraAction' . $quesitonIdNet]);
            $tv = $hasTv && isset($dataSurvey['answer' . $questionIdTv]) && in_array($dataSurvey['answer' . $questionIdTv], ['1', '2']) && isset($dataSurvey['extraAction' . $questionIdTv]);
            if ($net) {
                $resultNet = $this->checkActionCLPCL($quesitonIdNet, $uniqueCase, $successDeeper, $message, $dataSurvey);
//                dump($resultNet[1]);
//                   array_push($message,$resultNet[1]) ;
                $message = $resultNet[1];
//                $message.=$resultNet[1];
                $successDeeper = $resultNet[0];
            }

            if ($tv) {
                $resultTv = $this->checkActionCLPCL($questionIdTv, $uniqueCase, $successDeeper, $message, $dataSurvey);
//                dump($resultTv[1]);
//                 array_push($message,$resultTv[1]);
                $message = $resultTv[1];
//                $message=$resultTv[1];
                $successDeeper = $resultTv[0];
            }
            return [$successDeeper, $message];
        } else
            return [$success, $message];
    }

    private function checkActionCLPCL($questionId, $uniqueCase, $successDeeper, $message, $dataSurvey) {
        $action = $dataSurvey['extraAction' . $questionId];
        //Tạo CL
        if (in_array($action, ['118', '119'])) {
//            dump('Tạo CL');
            $actionData = Redis::get($uniqueCase);
            if ($actionData == null) {
                $successDeeper = false;
                array_push($message, 'Vui lòng hoàn tất quá trình tạo Checklist');
//                $message.=' \n Vui lòng hoàn tất quá trình tạo Checklist';
            } else {
                $actionDataDecode = json_decode($actionData);
                //Chọn tạo CL
//                        if ($dataSurvey['action'] == '2') {
                if (empty($actionDataDecode->listCL)) {
                    $successDeeper = false;
                    array_push($message, 'Vui lòng hoàn tất quá trình tạo Checklist');
//                    $message.=' \n Vui lòng hoàn tất quá trình tạo Checklist';
                }
//                        }
//                $DayUpdateData = json_decode($checklist_update_later);
//                $addListCLData = array_merge($DayUpdateData->listCL, $listCL_update_later);
//                Redis::set('checklist_update_later', json_encode(['time' => date('y-m-d'), 'listCL' => $addListCLData]));
            }
        } else if (in_array($action, ['117'])) {
//              dump('Tạo PCL');
            $actionData = Redis::get($uniqueCase);
            if ($actionData == null) {
                $successDeeper = false;
                array_push($message, 'Vui lòng hoàn tất quá trình tạo PreChecklist');
//                $message.='\n Vui lòng hoàn tất quá trình tạo PreChecklist';
            } else {
                $actionDataDecode = json_decode($actionData);
                //Chọn tạo CL
//                        if ($dataSurvey['action'] == '2') {
                if (empty($actionDataDecode->listPCL)) {
                    $successDeeper = false;
                    array_push($message, 'Vui lòng hoàn tất quá trình tạo PreChecklist');
//                    $message.='\n Vui lòng hoàn tất quá trình tạo PreChecklist';
                }
//                        }
//                $DayUpdateData = json_decode($checklist_update_later);
//                $addListCLData = array_merge($DayUpdateData->listCL, $listCL_update_later);
//                Redis::set('checklist_update_later', json_encode(['time' => date('y-m-d'), 'listCL' => $addListCLData]));
            }
        }
        return [$successDeeper, $message];
    }

    protected function viewSurveyhiFPT($contractNum, $type, $code) {
        $apiIsc = new Apiisc();
        $outboundAccount = new OutboundAccount();
        $outboundQuestions = new OutboundQuestions();
        $surveySections = new SurveySections();
        $surveyResult = new SurveyResult();
        $param = ['shd' => $contractNum, 'num_type' => $type, 'code' => $code];

        $surveyInfo = (array) $surveySections->getSurveySections($param);
        //Khảo sát không tồn tại
        if (empty($surveyInfo)) {
            return response(view('errors.error', ['message' => 'Khảo sát này không tồn tại']));
        }

//        dump(1);
        $accountInfo = $outboundAccount->getAccountInfoByContractZeroToArray($contractNum);
        $surveyResult = $surveyResult->getDetailSurvey($surveyInfo['section_id']);
        $surveyResultEdited = [];
        foreach ($surveyResult as $key => $value) {
            $surveyResultEdited[$value->survey_result_question_id] = $value;
        }

        $infoBanwidth = array('ObjID' => $accountInfo[0]['ObjID']);
        $arrayBandwidth = [0 => 'Không nâng băng thông', 1 => 'Chưa hoàn tất nâng băng thông', 2 => 'Hoàn tất nâng băng thông'];
//        $bandwidth = json_decode($apiIsc->CheckBandwidthByObjID($infoBanwidth), true);
//        $bandwidth=$arrayBandwidth[$bandwidth[0]['Result']];
//        $accountInfo[0]['Bandwidth']=$bandwidth;

        $accountInfo[0]['Bandwidth'] = 'Hoàn tất nâng băng thông';
//        dump($accountInfo, $allQuestion, $surveyInfo, $surveyResult);
//        die;

        return view('hiFPT.saoBaoTriHiFPT', ['accountInfo' => $accountInfo[0], 'surveyInfo' => $surveyInfo, 'surveyResult' => $surveyResultEdited]);
    }

    public function updateHiFPT(Request $request) {
//        dump($request->all());
//        {
        $messages = [
            'rateScore49.required' => 'Vui lòng chọn điểm đánh giá Internet',
            'subNote49.required' => 'Vui lòng nhập ghi chú Internet',
            'extraAction49.required' => 'Vui lòng chọn hành động xử lý Internet',
            'extraError49.required' => 'Vui lòng chọn loại lỗi Internet',
            'rateScore50.required' => 'Vui lòng chọn điểm đánh giá Tivi',
            'subNote50.required' => 'Vui lòng nhập ghi chú Tivi',
            'extraAction50.required' => 'Vui lòng chọn hành động xử lý Tivi',
            'extraError50.required' => 'Vui lòng chọn loại lỗi Tivi',
            'rateScore51.required' => 'Vui lòng chọn điểm đánh giá nhân viên bảo trì',
            'subNote51.required' => 'Vui lòng nhập ghi chú nhân viên bảo trì',
            'extraError51.required' => 'Vui lòng chọn loại lỗi nhân viên bảo trì',
        ];
        $input = $request->all();
//        dump($input);die;
        //Khởi tạo validator đúng cho 3 câu
        $validatorNet = $validatorTV = $validatorStaff = Validator::make(['input' => 'true'], [
                    'input' => 'required'
        ]);
        //Internet
        if (isset($input['rateScore49'])) {
            $validatorNet = Validator::make($input, [
                        'rateScore49' => 'required|in:1,2,3,4,5'
                            ], $messages);
            $validatorNet->sometimes(['subNote49', 'extraAction49', 'extraError49'], 'required', function($input) {
                return in_array($input->rateScore49, [1, 2]);
            });
        }
//         dump(aa);die;
        //Tivi
        if (isset($input['rateScore50'])) {
            $validatorTV = Validator::make($input, [
                        'rateScore50' => 'required|in:1,2,3,4,5'
                            ], $messages);
//             if(in_array(49, $listQuestionID12))
//             {
            $validatorTV->sometimes(['subNote50', 'extraAction50', 'extraError50'], 'required', function($input) {
                return in_array($input->rateScore50, [1, 2]);
            });
//             }
        }
        //Nhân viên bảo trì
        if (isset($input['rateScore51'])) {
            $validatorStaff = Validator::make($input, [
                        'rateScore51' => 'required|in:1,2,3,4,5',
                            ], $messages);
            $validatorStaff->sometimes(['subNote51', 'extraError51'], 'required', function($input) {
                return in_array($input->rateScore51, [1, 2]);
            });
        }
        if ($validatorNet->fails() || $validatorTV->fails() || $validatorStaff->fails()) {
            $errors = $validatorNet->messages()->merge($validatorTV->messages())->merge($validatorStaff->messages());
            return redirect()->back()
                            ->withErrors($errors)
//                            ->withErrors($validatorTV)
//                            ->withErrors($validatorStaff)
                            ->withInput();
        } else {
            try {
                DB::beginTransaction();
//                dump($input);
                $modelSurvey = SurveySections::find($input['sectionID']);
                $modelSurvey->section_connected = isset($input['connected']) ? $input['connected'] : null;
                $modelSurvey->section_action = isset($input['action']) ? $input['action'] : null;
//                $modelSurvey->section_time_completed = date('Y-m-d H:i:s');
//                $modelSurvey->section_time_completed_int = strtotime(date('Y-m-d H:i:s'));
                $modelSurvey->section_date_modified = date('Y-m-d H:i:s');
                $modelSurvey->section_user_name = Auth::user()->name;
                $modelSurvey->section_user_id = Auth::user()->id;
                $modelSurvey->section_user_modified = Auth::user()->name;
                $modelSurvey->section_user_modified = Auth::user()->name;
                $modelSurvey->save();
                $outboundQuestion = new OutboundQuestions();
                $listQuestion = $outboundQuestion->getQuestionIDByTypeOnly(12);
                SurveyResult::deleteSurveyResult($input['sectionID']);
//                $questionID
                foreach ($listQuestion as $key => $questionID) {
                    if (isset($input['rateScore' . $questionID])) {
                        $surveyResult = new SurveyResult();
                        $surveyResult->survey_result_section_id = $input['sectionID'];
                        $surveyResult->survey_result_question_id = $questionID;
                        $surveyResult->survey_result_answer_id = isset($input['rateScore' . $questionID]) ? $input['rateScore' . $questionID] : null;
                        $surveyResult->survey_result_answer_extra_id = isset($input['extraError' . $questionID]) ? $input['extraError' . $questionID] : null;
                        $surveyResult->survey_result_note = isset($input['subNote' . $questionID]) ? $input['subNote' . $questionID] : null;
                        $surveyResult->survey_result_action = isset($input['extraAction' . $questionID]) ? $input['extraAction' . $questionID] : null;
                        $surveyResult->save();
                    }
                }
                 $dateComplete = date('Y-m-d', strtotime($modelSurvey->section_time_completed));
                //Ngày hoàn thành khảo sát ko cùng ngày với update
                if ($dateComplete != date('Y-m-d')) {
                    $reUpdateTimeSummary = $dateComplete;
                } else {
                    $reUpdateTimeSummary = 0;
                }
                DB::commit();
                 //Có ngày cần update các bảng summary lại
                if ($reUpdateTimeSummary != 0) {
                    Redis::rpush('day_update_summary', $reUpdateTimeSummary);
                }
                $apiHelp = new ApiHelper();
                $paramCheck['sectionId'] = $input['sectionID'];
                $resCheck = $apiHelp->checkSendMail($paramCheck);
                if ($resCheck['status']) {
                    Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
                }

                $modelDetailSurveyResult = new SurveySections();
                $detail = $modelDetailSurveyResult->getAllDetailSurveyInfo($input['sectionID']);
                $connected = $modelSurvey->section_connected;
                $contactPhone = $modelSurvey->section_contact_phone;
                $mainNote = $modelSurvey->section_note;
                return response(view('layouts.success', ['detail' => $detail, 'contract' => $request->contractNum, 'connected' => $connected, 'contactPhone' => $contactPhone, 'mainNote' => $mainNote]));
            } catch (Exception $ex) {
                return response(view('errors.error', ['message' => $ex->getMessage()]));
                DB::rollBack();
            }
        }
    }

    public function setLocale($locale) {
        $language='en';
        $locales=['en', 'vi'];
        if (in_array($locale, $locales)) {
            $language = $locale;
        }
        Session::put('languageLocale',$language );
        return redirect()->back();
    }

    public function responseSuccess(Request $request){

    }
}
