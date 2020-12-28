<?php

/*
 * thực hiện kết nối tới api của ISC trả kết quả
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;
use SoapFault;
use SoapClient;
use Ixudra\Curl\Facades\Curl;

class Apiisc extends Model {

    var $link_API_QGD = 'http://hi-pri.fpt.vn/';
    var $link_API = 'http://cemcc.fpt.net/';
    var $link_API_new = 'http://parapi.fpt.vn/api/RadAPI/';
    var $link_API_test = 'http://cemcc-dev.fpt.net/';
    var $link_API_post = 'http://parapiora.fpt.vn/api/';
    var $link_API_Physical = 'http://systemapi.fpt.vn/api/';
    var $link_API_Physical_test = 'http://systemapitest.fpt.vn/api/';	
//  var $link_API_Cam = 'http://parcamapi.fpt.org/api/';
    var $link_API_Cam = 'http://parcamapi.fpt.vn/api/';
    var $link_API_Cam_Call = 'http://callcenterapi.fpt.vn/API/CAM/';
    var $link_API_118_69_241_22 = 'http://118.69.241.22/cam/';

    public function GetInforContractQGDApi($data) {
        $uri = $this->link_API_QGD . 'api/Contract/GetInforContractQGD';
        return $this->getApiMobiPay($uri, $data);
    }

    /*
     * lấy lịch sử hỗ trợ
     * •  spCC_GetCallerHistoryByObjID 
      Input:
      iObjID: ID hợp đồng (NOT NULL)
      iRecordCount: số cuộc gọi tối đa cần lấy (NOT NULL)
      Output:
      StartDate: thời gian bắt đầu cuộc gọi
      EndDate: thời gian kết thúc cuộc gọi
      ContactName: họ tên khách hàng
      ContactPhone: số điện thoại khách hàng gọi lên
      SupportInfo: nội dung hỗ trợ
      CallerTypeName: phân loại cuộc gọi
      HelpdeskIPPhone: số điện thoại nhân viên hỗ trợ
      HelpdeskName: account nhân viên hỗ trợ
      SupportTypeName: phân loại hỗ trợ
      CallerStatusName: tình trạng cuộc gọi
      DivisionName: phòng ban hỗ trợ
      SupDescription: ghi chú của Sup
      DVStatus: tình trạng division
      DVDescription: ghi chú Division
      DVDate: thời gian xử lý Division
      TimeDV: thời gian xử lý Division tính từ thời điểm tạo (đơn vị: giây)
      DUser: account nhân viên xử lý Division
      ID: ID cuộc gọi

      http://cemcc.fpt.net/wscustomerinfo.asmx/spCC_GetCallerHistoryByObjID?ObjID=1020104442&RecordCount=10

     */

    public function getCallerHistoryByObjID($inputArray) {
        $uri = $this->link_API_118_69_241_22 . 'api/get_call_histories.php';
        return $this->postAPIJson($uri, $inputArray);
    }

    /* Lấy thông tin khách hàng
     * spCC_ObjectGetByAll2 
      Input:
      CustomerName: tên khách hàng (không bắt buộc)
      Passport: số CMND của khách hàng (không bắt buộc)
      CompanyName: tên công ty (không bắt buộc)
      Certificate: số giấy đăng ký kinh doanh của KH đại lý (KH đăng ký gói Public) (không bắt buộc)
      Contract: số hợp đồng (NOT NULL)
      Phone: số điện thoại (không bắt buộc)
      Address: địa chỉ (không bắt buộc)
      LocationID: ID vùng miền (NOT NULL)
      LoginName: tên truy nhập (không bắt buộc)
      Email: địa chỉ Email (không bắt buộc)
      AddressType: =1: địa chỉ lắp đặt, =2: địa chỉ thanh toán (NOT NULL)
      Birthday: ngày sinh của khách hàng
      Sex: giới tính của khách hàng
      Output:
      ObjID: ID hợp đồng
      Contract: số hợp đồng
      FullName: họ tên khách hàng
      Status: tình trạng hợp đồng
      Passport: số CMND của khách hàng
      Address: địa chỉ của khách hàng
     */

    public function GetFullAccountInfo($inputArray) {
        $uri = $this->link_API_Cam . 'RPDeployment/GetByObjID';
        $result = $this->postAPI($uri, $inputArray);
        return $result;
    }

    public function GetSubTeamID($inputArray) {
        $uri = $this->link_API_Cam . 'RPMaintaince/GetSubTeamID_API';
        return $this->postAPI($uri, $inputArray);
    }

    public function checkRulePhysicalApi($inputArray) {
        $uri = $this->link_API_Physical . 'CLPhysicalErr/CLPhysicalError';
        return $this->postAPIPhysic($uri, $inputArray, '967044DB85F1AA32D8F66A0E80C00642');
    }

    public function checkCreateClApi($inputArray) {
        $uri = $this->link_API_post . 'ISMaintaince/PreSupportListCheck';
        return $this->postAPI($uri, $inputArray);
    }


    public function checkRemainPTCApi($inputArray) {
        $uri = $this->link_API_new . 'CheckRemainPTC';
        return $this->getAPI($uri, $inputArray);
    }
    
    public function SupportListCheckApi($inputArray) {
        $uri = $this->link_API_Cam . 'RPMaintaince/SupportListCheck';
        return $this->postAPI($uri, $inputArray);
    }
    

    public function SupportListDSLCreate($new) {
        $uri = $this->link_API_Cam . 'ISMaintaince/SupportListDSLCreate_API';
        return $this->postAPI($uri, $new);
    }

    public function SupportListGetByObjID($inputArray) {
        $uri = $this->link_API_Cam . 'RPMaintaince/CheckListGetList_API';
        return $this->postAPI($uri, $inputArray);
    }

    public function GetPreChecklistByObjID($inputArray) {
        $uri = $this->link_API_Cam_Call . 'GetPrechecklistByObjid';
        return $this->postAPI($uri, $inputArray);
    }
    
    public function GetFirstStatusNameApi() {
        $uri = $this->link_API_Cam_Call . 'GetFirstStatusName';
        return $this->postAPI($uri);
    }
    
    public function getGroupPointApi($inputArray)
    {
         $uri = $this->link_API_Cam . 'RPMaintaince/GetODCCableTypeContractADSL';
        return $this->postAPI($uri, $inputArray);
    }
    
    public function CreatePreChecklist($inputArray) {
        $uri = $this->link_API_Cam_Call . 'CreatePreChecklist';
        return $this->postAPI($uri, $inputArray);
    }

    public function getOwnerTypeApi($inputArray) {
        $uri = $this->link_API_post . 'ApiCommon/GetOwnerTypeByInitStatus';
        return $this->postAPI($uri, $inputArray);
    }

    public function PartnerTimezoneAbility_List($inputArray) {
        $uri = $this->link_API_Cam . 'RPMaintaince//TimezoneAbilityList_API';
        return $this->postAPI($uri, $inputArray);
    }

    public function supportListRemainCheckApi($inputArray) {
        $uri = $this->link_API_post . 'RPMaintaince/SupportListRemainCheck';
        return $this->postAPI($uri, $inputArray);
    }

    public function SupportList_Assign_Insert($inputArray) {
        $uri = $this->link_API_Cam . 'ISMaintaince/SupportListAssignInsert_API';
        return $this->postAPI($uri, $inputArray);
    }

    /*
     * Chuyển phòng ban khác
     */

    public function forwardDepartment($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/PR_InsertDiscussion';
        return $this->getAPI($uri, $info);
    }

    public function getListBillApi($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/sp_GetDataConnection';
        return $this->getAPI($uri, $info);
    }

    public function getInfoConnectApi($serviceChoosen, $info) {
        return $this->getWebServiceSoap($serviceChoosen, $info);
    }

    public function getPromoInfoApi($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/spCC_PromotionGetByObjID';
        return $this->getAPI($uri, $info);
    }

    public function getIptvStatusApi($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/BIL_Billing_CommitmentTime';
        return $this->getAPI($uri, $info);
    }

    public function GetInfoBoxApi($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/BIL_IPTV_GetInfoBox';
        return $this->getAPI($uri, $info);
    }

    public function GetInfoIptvApi($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/BIL_Billing_GetCustomerProm';
        return $this->getAPI($uri, $info);
    }

    public function GetInfoDeployApi($info) {
        $uri = $this->link_API_post . 'RPDeployment/TS_ViewInfoDeploymentTT';
        return $this->postAPI($uri, $info);
    }

    public function getGetListMacServiceApi($contract) {
        $uri = 'https://fbox-partners.fpt.vn/rad';
        $dataArray = ['method' => 'getCustomerService_Info',
            'Contract' => $contract
        ];
        return $this->getAPI($uri, $dataArray);
    }

    public function getInfoPtvApi($info) {
        $arrayResult = [];
        foreach ($info['Service'] as $key => $value) {
            $urlTv = 'http://fbox-es-api.fptplay.net.vn/history/user';
            $urlTv.='/' . $value['id'] . '/' . $info['Mac']['MAC'] . '/' . $info['StartDate'] . '/' . $info['EndDate'] . '/30';
            $listService = $this->getAPI($urlTv)['Root'];
            foreach ($listService as $key => $value) {
                array_push($arrayResult, $value);
            }
        }
        return $arrayResult;
    }

    public function CheckBandwidthByObjID($info) {
        $uri = $this->link_API . 'WSCustomerInfo.asmx/CheckBandwidthByObjID';
        return $this->getAPI($uri, $info);
    }

//    public function getWebServiceSoap($info) {
//        $opts = ['ssl' => ['ciphers' => 'RC4-SHA']];
//        $objSoapClient = new SoapClient(
//            'http://cc.fpt.net/Services.asmx?WSDL', [
//            'encoding' => 'ISO-8859-1',
//            'stream_context' => stream_context_create($opts)
//            ]
//        );
//
//        $result = $objSoapClient->__soapCall('GetConnPro', $info);
//    }

    public function getWebServiceSoap($serviceChosen, $info) {
// Add a new service to the wrapper
        $response = '';
        SoapWrapper::add(function ($service) {
            $service
                    ->name('GetConnPro')
                    ->wsdl('http://cc.fpt.net/Services.asmx?WSDL');
        });
        $resultResponse = SoapWrapper::service($serviceChosen, function ($service) use ($info, $serviceChosen, &$response) {
                    $responseResult = $service->call($serviceChosen, [$info]);
                    $xmlString = $responseResult->GetConnProResult->any;
                    $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $xml = json_decode(json_encode((array) $xml), true);
                    $response = $xml['NewDataSet']['Table1'];
                });
        return $response;
    }

    /*
     * gọi api của ISC qua CURL
     */

    public function getAPI($uri, $params = []) {
        $result = Curl::to($uri)
                ->withData($params)
                ->returnResponseObject()
                ->get();
        if (isset($result->error))
            return $result->error;
        else
            return $result->content;
    }

    /*
     * gọi api của Tuyen qua CURL
     */

    public function getAPITuyen($uri, $params = []) {
        $result = Curl::to($uri)
                ->withData($params)
//                ->returnResponseObject()
                ->get();
//        if (isset($result->error))
//            return $result->error;
//        else
//            return $result->content;
        return $result;
    }

    public function postAPI($uri, $params = []) {
        $result = Curl::to($uri)
                ->withData($params)
                ->returnResponseObject()
                ->post();
        if (isset($result->error))
            return $result->error;
        else
            return $result->content;
    }

    public function postAPIJson($uri, $params = []) {
        $result = Curl::to($uri)
            ->withData($params)
            ->returnResponseObject()
            ->asJson()
//            ->withProxy('210.245.31.15', 80)
            ->post();
        if (isset($result->error))
            return $result->error;
        else
            return $result->content;
    }

    private function postAPIPhysic($uri, $data, $header) {
        $str_data = json_encode($data);
//        $uri = $this->link_API_test_post . $url;
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Token: 967044DB85F1AA32D8F66A0E80C00642'
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

    public function getApiMultiple($uri, $params = '', $method = 'GET') {
//$dataString = json_encode($params);
        $ch[0] = curl_init();
        $ch[1] = curl_init();
        $mh = curl_multi_init();
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch[0], CURLOPT_POST, true);
            curl_setopt($ch[0], CURLOPT_POSTFIELDS, $dataString);
        } else if ($method == 'DELETE') {
            curl_setopt($ch[0], CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch[0], CURLOPT_POSTFIELDS, $dataString);
        } else if ($method == 'PUT') {
            curl_setopt($ch[0], CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch[0], CURLOPT_POSTFIELDS, $dataString);
        }
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch[1], CURLOPT_POST, true);
            curl_setopt($ch[1], CURLOPT_POSTFIELDS, $dataString);
        } else if ($method == 'DELETE') {
            curl_setopt($ch[1], CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch[1], CURLOPT_POSTFIELDS, $dataString);
        } else if ($method == 'PUT') {
            curl_setopt($ch[1], CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch[1], CURLOPT_POSTFIELDS, $dataString);
        }
        curl_setopt($ch[0], CURLOPT_URL, 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?IDPreCheckList=1019107572,1019107542,1019107482,1019107412,1019107392');
        curl_setopt($ch[0], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch[0], CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch[0], CURLOPT_TIMEOUT, 90);
        curl_setopt($ch[0], CURLOPT_HTTPGET, 1);


        curl_setopt($ch[1], CURLOPT_URL, 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?IDPreCheckList=1019107572,1019107542,1019107482,1019107412,1019107392');
        curl_setopt($ch[1], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch[1], CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch[1], CURLOPT_TIMEOUT, 90);
        curl_setopt($ch[1], CURLOPT_HTTPGET, 1);


        curl_multi_add_handle($mh, $ch[0]);
        curl_multi_add_handle($mh, $ch[1]);


        //Execute the handles
        $active = null;
//execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
//          var_dump(curl_exec($ch[1]));
        foreach ($ch as $a) {
            $result[] = curl_multi_getcontent($a);
            var_dump(curl_multi_getcontent($a));
            curl_multi_remove_handle($mh, $a);
//    parse_str($result, $arr);
//     print_r($arr);
//    echo "--------------------------------------------------\n";
//    var_dump($arr);
        }
//var_dump($result);
        //Close curl
        curl_multi_close($mh);
        die;
        if (FALSE === $result) {
//            throw new Exception(curl_error($ch), curl_errno($ch));
            var_dump(curl_error($ch));
            var_dump(curl_errno($ch));
            die;
        }
        var_dump($result);
        die;
        return $result;
    }

    public function getApiMobiPay($uri, $params = '', $method = 'POST') {
        $response = Curl::to($uri)
                ->withContentType('application/json')
                ->withData($params)
                ->asJson()
                ->returnResponseObject()
                ->post();
        if (isset($response->error)) {
            return
                    [
                        'success' => false,
                        'result' => $response->error
            ];
        } else {
            return
                    [
                        'success' => true,
                        'result' => $response->content
            ];
        }
    }

}
