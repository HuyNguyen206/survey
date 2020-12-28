<?php

namespace App\Models;
use App\Models\BaseModel;
class AccountProfiles extends BaseModel
{
    //
	protected $table = 'account_profiles';
	protected $primaryKey = 'id';
	//protected $timestamps = FALSE;
	protected $fillable =  ['ap_contract', 'ap_fullname', 'ap_birthday', 'ap_sex', 
							'ap_address_id', 'ap_address_bill', 'ap_address_setup', 'ap_user_update'];
	
	/*
	 * Lưu mới hoặc update thông tin khách hàng
	 * tiếng việt có dấu
	 */
	public $timestamps = false;
	public function updateOrCreateAccountProfiles($contractNumber,$accountInfo){
		//try {
			return AccountProfiles::updateOrCreate($contractNumber,$accountInfo);			
		//}catch (\Exception $ex) {
		//	var_dump( $ex );
		//	return FALSE;
		//}
	}
	public function updateAccountProfiles($contractNumber,$accountInfo){
		 AccountProfiles::where('ap_contract', '=', $contractNumber)->update($accountInfo);
                 return true;
	}
	public function insertAccountProfiles($accountInfo){
		return AccountProfiles::create($accountInfo);
	}
	public function getAccountProfilesByContract($contractNumber){
		return AccountProfiles::where('ap_contract', '=', $contractNumber)->first();
	}
}