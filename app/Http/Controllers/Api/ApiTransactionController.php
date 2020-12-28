<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Component\HelpProvider;
use App\Http\Controllers\Controller;
use App\Models\SurveySections;
use App\Models\Surveys;
use App\Models\SurveyResult;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Apiisc;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Models\OutboundAccount;
use App\Models\ContactProfile;
use App\Models\SurveySectionsEmail;
use App\Models\ApiTransactionLog;
use App\Models\OutboundQuestions;
use App\Models\AccountProfiles;
use Illuminate\Support\Facades\Redis;
use App\Models\Api\ApiHelper;

class ApiTransactionController extends Controller {

    //Lấy thông tin giao dịch, hợp đồng này có NPS hay không, trả về màn hình khảo sát của FPT.vn
    public function getInfoContractQGD(Request $request) {
        try {
            $data = $request->input();
            //Giả định mảng tham số bắt buộc phải gửi qua
            $arrayValidate = ['ContractId', 'TransactionCode', 'Type', 'SecureCode'];
            $arrayError = [];
            foreach ($arrayValidate as $key => $value) {
                //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                if (!isset($data[$value]) || $data[$value] == '')
                    array_push($arrayError, $value);
//                $error.=$value . ', ';
            }
            //Đầy đủ dữ liệu , không có lỗi
            if (empty($arrayError)) {
                $authorizeValidate = md5($data['ContractId'] . $data['TransactionCode'] . $data['Type'] . 'fptvn&survey');
                if ($authorizeValidate == false || $authorizeValidate != $data['SecureCode']) {
                    if ($authorizeValidate == false) {
                        $message = 'Qua trinh tinh toan SecureCode bi loi';
                    } else {
                        $message = 'Mã SecureCode không đúng';
                    }
                    return $this->returnFailRequest('503', 500, $message);
                }
                //Gọi Api ISC
                $api = new Apiisc();
                $arraySentToISC = array(
                    'ContractId' => $data['ContractId'],
                    'transactionId' => $data['TransactionCode'],
                    'key' => $data['Type']
                );
                $resultReturn = $api->GetInforContractQGDApi($arraySentToISC);
                //Gọi qua ISC thất bại
                if ($resultReturn['success'] == false) {
                    return $this->returnFailRequest('500', 500, $resultReturn['result']);
                }
                $mainData = $resultReturn['result'];
                $returnDataIsc = (isset($mainData->data) && $mainData->data != null ) ? $mainData->data : null;
                $data['TransactionInfo'] = $returnDataIsc;
                if ($data['TransactionInfo'] != null) {
                    $data['TransactionInfo'] = (array) $data['TransactionInfo'];
                    $data['TransactionInfo']['ObjID'] = $data['ContractId'];
                    $data['TransactionInfo'] = (object) $data['TransactionInfo'];
                }
                unset($data['SecureCode']);
                $type = in_array($data['Type'], [1, 2, 3, 6, 7, 8, 9]) ? 4 : 7;
                if ($type == 4) {
                    $dataApi = [
                        'id' => 'success',
                        'status' => '200',
                        'detail' => ($data),
                        'ques_ans' => $this->getQuesAns($type),
                        'nps' => ($returnDataIsc == null) ? false : $this->checkNPS($data['TransactionInfo']->ContractNumber),
                    ];
                } else {
                    $dataApi = [
                        'id' => 'success',
                        'status' => '200',
                        'detail' => ($data),
                        'ques_ans' => $this->getQuesAns($type),
                    ];
                }
                $status = 200;

                return response()->json($dataApi, $status);
            } else {
                return $this->returnFailRequest('503', 500, 'Truong ' . implode(',', $arrayError) . ' bi thieu hoac khong co du lieu');
            }
        } catch (Exception $e) {
            return $this->returnFailRequest('500', 500, $e->getMessage());
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
            $apiLog->survey_id = isset($allData['data'][0]['contract']['Type']) ? (in_array($allData['data'][0]['contract']['Type'], [1, 2, 3, 6, 7, 8, 9]) ? 4 : 7) : null;
            $apiLog->source = $source;
            $apiLog->input = $input;
            $apiLog->save();
            $outboundAccount = new OutboundAccount();
            $messageValidatePerTransaction = [];
            $messageErrorUpdate = [];
            $messageSuccessUpdate = [];
            if (!isset($allData['data']) || $allData['data'] == '') {
                return $this->returnFailRequest('503', 500, 'Thiếu data đầu vào');
            }
            foreach ($allData['data'] as $key => $data) {
                if (!empty($data) && isset($data['ques_ans']) && !empty($data['ques_ans']) && isset($data['contract']) && !empty($data['contract'])) {
                    $validateArray = ['questionID', 'answerID', 'note'];
                    $ques_ans = $data['ques_ans'];
                    $arrayError = [];
                    foreach ($ques_ans as $key2 => $value2) {
                        foreach ($validateArray as $key3 => $value3) {
                            if ($value3 == 'note') {
                                if ((!isset($value2[$value3]))) {
                                    if (!in_array($value3, $arrayError))
                                        array_push($arrayError, "Dữ liệu " . $value3 . " bị thiếu");
                                }
                            }
                            else {
                                //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                                if ((!isset($value2[$value3]) || $value2[$value3] == '')) {
                                    if (!in_array($value3, $arrayError))
                                        array_push($arrayError, "Dữ liệu " . $value3 . " bị thiếu");
                                }
                            }
                        }
                    }
                    //Thiếu TransactionInfo
                    if (!isset($data['contract']['TransactionInfo']) || $data['contract']['TransactionInfo'] == '') {
                        array_push($arrayError, 'Dữ liệu TransactionInfo bị thiếu hoặc không có');
                    } else {
                        if (!isset($data['contract']['TransactionInfo']['ContractNumber']) || $data['contract']['TransactionInfo']['ContractNumber'] == '') {
                            array_push($arrayError, 'Dữ liệu ContractNumber bị thiếu hoặc không có');
                        }


                        if (!isset($data['contract']['TransactionCode']) || $data['contract']['TransactionCode'] == '') {
                            array_push($arrayError, 'Dữ liệu TransactionCode bị thiếu hoặc không có');
                        }

                        if (!isset($data['contract']['Type']) || $data['contract']['Type'] == '') {
                            array_push($arrayError, 'Dữ liệu Type bị thiếu hoặc không có');
                        }
                    }

                    //Validate thành công
                    if (empty($arrayError)) {
                        DB::beginTransaction();
                        $surveySection = new SurveySections();
                        $dataQGD = $data['contract'];
                        $dataTransactionInfo = $dataQGD['TransactionInfo'];
                        $type = in_array($dataQGD['Type'], [1, 2, 3, 6, 7, 8, 9]) ? 4 : 7;
                        $resultCodes = $surveySection->checkExistCodes($dataQGD['TransactionCode'], $type, $dataTransactionInfo['ContractNumber']);
                        //Đã lưu thông tin rồi thì cập nhập                      
                        if (!empty($resultCodes)) {
                            //Xóa dữ liệu cũ
                            DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $resultCodes[0]->section_id)->delete();
                            foreach ($ques_ans as $key => $value) {
                                $flagSucess = true;
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $resultCodes[0]->section_id;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
                                    array_push($messageErrorUpdate, ['ContractId' => isset($dataQGD['ContractId']) ? $dataQGD['ContractId'] : '',
                                        'TransactionCode' => $dataQGD['TransactionCode'],
                                        'Type' => $dataQGD['Type'],
                                        'Message' => 'Lưu khảo sát thất bại']);
                                    $flagSucess = false;
                                    DB::rollback();
                                    break;
                                }
                            }
                            if ($flagSucess) {
                                array_push($messageSuccessUpdate, ['ContractId' => isset($dataQGD['ContractId']) ? $dataQGD['ContractId'] : '',
                                    'TransactionCode' => $dataQGD['TransactionCode'],
                                    'Type' => $dataQGD['Type']]);
                                DB::commit();
                            }
                            continue;
                        }
                        // lấy thông tin khách hàng trong database survey
                        $accountInfo = $outboundAccount->getAccountInfoByContractNum($dataTransactionInfo['ContractNumber']);
                        // update hoặc insert thông tin khách hàng
                        $dataTransactionInfo['ContractNum'] = $dataTransactionInfo['ContractNumber'];
                        unset($dataTransactionInfo['ContractNumber']);
                        $resultOutboundAccount = $outboundAccount->saveAccount($dataTransactionInfo);
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
                        $surveySection = $this->assignDataInfoTransaction($surveySection, $dataQGD, $resultOutboundAccount, $dataTransactionInfo, $type);
                        //Lưu thành công thông tin giao dịch
                        if ($surveySection->save()) {
                            $idDetail = $surveySection->section_id;
                            $flagSucess = true;
                            foreach ($ques_ans as $key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $idDetail;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
                                    $flagSucess = false;
                                    array_push($messageErrorUpdate, ['ContractId' => isset($dataQGD['ContractId']) ? $dataQGD['ContractId'] : '',
                                        'TransactionCode' => $dataQGD['TransactionCode'],
                                        'Type' => $dataQGD['Type'],
                                        'Message' => 'Lưu khảo sát thất bại']);
                                    DB::rollback();
                                    break;
                                }
                            }
                            if (!$flagSucess) {
                                continue;
                            }
                            $surveySectionEmail = new SurveySectionsEmail();
                            $surveySectionEmail->section_id = $idDetail;
                            $surveySectionEmail->section_time_start_transaction = isset($dataTransactionInfo['ThoiGianGiaoDich']) ? $dataTransactionInfo['ThoiGianGiaoDich'] : null;
                            $surveySectionEmail->section_user_create_transaction = isset($dataTransactionInfo['NguoiTaoGD']) ? $dataTransactionInfo['NguoiTaoGD'] : null;
                            $surveySectionEmail->section_name_change = isset($dataTransactionInfo['NameChange']) ? $dataTransactionInfo['NameChange'] : null;
                            $surveySectionEmail->section_office = isset($dataTransactionInfo['Office']) ? $dataTransactionInfo['Office'] : null;
                            $surveySectionEmail->section_kind_service = isset($dataTransactionInfo['KindGD']) ? $dataTransactionInfo['KindGD'] : null;
                            if ($surveySectionEmail->save()) {
                                //Lưu thành công cả 2 bảng
                                array_push($messageSuccessUpdate, ['ContractId' => isset($dataQGD['ContractId']) ? $dataQGD['ContractId'] : '',
                                    'TransactionCode' => $dataQGD['TransactionCode'],
                                    'Type' => $dataQGD['Type']]);
                                DB::commit();

                                $apiHelp = new ApiHelper();
                                $paramCheck['sectionId'] = $idDetail;
                                $resCheck = $apiHelp->checkSendMail($paramCheck);
                                if ($resCheck['status']) {
                                    Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
                                }
                            } else {
                                array_push($messageErrorUpdate, ['ContractId' => isset($dataQGD['ContractId']) ? $dataQGD['ContractId'] : '',
                                    'TransactionCode' => $dataQGD['TransactionCode'],
                                    'Type' => $dataQGD['Type'],
                                    'Message' => 'Lưu khảo sát thất bại']);
                                DB::rollback();
                                continue;
                            }
                        } else {
                            array_push($messageErrorUpdate, ['ContractId' => isset($dataTransactionInfo['ContractId']) ? $dataTransactionInfo['ContractId'] : '',
                                'TransactionCode' => $dataQGD['TransactionCode'],
                                'Type' => $dataQGD['Type'],
                                'Message' => 'Lưu khảo sát thất bại'
                                    ]
                            );
                            DB::rollback();
                            continue;
                        }
                    } else {
                        array_push($messageValidatePerTransaction, ['ContractId' => isset($data['contract']['ContractId']) ? $data['contract']['ContractId'] : '',
                            'TransactionCode' => isset($data['contract']['TransactionCode']) ? $data['contract']['TransactionCode'] : '',
                            'Type' => isset($data['contract']['Type']) ? $data['contract']['Type'] : null,
                            'Message' => implode(';', $arrayError)
                        ]);
                        continue;
                    }
                } else {

                    array_push($messageValidatePerTransaction, ['ContractId' => isset($data['contract']['ContractId']) ? $data['contract']['ContractId'] : '',
                        'TransactionCode' => isset($data['contract']['TransactionCode']) ? $data['contract']['TransactionCode'] : '',
                        'Type' => isset($data['contract']['Type']) ? $data['contract']['Type'] : null,
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
//            }
            //Không có dữ liệu gửi qua, hoặc gửi không đủ dữ liệu
            //ques_ans:Bộ câu hỏi, trả lời khảo sát, dạng mảng
            //contract:Thông tin giao dịch trả về từ api đầu tiên
        } catch (Exception $ex) {
            DB::rollback();
            return $this->returnFailRequest('500', 500, $ex->getMessage());
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

    public function saveInfoTransactionCounter(Request $request) {
        try {
            header("Access-Control-Allow-Origin: *");
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
                return $this->returnFailRequest('503', 500, 'Thiếu data đầu vào');
            }

            foreach ($allData['data'] as $key => $data) {
                if (isset($data['ques_ans']) && !empty($data['ques_ans']) && isset($data['survey_info']) && !empty($data['survey_info'])) {
                    $validateArray = ['questionID', 'answerID', 'note'];
                    $ques_ans = $data['ques_ans'];
                    $arrayError = [];
                    foreach ($ques_ans as $key2 => $value2) {
                        foreach ($validateArray as $key3 => $value3) {
                            if ($value3 == 'note') {
                                if ((!isset($value2[$value3]))) {
                                    if (!in_array($value3, $arrayError))
                                        array_push($arrayError, "Dữ liệu " . $value3 . " bị thiếu");
                                }
                            }
                            else {
                                //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
                                if ((!isset($value2[$value3]) || $value2[$value3] == '')) {
                                    if (!in_array($value3, $arrayError))
                                        array_push($arrayError, "Dữ liệu " . $value3 . " bị thiếu");
                                }
                            }
                        }
                    }

                    //Thiếu TransactionInfo
                    if (!isset($data['survey_info']) || $data['survey_info'] == '') {
                        array_push($arrayError, 'Dữ liệu survey_info bị thiếu hoặc không có');
                    } else {
                        if (!isset($data['survey_info']['ContractNumber']) || $data['survey_info']['ContractNumber'] == '') {
                            array_push($arrayError, 'Dữ liệu ContractNum bị thiếu hoặc không có');
                        }
                        if (!isset($data['survey_info']['SectionCode']) || $data['survey_info']['SectionCode'] == '') {
                            array_push($arrayError, 'Dữ liệu SectionCode bị thiếu hoặc không có');
                        }
                        if (!isset($data['survey_info']['Type']) || $data['survey_info']['Type'] == '') {
                            array_push($arrayError, 'Dữ liệu Type bị thiếu hoặc không có');
                        }
                    }

                    //Validate thành công
                    if (empty($arrayError)) {
                        DB::beginTransaction();
                        $surveySection = new SurveySections();
                        $resultCodes = $surveySection->checkExistCodes($data['survey_info']['SectionCode'], 8, $data['survey_info']['ContractNumber']);

                        //Đã lưu thông tin rồi nên bỏ qua
                        if (!empty($resultCodes)) {
                            $flagSuccess = true;
                            //Xóa dữ liệu cũ
                            DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $resultCodes[0]->section_id)->delete();
                            foreach ($ques_ans as $key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $resultCodes[0]->section_id;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                $surveyResult->survey_result_note = $value['note'];
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
                                    array_push($messageErrorUpdate, ['ContractNum' => $dataTransactionInfo['ContractNumber'],
                                        'TransactionCode' => $dataQGD['TransactionCode'],
                                        'Type' => $data['survey_info']['Type'],
                                        'Message' => 'Lỗi hệ thống']);
                                    $flagSuccess = false;
                                    DB::rollback();
                                    break;
                                }
                            }
                            if ($flagSuccess) {
                                array_push($messageSuccessUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'],
                                    'SectionCode' => $data['survey_info']['SectionCode'],
                                    'Type' => $data['survey_info']['Type']]);
                                DB::commit();
                            }
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
//                    var_dump(json_decode($responseAccountInfo['result'], true));die;
                        $responseAccountInfo = json_decode($responseAccountInfo['result'], true)['data'];


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
                        $surveySection = $this->assignDataCounter($surveySection, $data, $outboundAccount, $dataTransactionInfo, $modelOutboundAccount);
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
                                    $flagSuccess = false;
                                    array_push($messageErrorUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'],
                                        'SectionCode' => $data['survey_info']['SectionCode'],
                                        'Type' => $data['survey_info']['Type'],
                                        'Message' => 'Lỗi hệ thống']);
                                    DB::rollback();
                                    break;
                                }
                            }
                            if ($flagSuccess) {
                                array_push($messageSuccessUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'],
                                    'SectionCode' => $data['survey_info']['SectionCode'],
                                    'Type' => $data['survey_info']['Type']]);
                                DB::commit();
                            }
                            continue;
                        } else {
                            array_push($messageErrorUpdate, ['ContractNum' => $data['survey_info']['ContractNumber'],
                                'SectionCode' => $data['survey_info']['SectionCode'],
                                'Type' => $data['survey_info']['Type'],
                                'Message' => 'Lỗi hệ thống']);
                            DB::rollback();
                            continue;
                        }
                    } else {
                        array_push($messageValidatePerTransaction, ['ContractNum' => isset($data['survey_info']['ContractNumber']) ? $data['survey_info']['ContractNumber'] : null,
                            'SectionCode' => isset($data['survey_info']['SectionCode']) ? $data['survey_info']['SectionCode'] : null,
                            'Type' => isset($data['survey_info']['Type']) ? $data['survey_info']['Type'] : null,
                            'Message' => implode(';', $arrayError)]);
                        continue;
                    }
                } else {
                    $message = '';
                    if (!isset($data['ques_ans']) || empty($data['ques_ans']))
                        $message.='Thiếu ques_ans truyền vào hoặc truyền vào bằng rỗng';
                    else
                        $message.='Thiếu survey_info truyền vào hoặc truyền vào bằng rỗng';
                    array_push($messageValidatePerTransaction, $message);
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
            //Không có dữ liệu gửi qua, hoặc gửi không đủ dữ liệu
            //ques_ans:Bộ câu hỏi, trả lời khảo sát, dạng mảng
            //contract:Thông tin giao dịch trả về từ api đầu tiên
        } catch (Exception $ex) {
            DB::rollback();
            return $this->returnFailRequest('500', 500, $ex->getMessage());
        }
    }

//Lưu thông tin khảo sát và giao dịch đẩy qua từ FPT.vn
    public function saveInfoTransactionTablet(Request $request) {
        try {
            $allData = $request->input();
            $input = json_encode($allData['data']);
            //Lưu log gọi api
            $source = 'ApiTransactionController/saveInfoTransactiontTablet';
            $apiLog = new ApiTransactionLog();
            $apiLog->survey_id = 4;
            $apiLog->source = $source;
            $apiLog->input = $input;
            $apiLog->save();
            $outboundAccount = new OutboundAccount();
            $messageValidatePerTransaction = [];
            $messageErrorUpdate = [];
            $messageSuccessUpdate = [];
            if (!isset($allData['data']) || $allData['data'] == '') {
                return $this->returnFailRequest('503', 500, 'Thiếu data đầu vào');
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

                        DB::beginTransaction();
                        $surveySection = new SurveySections();
                        $type = 4;
                        $resultCodes = $surveySection->checkExistCodes($data['survey_info']['TransactionId'], $type, $data['survey_info']['ContractNumber'], 6);
                        //Đã lưu thông tin rồi thì cập nhập                      
                        if (!empty($resultCodes)) {
                            //Xóa dữ liệu cũ
                            DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $resultCodes[0]->section_id)->delete();
                            foreach ($ques_ans as $Key => $value) {
                                $flagSucess = true;
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $resultCodes[0]->section_id;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                //Rất ko hài lòng, ko hài lòng
                                if ($value['answerID'] == 1 || $value['answerID'] == 2) {
                                    $surveyResult->survey_result_answer_extra_id = isset($value['answerExtraID']) ? $value['answerExtraID'] : null;
                                    $surveyResult->survey_result_note = isset($value['note']) ? $value['note'] : null;
                                }
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
                                    array_push($messageErrorUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                                        'TransactionId' => $data['survey_info']['TransactionId'],
                                        'Key' => $data['survey_info']['Key'],
                                        'Message' => 'Lưu khảo sát thất bại']);
                                    $flagSucess = false;
                                    DB::rollback();
                                    break;
                                }
                            }
                            if ($flagSucess) {
                                array_push($messageSuccessUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                                    'TransactionId' => $data['survey_info']['TransactionId'],
                                    'Key' => $data['survey_info']['Key']]);
                                DB::commit();
                            }
                            continue;
                        }
//                             //Gọi Api ISC
                        $api = new Apiisc();
                        $arraySentToISC = array(
                            'ContractId' => $data['survey_info']['ContractId'],
                            'TransactionId' => $data['survey_info']['TransactionId'],
                            'Key' => $data['survey_info']['Key']
                        );
                        $resultReturn = $api->GetInforContractQGDApi($arraySentToISC);
//                        var_dump($resultReturn['result']);die;
                        $dataTransactionInfo = (array) $resultReturn['result']->data;
                        if ($dataTransactionInfo == null) {
                            $dataApi = [
                                'id' => 'fail',
                                'status' => '500',
                                'detail' => 'Dữ liệu data bằng null trả về từ api GetInforContractQGD',
                            ];
                            $status = 500;
                            return response()->json($dataApi, $status);
                        }
                        // lấy thông tin khách hàng trong database survey
                        $accountInfo = $outboundAccount->getAccountInfoByContractNum($data['survey_info']['ContractNumber']);
                        // update hoặc insert thông tin khách hàng
                        $dataTransactionInfo['ContractNum'] = $data['survey_info']['ContractNumber'];
                        unset($dataTransactionInfo['ContractNumber']);
                        $resultOutboundAccount = $outboundAccount->saveAccount($dataTransactionInfo);
                        if (empty($accountInfo->contract_num)) { // nếu chưa có thông tin khách hàng
                            $this->saveAccountProfiles($dataTransactionInfo);
                        } else {
                            // nếu tìm thấy thông tin khách hàng kiểm tra các thông tin
                            // Họ và tên, Ngày tháng năm sinh, giới tính, địa chỉ trên CMND, địa chỉ lắp đặt, địa chỉ thanh toán
                            // nếu các thông tin trên đã được lưu trong database survey và các thông tin này giống với thông tin đã gọi API của lần trước thì lấy thông tin trong database survey
                            // nếu thông tin này là khác với thông tin đã gọi API lần trước thì lưu mới thông tin khách hàng = thông tin API mới
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
                        $surveySection = $this->assignDataTablet($surveySection, $type, $data, $resultOutboundAccount, $dataTransactionInfo);
                        //Lưu thành công thông tin giao dịch
                        if ($surveySection->save()) {
                            $idDetail = $surveySection->section_id;
                            $flagSucess = true;
                            foreach ($ques_ans as $Key => $value) {
                                $surveyResult = new SurveyResult();
                                $surveyResult->survey_result_section_id = $idDetail;
                                $surveyResult->survey_result_question_id = $value['questionID'];
                                $surveyResult->survey_result_answer_id = $value['answerID'];
                                //Rất ko hài lòng, ko hài lòng
                                if ($value['answerID'] == 1 || $value['answerID'] == 2) {
                                    $surveyResult->survey_result_answer_extra_id = isset($value['answerExtraID']) ? $value['answerExtraID'] : null;
                                    $surveyResult->survey_result_note = isset($value['note']) ? $value['note'] : null;
                                }
                                //Lưu thất bại chi tiết khảo sát giao dịch đó
                                if (!$surveyResult->save()) {
                                    $flagSucess = false;
                                    array_push($messageErrorUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                                        'TransactionId' => $data['survey_info']['TransactionId'],
                                        'Key' => $data['survey_info']['Key'],
                                        'Message' => 'Lưu khảo sát thất bại']);
                                    DB::rollback();
                                    break;
                                }
                            }
                            if (!$flagSucess) {
                                continue;
                            }
                            $surveySectionEmail = new SurveySectionsEmail();
                            $surveySectionEmail->section_id = $idDetail;
                            $surveySectionEmail->section_time_start_transaction = isset($dataTransactionInfo['ThoiGianGiaoDich']) ? $dataTransactionInfo['ThoiGianGiaoDich'] : null;
                            $surveySectionEmail->section_user_create_transaction = isset($dataTransactionInfo['NguoiTaoGD']) ? $dataTransactionInfo['NguoiTaoGD'] : null;
                            if ($surveySectionEmail->save()) {
                                //Lưu thành công cả 2 bảng
                                array_push($messageSuccessUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                                    'TransactionId' => $data['survey_info']['TransactionId'],
                                    'Key' => $data['survey_info']['Key']]);
                                DB::commit();

                                $apiHelp = new ApiHelper();
                                $paramCheck['sectionId'] = $idDetail;
                                $resCheck = $apiHelp->checkSendMail($paramCheck);
                                if ($resCheck['status']) {
                                    Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
                                }
                            } else {
                                array_push($messageErrorUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                                    'TransactionId' => $data['survey_info']['TransactionId'],
                                    'Key' => $data['survey_info']['Key'],
                                    'Message' => 'Lưu khảo sát thất bại']);
                                DB::rollback();
                                continue;
                            }
                        } else {
                            array_push($messageErrorUpdate, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                                'TransactionId' => $data['survey_info']['TransactionId'],
                                'Key' => $data['survey_info']['Key'],
                                'Message' => 'Lưu khảo sát thất bại'
                                    ]
                            );
                            DB::rollback();
                            continue;
                        }
                    } else {
                        array_push($messageValidatePerTransaction, ['ContractId' => isset($data['survey_info']['ContractId']) ? $data['survey_info']['ContractId'] : '',
                            'TransactionId' => isset($data['survey_info']['TransactionId']) ? $data['survey_info']['TransactionId'] : '',
                            'Key' => isset($data['survey_info']['Key']) ? $data['survey_info']['Key'] : null,
                            'Message' => 'Dữ liệu ' . implode(';', $arrayError) . ' bị lỗi hoặc thiếu'
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
//            }
            //Không có dữ liệu gửi qua, hoặc gửi không đủ dữ liệu
            //ques_ans:Bộ câu hỏi, trả lời khảo sát, dạng mảng
            //contract:Thông tin giao dịch trả về từ api đầu tiên
        } catch (Exception $ex) {
            DB::rollback();
            return $this->returnFailRequest('500', 500, $ex->getMessage());
        }
    }

    private function processDataFromISC($data) {
        $dateFormat = 'Y-m-d H:i:s'; //config('app.datetime_format');
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

    private function getQuesAns($type) {
        $question = new OutboundQuestions();
        $resultSet = $question->getQuestionAnswer($type);
        return $resultSet;
    }

    private function saveAccountProfiles($accountCurrent, $accountStored = NULL) {
//        dump($accountCurrent["CustomerName"]);die;
        $AccountProfiles = new AccountProfiles;
        //$accountCurrent = (array)$accountCurrent;
        $accountStored = (array) $accountStored;
        if (empty($accountStored['contract_num'])) {
            $accountProfiles = array(
                "ap_contract" => isset($accountCurrent['ContractNum']) ? $accountCurrent['ContractNum'] : '',
                "ap_fullname" => isset($accountCurrent["CustomerName"]) ? $accountCurrent['CustomerName'] : '',
                "ap_birthday" => isset($accountCurrent["Birthday"]) ? $accountCurrent['Birthday'] : '',
                "ap_sex" => isset($accountCurrent["Sex"]) ? $accountCurrent['Sex'] : 1,
                "ap_address_id" => isset($accountCurrent["Address"]) ? $accountCurrent['Address'] : '',
                "ap_address_bill" => isset($accountCurrent["BillTo"]) ? $accountCurrent['BillTo'] : '',
                "ap_address_setup" => isset($accountCurrent["ObjAddress"]) ? $accountCurrent['ObjAddress'] : '',
//                "ap_user_update" => Auth::user()->id
            );
            $AccountProfiles->insertAccountProfiles($accountProfiles);
        } else {
            $accountProfiles = array();
            if ($accountCurrent["CustomerName"] != $accountStored['customer_name']) {
                $accountProfiles['ap_fullname'] = isset($accountCurrent["CustomerName"]) ? $accountCurrent['CustomerName'] : '';
            }
            if ($accountCurrent["Birthday"] != $accountStored['birthday']) {
                $accountProfiles['ap_birthday'] = isset($accountCurrent["Birthday"]) ? $accountCurrent['Birthday'] : '';
            }
//            if ($accountCurrent["Sex"] != $accountStored['sex']) {
//                $accountProfiles['ap_sex'] = $accountCurrent["Sex"];
//            }
            if ($accountCurrent["Address"] != $accountStored['address']) {
                $accountProfiles['ap_address_id'] = isset($accountCurrent["Address"]) ? $accountCurrent['Address'] : '';
            }
            if ($accountCurrent["BillTo"] != $accountStored['address_bill_to']) {
                $accountProfiles['ap_address_bill'] = isset($accountCurrent["BillTo"]) ? $accountCurrent['BillTo'] : '';
            }
            if ($accountCurrent["ObjAddress"] != $accountStored['obj_address']) {
                $accountProfiles['ap_address_setup'] = isset($accountCurrent["ObjAddress"]) ? $accountCurrent['ObjAddress'] : '';
            }
            $AccountProfiles->updateAccountProfiles($accountCurrent['ContractNum'], $accountProfiles);
        }
    }

    private function assignDataInfoTransaction($surveySection, $dataQGD, $resultOutboundAccount, $dataTransactionInfo, $type) {
        $surveySection->section_code = $dataQGD['TransactionCode'];
        $surveySection->section_account_id = $resultOutboundAccount['data']->id;
        $surveySection->section_contract_num = isset($dataTransactionInfo['ContractNum']) ? $dataTransactionInfo['ContractNum'] : null;
        $surveySection->section_customer_name = isset($dataTransactionInfo['CustomerName']) ? $dataTransactionInfo['CustomerName'] : null;
        $surveySection->section_survey_id = $type;
        $surveySection->section_record_channel = 2;
        $surveySection->sale_center_id = 3;
        $surveySection->section_phone = isset($dataTransactionInfo['Phone']) ? $dataTransactionInfo['Phone'] : null;
        //$surveySection->section_note = $dataQGD['note'];
        $surveySection->section_objAddress = isset($dataTransactionInfo['Address']) ? $dataTransactionInfo['Address'] : null;
        $surveySection->section_sub_parent_desc = isset($dataTransactionInfo['SubParentDesc']) ? $dataTransactionInfo['SubParentDesc'] : null;
        $surveySection->section_location = isset($dataTransactionInfo['ChiNhanh']) ? $dataTransactionInfo['ChiNhanh'] : null;
        $surveySection->section_fee_local_type = isset($dataTransactionInfo['ContractTypeName']) ? $dataTransactionInfo['ContractTypeName'] : null;

        $surveySection->section_time_start = date('y-m-d H:i:s', strtotime($dataQGD['SectionTimeStart']));
        $surveySection->section_time_completed = date('y-m-d H:i:s', strtotime($dataQGD['SectionTimeCompleted']));
        $surveySection->section_time_completed_int = strtotime($surveySection->section_time_completed);


        $surveySection->section_connected = 4;
        $surveySection->section_action = 1;
        $surveySection->section_region = isset($dataTransactionInfo['Region']) ? $dataTransactionInfo['Region'] : null;
        $surveySection->section_location_id = isset($dataTransactionInfo['LocationID']) ? $dataTransactionInfo['LocationID'] : null;
        $surveySection->section_branch_code = isset($dataTransactionInfo['BranchCode']) ? $dataTransactionInfo['BranchCode'] : null;
        $surveySection->section_package_sal = isset($dataTransactionInfo['PackageSal']) ? $dataTransactionInfo['PackageSal'] : null;
        $surveySection->section_payment_type = isset($dataTransactionInfo['PaymentType']) ? $dataTransactionInfo['PaymentType'] : null;
        $surveySection->section_account_payment = isset($dataTransactionInfo['AccountPayment']) ? $dataTransactionInfo['AccountPayment'] : null;
        $surveySection->section_use_service = isset($dataTransactionInfo['UseService']) ? $dataTransactionInfo['UseService'] : null;
        return $surveySection;
    }

    private function assignDataCounter($surveySection, $data, $outboundAccount, $dataTransactionInfo, $modelOutboundAccount) {
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
        $surveySection->section_time_start = date('y-m-d H:i:s');
        $surveySection->section_time_completed = date('y-m-d H:i:s');
        $surveySection->section_time_completed_int = strtotime(date('y-m-d H:i:s'));
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
        return $surveySection;
    }

    private function assignDataTablet($surveySection, $type, $data, $resultOutboundAccount, $dataTransactionInfo) {
        $surveySection->section_code = $data['survey_info']['TransactionId'];
        $surveySection->section_account_id = $resultOutboundAccount['data']->id;
        $surveySection->section_contract_num = isset($dataTransactionInfo['ContractNum']) ? $dataTransactionInfo['ContractNum'] : null;
        $surveySection->section_customer_name = isset($dataTransactionInfo['CustomerName']) ? $dataTransactionInfo['CustomerName'] : null;
        $surveySection->section_survey_id = $type;
        $surveySection->section_record_channel = 6;
        $surveySection->sale_center_id = 3;
        $surveySection->section_phone = isset($dataTransactionInfo['Phone']) ? $dataTransactionInfo['Phone'] : null;
        //$surveySection->section_note = $dataQGD['note'];
        $surveySection->section_objAddress = isset($dataTransactionInfo['Address']) ? $dataTransactionInfo['Address'] : null;
        $surveySection->section_sub_parent_desc = isset($dataTransactionInfo['SubParentDesc']) ? $dataTransactionInfo['SubParentDesc'] : null;
        $surveySection->section_location = isset($dataTransactionInfo['ChiNhanh']) ? $dataTransactionInfo['ChiNhanh'] : null;
        $surveySection->section_fee_local_type = isset($dataTransactionInfo['ContractTypeName']) ? $dataTransactionInfo['ContractTypeName'] : null;

        $surveySection->section_time_start = date('y-m-d H:i:s', strtotime($data['survey_info']['SectionTimeStart']));
        $surveySection->section_time_completed = date('y-m-d H:i:s', strtotime($data['survey_info']['SectionTimeCompleted']));
        $surveySection->section_time_completed_int = strtotime($surveySection->section_time_completed);
        $surveySection->section_connected = 4;
        $surveySection->section_action = 1;
        $surveySection->section_region = isset($dataTransactionInfo['Region']) ? $dataTransactionInfo['Region'] : null;
        $surveySection->section_location_id = isset($dataTransactionInfo['LocationID']) ? $dataTransactionInfo['LocationID'] : null;
        $surveySection->section_branch_code = isset($dataTransactionInfo['BranchCode']) ? $dataTransactionInfo['BranchCode'] : null;
        $surveySection->section_package_sal = isset($dataTransactionInfo['PackageSal']) ? $dataTransactionInfo['PackageSal'] : null;
        $surveySection->section_payment_type = isset($dataTransactionInfo['PaymentType']) ? $dataTransactionInfo['PaymentType'] : null;
        $surveySection->section_account_payment = isset($dataTransactionInfo['AccountPayment']) ? $dataTransactionInfo['AccountPayment'] : null;
        $surveySection->section_use_service = isset($dataTransactionInfo['UseService']) ? $dataTransactionInfo['UseService'] : null;
        $surveySection->section_note = isset($data['survey_info']['Note']) ? $data['survey_info']['Note'] : '';
        return $surveySection;
    }

    private function returnFailRequest($inStatus, $outStatus, $message) {
        $dataApi = [
            'id' => 'fail',
            'status' => $inStatus,
            'detail' => $message,
        ];
        return response()->json($dataApi, $outStatus);
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
                    $logger = new Loggner('my_logger');
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


    // Lưu thông tin khảo sát đẩy qua từ HiFPT
    public function saveInfoHiFPT(Request $request) {
        $help = new HelpProvider();
        $input = $request->all();

        //Lưu log gọi api
        $source = 'ApiTransactionController/saveInfoHiFPT';
        $surveyId = 2;
        $apiLog = new ApiTransactionLog();
        $paramLog = [
            'survey_id' => $surveyId,
            'source' => $source,
            'input' => json_encode($input)
        ];
        $apiLog->insertApiLog($paramLog);

        $resCheck = $help->checkPost($input, $help->getCondition('saveInfoHiFPT'));
        if ($resCheck['status'] !== true) {
            return $help->responseFail($resCheck['status'], $resCheck['msg']);
        }

        $outboundAccount = new OutboundAccount();
        $arrayError = [];
        //validate bộ câu hỏi bộ câu trả lời
        $validateQuestionAnswerArray = ['QuestionID', 'AnswerID'];
        $questionAnswers = $input['QuestionAnswer'];
        foreach($questionAnswers as $questionAnswer){
            $this->validateApiHiFPTField($validateQuestionAnswerArray, $questionAnswer, $arrayError);
        }

        //validate bộ thông tin
        $validateHiFPTInfoArray = ['SectionTimeStart', 'SectionTimeCompleted', 'ContractNumber', 'SectionCode', 'Type', 'CustomerPhone', 'ContractPerson'];
        $hiFPTInfo = $input['HiFPTInfo'];
        $this->validateApiHiFPTField($validateHiFPTInfoArray, $hiFPTInfo, $arrayError);

        // Trả các lỗi thiếu thông tin
        if(!empty($arrayError)){
            return $help->responseFail(406, $arrayError);
        }

        $surveySection = new SurveySections();
        $surveyResult = new SurveyResult();

        $code = $hiFPTInfo['SectionCode'];
        $type = $hiFPTInfo['Type'];
        $contract = $hiFPTInfo['ContractNumber'];

        //Gán một số field mặc định
        if($type == 2){
            $hiFPTInfo['Type'] = 12;
        }
        $hiFPTInfo['RecordChannel'] = 3;
        $hiFPTInfo['UserName'] = "HiFPT";

        $resultExistCodes = $surveySection->checkExistCodes($code, $hiFPTInfo['Type'], $contract);
        //Đã lưu thông tin rồi thì báo lỗi duplicate
        if (!empty($resultExistCodes)) {
            return $help->responseFail(406, 'Dữ liệu gửi trùng');
        }

        // Nếu không sẽ gọi api lấy thông tin bên inside
        $infoAcc = [
            'ObjID' => 0,
            'Contract' => $contract,
            'IDSupportlist' => $code,
            'Type' => $type,
        ];
        $apiIsc = new Apiisc();
        $responseAccountInfo = $apiIsc->GetFullAccountInfo($infoAcc);
        $responseAccountInfo = json_decode($responseAccountInfo);
        if ($responseAccountInfo->statusCode != 200){
            return $help->responseFail($responseAccountInfo->statusCode, $responseAccountInfo->data);
        }
        $responseAccountInfo = $responseAccountInfo->data;
        $responseAccountInfo[0] = $help->processDataFromISC($responseAccountInfo[0]);
        $accountInfoISC = (array) $responseAccountInfo[0];

        // update hoặc insert thông tin khách hàng
        $outboundAccount->saveAccountHiFPT($accountInfoISC);

        $accountInfoCEM = $outboundAccount->getAccountInfoByContract($contract);
        $accountInfoCEM = $accountInfoCEM['attributes'];
        $paramSurveySection = $this->assignDataHiFPTInfo($hiFPTInfo, $accountInfoISC, $accountInfoCEM);
        try {
            DB::beginTransaction();
            $surveySectionID = $surveySection->insertSurveyAndGetID($paramSurveySection);
            if (!empty($surveySectionID)) {
                foreach ($questionAnswers as $value) {
                    $paramSurveyResult = [
                        'survey_result_section_id' => $surveySectionID,
                        'survey_result_question_id' => $value['QuestionID'],
                        'survey_result_answer_id' => $value['AnswerID'],
                        'survey_result_answer_extra_id' => $value['ExtraAnswer'],
                        'survey_result_note' => $value['Note'],
                    ];
                    $surveyResult->saveSurveyResult($paramSurveyResult);
                }
            }
            DB::commit();

            $apiHelp = new ApiHelper();
            $paramCheck['sectionId'] = $surveySectionID;
            $resCheck = $apiHelp->checkSendMail($paramCheck);
            if ($resCheck['status']) {
                Redis::lpush('pushNotificationID', $paramCheck['sectionId']);
            }

            return $help->responseSuccess('Kết quả khảo sát được ghi nhận');
        } catch (Exception $ex) {
            DB::rollback();
            return $help->responseFail(500, $ex->getMessage());
        }
    }

    private function assignDataHiFPTInfo($hiFPTInfo, $accountInfoISC, $accountInfoCEM) {
        $dateTime = date('Y-m-d H:i:s');
        $surveySections['section_survey_id'] = isset($hiFPTInfo['Type']) ? $hiFPTInfo['Type'] : NULL;
        $surveySections['section_code'] = isset($hiFPTInfo['SectionCode']) ? $hiFPTInfo['SectionCode'] : NULL;
        $surveySections['section_contract_num'] = isset($hiFPTInfo['ContractNumber']) ? $hiFPTInfo['ContractNumber'] : NULL;
        $surveySections['section_record_channel'] = isset($hiFPTInfo['RecordChannel']) ? $hiFPTInfo['RecordChannel'] : NULL;

        $surveySections['section_account_id'] = isset($accountInfoCEM['id']) ? $accountInfoCEM['id'] : NULL;

        $surveySections['section_contact_phone'] = isset($hiFPTInfo['CustomerPhone']) ? $hiFPTInfo['CustomerPhone'] : NULL;
        $surveySections['section_user_id'] = isset($hiFPTInfo['UserID']) ? $hiFPTInfo['UserID'] : NULL;

        $surveySections['section_supporter'] = isset($accountInfoISC['Supporter']) ? $accountInfoISC['Supporter'] : NULL;
        $surveySections['section_subsupporter'] = isset($accountInfoISC['SubSupporter']) ? $accountInfoISC['SubSupporter'] : NULL;

        $surveySections['section_connected'] = isset($hiFPTInfo['Connected']) ? $hiFPTInfo['Connected'] : 4;
        $surveySections['section_count_connected'] = isset($hiFPTInfo['CountConnected']) ? $hiFPTInfo['CountConnected'] : 1;
        $surveySections['section_note'] = isset($hiFPTInfo['Note']) ? $hiFPTInfo['Note'] : "";
        $surveySections['section_contact'] = isset($hiFPTInfo['Contact']) ? $hiFPTInfo['Contact'] : "";
        $surveySections['section_action'] = isset($hiFPTInfo['Action']) ? $hiFPTInfo['Action'] : 1; // 1 không làm gì
        $surveySections['section_time_start'] = isset($hiFPTInfo['SectionTimeStart']) ? $hiFPTInfo['SectionTimeStart'] : $dateTime;
        $surveySections['section_time_completed'] = isset($hiFPTInfo['SectionTimeCompleted']) ? $hiFPTInfo['SectionTimeCompleted'] : $dateTime;
        $surveySections['section_time_completed_int'] = strtotime($surveySections['section_time_completed']);
        $surveySections['section_data'] = isset($hiFPTInfo['Data']) ? $hiFPTInfo['Data'] : "";

        $surveySections['section_location_id'] = isset($accountInfoISC['LocationID']) ? $accountInfoISC['LocationID'] : "";
        $surveySections['section_location'] = isset($accountInfoISC['Location']) ? $accountInfoISC['Location'] : "";
        $surveySections['section_sub_parent_desc'] = isset($accountInfoISC['SubParentDesc']) ? $accountInfoISC['SubParentDesc'] : "";
        $surveySections['section_region'] = isset($accountInfoISC['Region']) ? $accountInfoISC['Region'] : "";
        $surveySections['section_branch_code'] = isset($accountInfoISC['BranchCode']) ? $accountInfoISC['BranchCode'] : null;
        $surveySections['section_sale_branch_code'] = isset($accountInfoISC['BranchCodeSale']) ? $accountInfoISC['BranchCodeSale'] : null;

        $surveySections['section_contact_person'] = isset($hiFPTInfo['ContactPerson']) ? $hiFPTInfo['ContactPerson'] : "";

        $surveySections['section_center_list'] = isset($accountInfoISC['CenterList']) ? $accountInfoISC['CenterList'] : NULL;
        $surveySections['section_account_inf'] = isset($accountInfoISC['AccountINF']) ? $accountInfoISC['AccountINF'] : NULL;
        $surveySections['section_account_list'] = isset($accountInfoISC['AccountList']) ? $accountInfoISC['AccountList'] : NULL;
        $surveySections['section_acc_sale'] = isset($accountInfoISC['AccountSale']) ? $accountInfoISC['AccountSale'] : NULL;
        $surveySections['section_account_payment'] = isset($accountInfoISC['AccountPayment']) ? $accountInfoISC['AccountPayment'] : NULL;
        $surveySections['section_objAddress'] = isset($accountInfoISC['ObjAddress']) ? $accountInfoISC['ObjAddress'] : NULL;
        $surveySections['section_legal_entity_name'] = isset($accountInfoISC['LegalEntityName']) ? $accountInfoISC['LegalEntityName'] : NULL;
        $surveySections['section_partner_name'] = isset($accountInfoISC['PartnerName']) ? $accountInfoISC['PartnerName'] : NULL;
        $surveySections['section_fee_local_type'] = isset($accountInfoISC['FeeLocalType']) ? $accountInfoISC['FeeLocalType'] : NULL;
        $surveySections['section_description'] = isset($accountInfoISC['Description']) ? $accountInfoISC['Description'] : NULL;
        $surveySections['section_package_sal'] = isset($accountInfoISC['PackageSal']) ? $accountInfoISC['PackageSal'] : NULL;
        $surveySections['section_finish_date_inf'] = isset($accountInfoISC['FinishDateINF']) ? $accountInfoISC['FinishDateINF'] : NULL;
        $surveySections['section_finish_date_list'] = isset($accountInfoISC['FinishDateList']) ? $accountInfoISC['FinishDateList'] : NULL;

        $surveySections['section_phone'] = isset($hiFPTInfo['CustomerPhone']) ? $hiFPTInfo['CustomerPhone'] : NULL;

        $surveySections['section_use_service'] = isset($accountInfoISC['UseService']) ? $accountInfoISC['UseService'] : NULL;
        $surveySections['section_email_inf'] = isset($accountInfoISC['EmailINF']) ? $accountInfoISC['EmailINF'] : NULL;
        $surveySections['section_email_list'] = isset($accountInfoISC['EmailList']) ? $accountInfoISC['EmailList'] : NULL;
        $surveySections['section_email_sale'] = isset($accountInfoISC['EmailSale']) ? $accountInfoISC['EmailSale'] : NULL;
        $surveySections['section_kind_deploy'] = isset($accountInfoISC['KindDeploy']) ? $accountInfoISC['KindDeploy'] : NULL;
        $surveySections['section_kind_main'] = isset($accountInfoISC['KindMain']) ? $accountInfoISC['KindMain'] : NULL;
        $surveySections['section_payment_type'] = isset($accountInfoISC['PaymentType']) ? $accountInfoISC['PaymentType'] : NULL;
        $surveySections['section_customer_name'] = isset($accountInfoISC['CustomerName']) ? $accountInfoISC['CustomerName'] : NULL;
        $surveySections['section_company_name'] = isset($accountInfoISC['CompanyName']) ? $accountInfoISC['CompanyName'] : NULL;
        $surveySections['ip_client'] = NULL;
        $surveySections['section_user_name'] = isset($hiFPTInfo['UserName']) ? $hiFPTInfo['UserName'] : NULL;
        $surveySections['sale_center_id'] = isset($accountInfoISC['CenterID']) ? $accountInfoISC['CenterID'] : NULL;

//        // check lỗi tự động
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

    private function validateApiHiFPTField($validateArray, $array, &$error){
        foreach ($validateArray as $valueValidate) {
            //Dữ liệu gửi qua không có hoặc có nhưng bằng rỗng
            if ((!isset($array[$valueValidate]) || $array[$valueValidate] == '')) {
                $error[] = "Dữ liệu " . $valueValidate . " bị thiếu";
            }
        }
    }
}
