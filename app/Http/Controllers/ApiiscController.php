<?php
/*
 * Controlers kết nối tới API của ISC
 * 
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Apiisc;


class ApiiscController extends Controller
{
	public function test(){

		$apiIsc = new Apiisc();
		$input = array('Contract' => 'sgd070505');
		$uri ='http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_ObjectGetByObjID?ObjID=&Contract=HNH02119712312';
		
		$datareturn = $apiIsc->getApi( $uri );
		$datareturn = json_decode($datareturn);
		echo '<pre>';
		print_r($datareturn);
		
		$uri ='http://cemcc.fpt.net/wscustomerinfo.asmx/spCC_GetCallerHistoryByObjID?ObjID=1020104442&RecordCount=10';
		$datareturn = $apiIsc->getApi( $uri );
		$datareturn = json_decode($datareturn);
		print_r($datareturn);
	}
	
}