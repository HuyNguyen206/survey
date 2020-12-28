<?php

namespace App\Http\Controllers;

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
use App\Helpers\Helper;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Component\HelpProvider;
use App\Jobs\SendNotificationEmail;
use App\Models\Api\ApiHelper;
use Exception;
use DB;
//use Redis;
use Illuminate\Support\Facades\Redis;

class FunctionController extends Controller {

    public function getListBill(Request $request) {
        $data = $request->input();
        $infoAcc = array('ObjID' => $data['objid'],
        );
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->getListBillApi($infoAcc));
        return json_encode(['code' => 200, 'billInfo' => $result]);
    }

    public function getInfoConnect(Request $request) {
        $data = $request->input();
        $info = array(
            'parent' => $data['region'],
            'objId' => $data['objid']
//            'parent' => 'FTS',
//            'objId' => 1017286732
        );
        $apiIsc = new Apiisc();
        $result = $apiIsc->getInfoConnectApi('GetConnPro', $info);
        return json_encode(['code' => 200, 'connectInfo' => $result]);
    }

    public function getPromoInfo(Request $request) {
        $data = $request->input();
        $info = array(
            'objId' => $data['objid']
        );
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->getPromoInfoApi($info));
        return json_encode(['code' => 200, 'promoInfo' => $result]);
    }

    public function getIptvStatus(Request $request) {
        $data = $request->input();
        $info = array(
            'ObjID' => $data['objid']
        );
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->getIptvStatusApi($info));
        return json_encode(['code' => 200, 'statusIptv' => $result]);
    }

    public function GetInfoBox(Request $request) {
        $data = $request->input();
        $info = array(
            'ObjID' => $data['objid']
//            'ObjID' => 500488455,
        );
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->GetInfoBoxApi($info));
        return json_encode(['code' => 200, 'infoBox' => $result]);
    }

    public function GetInfoIptv(Request $request) {
        $data = $request->input();
        $info = array(
            'ObjID' => $data['objid']
//            'ObjID' => 1024900082,
        );
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->GetInfoIptvApi($info));
        return json_encode(['code' => 200, 'infoIptv' => $result]);
    }

    public function GetInfoDeploy(Request $request) {
        $data = $request->input();
        $info = array(
            'ObjID' => $data['objid'],
            'LocationID' => $data['locationId'],
//            'LocationID' => 4,
//            'ObjID' => 1023909552
        );
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->GetInfoDeployApi($info), true);
        return (['code' => 200, 'infoDeploy' => $result['data']]);
    }

    public function getInfoPtv(Request $request) {
        $data = ($request->input());
        $data = $data['data'];
        $StartDate = date('Y-m-d', strtotime(str_replace('/', '-', $data['StartDate'])));
        $EndDate = date('Y-m-d', strtotime(str_replace('/', '-', $data['EndDate'])));
        $data['StartDate'] = $StartDate;
        $data['EndDate'] = $EndDate;
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->getInfoPtvApi($data), true);
        return json_encode(['code' => 200, 'infoPtv' => $result]);
    }

    public function GetListMacService(Request $request) {
        $data = $request->input();
        $apiIsc = new Apiisc();
        $result = json_decode($apiIsc->getGetListMacServiceApi($data['contract']), true);
        if ($result['Root']['Result'] == 0) {
            return json_encode(['code' => 500]);
        } else
            return json_encode(['code' => 200, 'listMacSer' => $result['Root']['item']]);
    }

}
