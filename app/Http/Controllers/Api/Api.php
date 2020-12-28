<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Component\HelpProvider;
use App\Http\Controllers\Controller;
use App\Models\SurveySections;
use App\Models\OutboundAccount;
use App\Models\Surveys;
use App\Models\SurveyResult;
use Exception;
use App\Models\Api\ApiHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ReSendNotificationEmail;
use App\Models\PushNotification;
use Illuminate\Support\Facades\Redis;
use App\Models\SurveySectionsReport;
use App\Models\SurveyHifpt;
use App\Models\SurveyFptvn;
use App\Models\ContactProfile;
use App\Models\FormulaSalary\FormulaSalaryTinPNC;

class Api extends Controller {
    /* lấy lưu thông tin khảo sát */

    public function getResultSurveys(Request $request) {
        $help = new HelpProvider();
        $input = $request->all();
        $result = null;
        $resCheck = $help->checkPost($input, $help->getCondition('getResultSurveys'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }

        try {

            $survey = new SurveySections;
            $res = $survey->checkSurveyApiUpgrade($input['contract']);
            if ($res === false) {
                $result['status'] = 3;
            } elseif ($res === true) {
                $result['status'] = 1;
            } else {
                foreach ($res as $val) {
                    if (( $val->survey_result_question_id == '6' || $val->survey_result_question_id == '8' ) && $val->survey_result_answer_id != '-1') {
                        $result['status'] = 2;
                        break;
                    } else {
                        $result['status'] = 3;
                    }
                }
            }

            $res = ['code' => 200, 'msg' => 'Trả dữ liệu thành công', 'status' => $result['status']];
            return response()->json($res, 200);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    /*
      Lưu thông tin khảo sát
     */

    public function saveResultSurveys(Request $request) {

        $help = new HelpProvider();
        $input = $request->all();
        $resCheck = $help->checkPost($input, $help->getCondition('saveResultSurveys'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }
        try {
            $resValid = $help->validateDateStartEnd($input['time_start'], $input['time_completed']);
            if (!$resValid) {
                return $help->responseFail(406, 'time_start hoặc time_completed không hợp lệ');
            }
            $result = $this->insertSurvey($input);
            return $help->responseSuccess($result);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    /*
     * lấy lịch sử khảo sát của 1 hợp đồng
     */

    private function getHistorySurvey($contact_num) {
        $outboundAccount = new OutboundAccount();
        $response = $outboundAccount->getAccountInfoByContract($contact_num);
//        $responseInfo['accountInfoFromSurvey'] = $outboundAccount->getAccountInfoByContract($contact_num);
        $hasNPS = FALSE;
        $historyOutboundSurvey = array();
        if (isset($response->id)) {
            $SurveySections = new SurveySections();
            $accountInfoFromSurvey = $SurveySections->getAllSurveyInfoOfAccount($response->id);


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
//                $responseInfo['interval'] = $interval;
            }

            $responseInfo['outbound_history'] = $historyOutboundSurvey;
        } else {
            $responseInfo['outbound_history'] = $historyOutboundSurvey;
        }
        $responseInfo['NPS'] = $hasNPS;
        return $responseInfo;
    }

// Lưu khảo sát
    private function insertSurvey($input) {
        $Surveys = new Surveys();
        $dataAccount = $input['dataaccount'];
        if (!isset($dataAccount['ContractNum'])) {
            throw new Exception('1 Thiếu số hợp đồng', 400, null);
        }

        DB::beginTransaction();
        $OutboundAccount = new OutboundAccount();
        $resSaveAccountInfo = $OutboundAccount->saveAccount($dataAccount);
        if ($resSaveAccountInfo['code'] == 400) {
            DB::rollback();
            throw new Exception($resSaveAccountInfo['msg'], 400, null);
        }
        $accountInfo = $resSaveAccountInfo['data'];

        $datapost = $input['datapost'];
        $surveyID = $input['type'];
        $dataContact = $input['datacontact'];
        /*
         * tạo survey sections
         */
        // cần tạo try catch

        $user = $input['name'];
        $surveySections['section_account_inf'] = isset($dataAccount['AccountINF']) ? $dataAccount['AccountINF'] : NULL;
        $surveySections['section_account_list'] = isset($dataAccount['AccountList']) ? $dataAccount['AccountList'] : NULL;
        $surveySections['section_account_payment'] = isset($dataAccount['AccountPayment']) ? $dataAccount['AccountPayment'] : NULL;
        $surveySections['section_acc_sale'] = isset($dataAccount['AccountSale']) ? $dataAccount['AccountSale'] : NULL;
        $surveySections['section_objAddress'] = isset($dataAccount['ObjAddress']) ? $dataAccount['ObjAddress'] : NULL;
        $surveySections['section_branch_code'] = isset($dataAccount['BranchCode']) ? $dataAccount['BranchCode'] : "";
        $surveySections['section_center_list'] = isset($dataAccount['CenterList']) ? $dataAccount['CenterList'] : NULL;
        $surveySections['section_company_name'] = isset($dataAccount['CompanyName']) ? $dataAccount['CompanyName'] : NULL;
        $surveySections['section_contract_num'] = isset($dataAccount['ContractNum']) ? $dataAccount['ContractNum'] : NULL;
        $surveySections['section_customer_name'] = isset($dataAccount['CustomerName']) ? $dataAccount['CustomerName'] : NULL;
        $surveySections['section_description'] = isset($dataAccount['Description']) ? $dataAccount['Description'] : NULL;
        $surveySections['section_email_inf'] = isset($dataAccount['EmailINF']) ? $dataAccount['EmailINF'] : NULL;
        $surveySections['section_email_list'] = isset($dataAccount['EmailList']) ? $dataAccount['EmailList'] : NULL;
        $surveySections['section_email_sale'] = isset($dataAccount['EmailSale']) ? $dataAccount['EmailSale'] : NULL;
        $surveySections['section_fee_local_type'] = isset($dataAccount['FeeLocalType']) ? $dataAccount['FeeLocalType'] : NULL;
        $surveySections['section_finish_date_inf'] = isset($dataAccount['FinishDateINF']) ? $dataAccount['FinishDateINF'] : NULL;
        $surveySections['section_finish_date_list'] = isset($dataAccount['FinishDateList']) ? $dataAccount['FinishDateList'] : NULL;
        $surveySections['section_kind_deploy'] = isset($dataAccount['KindDeploy']) ? $dataAccount['KindDeploy'] : NULL;
        $surveySections['section_legal_entity_name'] = isset($dataAccount['LegalEntityName']) ? $dataAccount['LegalEntityName'] : NULL;
        $surveySections['section_location_id'] = isset($dataAccount['LocationID']) ? $dataAccount['LocationID'] : "";
        $surveySections['section_location'] = isset($dataAccount['Location']) ? $dataAccount['Location'] : "";
        $surveySections['section_package_sal'] = isset($dataAccount['PackageSal']) ? $dataAccount['PackageSal'] : NULL;
        $surveySections['section_partner_name'] = isset($dataAccount['PartnerName']) ? $dataAccount['PartnerName'] : NULL;
        $surveySections['section_payment_type'] = isset($dataAccount['PaymentType']) ? $dataAccount['PaymentType'] : NULL;
        $surveySections['section_phone'] = isset($dataAccount['Phone']) ? $dataAccount['Phone'] : NULL;
        $surveySections['section_region'] = isset($dataAccount['Region']) ? $dataAccount['Region'] : "";
        $surveySections['section_sub_parent_desc'] = isset($dataAccount['SubParentDesc']) ? $dataAccount['SubParentDesc'] : "";
        $surveySections['section_subsupporter'] = isset($dataAccount['SubSupporter']) ? $dataAccount['SubSupporter'] : NULL;
        $surveySections['section_supporter'] = isset($dataAccount['Supporter']) ? $dataAccount['Supporter'] : NULL;
        $surveySections['section_use_service'] = isset($dataAccount['UseService']) ? $dataAccount['UseService'] : NULL;

        //trường mới cần thêm
//		$surveySections['section_account_list_indo'] = isset($dataAccount['AccountListINDO']) ? $dataAccount['AccountListINDO'] : NULL;
//		$surveySections['section_CenterINF'] = isset($dataAccount['CenterINF']) ? $dataAccount['CenterINF'] : "";
        $surveySections['section_record_channel'] = 4;
        //
        // trường cũ
        $surveySections['section_connected'] = isset($input['connected']) ? $input['connected'] : 0;
        $surveySections['section_count_connected'] = ($surveySections['section_connected'] == 1 || $surveySections['section_connected'] == 3) ? 1 : 0;
        $surveySections['section_note'] = isset($datapost['note']) ? $datapost['note'] : "";
        $surveySections['section_contact'] = isset($datapost['contact']) ? $datapost['contact'] : "";
        $surveySections['section_account_id'] = $accountInfo->id;
        $surveySections['section_user_id'] = NULL;
        $surveySections['section_user_name'] = isset($user) ? $user : NULL;
        $surveySections['section_contact_person'] = isset($dataAccount['contactPerson']) ? $dataAccount['contactPerson'] : "";
        $surveySections['section_kind_main'] = isset($dataAccount['KindMain']) ? $dataAccount['KindMain'] : NULL;
        $surveySections['section_code'] = isset($input['id']) ? $input['id'] : NULL;

        $arrayAction = [
            '115' => 1,
            '116' => 5,
            '117' => 3,
            '118' => 2,
            '119' => 4,
            '128' => 1,
        ];
        $surveySections['section_action'] = isset($input['action']) ? $arrayAction[$input['action']] : 1;
        $datetime = $input['time_completed']->format('Y-m-d H:i:s');
        $surveySections['section_time_completed'] = $datetime;
        $surveySections['section_time_completed_int'] = strtotime($datetime);
        $surveySections['section_time_start'] = $input['time_start'];

        //thêm thông tin liên hệ
        $modelContactProfile = new ContactProfile();
        $accountID = NULL;
        $userCreatedName = NULL;
        $response = $modelContactProfile->saveContactProfile($dataContact, $accountID, NULL, $userCreatedName, $dataAccount['ContractNum']);
        if ($response['code'] != 200) {
            throw new Exception(' 3 Thông tin người liên hệ chưa được thêm vào', 400, null);
        }

        // khách hàng đồng ý khảo sát và chọn 1 nội dung khảo sát
        if (isset($surveyID)) {
            $surveyDetail = $Surveys->getDetailSurvey($surveyID);
            if (!isset($surveyDetail->survey_id)) {
                DB::rollback();
                throw new Exception(' 4 Loại khảo sát không tồn tại', 400, null);
            }
            $surveySections['section_survey_id'] = $surveyDetail->survey_id;
        }
        /* lấy dữ liệu của survey
         * nếu không có dự liệu thì trả về kết quả không tìm thấy
         */

        $surveySectionID = $Surveys->saveSurveySections($surveySections);
        if (!$surveySectionID) {
            DB::rollback();
            throw new Exception(' 5 Không thể lưu khảo sát', 400, null);
        }
        /*
         * Nếu khách hàng đồng ý trả lời và chọn 1 nội dung khảo sát
         * Lấy danh sách các câu hỏi của surveys
         */

        $surveyRes = new SurveyResult();
        foreach ($datapost as $val) {
            $temp['survey_result_section_id'] = $surveySectionID;
            $temp['survey_result_question_id'] = $val['questionid'];
            $temp['survey_result_answer_id'] = $val['answerid'];
            $temp['survey_result_note'] = $val['note'];
            $temp['survey_result_answer_extra_id'] = !empty($val['extraidquestion']) ? $val['extraidquestion'] : NULL;
            $temp['survey_result_other'] = null;
            $temp['survey_result_action'] = !empty($val['actionid']) ? $val['actionid'] : NULL;


            $surveyResultID = $surveyRes->saveSurveyResult($temp);
            if (!$surveyResultID) {
                DB::rollback();
                throw new Exception(' 6 Không thể lưu nội dung khảo sát', 400, null);
            }
        }

        DB::commit();

        $apiHelp = new ApiHelper();
        $paramCheck['sectionId'] = $surveySectionID;
        $resCheck = $apiHelp->checkSendMail($paramCheck);
        if ($resCheck['status']) {
            $apiHelp->prepareSendMail($paramCheck, $resCheck);
        }
        return $surveySectionID;
    }

    private function updateSurvey($input) {
        $SurveyResult = new SurveyResult();
        $modelSurvey = SurveySections::find($input['idS']);
        if (empty($modelSurvey)) {
            throw new Exception('id của khảo sát này không tồn tại', 400, null);
        }
        $dateComplete = date('Y-m-d', strtotime($modelSurvey->section_time_completed));
        //Ngày hoàn thành khảo sát ko cùng ngày với update
        if ($dateComplete != date('Y-m-d')) {
            $reUpdateTimeSummary = $dateComplete;
        } else {
            $reUpdateTimeSummary = 0;
        }
        $modelSurvey->section_connected = $input['datapost']['connected'];
        $modelSurvey->section_note = $input['datapost']['note'];
        $modelSurvey->section_action = $input['datapost']['action'];
        $modelSurvey->section_time_completed = date('Y-m-d H:i:s');
        if ($modelSurvey->section_connected == 1 || $modelSurvey->section_connected == 3) {
            $modelSurvey->section_count_connected = $modelSurvey->section_count_connected + 1;
        }
        if (!$modelSurvey->save()) {
            throw new Exception(null, 500, null);
        }
        $msg = $SurveyResult->updateDetailSurvey($input['idS'], $input['datapost'], null, $input['arrayAnswer']);
        if ($reUpdateTimeSummary != 0) {
            $day_update_summary = Redis::get('day_update_summary');
            if ($day_update_summary == null) {
                Redis::set('day_update_summary', json_encode([$reUpdateTimeSummary]));
            } else {
                $arrayDayUpdate = json_decode($day_update_summary);
                //Ngày mới nên thêm vào
                if (!in_array($reUpdateTimeSummary, $arrayDayUpdate)) {
                    array_push($arrayDayUpdate, $reUpdateTimeSummary);
                }
                Redis::set('day_update_summary', json_encode($arrayDayUpdate));
            }
        }

        $apiHelp = new ApiHelper();
        $paramCheck['sectionId'] = $input['idS'];
        $resCheck = $apiHelp->checkSendMail($paramCheck);
        if ($resCheck['status']) {
            $apiHelp->prepareSendMail($paramCheck, $resCheck);
        }

        return $msg;
    }

    /* Bộ api lấy lương */

    public function getInfoSalaryIBB(Request $request) {
        $help = new HelpProvider();
        $input = $request->all();
        $resCheck = $help->checkPost($input, $help->getCondition('getInfoSalaryIBB'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }

        $validate = HelpProvider::validateDateStartEndForSearchFullDay($input['date_start'], $input['date_end']);
        if (!$validate) {
            return $help->responseFail(406, 'date_start hoặc date_end không hợp lệ');
        }

        try {
            $sr = new SurveyResult();
            $result = $sr->apiGetInfoSurveySalaryIBB('1', '1,2,3,4,5', $input['date_start'], $input['date_end']);
            return $help->responseSuccess($result);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function getInfoSalaryTinPNC(Request $request) {
        $help = new HelpProvider();
        $input = $request->all();
        $resCheck = $help->checkPost($input, $help->getCondition('getInfoSalaryTinPNC'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }

        $validate = HelpProvider::validateDateStartEndForSearchFullDay($input['date_start'], $input['date_end']);
        if (!$validate) {
            return $help->responseFail(406, 'date_start hoặc date_end không hợp lệ');
        }

        try {
            $formula = new FormulaSalaryTinPNC();
            $result = $formula->getRecordByParam($input);
            return $help->responseSuccess($result);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    // lưu lại xác nhận khảo sát

    public function saveReponseAcceptInfo(Request $request) {
        $help = new HelpProvider();
        //Lấy thông tin POST
        $input = $request->all();

        //Kiểm tra dữ liệu POST
        $resCheck = $help->checkPost($input, $help->getCondition('getReponseAcceptInfo'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }

        try {
            $model_push = new PushNotification();

            //Lấy thông tin push_notification 
            $resPush = $model_push->getPushNotificationOnConfirmCode($input['code']);
            if (!empty($resPush)) {
                $param['confirm_code'] = $input['code'];
                $param['confirm_note'] = NULL;
                $param['confirmed_at'] = date('Y-m-d H:i:s');

                //Kiểm tra xem có phải là người cần xác nhận hay không
                $user = $input['name'];
//				$resCheck = $help->checkConfirmEmail($user, $resPush->push_notification_send_to);
//				if(!$resCheck){
//					return $help->responseFail(406, 'Bạn không được phép xác nhận thông tin');
//				}
                $param['confirm_user'] = $user;
                $param['api_is_reSend'] = 0;

                //Cập nhật thông tin push_notification đã nhận được
                $resUp = $model_push->updatePushNotificationOnConfirmNotification($param);
                if ($resUp) {
                    $result = 'Đã cập nhật';
                    return $help->responseSuccess($result);
                } else {
                    return $help->responseFail(406, 'Không cập nhật được dữ liệu');
                }
            } else {
                return $help->responseFail(406, 'Không tồn tại mã xác nhận trong hệ thống');
            }
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function sendNotificationAgain() {
        $help = new HelpProvider();
        try {
            //Lấy ra danh sách api Net cần send lại
            $model_push = new PushNotification();
            $resPush = $model_push->getPushNotificationSendMailAgain();
            foreach ($resPush as $val) {
                $input = (array) $val;
                //Đưa vào hàng đợi gửi lại thông báo
                $job = (new ReSendNotificationEmail($input))->onQueue('emails');
                Bus::dispatch($job);
            }
            $result = 'Đã tiến hành gửi';
            return $help->responseSuccess($result);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function getPushSurveyId() {
        for($i = 0; $i <= 4; $i++){
            $redis = Redis::exists('pushNotificationID');
            if ($redis) {
                $apiHelp = new ApiHelper();
                $paramCheck['sectionId'] = Redis::rpop('pushNotificationID');
                $resCheck = $apiHelp->checkSendMail($paramCheck);
                if ($resCheck['status']) {
                    $apiHelp->prepareSendMail($paramCheck, $resCheck);
                }
            }
        }
        $help = new HelpProvider();
        return $help->responseSuccess('Đã tiến hành gửi');
    }

    public function transferToReportByInsertMiddle(Request $request) {
        $help = new HelpProvider();
        $modelSurSecRep = new SurveySectionsReport();
        try {
            $temp = $modelSurSecRep->getMaxIDMiddle();
            $maxId = $temp->section_id;
            $arraySurSec = $modelSurSecRep->getSurveySectionForTranferMiddle($maxId, 200);
            foreach ($arraySurSec as $val) {
                $result = $this->saveTableSurveySectionsReport($val->section_id);
                if (!$result['state']) {
                    return $help->responseFail(406, $result['message']);
                }
            }
            return $help->responseSuccess('Đã hoàn thành chuyển đổi');
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function transferToReportByInsertTop(Request $request) {
        $help = new HelpProvider();
        $modelSurSecRep = new SurveySectionsReport();
        try {
            $temp = $modelSurSecRep->getMaxIDTop();
            $maxId = $temp->section_id;
            $arraySurSec = $modelSurSecRep->getSurveySectionForTranferTop($maxId, 200);
            foreach ($arraySurSec as $val) {
                $result = $this->saveTableSurveySectionsReport($val->section_id);
//				$result = $this->saveTableSurveySectionsReport('300196');
                if (!$result['state']) {
                    return $help->responseFail(406, $result['message']);
                }
            }
            return $help->responseSuccess('Đã hoàn thành chuyển đổi');
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function transferToReportByUpdate(Request $request) {
        $help = new HelpProvider();
        $modelSurSecRep = new SurveySectionsReport();
        try {
            $arraySurSec = $modelSurSecRep->getSurveySectionNeedUpdate(200);
            foreach ($arraySurSec as $val) {
                $result = $this->saveTableSurveySectionsReport($val->section_id, 'update');
                if (!$result['state']) {
                    return $help->responseFail(406, $result['message']);
                }
            }
            return $help->responseSuccess('Đã hoàn thành chuyển đổi');
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function transferToReportByUpdateNow(Request $request) {
        $help = new HelpProvider();
        $modelSurSecRep = new SurveySectionsReport();
        try {
            $arraySurSec = $modelSurSecRep->getSurveySectionNeedUpdateNow();
            foreach ($arraySurSec as $val) {
                $result = $this->saveTableSurveySectionsReport($val->section_id, 'update');
                if (!$result['state']) {
                    return $help->responseFail(406, $result['message']);
                }
            }
            return $help->responseSuccess('Đã hoàn thành chuyển đổi');
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    private function saveTableSurveySectionsReport($sectionId, $type = 'insert') {
        $modelSurSecRep = new SurveySectionsReport();

        //Lấy thông tin khảo sát
        $infoSurSec = (array) $modelSurSecRep->getInfoForReport($sectionId);
        if (empty($infoSurSec)) {
            return ['state' => 'false', 'message' => 'Mã khảo sát không hợp thời'];
        }

        //Các field trong bảng survey_section_report
        $arraySection = [
            'section_id',
            'section_survey_id',
            'section_code',
            'section_contract_num',
            'section_contact_phone',
            'section_note',
            'section_sub_parent_desc',
            'section_supporter',
            'section_subsupporter',
            'section_user_name',
            'section_connected',
            'section_acc_sale',
            'section_action',
            'section_location_id',
            'section_location',
            'section_branch_code',
            'section_center_list',
            'section_user_modified',
            'section_date_modified',
            'section_time_start',
            'section_time_completed',
            'section_time_completed_int',
            'section_account_inf',
            'section_account_list',
            'sale_center_id',
            'section_count_connected',
            'section_sale_branch_code'
        ];

        $array = [
            'question',
            'answer',
            'answer_extra_id',
            'note'
        ];

        // question_id => prefix
        $arrayPrefix = [
            '6' => 'nps_',
            '8' => 'nps_',
            '5' => 'nps_',
            '7' => 'nps_',
            '16' => 'nps_',
            '17' => 'nps_',
            '24' => 'nps_',
            '25' => 'nps_',
            '1' => 'csat_salesman_',
            '23' => 'csat_salesman_',
            '2' => 'csat_deployer_',
            '22' => 'csat_deployer_',
            '10' => 'csat_net_',
            '14' => 'csat_net_',
            '20' => 'csat_net_',
            '11' => 'csat_tv_',
            '15' => 'csat_tv_',
            '21' => 'csat_tv_',
            '4' => 'csat_maintenance_staff_',
            '12' => 'csat_maintenance_net_',
            '13' => 'csat_maintenance_tv_'
        ];

        //Đưa giá trị section vào
        foreach ($arraySection as $val) {
            $param[$val] = $infoSurSec[$val];
        }

        $surveyId = $infoSurSec['section_survey_id'];
        //Lấy thông tin điểm khảo sát
        $infoNPS = (array) $modelSurSecRep->getInfoNPS($sectionId, $surveyId);

        //Kiểm tra và gán các giá trị đã có vào các field mới
        foreach ($infoNPS as $oneQuestion) {
            $oneQuestion = (array) $oneQuestion;

            $check = array_key_exists($oneQuestion['question'], $arrayPrefix);
            if (!$check) {
                continue;
            }

            $prefix = $arrayPrefix[$oneQuestion['question']];
            switch ($oneQuestion['question']) {
                case '1':
                case '23':
                    $param[$prefix . 'point'] = $oneQuestion['kinhdoanh'];
                    break;
                case '2':
                case '4':
                case '22':
                    $param[$prefix . 'point'] = $oneQuestion['kythuat'];
                    break;
                case '5':
                case '7':
                case '17':
                case '25':
                    $param[$prefix . 'improvement'] = $oneQuestion[$prefix . 'improvement'];
                    $param[$prefix . 'improvement_note'] = $oneQuestion[$prefix . 'improvement_note'];
                    break;
                case '6':
                case '8':
                case '24':
                    $param[$prefix . 'point'] = $oneQuestion['point'];
                    break;
                case '10':
                case '12':
                case '20':
                    $param[$prefix . 'point'] = $oneQuestion['internet'];
                    $param['result_action_net'] = $oneQuestion['survey_result_action'];
                    break;
                case '14':
                    $param[$prefix . 'point'] = $oneQuestion['internet'];
                    break;
                case '11':
                case '13':
                case '21':
                    $param[$prefix . 'point'] = $oneQuestion['truyenhinh'];
                    $param['result_action_tv'] = $oneQuestion['survey_result_action'];
                    break;
                case '15':
                    $param[$prefix . 'point'] = $oneQuestion['truyenhinh'];
                    break;
            }

            if ($oneQuestion['question'] == "5" || $oneQuestion['question'] == "7" || $oneQuestion['question'] == "17" || $oneQuestion['question'] == "25") {
                continue;
            }

            foreach ($array as $val) {
                $param[$prefix . $val] = $oneQuestion[$val];
            }
        }

        //Gán các giá trị ngoại lệ
        $param['section_objID'] = $infoSurSec['objid'];
        if ($type == 'insert') {
            $param['insert_at'] = date('Y-m-d H:i:s');
            $param['updated_at'] = date('Y-m-d H:i:s');
            $result = $modelSurSecRep->insertSurveySectionReport($param);
        } else {
            $param['updated_at'] = date('Y-m-d H:i:s');
            $result = $modelSurSecRep->updateSurveySectionReport($param);
        }

        $message = 'Không thể chuyển đổi dữ liệu';
        if ($result) {
            $message = 'Dữ liệu chuyển đổi thành công';
        }

        return ['state' => $result, 'message' => $message];
    }

    /**
     * get câu hỏi khảo sát cho HiFPT
     */
    public function getInfoQuestionsSurveyApp() {
        $help = new HelpProvider();
        try {
            $res = $this->getInfoQuestionsApp();
            return $help->responseSuccess($res);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    /**
     * insert câu trả lời từ HiFpt
     */
    public function insertSurveyApp(Request $request) {
        $help = new HelpProvider();
        $modelSurvey = new SurveyHifpt();
        try {
            $date = date('Y-m-d H:i:s');
            $req = $request->all();
            //các tham số truyền từ HiFpt
            $param['contract_num'] = !empty($req['contractNum']) ? $req['contractNum'] : '';
            $param['contract_phone'] = !empty($req['contractPhone']) ? $req['contractPhone'] : '';
            $param['objID'] = !empty($req['objID']) ? $req['objID'] : '';
            $param['region'] = !empty($req['region']) ? $req['region'] : '';
            $param['location_id'] = !empty($req['locationID']) ? $req['locationID'] : '';
            $param['location_text'] = !empty($req['locationText']) ? $req['locationText'] : '';
            $param['time_start'] = !empty($req['timeStart']) ? $req['timeStart'] : '';
            $param['time_completed'] = !empty($req['timeCompleted']) ? $req['timeCompleted'] : '';
            $param['insert_at'] = $date;

            $info = $this->getInfoQuestionsApp();
            $param['csat_app_id'] = !empty($info[0]['id']) ? $info[0]['id'] : '';
            $param['csat_app_question'] = !empty($info[0]['question']) ? $info[0]['question'] : '';
            $param['csat_app_point'] = !empty($req[0]['answers']['checkbox']) ? $req[0]['answers']['checkbox'] : '-1';
            $param['csat_app_note'] = !empty($req[0]['answers']['textarea']) ? $req[0]['answers']['textarea'] : '';

            $param['csat_question2_id'] = !empty($info[1]['id']) ? $info[1]['id'] : '';
            $param['csat_question2_question'] = !empty($info[1]['question']) ? $info[1]['question'] : '';
            $param['csat_question2_point'] = !empty($req[1]['answers']['radio']) ? $req[1]['answers']['radio'] : '-1';

            $param['csat_question3_id'] = !empty($info[2]['id']) ? $info[2]['id'] : '';
            $param['csat_question3_question'] = !empty($info[2]['question']) ? $info[2]['question'] : '';
            $param['csat_question3_point'] = !empty($req[2]['answers']['radio']) ? $req[2]['answers']['radio'] : '-1';

            $param['csat_question4_id'] = !empty($info[3]['id']) ? $info[3]['id'] : '';
            $param['csat_question4_question'] = !empty($info[3]['question']) ? $info[3]['question'] : '';
            $param['csat_question4_point'] = !empty($req[3]['answers']['radio']) ? $req[3]['answers']['radio'] : '-1';

            $param['csat_question5_id'] = !empty($info[4]['id']) ? $info[4]['id'] : '';
            $param['csat_question5_question'] = !empty($info[4]['question']) ? $info[4]['question'] : '';
            $param['csat_question5_point'] = !empty($req[4]['answers']['radio']) ? $req[4]['answers']['radio'] : '-1';

            $param['csat_question6_id'] = !empty($info[5]['id']) ? $info[5]['id'] : '';
            $param['csat_question6_question'] = !empty($info[5]['question']) ? $info[5]['question'] : '';
            $param['csat_question6_point'] = !empty($req[5]['answers']['radio']) ? $req[5]['answers']['radio'] : '-1';

            $param['csat_question7_id'] = !empty($info[6]['id']) ? $info[6]['id'] : '';
            $param['csat_question7_question'] = !empty($info[6]['question']) ? $info[6]['question'] : '';
            $param['csat_question7_point'] = !empty($req[6]['answers']['radio']) ? $req[6]['answers']['radio'] : '-1';

            $param['csat_question8_id'] = !empty($info[7]['id']) ? $info[7]['id'] : '';
            $param['csat_question8_question'] = !empty($info[7]['question']) ? $info[7]['question'] : '';
            $param['csat_question8_point'] = !empty($req[7]['answers']['radio']) ? $req[7]['answers']['radio'] : '-1';

            $param['csat_note_id'] = !empty($info[8]['id']) ? $info[8]['id'] : '';
            $param['csat_note_question'] = !empty($info[8]['question']) ? $info[8]['question'] : '';
            $param['csat_note'] = !empty($req[8]['answers']['textarea']) ? $req[8]['answers']['textarea'] : '';

            $param['feedback_id'] = !empty($info[9]['id']) ? $info[9]['id'] : '';
            $param['feedback_question'] = !empty($info[9]['question']) ? $info[9]['question'] : '';
            $param['feedback'] = !empty($req[9]['answers']['textarea']) ? $req[9]['answers']['textarea'] : '';
            $result = $modelSurvey->insertSurveyHifpt($param);
            if (!$result) {
                return $help->responseFail(406, 'Insert dữ liệu không thành công');
            }
            return $help->responseSuccess('Đã insert dữ liệu thành công.');
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    private function getInfoQuestionsApp() {
        $arrayQuestion = [
            'Quý khách vui lòng cho biết mức độ hài lòng của Quý khách đối với ứng dụng HiFPT',
            'Về tổng quan, HiFPT là một ứng dụng dễ sử dụng',
            'Việc cài đặt và đăng nhập lần đầu vào ứng dụng HiFPT là đơn giản',
            'Giao diện của ứng dụng HiFPT trực quan và hấp dẫn',
            'Các tính năng của ứng dụng HiFPT là rõ ràng và dễ sử dụng',
            'Tôi hài lòng với chức năng Báo hỏng của ứng dụng HiFPT',
            'Việc xem các thông tin về dịch vụ của tôi trên ứng dụng HiFPT là thuận tiện và dễ dàng',
            'Việc xem các thông tin về cước phí của tôi trên HiFPT là thuận tiện và dễ dàng',
            'Chúng tôi mong muốn nhận được tất cả các góp ý của Quý khách để tiếp tục hoàn thiện ứng dụng HiFPT. Xin quý khách vui lòng điền góp ý vào ô dưới đây',
            'Với phương châm khách hàng là trọng tâm, FPT Telecom luôn luôn mong muốn được lắng nghe nhiều hơn ý kiến từ phía khách hàng. Mọi ý kiến đóng góp từ phía Quý khách sẽ được chúng tôi ghi nhận và sử dụng để liên tục cải thiện và nâng cao chất lượng dịch vụ. Quý khách có thể cung cấp các ý kiến phản hổi về FPT Telecom theo mẫu dưới đây. Xin chân thành cảm ơn ! <br /> Nội dung:',
        ];

        $res = [];
        foreach ($arrayQuestion as $key => $val) {
            $temp['id'] = $key + 1;
            $temp['question'] = $val;
            $temp['answers'] = ($temp['id'] <= 8) ? [1 => 'Rất không hài lòng', 2 => 'Không hài lòng', 3 => 'Trung lập', 4 => 'Hài lòng', 5 => 'Rất hài lòng'] : '';
            if ($temp['id'] == 1) {
                $temp['type'] = ['checkbox' => 'answers', 'textarea' => 'Quý khách vui lòng cho biết lý do nếu có'];
            } else if ($temp['id'] == 9) {
                $temp['type'] = ['textarea' => 'Chúng tôi mong muốn nhận được tất cả các góp ý của Quý khách để tiếp tục hoàn thiện ứng dụng HiFPT. Xin quý khách vui lòng điền góp ý vào ô dưới đây'];
            } else if ($temp['id'] == 10) {
                $temp['type'] = ['textarea' => 'Với phương châm khách hàng là trọng tâm, FPT Telecom luôn luôn mong muốn được lắng nghe nhiều hơn ý kiến từ phía khách hàng. Mọi ý kiến đóng góp từ phía Quý khách sẽ được chúng tôi ghi nhận và sử dụng để liên tục cải thiện và nâng cao chất lượng dịch vụ. Quý khách có thể cung cấp các ý kiến phản hổi về FPT Telecom theo mẫu dưới đây. Xin chân thành cảm ơn ! <br /> Nội dung: '];
            } else {
                $temp['type'] = ['radio' => 'answers'];
            }
            array_push($res, $temp);
        }
        return $res;
    }

    //Chỉnh sửa lại nội dung mail resend
    private function updateApiResendNotificationNet($input) {
        $apiHelp = new ApiHelper();
        //Điều chỉnh lại tham số gồm confirm link, template của api
        $pushNotificationParam = json_decode($input['push_notification_param'], 1);
        $pushNotificationParam['confirm_link'] = $apiHelp->domain_confirm . 'confirm-notification?code=' . $input['confirm_code'];
        $pushNotificationParam['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $pushNotificationParam]));

        //Lấy input api để chỉnh sửa thêm vào số lần gửi
        $apiInput = json_decode($input['api_input'], 1);
        $lan = $input['api_send_count'] + 1;
        if (isset($apiInput['Subject'])) {
            if ($input['api_status'] != 0) {
                $pos = strpos($apiInput['Subject'], ' - lần');
                if ($pos !== false) {
                    $temp = str_split($apiInput['Subject'], $pos);
                    $apiInput['Subject'] = $temp[0] . ' - lần ' . $lan;
                } else {
                    $apiInput['Subject'] .= ' - lần ' . $lan;
                }
            }
        } else {
            $apiInput['Subject'] = 'Template cũ';
        }
        $apiInput['Description'] = $pushNotificationParam['template'];

        //Điều chỉnh lại thông số lần cuối của api để chuẩn bị cập nhật
        $input['push_notification_param'] = json_encode($pushNotificationParam);
        $input['push_notification_subjects'] = $apiInput['Subject'];
        $input['api_input'] = json_encode($apiInput);

        //Cập nhật lại thông số api
        $model_notification = new PushNotification();
        $model_notification->updatePushNotificationOnSendNotificationOnId($input);
        return $input;
    }

    private function updateApiResendNotificationTele($input) {
        //Điều chỉnh lại tham số gồm confirm link, template của api
        $pushNotificationParam = json_decode($input['push_notification_param'], 1);

        //Lấy input api để chỉnh sửa thêm vào số lần gửi
        $lan = $input['api_send_count'] + 1;
        if ($input['api_status'] != 0) {
            $pos = strpos($pushNotificationParam['subject'], ' - lần');
            if ($pos !== false) {
                $temp = str_split($pushNotificationParam['subject'], $pos);
                $pushNotificationParam['subject'] = $temp[0] . ' - lần ' . $lan;
            } else {
                $pushNotificationParam['subject'] .= ' - lần ' . $lan;
            }
        }

        //Điều chỉnh lại thông số lần cuối của api để chuẩn bị cập nhật
        $input['push_notification_param'] = json_encode($pushNotificationParam);
        $input['push_notification_subjects'] = $pushNotificationParam['subject'];

        //Cập nhật lại thông số api
        $model_notification = new PushNotification();
        $model_notification->updatePushNotificationOnSendNotificationOnId($input);
        return $input;
    }

    /**
     * get câu hỏi khảo sát cho Fpt.vn
     */
    public function getInfoQuestionsSurveyFPTVN($objSurvey) {
        $help = new HelpProvider();
        try {
            $res = $this->getInfoQuestionsFPTVN($objSurvey);
            return $help->responseSuccess($res);
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    private function getInfoQuestionsFPTVN($objSurvey) {
        $res = $temp = [];
        if (intval($objSurvey) === 0 || $objSurvey >= 3) {
            return $res;
        }
        $arrQuestionsWeb = [
            //câu hỏi về website fpt.vn
            'Nhóm nào dưới đây mô tả chính xác về quý khách? <br/><i>(Vui lòng tích chọn vào các ô có câu trả lời chính xác nhất )</i>',
            'Quý khách vui lòng cho biết mức độ hài lòng của quý khách đối với website fpt.vn <br/><i>(Vui lòng tích chọn vào biểu tượng tương ứng với mức độ hài lòng của Quý khách)</i>',
            'Về tổng quan, fpt.vn là một website dễ sử dụng',
            'Tìm kiếm thông tin trên fpt.vn dễ dàng',
            'Giao diện của fpt.vn trực quan và hấp dẫn',
            'Tính năng Đăng ký Online trên fpt.vn là rõ ràng và dễ sử dụng',
            'Tính năng Hỗ trợ trên fpt.vn là rõ ràng và dễ sử dụng',
            'Chia sẻ từ fpt.vn đến mạng xã hội dễ dàng',
            'Chúng tôi mong muốn nhận được tất cả các góp ý của quý khách để tiếp tục hoàn thiện website fpt.vn. Xin quý khách vui lòng điền góp ý vào ô dưới đây:',
        ];
        $arrayQuestionsService = [
            //câu hỏi về dịch vụ
            'Quý khách vui lòng cho biết mức độ hài lòng của quý khách đối với dịch vụ <i>Internet</i> của FPT Telecom <br/><i>(Vui lòng tích chọn vào biểu tượng tương ứng với mức độ hài lòng của Quý khách)</i>',
            'Quý khách vui lòng cho biết mức độ hài lòng của quý khách đối với dịch vụ <i>Truyền hình</i> của FPT Telecom <br/><i>(Vui lòng tích chọn vào biểu tượng tương ứng với mức độ hài lòng của Quý khách)</i>',
            'Khả năng quý khách sẵn sàng giới thiệu dịch vụ của FPT Telecom đến bạn bè, người thân là bao nhiêu điểm, trên thang điểm từ 0 đến 10? <br/><i>(Vui lòng tích chọn vào số điểm mà Quý khách lựa chọn)</i>',
            'Theo quý khách, FPT Telecom cần phải làm gì để quý khách có thể cho điểm 10?',
        ];

        if ($objSurvey == 1) {//website
            foreach ($arrQuestionsWeb as $key => $val) {
                $temp['id'] = $key + 1;
                $temp['question'] = $val;
                $temp['answers'] = '';
                if ($temp['id'] == 1) {
                    $temp['answers'] = [1 => 'Đây là lần đầu tiên tôi truy cập vào <strong>fpt.vn</strong>',
                        2 => 'Tôi thỉnh thoảng truy cập vào <strong>fpt.vn</strong>',
                        3 => 'Tôi thường xuyên truy cập vào <strong>fpt.vn</strong>'];
                    $temp['type'] = ['radio' => 'answers'];
                } else if ($temp['id'] == 2) {
                    $temp['answers'] = [1 => 'Rất không hài lòng', 2 => 'Không hài lòng', 3 => 'Trung lập', 4 => 'Hài lòng', 5 => 'Rất hài lòng'];
                    $temp['type'] = ['radio' => 'answers', 'textarea' => 'Quý khách vui lòng cho biết lý do nếu có'];
                } else if ($temp['id'] >= 3 && $temp['id'] <= 8) {
                    $temp['answers'] = [1 => 'Rất không đồng ý', 2 => 'Không đồng ý', 3 => 'Trung lập', 4 => 'Đồng ý', 5 => 'Rất đồng ý'];
                    $temp['type'] = ['radio' => 'answers'];
                } else if ($temp['id'] == 9) {
                    $temp['type'] = ['textarea' => 'Chúng tôi mong muốn nhận được tất cả các góp ý của Quý khách để tiếp tục hoàn thiện website fpt.vn. Xin quý khách vui lòng điền góp ý vào ô dưới đây'];
                }
                array_push($res, $temp);
            }
        } else { //dịch vụ
            foreach ($arrayQuestionsService as $key => $val) {
                $temp['id'] = $key + 1;
                $temp['question'] = $val;
                $temp['answers'] = '';
                if ($temp['id'] <= 2) {
                    $temp['answers'] = [1 => 'Rất không đồng ý', 2 => 'Không đồng ý', 3 => 'Trung lập', 4 => 'Đồng ý', 5 => 'Rất đồng ý'];
                    $temp['type'] = ['radio' => 'answers', 'textarea' => 'Quý khách vui lòng cho biết lý do nếu có'];
                } else if ($temp['id'] == 3) {
                    $temp['answers'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                    $temp['type'] = ['radio' => 'answers'];
                } else {
                    $temp['answers'] = [1 => 'Chất lượng đường truyền', 2 => 'Chất lượng thiết bị', 3 => 'Giá cước', 4 => 'Nhân viên kĩ thuật(triển khai, bảo trì)', 5 => 'Nhân viên kinh doanh', 6 => 'Khác'];
                    $temp['type'] = ['checkbox' => 'answers', 'textarea' => 'Quý khách vui lòng nhập nội dung góp ý chi tiết vào ô bên dưới:'];
                }
                array_push($res, $temp);
            }
        }
        return $res;
    }

    /**
     * Insert câu trả lời từ website fpt.vn
     */
    public function insertSurveyFptvn(Request $request) {
        $help = new HelpProvider();
        $modelSurvey = new SurveyFptvn();
        try {
            $date = date('Y-m-d H:i:s');
            $req = $request->all();
            //các tham số truyền từ Fptvn
            $param['time_start'] = !empty($req['timeStart']) ? $req['timeStart'] : '';
            $param['time_completed'] = !empty($req['timeCompleted']) ? $req['timeCompleted'] : '';
            $param['insert_at'] = $date;

            $info = $this->getInfoQuestionsFPTVN($req['objSurvey']);
            $param['csat_question1_id'] = !empty($info[0]['id']) ? $info[0]['id'] : '';
            $param['csat_question1_question'] = !empty($info[0]['question']) ? $info[0]['question'] : '';
            $param['csat_question1_point'] = !empty($req['questionSurvey'][1]['answers']['radio']) ? $req['questionSurvey'][1]['answers']['radio'] : '-1';

            $param['csat_question2_id'] = !empty($info[1]['id']) ? $info[1]['id'] : '';
            $param['csat_question2_question'] = !empty($info[1]['question']) ? $info[1]['question'] : '';
            $param['csat_question2_point'] = !empty($req['questionSurvey'][2]['answers']['radio']) ? $req['questionSurvey'][2]['answers']['radio'] : '-1';
            $param['csat_question2_note'] = !empty($req['questionSurvey'][2]['answers']['textarea']) ? $req['questionSurvey'][2]['answers']['textarea'] : '';

            $param['csat_question3_id'] = !empty($info[2]['id']) ? $info[2]['id'] : '';
            $param['csat_question3_question'] = !empty($info[2]['question']) ? $info[2]['question'] : '';
            $param['csat_question3_point'] = !empty($req['questionSurvey'][3]['answers']['radio']) ? $req['questionSurvey'][3]['answers']['radio'] : '-1';

            $param['csat_question4_id'] = !empty($info[3]['id']) ? $info[3]['id'] : '';
            $param['csat_question4_question'] = !empty($info[3]['question']) ? $info[3]['question'] : '';
            $param['csat_question4_point'] = !empty($req['questionSurvey'][4]['answers']['radio']) ? $req['questionSurvey'][4]['answers']['radio'] : '-1';

            $param['csat_question5_id'] = !empty($info[4]['id']) ? $info[4]['id'] : '';
            $param['csat_question5_question'] = !empty($info[4]['question']) ? $info[4]['question'] : '';
            $param['csat_question5_point'] = !empty($req['questionSurvey'][5]['answers']['radio']) ? $req['questionSurvey'][5]['answers']['radio'] : '-1';

            $param['csat_question6_id'] = !empty($info[5]['id']) ? $info[5]['id'] : '';
            $param['csat_question6_question'] = !empty($info[5]['question']) ? $info[5]['question'] : '';
            $param['csat_question6_point'] = !empty($req['questionSurvey'][6]['answers']['radio']) ? $req['questionSurvey'][6]['answers']['radio'] : '-1';

            $param['csat_question7_id'] = !empty($info[6]['id']) ? $info[6]['id'] : '';
            $param['csat_question7_question'] = !empty($info[6]['question']) ? $info[6]['question'] : '';
            $param['csat_question7_point'] = !empty($req['questionSurvey'][7]['answers']['radio']) ? $req['questionSurvey'][7]['answers']['radio'] : '-1';

            $param['csat_question8_id'] = !empty($info[7]['id']) ? $info[7]['id'] : '';
            $param['csat_question8_question'] = !empty($info[7]['question']) ? $info[7]['question'] : '';
            $param['csat_question8_point'] = !empty($req['questionSurvey'][8]['answers']['radio']) ? $req['questionSurvey'][8]['answers']['radio'] : '-1';

            $param['csat_question9_id'] = !empty($info[9]['id']) ? $info[9]['id'] : '';
            $param['csat_question9_question'] = !empty($info[9]['question']) ? $info[9]['question'] : '';
            $param['csat_question9_text'] = !empty($req['questionSurvey'][10]['answers']['radio']) ? $req['questionSurvey'][10]['answers']['radio'] : '-1';

            $param['csat_question10_id'] = !empty($info[10]['id']) ? $info[10]['id'] : '';
            $param['csat_question10_question'] = !empty($info[10]['question']) ? $info[10]['question'] : '';
            $param['csat_question10_point'] = !empty($req['questionSurvey'][11]['answers']['radio']) ? $req['questionSurvey'][11]['answers']['radio'] : '-1';

            $param['csat_question11_id'] = !empty($info[11]['id']) ? $info[11]['id'] : '';
            $param['csat_question11_question'] = !empty($info[11]['question']) ? $info[11]['question'] : '';
            $param['csat_question11_point'] = !empty($req['questionSurvey'][12]['answers']['radio']) ? $req['questionSurvey'][12]['answers']['radio'] : '-1';

            $param['csat_question12_id'] = !empty($info[12]['id']) ? $info[12]['id'] : '';
            $param['csat_question12_question'] = !empty($info[12]['question']) ? $info[12]['question'] : '';
            $param['csat_question12_answers'] = !empty($req['questionSurvey'][13]['answers']['checkbox']) ? $req['questionSurvey'][13]['answers']['checkbox'] : '-1';
            $param['csat_question12_note'] = !empty($req['questionSurvey'][13]['answers']['textarea']) ? $req['questionSurvey'][13]['answers']['textarea'] : '-1';

            $param['feedback_id'] = !empty($info[8]['id']) ? $info[8]['id'] : '';
            $param['feedback_question'] = !empty($info[8]['question']) ? $info[8]['question'] : '';
            $param['feedback'] = !empty($req['questionSurvey'][9]['answers']['textarea']) ? $req['questionSurvey'][9]['answers']['textarea'] : '';
            $result = $modelSurvey->insertSurveyFptvn($param);
            if (!$result) {
                return $help->responseFail(406, 'Insert dữ liệu không thành công');
            }
            return $help->responseSuccess('Đã insert dữ liệu thành công.');
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function insertOneRecord(Request $request) {
        $data = $request->all();
        $ids = explode(',', $data['id']);
        foreach ($ids as $id) {
            //chạy khoảng 20 records 1 lần
            $this->saveTableSurveySectionsReport($id, 'insert');
        }
    }

    public function updateOneRecord(Request $request) {
        $data = $request->all();
        $ids = explode(',', $data['id']);
        foreach ($ids as $id) {
            //chạy khoảng 20 records 1 lần
            $this->saveTableSurveySectionsReport($id, 'update');
        }
    }

    public function updateMultiRecordReports(Request $request) {
        $result = DB::table('survey_section_report')->select('section_id')
                ->where(function ($query) {
                    $query->where(DB::raw('csat_net_point'))
                    ->orWhere(DB::raw('csat_tv_point'));
                })
                ->where('section_survey_id', '=', 3)
                ->get();
        foreach ($result as $v) {
            $this->saveTableSurveySectionsReport($v->section_id, 'update');
        }
    }

    /**
     * API Rating Checklist HiFpt
     */
    public function ratingChecklistHifpt(Request $request) {
        $help = new HelpProvider();
        $modelHifpt = new SurveyHifpt();
        $modelSurveySections = new SurveySections();
        try {
            DB::beginTransaction();
            $data = $request->all();
            foreach ($data as $val) {
                if (empty($val['contract_no']) || empty($val['rating_number']) || empty($val['report_id'])) {
                    return $help->responseFail(406, 'Dữ liệu truyền vào không hợp lệ');
                }
                $date = date('Y-m-d H:i:s');
                $paramSection['section_survey_id'] = 5; //id rating hifpt
                $paramSection['section_contract_num'] = !empty($val['contract_no']) ? $val['contract_no'] : '';
                $paramSection['section_code'] = !empty($val['checklist_id']) ? $val['checklist_id'] : uniqid('hifpt_');
                $paramSection['section_note'] = !empty($val['comment']) ? $val['comment'] : '';
                $paramSection['section_time_start'] = $date;
                $paramSection['section_time_completed'] = (!empty($val['date_survey']) && $help->validateDate($val['date_survey'])) ? $val['date_survey'] : $date;
                $paramSection['section_report_id'] = $val['report_id'];

                $id = $modelHifpt->insertRatingHifpt($paramSection);

                $paramResult['survey_result_section_id'] = is_integer($id) ? $id : 0;
                $paramResult['survey_result_question_id'] = 18; //id câu hỏi rating hifpt
                $paramResult['survey_result_answer_id'] = !empty($val['rating_number']) ? $val['rating_number'] : '';
                $result = $modelHifpt->insertRatingResultHifpt($paramResult);

                if (!$result) {
                    DB::rollback();
                    return $help->responseFail(406, 'Insert dữ liệu không thành công');
                }
            }
            DB::commit();
            return $help->responseSuccess('Đã insert dữ liệu thành công.');
        } catch (Exception $e) {
            DB::rollback();
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Cron chạy hàng ngày lúc 23h59, fix các case khảo sát bị sót khi đưa dữ liệu sang bảng report
     */
    public function fixMissedSurveys() {
        $result = $this->checkMissedSurveys();
        $help = new HelpProvider();
        if (!empty($result)) {
            foreach ($result as $val) {
                $this->saveTableSurveySectionsReport($val->section_id, 'insert');
            }
        }
        return $help->responseSuccess('Đã cập nhật thông tin qua bảng report');
    }

    private function checkMissedSurveys() {
        $dateStart = date('Y-m-d 00:00:00');
        $dateEnd = date('Y-m-d 23:30:00');
        $result = DB::table('outbound_survey_sections')
                ->select('section_id')
                ->whereNotIn('section_id', function($query) use ($dateStart, $dateEnd) {
                    $query->select('r.section_id')
                    ->from('survey_section_report AS r')
                    ->where('r.section_time_completed', '>=', $dateStart)
                    ->where('r.section_time_completed', '<=', $dateEnd)
                    ->whereIn('r.section_survey_id', [1, 2, 3]);
                })
                ->where('section_time_completed', '>=', $dateStart)
                ->where('section_time_completed', '<=', $dateEnd)
                ->whereIn('section_survey_id', [1, 2, 3])
                ->get();
        return $result;
    }

    public function fixMissedSurveysTime($dayFrom, $dayTo) {
        try {
            $result = $this->checkMissedSurveysTime($dayFrom, $dayTo);
            $help = new HelpProvider();
            if (!empty($result)) {
                foreach ($result as $val) {
                    $this->saveTableSurveySectionsReport($val->section_id, 'insert');
                }
            }
            return $help->responseSuccess('Đã cập nhật thông tin qua bảng report');
        } catch (\Exception $e) {
            dump($e->getMessage());
            die;
        }
    }

    private function checkMissedSurveysTime($dayFrom, $dayTo) {
        $dateStart = $dayFrom . ' 00:00:00';
        $dateEnd = $dayTo . ' 23:30:00';
        $result = DB::table('outbound_survey_sections')
                ->select('section_id')
                ->whereNotIn('section_id', function($query) use ($dateStart, $dateEnd) {
                    $query->select('r.section_id')
                    ->from('survey_section_report AS r')
                    ->where('r.section_time_completed', '>=', $dateStart)
                    ->where('r.section_time_completed', '<=', $dateEnd)
                    ->whereIn('r.section_survey_id', [1, 2, 3]);
                })
                ->where('section_time_completed', '>=', $dateStart)
                ->where('section_time_completed', '<=', $dateEnd)
                ->whereIn('section_survey_id', [1, 2, 3])
                ->get();
        return $result;
    }

    public function generateLinkEmailSurvey(Request $request) {
        header("Access-Control-Allow-Origin: *");
        $help = new HelpProvider();
        $input = $request->all();
        $result = null;
        $resCheck = $help->checkPost($input, $help->getCondition('generateLinkEmailSurvey'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }

        try {
            $key = 'ISC+R@D';
            $secKey = md5($input['ContractID'] . $input['TransactionID'] . $input['Type'] . $key);
            $domain = 'https://fpt.vn/khaosat/';
            $uri = 'danh-gia/' . $input['ContractID'] . '/' . $input['TransactionID'] . '/' . $input['Type'] . '/' . $secKey . '.html';
            $link = $domain . $uri;
            return $link;
        } catch (Exception $e) {
            return $help->responseFail($e->getCode(), $e->getMessage());
        }
    }

    public function saveInfoTransactionTabletOnlyTest(Request $request) {
        try {
            $allData = $request->input();
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

            foreach ($allData['data'] as $Key => $data) {
                if (!empty($data) && isset($data['ques_ans']) && !empty($data['ques_ans'])) {
                    $validateArray = ['questionID', 'answerID'];
                    $ques_ans = $data['ques_ans'];
                    $arrayError = [];
                    foreach ($ques_ans as $Key2 => $value2) {
                        foreach ($validateArray as $Key3 => $value3) {
                            //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                            if ((!isset($value2[$value3]) || $value2[$value3] == '')) {
                                if (!in_array($value3, $arrayError))
                                    array_push($arrayError, $value3);
                            }
                        }
                    }
                    //Thiếu TransactionInfo
                    if (!isset($data['survey_info']) || $data['survey_info'] == '') {
                        array_push($arrayError, 'Dữ liệu survey_info bị thiếu hoặc không có');
                    } else {
                        if (!isset($data['survey_info']['ContractId']) || $data['survey_info']['ContractId'] == '') {
                            array_push($arrayError, 'Dữ liệu ContractId bị thiếu hoặc không có');
                        }

                        if (!isset($data['survey_info']['ContractNumber']) || $data['survey_info']['ContractNumber'] == '') {
                            array_push($arrayError, 'Dữ liệu ContractNumber bị thiếu hoặc không có');
                        }


                        if (!isset($data['survey_info']['TransactionId']) || $data['survey_info']['TransactionId'] == '') {
                            array_push($arrayError, 'Dữ liệu TransactionId bị thiếu hoặc không có');
                        }

                        if (!isset($data['survey_info']['Key']) || $data['survey_info']['Key'] == '') {
                            array_push($arrayError, 'Dữ liệu Key bị thiếu hoặc không có');
                        }
                    }

                    //Validate thành công
                    if (empty($arrayError)) {
                        array_push($messageSuccessUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                            'TransactionId' => $data['survey_info']['TransactionId'],
                            'Key' => $data['survey_info']['Key']]);
                    } else {
                        array_push($messageValidatePerTransaction, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                            'TransactionId' => isset($data['survey_info']['TransactionId']) ? $data['survey_info']['TransactionId'] : '',
                            'Key' => isset($data['survey_info']['Key']) ? $data['survey_info']['Key'] : null,
                            'Message' => implode(';', $arrayError)
                        ]);
                        continue;
                    }
                } else {
                    array_push($messageValidatePerTransaction, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                        'TransactionId' => isset($data['survey_info']['TransactionId']) ? $data['survey_info']['TransactionTransactionIdCode'] : '',
                        'Key' => isset($data['survey_info']['Key']) ? $data['survey_info']['Key'] : null,
                        'Message' => 'Dữ liệu ques_ans, contract  bị thiếu hoặc không có '
                    ]);
                    continue;
                }
            }
            $dataApi = [
                'id' => 'success',
                'status' => '200',
                'detail' => ['ErrorRecord' => ($messageValidatePerTransaction != '') ? $messageValidatePerTransaction : 'None',
                    'SucessRecord' => ($messageSuccessUpdate != '') ? $messageSuccessUpdate : 'None',
                    'FailRecord' => ($messageErrorUpdate != '') ? $messageErrorUpdate : 'None',
                ]
            ];
            $status = 200;
            return response()->json($dataApi, $status);
        } catch (Exception $ex) {
            $dataApi = [
                'id' => 'fail',
                'status' => '500',
                'detail' => $ex->getMessage(),
            ];
            $status = 500;
            return response()->json($dataApi, $status);
        }
    }
}
