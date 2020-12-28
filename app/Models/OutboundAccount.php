<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class OutboundAccount extends Model {

    protected $table = 'outbound_accounts';
    protected $fillable = [

        'objid', 'contract_num', 'contract_date', 'customer_name', 'passport', 'company_name',
        'certificate_number', 'address', 'address_bill_to', 'contract_type', 'contract_type_name',
        'contract_status', 'contract_status_name', 'login_name', 'email', 'location', 'region',
        'location_id', 'user_name', 'branch_code', 'suspend_date', 'suspend_reason', 'obj_address',
        'legal_entity_name', 'partner_name', 'eoc_name', 'fee_local_type', 'description', 'birthday',
        'sex', 'acc_sale', 'package_sal', 'account_inf', 'finish_date_inf', 'account_list',
        'finish_date_list', 'center_list', 'phone', 'payment_type', 'account_payment', 'payment_account',
        'sub_parent_desc', 'use_service', 'email_inf', 'email_list', 'email_sale',
        'kind_deploy', 'kind_main',
        'location_id', 'location', 'sub_parent_desc', 'region', 'branch_code', 'loai_hd', 'tinh_trang', 'vung_mien', 'ten_kh', 'passport', 'company_name', 'fee_local_type', 'dia_chi_kh', 'acc_sale', 'acc_tin_pnc_thicong', 'acc_tin_pnc_baotri',
        'so_hd', 'ngay_tao_hd', 'ten_truy_cap', 'email', 'dia_chi_lap_dat', 'dia_chi_thanh_toan', 'kh_tiep_nhan_ld', 'kh_su_dung_internet', 'kh_su_dung_truyenhinh', 'user_created', 'date_entered', 'user_gender', 'payment_type', 'payment_account', 'contact_person', 'birthday', 'supporter', 'sub_supporter'];

    /*
     * lấy thông tin khách hàng từ database survey
     */

    public function getAccountInfoByContractNum($Contractnum) {
        return DB::table('outbound_accounts')->where('contract_num', '=', $Contractnum)->first();
    }

    //save account
    public function saveAccount($infoSave) {
        $contract = $createContract = '';
        try {
//            if(!empty($infoSave['ContractNum'])){
//                $temp = explode('/ ',$infoSave['ContractNum']);
//                $contract = $temp[0];
//                $createContract = $temp[1];
//            }
            $contract = isset($infoSave['ContractNum']) ? $infoSave['ContractNum'] : $infoSave['contract_num'];
            $result = OutboundAccount::updateOrCreate(['so_hd' => $contract], [
                    'objid' => isset($infoSave['ObjID']) ? $infoSave['ObjID'] : '',
                    'contract_num' => isset($infoSave['ContractNum']) ? $infoSave['ContractNum'] : '',
                    'contract_date' => isset($infoSave['ContractDate']) ? $infoSave['ContractDate'] : '',
                    'customer_name' => isset($infoSave['CustomerName']) ? $infoSave['CustomerName'] : '',
                    'passport' => isset($infoSave['Passport']) ? $infoSave['Passport'] : '',
                    'company_name' => isset($infoSave['CompanyName']) ? $infoSave['CompanyName'] : '', //'ten cong ty',
                    'certificate_number' => isset($infoSave['CertificateNumber']) ? $infoSave['CertificateNumber'] : '', // 'số giấy đăng ký kinh doanh của KH đại lý (KH đăng ký gói Public)',
                    'address' => isset($infoSave['Address']) ? $infoSave['Address'] : '',
                    'address_bill_to' => isset($infoSave['BillTo']) ? $infoSave['BillTo'] : '',
                    'contract_type' => isset($infoSave['ContractType']) ? $infoSave['ContractType'] : '',
                    'contract_type_name' => isset($infoSave['ContractTypeName']) ? $infoSave['ContractTypeName'] : '',
                    'contract_status' => isset($infoSave['ContractStatus']) ? $infoSave['ContractStatus'] : '',
                    'contract_status_name' => isset($infoSave['ContractStatusName']) ? $infoSave['ContractStatusName'] : '',
                    'login_name' => isset($infoSave['LoginName']) ? $infoSave['LoginName'] : '',
                    'email' => isset($infoSave['Email']) ? $infoSave['Email'] : '',
                    'location' => isset($infoSave['Location']) ? $infoSave['Location'] : '', // 'Location: vùng miền ',
                    'region' => isset($infoSave['Region']) ? $infoSave['Region'] : '', // 'khu vực (miền Bắc, miền Nam)',
                    'location_id' => isset($infoSave['LocationID']) ? $infoSave['LocationID'] : '', //'ID vùng miền',
                    'user_name' => isset($infoSave['user_name']) ? $infoSave['user_name'] : '',
                    'branch_code' => isset($infoSave['BranchCode']) ? $infoSave['BranchCode'] : '', // 'chi nhánh',
                    'suspend_date' => isset($infoSave['Suspend_Date']) ? $infoSave['Suspend_Date'] : '', // 'ngày tạm dừng dịch vụ',
                    'suspend_reason' => isset($infoSave['Suspend_Reason']) ? $infoSave['Suspend_Reason'] : '', // 'nguyên nhân tạm dừng',
                    'obj_address' => isset($infoSave['ObjAddress']) ? $infoSave['ObjAddress'] : '', // 'địa chỉ hợp đồng',
                    'legal_entity_name' => isset($infoSave['LegalEntityName']) ? $infoSave['LegalEntityName'] : '', // 'Loại khách hàng (FPT/Doi tac)',
                    'partner_name' => isset($infoSave['PartnerName']) ? $infoSave['PartnerName'] : '', // 'tên đối tác',
                    'eoc_name' => isset($infoSave['EocName']) ? $infoSave['EocName'] : '', // 'hạ tầng của khách hàng',
                    'fee_local_type' => isset($infoSave['FeeLocalType']) ? $infoSave['FeeLocalType'] : '', //goi tinh cuo
                    'description' => isset($infoSave['Description']) ? $infoSave['Description'] : '',
                    'birthday' => isset($infoSave['Birthday']) ? $infoSave['Birthday'] : '',
                    'sex' => isset($infoSave['Sex']) ? $infoSave['Sex'] : '',
                    'acc_sale' => isset($infoSave['AccountSale']) ? $infoSave['AccountSale'] : '', // 'acc sale bán',
                    'package_sal' => isset($infoSave['PackageSal']) ? $infoSave['PackageSal'] : '', //'Dịch vụ truyền hình',
                    'account_inf' => isset($infoSave['AccountINF']) ? $infoSave['AccountINF'] : '', // 'Account thi công',
                    'finish_date_inf' => isset($infoSave['FinishDateINF']) ? $infoSave['FinishDateINF'] : '', // 'Hoàn tất thi công',
                    'account_list' => isset($infoSave['AccountList']) ? $infoSave['AccountList'] : '', // 'Account bảo trì',
                    'finish_date_list' => isset($infoSave['FinishDateList']) ? $infoSave['FinishDateList'] : '', // 'Hoàn tất bảo trì',
                    'center_list' => isset($infoSave['CenterList']) ? $infoSave['CenterList'] : '', // 'Trung tâm (INDO hoặc TIN/PNC)',
                    'phone' => isset($infoSave['Phone']) ? $infoSave['Phone'] : '', //,
                    'payment_type' => isset($infoSave['PaymentType']) ? $infoSave['PaymentType'] : '', //,
                    'account_payment' => isset($infoSave['AccountPayment']) ? $infoSave['AccountPayment'] : '', // 'account thu cước',
                    'payment_account' => isset($infoSave['AccountPayment']) ? $infoSave['AccountPayment'] : '', //,
                    'sub_parent_desc' => isset($infoSave['SubParentDesc']) ? $infoSave['SubParentDesc'] : '', // 'Vùng kinh doanh (Vùng 1, Vùng 2, Vùng 3',
                    'use_service' => isset($infoSave['UseService']) ? $infoSave['UseService'] : '', // 'dịch vụ sử dụng (1=TV only; 2=Internet; 3=TV & Internet)',
                    'email_inf' => isset($infoSave['EmailINF']) ? $infoSave['EmailINF'] : '', // 'Email nhân viên triển khai',
                    'email_list' => isset($infoSave['EmailList']) ? $infoSave['EmailList'] : '', // 'Email nhân viên bảo trì',
                    'email_sale' => isset($infoSave['EmailSale']) ? $infoSave['EmailSale'] : '', // 'Email nhân viên sale',
                    'kind_deploy' => isset($infoSave['KindDeploy']) ? $infoSave['KindDeploy'] : '', // 'Loại dịch vụ triển khai',
                    'kind_main' => isset($infoSave['KindMain']) ? $infoSave['KindMain'] : '', // 'Loại dịch vụ bảo trì',
                    'loai_hd' => isset($infoSave['ContractTypeName']) ? $infoSave['ContractTypeName'] : '',
                    'tinh_trang' => isset($infoSave['ContractStatusName']) ? $infoSave['ContractStatusName'] : '',
                    'vung_mien' => isset($infoSave['Region']) ? $infoSave['Region'] : '',
                    'ten_kh' => isset($infoSave['CustomerName']) ? $infoSave['CustomerName'] : '',
                    'passport' => isset($infoSave['Passport']) ? $infoSave['Passport'] : '',
                    'company_name' => isset($infoSave['CompanyName']) ? $infoSave['CompanyName'] : '',
                    'fee_local_type' => isset($infoSave['FeeLocalType']) ? $infoSave['FeeLocalType'] : '',
                    'dia_chi_kh' => isset($infoSave['ObjAddress']) ? $infoSave['ObjAddress'] : '',
                    'acc_sale' => isset($infoSave['AccountSale']) ? $infoSave['AccountSale'] : '',
                    'acc_tin_pnc_thicong' => isset($infoSave['Constructor']) ? $infoSave['Constructor'] : '',
                    'acc_tin_pnc_baotri' => isset($infoSave['Maintenance']) ? $infoSave['Maintenance'] : '',
                    'so_hd' => isset($contract) ? $contract : '',
                    'ngay_tao_hd' => isset($createContract) ? $createContract : '',
                    'ten_truy_cap' => isset($infoSave['UserName']) ? $infoSave['UserName'] : '',
                    'email' => isset($infoSave['Email']) ? $infoSave['Email'] : '',
                    'birthday' => isset($infoSave['Birthday']) ? $infoSave['Birthday'] : '',
                    'location_id' => isset($infoSave['LocationID']) ? $infoSave['LocationID'] : '',
                    'location' => isset($infoSave['Location']) ? $infoSave['Location'] : '',
                    'sub_parent_desc' => isset($infoSave['SubParentDesc']) ? $infoSave['SubParentDesc'] : '',
                    'region' => isset($infoSave['Region']) ? $infoSave['Region'] : '',
                    'branch_code' => isset($infoSave['BranchCode']) ? $infoSave['BranchCode'] : '',
//                'contact_person' => isset($infoSave['con']) ? $infoSave['Phone'] : '',
                    'payment_account' => isset($infoSave['AccountPayment']) ? $infoSave['AccountPayment'] : '',
                    'dia_chi_lap_dat' => isset($infoSave['Address']) ? $infoSave['Address'] : '',
                    'dia_chi_thanh_toan' => isset($infoSave['BillTo']) ? $infoSave['BillTo'] : '',
                    'kh_tiep_nhan_ld' => isset($infoSave['CusReceivingSetup']) ? $infoSave['CusReceivingSetup'] : '',
                    'kh_su_dung_internet' => isset($infoSave['internet']) ? $infoSave['internet'] : '',
                    'kh_su_dung_truyenhinh' => isset($infoSave['paytv']) ? $infoSave['paytv'] : '',
                    'user_created' => 1,
                    'user_gender' => isset($infoSave['gender']) ? $infoSave['gender'] : '',
                    'date_entered' => date('Y-m-d'),
                    'supporter' => isset($infoSave['Supporter']) ? $infoSave['Supporter'] : '',
                    'sub_supporter' => isset($infoSave['SubSupporter']) ? $infoSave['SubSupporter'] : '',
            ]);
            $res['code'] = 200;
            $res['msg'] = 'Successful';
            $res['data'] = $result;
            return $res;
        } catch (\Exception $ex) {
            $res['code'] = 400;
            $res['msg'] = $ex->getMessage();
            $res['data'] = '';
            return $res;
        }
    }

    public function getAccountInfoByContract($contarct) {
        $result = OutboundAccount::select()->where('so_hd', "=", $contarct)->first();
        if (isset($result->id))
            return $result;
        return NULL;
    }

    public function getAccountInfoByContractZero($contarct) {
        $result = OutboundAccount::select(DB::raw("id, objid AS ObjID,contract_num AS ContractNum ,contract_date  AS ContractDate,customer_name  AS CustomerName , passport AS Passport, company_name AS CompanyName , certificate_number AS CertificateNumber, address AS Address,address_bill_to  AS BillTo ,contract_type AS ContractType ,contract_type_name AS ContractTypeName ,contract_status AS ContractStatus,contract_status_name AS ContractStatusName ,  login_name AS LoginName , email AS  Email , location AS Location, region AS Region , location_id  AS LocationID ,branch_code AS BranchCode, suspend_date  AS Suspend_Date, suspend_reason  AS Suspend_Reason, obj_address AS ObjAddress ,legal_entity_name AS LegalEntityName , partner_name AS PartnerName ,eoc_name  AS EocName ,fee_local_type  AS FeeLocalType, description  AS Description ,birthday AS Birthday, sex AS Sex ,acc_sale AS  AccountSale ,package_sal AS PackageSal , account_inf AS  AccountINF , finish_date_inf AS  FinishDateINF, account_list  AS  AccountList ,finish_date_list AS FinishDateList, center_list  AS CenterList ,phone AS Phone, payment_type  AS PaymentType ,account_payment  AS AccountPayment ,sub_parent_desc  AS SubParentDesc ,use_service  AS UseService ,email_inf  AS   EmailINF ,email_list AS EmailList , email_sale  AS EmailSale , kind_deploy AS KindDeploy ,kind_main  AS  KindMain ,supporter AS Supporter ,sub_supporter AS SubSupporter"))->where('so_hd', "=", $contarct)->first();
        return $result;
    }

    public function saveAccountHiFPT($infoSave) {
        $createContract = '';
        $contract = isset($infoSave['ContractNum']) ? $infoSave['ContractNum'] : $infoSave['contract_num'];
        $result = OutboundAccount::updateOrCreate(['so_hd' => $contract], [
            'objid' => isset($infoSave['ObjID']) ? $infoSave['ObjID'] : '',
            'contract_num' => isset($infoSave['ContractNum']) ? $infoSave['ContractNum'] : '',
            'contract_date' => isset($infoSave['ContractDate']) ? $infoSave['ContractDate'] : '',
            'customer_name' => isset($infoSave['CustomerName']) ? $infoSave['CustomerName'] : '',
            'passport' => isset($infoSave['Passport']) ? $infoSave['Passport'] : '',
            'company_name' => isset($infoSave['CompanyName']) ? $infoSave['CompanyName'] : '', //'ten cong ty',
            'certificate_number' => isset($infoSave['CertificateNumber']) ? $infoSave['CertificateNumber'] : '', // 'số giấy đăng ký kinh doanh của KH đại lý (KH đăng ký gói Public)',
            'address' => isset($infoSave['Address']) ? $infoSave['Address'] : '',
            'address_bill_to' => isset($infoSave['BillTo']) ? $infoSave['BillTo'] : '',
            'contract_type' => isset($infoSave['ContractType']) ? $infoSave['ContractType'] : '',
            'contract_type_name' => isset($infoSave['ContractTypeName']) ? $infoSave['ContractTypeName'] : '',
            'contract_status' => isset($infoSave['ContractStatus']) ? $infoSave['ContractStatus'] : '',
            'contract_status_name' => isset($infoSave['ContractStatusName']) ? $infoSave['ContractStatusName'] : '',
            'login_name' => isset($infoSave['LoginName']) ? $infoSave['LoginName'] : '',
            'email' => isset($infoSave['Email']) ? $infoSave['Email'] : '',
            'location' => isset($infoSave['Location']) ? $infoSave['Location'] : '', // 'Location: vùng miền ',
            'region' => isset($infoSave['Region']) ? $infoSave['Region'] : '', // 'khu vực (miền Bắc, miền Nam)',
            'location_id' => isset($infoSave['LocationID']) ? $infoSave['LocationID'] : '', //'ID vùng miền',
            'user_name' => isset($infoSave['user_name']) ? $infoSave['user_name'] : '',
            'branch_code' => isset($infoSave['BranchCode']) ? $infoSave['BranchCode'] : '', // 'chi nhánh',
            'suspend_date' => isset($infoSave['Suspend_Date']) ? $infoSave['Suspend_Date'] : '', // 'ngày tạm dừng dịch vụ',
            'suspend_reason' => isset($infoSave['Suspend_Reason']) ? $infoSave['Suspend_Reason'] : '', // 'nguyên nhân tạm dừng',
            'obj_address' => isset($infoSave['ObjAddress']) ? $infoSave['ObjAddress'] : '', // 'địa chỉ hợp đồng',
            'legal_entity_name' => isset($infoSave['LegalEntityName']) ? $infoSave['LegalEntityName'] : '', // 'Loại khách hàng (FPT/Doi tac)',
            'partner_name' => isset($infoSave['PartnerName']) ? $infoSave['PartnerName'] : '', // 'tên đối tác',
            'eoc_name' => isset($infoSave['EocName']) ? $infoSave['EocName'] : '', // 'hạ tầng của khách hàng',
            'fee_local_type' => isset($infoSave['FeeLocalType']) ? $infoSave['FeeLocalType'] : '', //goi tinh cuo
            'description' => isset($infoSave['Description']) ? $infoSave['Description'] : '',
            'birthday' => isset($infoSave['Birthday']) ? $infoSave['Birthday'] : '',
            'sex' => isset($infoSave['Sex']) ? $infoSave['Sex'] : '',
            'acc_sale' => isset($infoSave['AccountSale']) ? $infoSave['AccountSale'] : '', // 'acc sale bán',
            'package_sal' => isset($infoSave['PackageSal']) ? $infoSave['PackageSal'] : '', //'Dịch vụ truyền hình',
            'account_inf' => isset($infoSave['AccountINF']) ? $infoSave['AccountINF'] : '', // 'Account thi công',
            'finish_date_inf' => isset($infoSave['FinishDateINF']) ? $infoSave['FinishDateINF'] : '', // 'Hoàn tất thi công',
            'account_list' => isset($infoSave['AccountList']) ? $infoSave['AccountList'] : '', // 'Account bảo trì',
            'finish_date_list' => isset($infoSave['FinishDateList']) ? $infoSave['FinishDateList'] : '', // 'Hoàn tất bảo trì',
            'center_list' => isset($infoSave['CenterList']) ? $infoSave['CenterList'] : '', // 'Trung tâm (INDO hoặc TIN/PNC)',
            'phone' => isset($infoSave['Phone']) ? $infoSave['Phone'] : '', //,
            'payment_type' => isset($infoSave['PaymentType']) ? $infoSave['PaymentType'] : '', //,
            'account_payment' => isset($infoSave['AccountPayment']) ? $infoSave['AccountPayment'] : '', // 'account thu cước',
            'payment_account' => isset($infoSave['AccountPayment']) ? $infoSave['AccountPayment'] : '', //,
            'sub_parent_desc' => isset($infoSave['SubParentDesc']) ? $infoSave['SubParentDesc'] : '', // 'Vùng kinh doanh (Vùng 1, Vùng 2, Vùng 3',
            'use_service' => isset($infoSave['UseService']) ? $infoSave['UseService'] : '', // 'dịch vụ sử dụng (1=TV only; 2=Internet; 3=TV & Internet)',
            'email_inf' => isset($infoSave['EmailINF']) ? $infoSave['EmailINF'] : '', // 'Email nhân viên triển khai',
            'email_list' => isset($infoSave['EmailList']) ? $infoSave['EmailList'] : '', // 'Email nhân viên bảo trì',
            'email_sale' => isset($infoSave['EmailSale']) ? $infoSave['EmailSale'] : '', // 'Email nhân viên sale',
            'kind_deploy' => isset($infoSave['KindDeploy']) ? $infoSave['KindDeploy'] : '', // 'Loại dịch vụ triển khai',
            'kind_main' => isset($infoSave['KindMain']) ? $infoSave['KindMain'] : '', // 'Loại dịch vụ bảo trì',
            'loai_hd' => isset($infoSave['ContractTypeName']) ? $infoSave['ContractTypeName'] : '',
            'tinh_trang' => isset($infoSave['ContractStatusName']) ? $infoSave['ContractStatusName'] : '',
            'vung_mien' => isset($infoSave['Region']) ? $infoSave['Region'] : '',
            'ten_kh' => isset($infoSave['CustomerName']) ? $infoSave['CustomerName'] : '',
            'passport' => isset($infoSave['Passport']) ? $infoSave['Passport'] : '',
            'company_name' => isset($infoSave['CompanyName']) ? $infoSave['CompanyName'] : '',
            'fee_local_type' => isset($infoSave['FeeLocalType']) ? $infoSave['FeeLocalType'] : '',
            'dia_chi_kh' => isset($infoSave['ObjAddress']) ? $infoSave['ObjAddress'] : '',
            'acc_sale' => isset($infoSave['AccountSale']) ? $infoSave['AccountSale'] : '',
            'acc_tin_pnc_thicong' => isset($infoSave['Constructor']) ? $infoSave['Constructor'] : '',
            'acc_tin_pnc_baotri' => isset($infoSave['Maintenance']) ? $infoSave['Maintenance'] : '',
            'so_hd' => isset($contract) ? $contract : '',
            'ngay_tao_hd' => isset($createContract) ? $createContract : '',
            'ten_truy_cap' => isset($infoSave['UserName']) ? $infoSave['UserName'] : '',
            'email' => isset($infoSave['Email']) ? $infoSave['Email'] : '',
            'birthday' => isset($infoSave['Birthday']) ? $infoSave['Birthday'] : '',
            'location_id' => isset($infoSave['LocationID']) ? $infoSave['LocationID'] : '',
            'location' => isset($infoSave['Location']) ? $infoSave['Location'] : '',
            'sub_parent_desc' => isset($infoSave['SubParentDesc']) ? $infoSave['SubParentDesc'] : '',
            'region' => isset($infoSave['Region']) ? $infoSave['Region'] : '',
            'branch_code' => isset($infoSave['BranchCode']) ? $infoSave['BranchCode'] : '',
            'payment_account' => isset($infoSave['AccountPayment']) ? $infoSave['AccountPayment'] : '',
            'dia_chi_lap_dat' => isset($infoSave['Address']) ? $infoSave['Address'] : '',
            'dia_chi_thanh_toan' => isset($infoSave['BillTo']) ? $infoSave['BillTo'] : '',
            'kh_tiep_nhan_ld' => isset($infoSave['CusReceivingSetup']) ? $infoSave['CusReceivingSetup'] : '',
            'kh_su_dung_internet' => isset($infoSave['internet']) ? $infoSave['internet'] : '',
            'kh_su_dung_truyenhinh' => isset($infoSave['paytv']) ? $infoSave['paytv'] : '',
            'user_created' => 1,
            'user_gender' => isset($infoSave['gender']) ? $infoSave['gender'] : '',
            'date_entered' => date('Y-m-d'),
            'supporter' => isset($infoSave['Supporter']) ? $infoSave['Supporter'] : '',
            'sub_supporter' => isset($infoSave['SubSupporter']) ? $infoSave['SubSupporter'] : '',
        ]);
        return $result;
    }
    public function getAccountInfoByContractZeroToArray($contarct) {
        $result = OutboundAccount::select(DB::raw("objid AS ObjID,contract_num AS ContractNum ,contract_date  AS ContractDate,customer_name  AS CustomerName , passport AS Passport, company_name AS CompanyName , certificate_number AS CertificateNumber, address AS Address,address_bill_to  AS BillTo ,contract_type AS ContractType ,contract_type_name AS ContractTypeName ,contract_status AS ContractStatus,contract_status_name AS ContractStatusName ,  login_name AS LoginName , email AS  Email , location AS Location, region AS Region , location_id  AS LocationID ,branch_code AS BranchCode, suspend_date  AS Suspend_Date, suspend_reason  AS Suspend_Reason, obj_address AS ObjAddress ,legal_entity_name AS LegalEntityName , partner_name AS PartnerName ,eoc_name  AS EocName ,fee_local_type  AS FeeLocalType, description  AS Description ,birthday AS Birthday, sex AS Sex ,acc_sale AS  AccountSale ,package_sal AS PackageSal , account_inf AS  AccountINF , finish_date_inf AS  FinishDateINF, account_list  AS  AccountList ,finish_date_list AS FinishDateList, center_list  AS CenterList ,phone AS Phone, payment_type  AS PaymentType ,account_payment  AS AccountPayment ,sub_parent_desc  AS SubParentDesc ,use_service  AS UseService ,email_inf  AS   EmailINF ,email_list AS EmailList , email_sale  AS EmailSale , kind_deploy AS KindDeploy ,kind_main  AS  KindMain ,supporter AS Supporter ,sub_supporter AS SubSupporter"))->where('so_hd', "=", $contarct)->get();
//        if (isset($result->id))
        return $result->toArray();
//        return NULL;
    }

}
