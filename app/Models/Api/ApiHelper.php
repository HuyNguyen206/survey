<?php

namespace App\Models\Api;

use App\Models\SurveySections;
use App\Models\SurveyResult;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendNotificationEmail;
use App\Jobs\SendNotificationMobile;
use App\Component\HelpProvider;
use App\Models\PushNotification;
use App\Models\RecordChannel;
use App\Models\OutboundAnswers;

class ApiHelper {

    public $domain_confirm = 'http://cem.opennet.com.kh/';

    public function checkSendMail($param){
        $surRes = new SurveySections();
        $resSurRes = $surRes->getSurveySectionsAndResult($param);
        $isSendCL = false;

        $arrBadSaleNote     = $arrBadTechNote   = $arrBadTeleNote   = $arrBadNetNote    = $arrBadTvNote     = [];
        $arrBadSaleError    = $arrBadTechError  = $arrBadTeleError  = $arrBadNetError   = $arrBadTvError    = null;
        $arrBadSalePoint    = $arrBadTechPoint  = $arrBadTelePoint  = $arrBadNetPoint   = $arrBadTvPoint    = null;
        $isSendSale         = $isSendTech       = $isSendTele       = $isSendNet        = $isSendTv         = false;

        $arrBadTransactionNote  = $arrBadChargeAtHomeStaffNote  = $arrBadTransactionSaleNote   = [];
        $arrBadTransactionError = $arrBadChargeAtHomeStaffError = $arrBadTransactionSaleError  = null;
        $arrBadTransactionPoint = $arrBadChargeAtHomeStaffPoint = $arrBadTransactionSalePoint  = null;
        $isSendTransaction      = $isSendChargeAtHomeStaff      = $isSendTransactionSale       = false;

        //Kiểm tra toàn bộ kết quả của bảng đánh giá
        foreach ($resSurRes as $resSur) {
            $not = $resSur->survey_result_note;
            $que = $resSur->survey_result_question_id;
            $ans = $resSur->survey_result_answer_id;
            $ansExt = $resSur->survey_result_answer_extra_id;

            // Nhân viên kinh doanh triển khai DirectSale
            if ($que == 1) {
                array_push($arrBadSaleNote, $not);
                $arrBadSaleError = $ansExt;
                $arrBadSalePoint = $ans;
                if(in_array($ans, [1, 2])){
                    $isSendSale = true;
                }
            }

            // Nhân viên kỹ thuật triển khai Direct, bảo trì, Sale tại quầy, sau swap
            if (in_array($que, [2, 6])) {
                array_push($arrBadTechNote, $not);
                $arrBadTechError = $ansExt;
                $arrBadTechPoint = $ans;
                if(in_array($ans, [1, 2])){
                    $isSendTech = true;
                }
            }

            // Nhân viên kinh doanh telesale
            if ($que == 23) {
                array_push($arrBadTeleNote, $not);
                $arrBadTeleError = $ansExt;
                $arrBadTelePoint = $ans;
                if(in_array($ans, [1, 2])){
                    $isSendTele = true;
                }
            }

            // Dịch vụ internet
            if (in_array($que, [5,9])) {
                array_push($arrBadNetNote, $not);
                $arrBadNetError = $ansExt;
                $arrBadNetPoint = $ans;
            }

            // Dịch vụ truyền hình
            if (in_array($que, [100])) {
                array_push($arrBadTvNote, $not);
                $arrBadTvError = $ansExt;
                $arrBadTvPoint = $ans;
            }

            // Giao dịch tại quầy
            if($que == 101){
                array_push($arrBadTransactionNote, $not);
                $arrBadTransactionError = $ansExt;
                $arrBadTransactionPoint = $ans;
                if (in_array($ans, [1, 2])) {
                    $isSendTransaction = true;
                }
            }

            // Nhân viên kinh doanh giao dịch tại quầy, sale tại quầy
            if(in_array($que, [102, 103])){
                array_push($arrBadTransactionSaleNote, $not);
                $arrBadTransactionSaleError = $ansExt;
                $arrBadTransactionSalePoint = $ans;
                if (in_array($ans, [1, 2])) {
                    $isSendTransactionSale = true;
                }
            }

            // Nhân viên thu cước
            if($que == 104){
                array_push($arrBadChargeAtHomeStaffNote, $not);
                $arrBadChargeAtHomeStaffError = $ansExt;
                $arrBadChargeAtHomeStaffPoint = $ans;
                if (in_array($ans, [1, 2])) {
                    $isSendChargeAtHomeStaff = true;
                }
            }
        }

        $desc['badSale'] = implode('.', $arrBadSaleNote);
        $desc['badTech'] = implode('.', $arrBadTechNote);
        $desc['badTele'] = implode('.', $arrBadTeleNote);
        $desc['badNet'] = implode('.', $arrBadNetNote);
        $desc['badTv'] = implode('.', $arrBadTvNote);
        $desc['badTransaction'] = implode('.', $arrBadTransactionNote);
        $desc['badTransactionSale'] = implode('.', $arrBadTransactionSaleNote);
        $desc['badChargeAtHomeStaff'] = implode('.', $arrBadChargeAtHomeStaffNote);

        $send = false;
        if ($isSendTransaction || $isSendTransactionSale || $isSendChargeAtHomeStaff || $isSendSale || $isSendTech || $isSendTele || $isSendCL) {
            $send = true;
        }
        $res['status'] = $send;
        $res['sendCL'] = $isSendCL;
        $res['rule'] = [
            'badNet' => $isSendNet,
            'badTv' => $isSendTv,
            'badTech' => $isSendTech,
            'badTele' => $isSendTele,
            'badSale' => $isSendSale,
            'badTransaction' => $isSendTransaction,
            'badTransactionSale' => $isSendTransactionSale,
            'badChargeAtHomeStaff' => $isSendChargeAtHomeStaff,
        ];
        $res['msg'] = [
            'badNet' => $desc['badNet'],
            'badTv' => $desc['badTv'],
            'badTech' => $desc['badTech'],
            'badTele' => $desc['badTele'],
            'badSale' => $desc['badSale'],
            'badTransaction' => $desc['badTransaction'],
            'badTransactionSale' => $desc['badTransactionSale'],
            'badChargeAtHomeStaff' => $desc['badChargeAtHomeStaff'],
        ];
        $res['error'] = [
            'badNet' => $arrBadNetError,
            'badTv' => $arrBadTvError,
            'badTech' => $arrBadTechError,
            'badTele' => $arrBadTeleError,
            'badSale' => $arrBadSaleError,
            'badTransaction' => $arrBadTransactionError,
            'badTransactionSale' => $arrBadTransactionSaleError,
            'badChargeAtHomeStaff' => $arrBadChargeAtHomeStaffError,
        ];
        $res['point'] = [
            'badNet' => $arrBadNetPoint,
            'badTv' => $arrBadTvPoint,
            'badTech' => $arrBadTechPoint,
            'badTele' => $arrBadTelePoint,
            'badSale' => $arrBadSalePoint,
            'badTransaction' => $arrBadTransactionPoint,
            'badTransactionSale' => $arrBadTransactionSalePoint,
            'badChargeAtHomeStaff' => $arrBadChargeAtHomeStaffPoint,
        ];
        return $res;
    }

    public function prepareSendMail($param,$resCheckSend){
        $surRes = new SurveySections();
        $surSec = $surRes->getSurveySectionsWithEmailTransaction($param);

        $paramMail = $param;
        switch($surSec->section_survey_id){
            case 1:
                $paramMail['type'] = 'Deployment';
                $paramMail['poc'] = 'After Deployment';
                $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
                $paramMail['user_name_send'] = $surSec->section_user_name;
                break;
            case 2:
            case 12:
                $paramMail['type'] = 'Maintenance';
                $paramMail['poc'] = 'After Maintenance';
                $paramMail['timeComplete'] = $surSec->section_finish_date_list;
                $paramMail['user_name_send'] = $surSec->section_user_name;
                break;
            case 3:
                break;
            case 4:
                $paramMail['type'] = 'Giao dịch tại quầy';
                $paramMail['poc'] = 'Sau Giao dịch tại quầy';
                $paramMail['timeComplete'] = $surSec->section_time_start_transaction;
                $paramMail['user_name_send'] = $surSec->section_user_create_transaction;
                break;
            case 5:
                break;
            case 6:
                if($surSec->sale_center_id == 2){
                    $paramMail['type'] = 'Triển khai TeleSales';
                    $paramMail['poc'] = 'Sau Triển khai TeleSales';
                    $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
                    $paramMail['user_name_send'] = $surSec->section_user_name;
                }else{
                    return false;
                }
                break;
            case 7:
                $paramMail['type'] = 'Thu cước tại nhà';
                $paramMail['poc'] = 'Sau Thu cước tại nhà';
                $paramMail['timeComplete'] = $surSec->section_time_start_transaction;
                $paramMail['user_name_send'] = $surSec->section_user_create_transaction;
                break;
            case 8:
                break;
            case 9:
                $paramMail['type'] = 'Triển khai sale tại quầy';
                $paramMail['poc'] = 'Sau Triển khai sale tại quầy';
                $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
                $paramMail['user_name_send'] = $surSec->section_user_name;
                break;
            case 10:
                $paramMail['type'] = 'Triển khai Swap';
                $paramMail['poc'] = 'Sau Triển khai Swap';
                $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
                $paramMail['user_name_send'] = $surSec->section_user_name;
                break;
            default:
                return false;
        }

        // Các thông tin gửi mail chung của tất cả các loại gửi mail
        $paramMail['saleMan'] = $surSec->section_acc_sale;
        $paramMail['time'] = $surSec->section_time_completed;

        switch($surSec->section_survey_id){
            case 2:
                $paramMail['team'] = $surSec->section_account_list;
                break;
            default:
                $paramMail['team'] = $surSec->section_account_inf;
        }

        $paramMail['date'] = date('Y-m-d H:i:s');

        $paramMail['num_type'] = $surSec->section_survey_id;
        $paramMail['code'] = $surSec->section_code;
        $paramMail['shd'] = $surSec->section_contract_num;

        $paramMail['name'] = $surSec->section_customer_name;
        $paramMail['address'] = $surSec->section_objAddress;
        $paramMail['phone'] = $surSec->section_phone;
        $location = explode('-', $surSec->section_location);
        $paramMail['location'] = trim($location[0]);
        $paramMail['location_id'] = $surSec->section_location_id;
        $paramMail['branch_code'] = $surSec->section_branch_code;
        $paramMail['point'] = '0';
        $paramMail['note'] = '0';
        $paramMail['csat'] = '0';

        $paramMail['transactionKind'] = $surSec->section_kind_service;
        $paramMail['transactionSale'] = $surSec->section_user_create_transaction;

        // Thông tin kênh ghi nhận
        $modelChannel = new RecordChannel();
        $channels = $modelChannel->getAllRecordChannel();
        foreach($channels as $channel){
            if($channel->record_channel_code == $surSec->section_record_channel){
                $paramMail['channel'] = trans('pointOfContact.'.$channel->record_channel_key);
            }
        }

        $paramMail['results'] = [];
        $object = [
            'badNet' => 'Internet',
            'badTv' => 'Pay TV',
            'badTech' => 'SIR:'.$paramMail['team'],
            'badTele' => 'NVKD:'.$paramMail['saleMan'],
            'badSale' => 'Sale:'.$paramMail['saleMan'],
            'badTransaction' => 'CL Giao dịch tại quầy',
            'badTransactionSale' => 'NV Giao dịch',
            'badChargeAtHomeStaff' => 'NV Thu cước',
        ];

        $mainTitle =[
            'badNet' => 'Chất lượng dịch vụ',
            'badTv' => 'Chất lượng dịch vụ',
            'badTech' => 'SIR',
            'badTele' => 'Nhân viên kinh doanh',
            'badSale' => 'Sale',
            'badTransaction' => 'Chất lượng giao dịch',
            'badTransactionSale' => 'Nhân viên giao dịch',
            'badChargeAtHomeStaff' => 'Nhân viên thu cước',
        ];

        $recordBy = [
            '1' => 'Staff survey',
            '2' => 'Web khảo sát',
            '3' => 'Hi FPT',
            '4' => 'NVTC',
            '5' => 'NVGD',
            '6' => 'Tablet',
        ];

        // Thông tin điểm
        $modelStatus = new OutboundAnswers();
        $statuses = $modelStatus->getAnswerByGroup([0, 1]);
        // Loại điểm
        $typePoint = [];
        foreach($statuses as $point){
            $typePoint[$point->answer_id] = trans('answer.'.$point->answers_key);
        }

        // Loại lỗi
        $statuses = $modelStatus->getAnswerByGroup([20]);
        $typeError = [];
        foreach($statuses as $ans){
            $typeError[$ans->answer_id] = trans('error.'.$ans->answers_key);
        }

        // Tạo bộ câu trả lời cho email
        $isGoodCL = true;
        $paramMail['results'] = [
            'badCL' => null
        ];
        foreach($resCheckSend['rule'] as $type => $isSend){
            if(in_array($type,["badTransaction", "badTransactionSale"])){
                $type = "badQGD";
            } elseif(in_array($type,["badChargeAtHomeStaff"])){
                $type = "badCUS";
            }

            if($isSend){
                $paramMail['results'][$type] = null;
            }
        }

        foreach($resCheckSend['rule'] as $type => $isSend){
            $point = $resCheckSend['point'][$type];
            if(!empty($point) && $point > 0){
                $arrayWarning = [];
                $arrayWarning['object'] = $object[$type];
                $arrayWarning['csat'] = $typePoint[$point];
                $arrayWarning['point'] = $point;
                if(in_array($point,[1,2])){
                    $isGoodCL = false;
                }
                $arrayWarning['typeError'] = (!empty($resCheckSend['error'][$type]))? (isset($typeError[$resCheckSend['error'][$type]])? $typeError[$resCheckSend['error'][$type]]: null) : null;
                $arrayWarning['note'] = $resCheckSend['msg'][$type];

                $tempTitle = $mainTitle[$type];
                if(in_array($type,["badTransaction", "badTransactionSale"])){
                    $type = "badQGD";
                } elseif(in_array($type,["badChargeAtHomeStaff"])){
                    $type = "badCUS";
                }

                foreach($paramMail['results'] as $typeSendNotification => $val){
                    if($isSend && $type == $typeSendNotification){
                        switch($type){
                            case 'badTech':
                                $paramMail['results'][$type]['main'] = $arrayWarning;
                                break;
                            case 'badTele':
                                $paramMail['results'][$type]['main'] = $arrayWarning;
                                break;
                            case 'badSale':
                                $paramMail['results'][$type]['main'] = $arrayWarning;
                                break;
                            default:
                                $paramMail['results'][$type][] = $arrayWarning;
                        }

                        if(in_array($point,[1,2])){
                            $paramMail['results'][$type]['other']['alertGood'] = false;
                        }else{
                            $paramMail['results'][$type]['other']['alertGood'] = true;
                        }
                        $paramMail['results'][$type]['other']['mainTitle'] = $tempTitle;
                        $paramMail['results'][$type]['other']['recordBy'] = $recordBy[$surSec->section_record_channel];
                        $paramMail['results'][$type]['other']['point'] = $arrayWarning['point'];
                        $paramMail['results'][$type]['other']['note'] = $arrayWarning['note'];
                        $paramMail['results'][$type]['other']['csat'] = $arrayWarning['csat'];
                    }else{
                        $paramMail['results'][$typeSendNotification][] = $arrayWarning;
                    }
                }

                if(in_array($type,['badTv','badNet'])){
                    $paramMail['results']['badCL']['other']['point'] = $arrayWarning['point'];
                    $paramMail['results']['badCL']['other']['note'] = $arrayWarning['note'];
                    $paramMail['results']['badCL']['other']['csat'] = $arrayWarning['csat'];
                }
            }
        }
        $paramMail['results']['badCL']['other']['alertGood'] = $isGoodCL;
        $paramMail['results']['badCL']['other']['mainTitle'] = 'Chất lượng dịch vụ';
        $paramMail['results']['badCL']['other']['recordBy'] = $recordBy[$surSec->section_record_channel];

        // Kiểm tra và send mail theo từng loại tương ứng
        $result = '';
        foreach($paramMail['results'] as $type => $arrayResult){
            switch($type){
                case 'badSale':
                    $paramMail['sale_net_type'] = 'Sale';
                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];

                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
                    $isAgain = $this->isSendAgain($paramMail);
                    if (!$isAgain) {
                        $this->send($paramMail);
                    } else {
                        $this->updateNoteForResend($paramMail);
                    }
                    break;
                case 'badTech':
                    $paramMail['sale_net_type'] = 'Tech';
                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];

                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
                    $isAgain = $this->isSendAgain($paramMail);
                    if (!$isAgain) {
                        $this->send($paramMail);
                    } else {
                        $this->updateNoteForResend($paramMail);
                    }
                    break;
                case 'badTele':
                    $paramMail['sale_net_type'] = 'Tele';
                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];

                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
                    $isAgain = $this->isSendAgain($paramMail);
                    if (!$isAgain) {
                        $this->send($paramMail);
                    } else {
                        $this->updateNoteForResend($paramMail);
                    }
                    break;
                case 'badQGD':
                    $paramMail['sale_net_type'] = 'QGD';
                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];

                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
                    $isAgain = $this->isSendAgain($paramMail);
                    if (!$isAgain) {
                        $this->send($paramMail);
                    }
                    break;
                case 'badCUS':
                    $paramMail['sale_net_type'] = 'CUS';
                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];

                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
                    $isAgain = $this->isSendAgain($paramMail);
                    if (!$isAgain) {
                        $this->send($paramMail);
                    }
                    break;
                case 'badCL':
                    if($resCheckSend['sendCL']){
                        // Trường hợp tạo checklist mà không đánh giá
                        if(count($paramMail['results']['badCL']) == 1){
                            continue;
                        }
                        $paramMail['sale_net_type'] = 'CL';
                        $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
                        $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
                        $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];

                        //Kiểm tra trường hợp bị trùng lặp dữ liệu
                        $isAgain = $this->isSendAgain($paramMail);
                        if (!$isAgain) {
                            $this->send($paramMail);
                        }
                    }
                    break;
                default:
            }
        }

        return $result;
    }

    private function send($paramMail) {
        $help = new HelpProvider();
        $model_notification = new PushNotification();
        $paramMail['confirm_code'] = md5($paramMail['shd'] . '-' . $paramMail['code'] . '-' . $paramMail['sale_net_type'] . '-' . $paramMail['date']);
        $paramMail['confirm_link'] = $this->domain_confirm . 'confirm-notification?code=' . $paramMail['confirm_code'];

        //Kiểm tra để lấy input theo loại gọi api Sale hay net
        switch($paramMail['sale_net_type']){
            case 'Sale':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
                $paramMail['subject'] = "CEM – Customers' Satisfaction – " . $paramMail['location'] . ' – ' . $paramMail['point'] . ' point – ' . $paramMail['shd'];
                $paramMail['description'] = $help->getDescriptionForSendSale($paramMail);
                $input = $help->getParamApiSale($paramMail);
                break;
            case 'Tech':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
                $paramMail['subject'] = "CEM – Customers' Satisfaction – " . $paramMail['location'] . ' – ' . $paramMail['point'] . ' point – ' . $paramMail['shd'];
                $input = $help->getParamApiTech($paramMail);
                break;
            case 'Tele':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
                $paramMail['subject'] = "CEM – Customers' Satisfaction – " . $paramMail['location'] . ' – ' . $paramMail['point'] . ' point – ' . $paramMail['shd'];
                $input = $help->getParamApiTele($paramMail);
                break;
            case 'CL':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotificationCheckList', ['param' => $paramMail]));
                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – Checklist phát sinh – CSAT CLDV Internet/Truyền hình -  '. $paramMail['point'] .' point – ' . $paramMail['shd'];
                $input = $help->getParamApiCL($paramMail);
                break;
            case 'QGD':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotificationQGD', ['param' => $paramMail]));
                $paramMail['subject'] = "CEM – Customers' Satisfaction – " . $paramMail['location'] . ' – CSAT'.$paramMail['point'] . ' – ' . $paramMail['shd'];
                $input = $help->getParamApiQGD($paramMail);
                break;
            case 'CUS':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotificationCUS', ['param' => $paramMail]));
                $paramMail['subject'] = "CEM – Customers' Satisfaction – " . $paramMail['location'] . ' – CSAT'.$paramMail['point'] . ' – ' . $paramMail['shd'];
                $input = $help->getParamApiCUS($paramMail);
                break;
            default:
                return;
        }

        //Lưu lại thông tin trước khi push sang bên ISC
        $model_notification->insertPushNotification($input[strtolower($paramMail['sale_net_type'])], $paramMail);

        $input['paramMail'] = $paramMail;
        //Đưa vào hàng đợi gọi api
//        switch($paramMail['sale_net_type']){
//            case 'Sale':
//                $job = (new SendNotificationMobile($input))->onQueue('mobile');
//                Bus::dispatch($job);
//                break;
//            default:
//        }

        $job = (new SendNotificationEmail($input))->onQueue('emails');
        Bus::dispatch($job);

//        switch($paramMail['sale_net_type']){
//            case 'Sale':
//                $job = (new SendNotificationMobile($input))->onQueue('mobile');
//                break;
//            default:
//                $job = (new SendNotificationEmail($input))->onQueue('emails');
//        }
//        Bus::dispatch($job);
    }

//    public function prepareSendMailTest($param,$resCheckSend){
//        $surRes = new SurveySections();
//        $surSec = $surRes->getSurveySectionsWithEmailTransaction($param);
//
//        $paramMail = $param;
//        switch($surSec->section_survey_id){
//            case 1:
//                if($surSec->sale_center_id == 1){
//                    $paramMail['type'] = 'Deployment';
//                    $paramMail['poc'] = 'After Deployment';
//                    $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
//                    $paramMail['user_name_send'] = $surSec->section_user_name;
//                }else{
//                    return false;
//                }
//                break;
//            case 2:
//            case 12:
//                $paramMail['type'] = 'Maintenance';
//                $paramMail['poc'] = 'After Maintenance';
//                $paramMail['timeComplete'] = $surSec->section_finish_date_list;
//                $paramMail['user_name_send'] = $surSec->section_user_name;
//                break;
//            case 3:
//                break;
//            case 4:
//                $paramMail['type'] = 'Giao dịch tại quầy';
//                $paramMail['poc'] = 'Sau Giao dịch tại quầy';
//                $paramMail['timeComplete'] = $surSec->section_time_start_transaction;
//                $paramMail['user_name_send'] = $surSec->section_user_create_transaction;
//                break;
//            case 5:
//                break;
//            case 6:
//                if($surSec->sale_center_id == 2){
//                    $paramMail['type'] = 'Triển khai TeleSales';
//                    $paramMail['poc'] = 'Sau Triển khai TeleSales';
//                    $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
//                    $paramMail['user_name_send'] = $surSec->section_user_name;
//                }else{
//                    return false;
//                }
//                break;
//            case 7:
//                $paramMail['type'] = 'Thu cước tại nhà';
//                $paramMail['poc'] = 'Sau Thu cước tại nhà';
//                $paramMail['timeComplete'] = $surSec->section_time_start_transaction;
//                $paramMail['user_name_send'] = $surSec->section_user_create_transaction;
//                break;
//            case 8:
//                break;
//            case 9:
//                $paramMail['type'] = 'Triển khai sale tại quầy';
//                $paramMail['poc'] = 'Sau Triển khai sale tại quầy';
//                $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
//                $paramMail['user_name_send'] = $surSec->section_user_name;
//                break;
//            case 10:
//                $paramMail['type'] = 'Triển khai Swap';
//                $paramMail['poc'] = 'Sau Triển khai Swap';
//                $paramMail['timeComplete'] = $surSec->section_finish_date_inf;
//                $paramMail['user_name_send'] = $surSec->section_user_name;
//                break;
//            default:
//                return false;
//        }
//
//        // Các thông tin gửi mail chung của tất cả các loại gửi mail
//        $paramMail['saleMan'] = $surSec->section_acc_sale;
//        $paramMail['time'] = $surSec->section_time_completed;
//        $paramMail['team'] = $surSec->section_supporter . ' - ' . $surSec->section_subsupporter;
//        $paramMail['date'] = date('Y-m-d H:i:s');
//
//        $paramMail['num_type'] = $surSec->section_survey_id;
//        $paramMail['code'] = $surSec->section_code;
//        $paramMail['shd'] = $surSec->section_contract_num;
//
//        $paramMail['name'] = $surSec->section_customer_name;
//        $paramMail['address'] = $surSec->section_objAddress;
//        $paramMail['phone'] = $surSec->section_phone;
//        $location = explode('-', $surSec->section_location);
//        $paramMail['location'] = trim($location[0]);
//        $paramMail['location_id'] = $surSec->section_location_id;
//        $paramMail['branch_code'] = $surSec->section_branch_code;
//        $paramMail['point'] = '0';
//        $paramMail['note'] = '0';
//        $paramMail['csat'] = '0';
//
//        $paramMail['transactionKind'] = $surSec->section_kind_service;
//        $paramMail['transactionSale'] = $surSec->section_user_create_transaction;
//
//        // Thông tin kênh ghi nhận
//        $modelChannel = new RecordChannel();
//        $channels = $modelChannel->getAllRecordChannel();
//        foreach($channels as $channel){
//            if($channel->record_channel_code == $surSec->section_record_channel){
//                $paramMail['channel'] = trans('pointOfContact.'.$channel->record_channel_key);
//            }
//        }
//
//        $paramMail['results'] = [];
//        $object = [
//            'badNet' => 'Internet',
//            'badTv' => 'Pay TV',
//            'badTech' => 'SIR:'.$paramMail['team'],
//            'badTele' => 'NVKD:'.$paramMail['saleMan'],
//            'badSale' => 'Sale:'.$paramMail['saleMan'],
//            'badTransaction' => 'CL Giao dịch tại quầy',
//            'badTransactionSale' => 'NV Giao dịch',
//            'badChargeAtHomeStaff' => 'NV Thu cước',
//        ];
//
//        $mainTitle =[
//            'badNet' => 'Chất lượng dịch vụ',
//            'badTv' => 'Chất lượng dịch vụ',
//            'badTech' => 'SIR',
//            'badTele' => 'Nhân viên kinh doanh',
//            'badSale' => 'Sale',
//            'badTransaction' => 'Chất lượng giao dịch',
//            'badTransactionSale' => 'Nhân viên giao dịch',
//            'badChargeAtHomeStaff' => 'Nhân viên thu cước',
//        ];
//
//        $recordBy = [
//            '1' => 'Staff survey',
//            '2' => 'Web khảo sát',
//            '3' => 'Hi FPT',
//            '4' => 'NVTC',
//            '5' => 'NVGD',
//            '6' => 'Tablet',
//        ];
//
//        // Thông tin điểm
//        $modelStatus = new OutboundAnswers();
//        $statuses = $modelStatus->getAnswerByGroup([0, 1]);
//        // Loại điểm
//        $typePoint = [];
//        foreach($statuses as $point){
//            $typePoint[$point->answer_id] = trans('answer.'.$point->answers_key);
//        }
//
//        // Loại lỗi
//        $statuses = $modelStatus->getAnswerByGroup([20]);
//        $typeError = [];
//        foreach($statuses as $ans){
//            $typeError[$ans->answer_id] = trans('error.'.$ans->answers_key);
//        }
//
//        // Tạo bộ câu trả lời cho email
//        $isGoodCL = true;
//        $paramMail['results'] = [
//            'badCL' => null
//        ];
//        foreach($resCheckSend['rule'] as $type => $isSend){
//            if(in_array($type,["badTransaction", "badTransactionSale"])){
//                $type = "badQGD";
//            } elseif(in_array($type,["badChargeAtHomeStaff"])){
//                $type = "badCUS";
//            }
//
//            if($isSend){
//                $paramMail['results'][$type] = null;
//            }
//        }
//
//        foreach($resCheckSend['rule'] as $type => $isSend){
//            $point = $resCheckSend['point'][$type];
//            if(!empty($point) && $point > 0){
//                $arrayWarning = [];
//                $arrayWarning['object'] = $object[$type];
//                $arrayWarning['csat'] = $typePoint[$point];
//                $arrayWarning['point'] = $point;
//                if(in_array($point,[1,2])){
//                    $isGoodCL = false;
//                }
//                $arrayWarning['typeError'] = (!empty($resCheckSend['error'][$type]))? (isset($typeError[$resCheckSend['error'][$type]])? $typeError[$resCheckSend['error'][$type]]: null) : null;
//                $arrayWarning['note'] = $resCheckSend['msg'][$type];
//
//                $tempTitle = $mainTitle[$type];
//                if(in_array($type,["badTransaction", "badTransactionSale"])){
//                    $type = "badQGD";
//                } elseif(in_array($type,["badChargeAtHomeStaff"])){
//                    $type = "badCUS";
//                }
//
//                foreach($paramMail['results'] as $typeSendNotification => $val){
//                    if($isSend && $type == $typeSendNotification){
//                        switch($type){
//                            case 'badTech':
//                                $paramMail['results'][$type]['main'] = $arrayWarning;
//                                break;
//                            case 'badTele':
//                                $paramMail['results'][$type]['main'] = $arrayWarning;
//                                break;
//                            case 'badSale':
//                                $paramMail['results'][$type]['main'] = $arrayWarning;
//                                break;
//                            default:
//                                $paramMail['results'][$type][] = $arrayWarning;
//                        }
//
//                        if(in_array($point,[1,2])){
//                            $paramMail['results'][$type]['other']['alertGood'] = false;
//                        }else{
//                            $paramMail['results'][$type]['other']['alertGood'] = true;
//                        }
//                        $paramMail['results'][$type]['other']['mainTitle'] = $tempTitle;
//                        $paramMail['results'][$type]['other']['recordBy'] = $recordBy[$surSec->section_record_channel];
//                        $paramMail['results'][$type]['other']['point'] = $arrayWarning['point'];
//                        $paramMail['results'][$type]['other']['note'] = $arrayWarning['note'];
//                        $paramMail['results'][$type]['other']['csat'] = $arrayWarning['csat'];
//                    }else{
//                        $paramMail['results'][$typeSendNotification][] = $arrayWarning;
//                    }
//                }
//
//                if(in_array($type,['badTv','badNet'])){
//                    $paramMail['results']['badCL']['other']['point'] = $arrayWarning['point'];
//                    $paramMail['results']['badCL']['other']['note'] = $arrayWarning['note'];
//                    $paramMail['results']['badCL']['other']['csat'] = $arrayWarning['csat'];
//                }
//            }
//        }
//        $paramMail['results']['badCL']['other']['alertGood'] = $isGoodCL;
//        $paramMail['results']['badCL']['other']['mainTitle'] = 'Chất lượng dịch vụ';
//        $paramMail['results']['badCL']['other']['recordBy'] = $recordBy[$surSec->section_record_channel];
//
//        // Kiểm tra và send mail theo từng loại tương ứng
//        $result = '';
//        foreach($paramMail['results'] as $type => $arrayResult){
//            switch($type){
//                case 'badSale':
//                    $paramMail['sale_net_type'] = 'Sale';
//                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
//                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
//                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];
//
//                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
//                    $isAgain = $this->isSendAgain($paramMail);
//                    if (!$isAgain) {
//                        $this->sendTest($paramMail);
//                    } else {
//                        $this->updateNoteForResend($paramMail);
//                    }
//                    break;
//                case 'badTech':
//                    $paramMail['sale_net_type'] = 'Tech';
//                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
//                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
//                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];
//
//                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
//                    $isAgain = $this->isSendAgain($paramMail);
//                    if (!$isAgain) {
//                        $this->sendTest($paramMail);
//                    } else {
//                        $this->updateNoteForResend($paramMail);
//                    }
//                    break;
//                case 'badTele':
//                    $paramMail['sale_net_type'] = 'Tele';
//                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
//                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
//                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];
//
//                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
//                    $isAgain = $this->isSendAgain($paramMail);
//                    if (!$isAgain) {
//                        $this->sendTest($paramMail);
//                    } else {
//                        $this->updateNoteForResend($paramMail);
//                    }
//                    break;
//                case 'badQGD':
//                    $paramMail['sale_net_type'] = 'QGD';
//                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
//                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
//                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];
//
//                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
//                    $isAgain = $this->isSendAgain($paramMail);
//                    if (!$isAgain) {
//                        $this->sendTest($paramMail);
//                    }
//                    break;
//                case 'badCUS':
//                    $paramMail['sale_net_type'] = 'CUS';
//                    $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
//                    $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
//                    $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];
//
//                    //Kiểm tra trường hợp bị trùng lặp dữ liệu
//                    $isAgain = $this->isSendAgain($paramMail);
//                    if (!$isAgain) {
//                        $this->sendTest($paramMail);
//                    }
//                    break;
//                case 'badCL':
//                    if($resCheckSend['sendCL']){
//                        // Trường hợp tạo checklist mà không đánh giá
//                        if(count($paramMail['results']['badCL']) == 1){
//                            continue;
//                        }
//                        $paramMail['sale_net_type'] = 'CL';
//                        $paramMail['point'] = $paramMail['results'][$type]['other']['point'];
//                        $paramMail['note'] = $paramMail['results'][$type]['other']['note'];
//                        $paramMail['csat'] = $paramMail['results'][$type]['other']['csat'];
//
//                        //Kiểm tra trường hợp bị trùng lặp dữ liệu
//                        $isAgain = $this->isSendAgain($paramMail);
//                        if (!$isAgain) {
//                            $this->sendTest($paramMail);
//                        }
//                    }
//                    break;
//                default:
//            }
//        }
//
//        return $result;
//    }
//
//    private function sendTest($paramMail) {
//        $help = new HelpProvider();
//        $model_notification = new PushNotification();
//        $paramMail['confirm_code'] = md5($paramMail['shd'] . '-' . $paramMail['code'] . '-' . $paramMail['sale_net_type'] . '-' . $paramMail['date']);
//        $paramMail['confirm_link'] = $this->domain_confirm . 'confirm-notification?code=' . $paramMail['confirm_code'];
//
//        //Kiểm tra để lấy input theo loại gọi api Sale hay net
//        switch($paramMail['sale_net_type']){
//            case 'Sale':
//                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
//                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – ' . $paramMail['point'] . ' điểm – ' . $paramMail['shd'];
//                echo $paramMail['template'];
//                $paramMail['description'] = $help->getDescriptionForSendSale($paramMail);
//                $input = $help->getParamApiSale($paramMail);
////                return json_encode($input);
//                break;
//            case 'Tech':
//                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
//                echo $paramMail['template'];
//                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – ' . $paramMail['point'] . ' điểm – ' . $paramMail['shd'];
//                $input = $help->getParamApiTech($paramMail);
//                dump($paramMail);
////                return $paramMail['template'];
//                break;
//            case 'Tele':
//                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
//                echo $paramMail['template'];
//                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – ' . $paramMail['point'] . ' điểm – ' . $paramMail['shd'];
//                $input = $help->getParamApiTele($paramMail);
//                dump($paramMail);
////                return $paramMail['template'];
//                break;
//            case 'CL':
//                $paramMail['template'] = html_entity_decode(view('emails.sendNotificationCheckList', ['param' => $paramMail]));
//                echo $paramMail['template'];
//                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – Checklist phát sinh – CSAT CLDV Internet/Truyền hình -  '. $paramMail['point'] .' điểm – ' . $paramMail['shd'];
//                $input = $help->getParamApiCL($paramMail);
//                dump($paramMail);
////                return $paramMail['template'];
//                break;
//            case 'QGD':
//                $paramMail['template'] = html_entity_decode(view('emails.sendNotificationQGD', ['param' => $paramMail]));
//                echo $paramMail['template'];
//                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – CSAT'.$paramMail['point'] . ' – ' . $paramMail['shd'];
//                $input = $help->getParamApiQGD($paramMail);
//                dump("paramMail trong send Test");
//                dump($paramMail);
////                return $paramMail['template'];
//                break;
//            case 'CUS':
//                $paramMail['template'] = html_entity_decode(view('emails.sendNotificationCUS', ['param' => $paramMail]));
//                echo $paramMail['template'];
//                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – CSAT'.$paramMail['point'] . ' – ' . $paramMail['shd'];
//                $input = $help->getParamApiCUS($paramMail);
//                dump($paramMail);
////                return $paramMail['template'];
//                break;
//            default:
//                return;
//        }
//
//        dump('input');
//        dump($input);
////        //Lưu lại thông tin trước khi push sang bên ISC
////        $resultInsert = $model_notification->insertPushNotification($input[strtolower($paramMail['sale_net_type'])], $paramMail);
////
////        dump($resultInsert);
////
////        $input['paramMail'] = $paramMail;
////
////        $typeSend = ['sale', 'tech', 'tele', 'cl', 'qgd', 'cus'];
////        //Xét tất cả các trường hợp gửi api cho ISC
////        foreach($typeSend as $type){
////            if(!empty($input[$type])){
////                $this->sendAPITest($type, $input);
////            }
////        }
//
//        $input['paramMail'] = $paramMail;
//        return $input;
//    }
//
//    private function sendAPITest($type, $input){
//        $sale = new ApiSale();
//        $mobi = new ApiMobi();
//        $tele = new ApiTele();
//        $modelListEmailQGD = new ListEmailQGD();
//        $modelListEmailCUS = new ListEmailCUS();
//        $tempCUS = false;
//        $model_notification = new PushNotification();
//        $modelHelper = new HelpProvider();
//        $date = date('Y-m-d H:i:s');
//        //Gọi api của sale, net tùy trường hợp
//        switch($type){
//            case 'sale':
//                $res = $sale->pushNotificationToSale($input[$type]);
//                break;
//            case 'tech':
////                $res = $mobi->pushNotificationToNet($this->input[$type]);
////                $param['push_notification_subjects'] = $this->input['paramMail']['subject'];
//                $res = $mobi->pushNotificationToISCGetEmailList($input[$type]);
//                break;
//            case 'tele':
//                $res = $tele->pushNotificationToISCGetEmailList($input[$type]);
//                break;
//            case 'cl':
//                $res = $mobi->pushNotificationToISCGetEmailList($input[$type]);
//                break;
//            case 'qgd':
//                $paramQGD['location_id'] = $input['paramMail']['location_id'];
//                $paramQGD['branch_code'] = $input['paramMail']['branch_code'];
//                $resQGD = $modelListEmailQGD->getListEmail($paramQGD);
//                dump($resQGD);
//                $res['error'] = false;
//                $res['output'] = json_encode($resQGD);
//                $res['msg'] = 'Có list mail';
//                if(empty($resQGD)){
//                    $res['error'] = true;
//                    $res['msg'] = 'Không có list mail';
//                }
//                break;
//            case 'cus':
//                $paramCUS['location_id'] = $input['paramMail']['location_id'];
//                $paramCUS['branch_code'] = $input['paramMail']['branch_code'];
//                $resCUS = $modelListEmailCUS->getListEmail($paramCUS);
//                $res['error'] = false;
//                $res['output'] = json_encode($resCUS);
//                $res['msg'] = 'Có list mail';
//                if(empty($resCUS)){
//                    $tempCUS = true;
//                }
//                break;
//            default:
//                return;
//        }
//
//        if($res['error']){
//            $status = 0;
//        }else{
//            $status = 1;
//        }
//
//        $param['confirm_code'] = $input['paramMail']['confirm_code'];
//        $param['api_status'] = $status;
//        $param['api_created_at'] = $date;
//        $param['api_last_sent_at'] = $date;
//        $param['api_output'] = $res['output'];
//        $param['api_message'] = $res['msg'];
//        $param['api_send_count'] = 1;
//
//        if($status){
//            $temp = json_decode($param['api_output'],1);
//
//            //Bổ sung thông tin mail đối với trường hợp gọi api thành công
//            if(isset($temp['msg']['email'])){
//                $param['push_notification_send_to'] = $temp['msg']['email'];
//            }
//            if(isset($temp['msg']['ccemail'])){
//                $param['push_notification_send_cc'] = $temp['msg']['ccemail'];
//            }
//            if(isset($temp['msg']['accountinside'])){
//                $param['push_notification_inside_confirm'] = strtolower($temp['msg']['accountinside']);
//            }
//
//            // Bổ sung thông tin mail đối với trường hợp send mail
//            if(isset($temp['msg']['data'][0]['EmailLeader'])){
//                $param['push_notification_send_to'] = $temp['msg']['data'][0]['EmailLeader'];
//            }
//            if(isset($temp['msg']['data'][0]['AccountInsideLeader'])){
//                $param['push_notification_inside_confirm'] = strtolower($temp['msg']['data'][0]['AccountInsideLeader']);
//            }
//
//            //Bổ sung thông tin đối với trường hợp send mail qgd
//            if(isset($temp['email_list_to'])){
//                $param['push_notification_send_to'] = $temp['email_list_to'];
//            }
//        }
//
//        //Cập nhật lại push_notification
//        $model_notification->updatePushNotificationOnSendNotification($param);
//
//        switch($type){
//            case 'sale':
//                break;
//            case 'tech':
//                if(!$res['error']){
//                    $type = 'Tech';
//                    $tempRes = json_decode($res['output'],1);
//                    $mail = $tempRes['msg']['email'];
//                    $cc = $tempRes['msg']['ccemail'];
//                    $realCc = [];
//                    $temp = explode(';', $cc);
//                    foreach($temp as $val){
//                        if(!empty($val)){
//                            array_push($realCc, $val);
//                        }
//                    }
//                    try{
//                        $modelHelper->sendMail($input, $mail, $type, $realCc);
//                    }catch(Exception $ex){
//                        $param['push_notification_note'] = $ex->getMessage();
//                    }
//                }
//                break;
//            case 'tele':
//                if(!$res['error']){
//                    $type = 'Tele';
//                    $tempRes = json_decode($res['output'],1);
//                    $mail = $tempRes['msg']['data'][0]['EmailLeader'];
//                    $realCc = [];
//                    try{
//                        $modelHelper->sendMail($input, $mail, $type, $realCc);
//                    }catch(Exception $ex){
//                        $param['push_notification_note'] = $ex->getMessage();
//                    }
//                }
//                break;
//            case 'cl':
//                if(!$res['error']){
//                    $type = 'CL';
//                    $tempRes = json_decode($res['output'],1);
//                    $mail = $tempRes['msg']['email'];
//                    $cc = $tempRes['msg']['ccemail'];
//                    $realCc = [];
//                    $temp = explode(';', $cc);
//                    foreach($temp as $val){
//                        if(!empty($val)){
//                            array_push($realCc, $val);
//                        }
//                    }
//                    try{
//                        $modelHelper->sendMail($input, $mail, $type, $realCc);
//                    }catch(Exception $ex){
//                        $param['push_notification_note'] = $ex->getMessage();
//                    }
//                }
//                break;
//            case 'qgd':
//                if(!$res['error']){
//                    $type = 'QGD';
//                    $tempRes = json_decode($res['output'],1);
//                    $mail = explode(';', $tempRes['email_list_to']);
//                    $realCc = explode(';', $tempRes['email_list_cc']);
//dump('send email');
//                    dump($mail, $type, $realCc);
//                    try{
//                        $modelHelper->sendMail($input, $mail, $type, $realCc);
//                    }catch(Exception $ex){
//                        $param['push_notification_note'] = $ex->getMessage();
//                    }
//                }
//                break;
//            case 'cus':
//                if(!$res['error']){
//                    $type = 'CUS';
//                    $tempRes = json_decode($res['output'],1);
//                    if($tempCUS){
//                        $mail = [
//                            'anhdv4@fpt.com.vn',
//                            'hantp@fpt.com.vn',
//                            'toannm@fpt.com.vn',
//                            'phutm@fpt.com.vn',
//                        ];
//                        $realCc = [
//                        ];
//                    }else{
//                        $mail = explode(';', $tempRes['email_list']);
//                        $realCc = [
//                            'anhdv4@fpt.com.vn',
//                            'hantp@fpt.com.vn',
//                            'toannm@fpt.com.vn',
//                            'phutm@fpt.com.vn',
//                        ];
//                    }
//                    try{
//                        $modelHelper->sendMail($input, $mail, $type, $realCc);
//                    }catch(Exception $ex){
//                        $param['push_notification_note'] = $ex->getMessage();
//                    }
//                }
//                break;
//            default:
//                return;
//        }
//        dump($param);
//    }

    public function updateNoteForResend($paramMail) {
        $help = new HelpProvider();
        $model_notification = new PushNotification();
        $res = (array) $model_notification->getPushNotificationToCheckDuplicate($paramMail);

        $paramMail['confirm_code'] = $res['confirm_code'];
        $paramMail['confirm_link'] = $this->domain_confirm . 'confirm-notification?code=' . $paramMail['confirm_code'];

        //Kiểm tra để lấy input theo loại gọi api Sale hay net
        switch($paramMail['sale_net_type']){
            case 'Sale':
                $paramMail['description'] = $help->getDescriptionForSendSale($paramMail);
                $input = $help->getParamApiSale($paramMail);
                break;
            case 'Tech':
                $paramMail['template'] = html_entity_decode(view('emails.sendNotification', ['param' => $paramMail]));
                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – ' . $paramMail['point'] . ' điểm – ' . $paramMail['shd'];
                $input = $help->getParamApiTech($paramMail);
                break;
            case 'Tele':
                $paramMail['subject'] = '[CEM – Độ hài lòng KH] – ' . $paramMail['location'] . ' – ' . $paramMail['point'] . ' điểm – ' . $paramMail['shd'];
                $input = $help->getParamApiTele($paramMail);
                break;
            default:
                return;
        }

        $res['api_input'] = json_encode($input);
        $res['push_notification_note'] = $paramMail['note'];
        $res['push_notification_param'] = json_encode($paramMail);
        $model_notification->updateNoteNotification($res);
    }

    private function isSendAgain($param) {
        //Tìm thông tin trong queue nếu có
        $model_notification = new PushNotification();
        $out = $model_notification->getPushNotificationToCheckDuplicate($param);
        $res = false;
        //Nếu tìm thấy dữ liệu trùng
        if (count($out) > 0) {
            $res = true;
        }
        return $res;
    }
}
