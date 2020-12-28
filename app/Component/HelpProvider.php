<?php

namespace App\Component;

use Illuminate\Support\Facades\Mail;

class HelpProvider {

    public function responseSuccess($info) {
        $data = [
            'id' => 'success',
            'status' => 200,
            'detail' => $info,
        ];
        $status = 200;
        return response()->json($data, $status);
    }

    public function responseFail($error, $msg = null) {
        $allFalse = ['missingPost', 'emptyPost', '400', '500', '406', 'missing', 'empty'];

        if (is_int($error)) {
            if ($error < 400 || $error > 500) {
                $error = 500;
            }
        } else {
            $search = array_search($error, $allFalse);
            if ($search === false) {
                $error = 500;
            }
        }

        $content1 = config('errors.error_' . $error);
        $content2 = config('errors.' . $content1['id']);
        $data = array_merge($content1, $content2);
        if (!empty($msg)) {
            $data['detail'] = $msg;
        }
        $data['status'] = (int)$data['status'];
        $status = 200;
        return response()->json($data, $status);
    }

    public function checkPost($input, $condition) {
        foreach ($condition as $val) {
            if (!isset($input[$val])) {
                return ['status' => 'missingPost', 'msg' => 'Missing field ' . $val . ' input'];
            }

            if (is_array($input[$val])) {
                if (empty($input[$val])) {
                    return ['status' => 'emptyPost', 'msg' => $val . ' is empty'];
                }
            } else {
                if (trim($input[$val]) === '') {
                    return ['status' => 'emptyPost', 'msg' => $val . ' is empty'];
                }
            }
        }
        return ['status' => true, 'msg' => null];
    }

    public static function validateDate(&$date) {
        $testDate = date_create($date);
        if ($testDate === false) {
            return false;
        }
        return true;
    }

    public static function validateDateStartEnd(&$date_start, &$date_end) {
        $start = date_create($date_start);
        if ($start === false) {
            return false;
        }

        $end = date_create($date_end);
        if ($end === false) {
            return false;
        }

        $res = date_diff($start, $end);
        if ($res->invert !== 0) {
            return false;
        }
        $date_start = $start;
        $date_end = $end;
        return true;
    }

    public static function validateDateStartEndForSearchFullDay(&$date_start, &$date_end) {
        $testStart = date_create($date_start);
        if ($testStart === false) {
            return false;
        }
        $testEnd = date_create($date_end);
        if ($testEnd === false) {
            return false;
        }

        $start = date_create(date_format($testStart, 'Y-m-d 0:0:0'));
        $end = date_create(date_format($testEnd, 'Y-m-d 23:59:59'));
        $res = date_diff($start, $end);
        if ($res->invert !== 0) {
            return false;
        }
        $date_start = $start;
        $date_end = $end;
        return true;
    }

    public function getCondition($name) {
        $condition = [
            'getResultSurveys' => ['contract'],
            'saveResultSurveys' => [
                'dataaccount', 'datapost', 'name',
                'note', 'type', 'time_completed', 'time_start'
            ],
            'getInfoSalaryIBB' => ['date_start', 'date_end'],
            'getInfoSalaryTinPNC' => ['date_start', 'date_end'],
            'getReponseAcceptInfo' => ['code', 'name'],
            'generateLinkEmailSurvey' => ['ContractID', 'TransactionID', 'Type'],
            'saveInfoHiFPT' => ['QuestionAnswer', 'HiFPTInfo'],
        ];
        return $condition[$name];
    }

    public function getDescriptionForSendSale($template) {
        $result = [
            'code' => $template['code'],
            'csat' => $template['csat'],
            'point' => $template['point'],
            'note' => $template['note'],
            'confirm_code' => $template['confirm_code'],
            'time' => $template['time'],
        ];
        return json_encode($result);
    }

    public function getParamApiSale($paramMail) {
        $arraySale = [
            'UserName' => $paramMail['user_name_send'],
            'Contract' => $paramMail['shd'],
            'ActionID' => 442,
            'Desc' => $paramMail['description'],
            'DivisionID' => 82,
            'Priority' => 3,
            'Email' => null,
            'FullName' => null,
            'PhoneNumber' => null,
        ];

        $input = [
            'sale' => $arraySale,
            'tech' => null,
            'tele' => null,
        ];
        return $input;
    }

    public function getParamApiTech($paramMail) {
        $arrayTech = [
            "SupportID" => $paramMail['code'],
            "SupportType" => $paramMail['num_type'],
//			"Description" => $paramMail['template'],
            "RefID" => "",
            "RefType" => 0,
//			'Subject' => $paramMail['subject'],
        ];

        $input = [
            'tele' => null,
            'sale' => null,
            'tech' => $arrayTech
        ];
        return $input;
    }

    public function getParamApiTele($paramMail) {
        $arrayTele = [
            'Contract' => $paramMail['shd'],
        ];

        $input = [
            'tele' => $arrayTele,
            'sale' => null,
            'tech' => null,
        ];
        return $input;
    }

    public function getParamApiCL($paramMail) {
        $arrayCL = [
            "SupportID" => $paramMail['code'],
            "SupportType" => $paramMail['num_type'],
            "RefID" => "",
            "RefType" => 0,
        ];

        $input = [
            'tele' => null,
            'sale' => null,
            'tech' => null,
            'cl' => $arrayCL,
        ];
        return $input;
    }

    public function getParamApiQGD($paramMail) {
        $arrayQGD = [
            "Key" => $paramMail['num_type'],
            "CreateBy" => $paramMail['user_name_send'],
        ];

        $input = [
            'tele' => null,
            'sale' => null,
            'tech' => null,
            'cl' => null,
            'qgd' => $arrayQGD,
        ];
        return $input;
    }

    public function getParamApiCUS($paramMail) {
        $arrayCUS = [
            "Key" => $paramMail['num_type'],
            "CreateBy" => $paramMail['user_name_send'],
        ];

        $input = [
            'tele' => null,
            'sale' => null,
            'tech' => null,
            'cl' => null,
            'qgd' => null,
            'cus' => $arrayCUS,
        ];
        return $input;
    }

    public function sendMail($input, $mail, $type = 'Tele', $mailCC = []) {
        $cc = [
            'huydp2@fpt.com.vn',
        ];

        if (!empty($mailCC)) {
            $cc = $mailCC;
            $cc[] = 'huydp2@fpt.com.vn';
        }

        if (env('APP_ENV') == 'local') {
            $mail = 'huydp2@fpt.com.vn';
            $cc = [
            ];
        }

        $subject = $input['paramMail']['subject'];

        switch ($type) {
            case 'Tele':
                Mail::send('emails.sendNotification', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to($mail);
                    $message->cc($cc);
                    $message->subject($subject);
                });
                break;
            case 'Tech':
                Mail::send('emails.sendNotification', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to($mail);
                    $message->cc($cc);
                    $message->subject($subject);
                });
                break;
            case 'CL':
                Mail::send('emails.sendNotificationCheckList', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to($mail);
                    $message->cc($cc);
                    $message->subject($subject);
                });
                break;
            case 'QGD':
                Mail::send('emails.sendNotificationQGD', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to($mail);
                    $message->cc($cc);
                    $message->subject($subject);
                });
                break;
            case 'CUS':
                Mail::send('emails.sendNotificationCUS', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
                    $message->from('rad.support@fpt.com.vn', 'Support');
                    $message->to($mail);
                    $message->cc($cc);
                    $message->subject($subject);
                });
                break;
            default:
                break;
        }
    }

//    public function sendMailTest($input, $mail,$type = 'Tele', $mailCC = []){
//        $mail = 'huydp2@fpt.com.vn';
//        $cc = [
//
//        ];
//
//        $subject = $input['paramMail']['subject'];
//
//        switch($type){
//            case 'Tele':
//                Mail::send('emails.sendNotification', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
//                    $message->from('rad.support@fpt.com.vn', 'Support');
//                    $message->to($mail);
//                    $message->cc($cc);
//                    $message->subject($subject);
//                });
//                break;
//            case 'CL':
//                Mail::send('emails.sendNotificationCheckList', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
//                    $message->from('rad.support@fpt.com.vn', 'Support');
//                    $message->to($mail);
//                    $message->cc($cc);
//                    $message->subject($subject);
//                });
//                break;
//            case 'QGD':
//                Mail::send('emails.sendNotificationQGD', ['param' => $input['paramMail']], function ($message) use ( $mail, $cc, $subject) {
//                    $message->from('rad.support@fpt.com.vn', 'Support');
//                    $message->to($mail);
//                    $message->cc($cc);
//                    $message->subject($subject);
//                });
//                break;
//            default:
//                break;
//        }
//    }

    public function checkCountFromString($str1, $str2) {
        $num1 = explode(',', $str1);
        $num2 = explode(',', $str2);
        if (count($num1) >= count($num2)) {
            return true;
        }
        return false;
    }

    public function checkConfirmEmail($str, $mail) {
        return str_contains(strtolower($mail), strtolower($str));
    }

    // xử lý dữ liệu từ ISC trả về
    public function processDataFromISC($data) {
        $dateFormat = 'Y-m-d h:i:s'; //config('app.datetime_format');
        if (!empty($data->ContractDate)) {
            $data->ContractDate = date($dateFormat, strtotime($data->ContractDate));
        }
        // end kiểm tra triển khai, bảo trì	
        if (!empty($data->FinishDateINF)) {
            $data->FinishDateINF = date($dateFormat, strtotime($data->FinishDateINF));
        }
        if (!empty($data->FinishDateList)) {
            $data->FinishDateList = date($dateFormat, strtotime($data->FinishDateList));
        }
        return $data;
    }

}
