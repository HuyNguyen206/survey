<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Account extends Model
{
    public $connection = null;
    public $no_error = 0;
    //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kb_users';

    protected $fillable = array('user_name');
    public function __construct($options = array()) {
        parent::__construct($options);
        $this->connections = DB::connection('sqlsrv');
    }
    //////Get Objid truyen vao so hop dong
    public function getObjid($Contractnum) {
        $result = DB::table('kb_customer_info')->where('ci_contractnum','=',$Contractnum)->first();
        if(empty($result))return $result->ci_objid = 1;
        else return $result->ci_objid;
    }
    ////Get objid tu store
    /// Store get thong tin khach hang
    public function StoreGetObjid($var)
    {
        $result_obj = $this->connections->select("EXEC dbo.spCC_ObjectGetByAll2 @CustomerName = :name , "
                                    . "@Passport = :passport , "
                                    . "@CompanyName = :company_name , "
                                    . "@Certificate = :certificate , "
                                    . "@Contract = :contract , "
                                    . "@Phone = :phone , "
                                    . "@Address = :address , "
                                    . "@LocationID = :location , "
                                    . "@LoginName = :loginName , "
                                    . "@Email = :email , "
                                    . "@AddressType = 1",['name' => $var['name'],
                                                          'passport' => $var['passport'],
                                                          'company_name' => $var['company_name'],
                                                          'certificate' => '',
                                                          'contract' => $var['contract'],
                                                          'phone' => $var['phone'],
                                                          'address' => $var['address'],
                                                          'location' => $var['locationID'],
                                                          'loginName' => '',
                                                          'email' => ''
                                                          ]);
        return $result_obj;
    }
//     ////Get thong tin khach hang tu db mo
//     public function getInfoCustomerFromMo($Contractnum) {
//     	$result = Account::table( 'outbound_accounts' )
//         ->where('so_hd', '=', $Contractnum )
//         ->get();
//         print_r($result);
//         return $result;
        
//     }
    ////Get lich su ho tro tu db mo
    public function getInfoHistorySupport($Contractnum) {	
		$result = DB::table('kb_history')->skip(0)->take(1)->get();
		return $result;
    }
    /// Store get thong tin khach hang
    public function StoreGetCustomerInfo($var)
    {
        $result_cus = $this->connections->select("EXEC dbo.spCC_ObjectGetByAll2 @CustomerName = :name , "
                                    . "@Passport = :passport , "
                                    . "@CompanyName = :company_name , "
                                    . "@Certificate = :certificate , "
                                    . "@Contract = :contract , "
                                    . "@Phone = :phone , "
                                    . "@Address = :address , "
                                    . "@LocationID = :location , "
                                    . "@LoginName = :loginName , "
                                    . "@Email = :email , "
                                    . "@AddressType = 1",['name' => $var['name'],
                                                          'passport' => $var['passport'],
                                                          'company_name' => $var['company_name'],
                                                          'certificate' => '',
                                                          'contract' => $var['contract'],
                                                          'phone' => $var['phone'],
                                                          'address' => $var['address'],
                                                          'location' => $var['locationID'],
                                                          'loginName' => '',
                                                          'email' => ''
                                                          ]);
        return $result_cus;
    }
    /// Store getfull kh
    public function StoreGetCustomerFullInfo($Objid)
    {
        $result_cusful = $this->connections->select("EXEC spCC_ObjectGetByObjID $Objid");
        return $result_cusful;
    }
     //Store history support
    public function StoreGetHistorySup($Objid)
    {
        $result_sup = $this->connections->select("EXEC CallCenter.dbo.spCC_GetCallerHistoryByObjID @iObjID = $Objid, @iRecordCount = 10");
        return $result_sup;
    }
}