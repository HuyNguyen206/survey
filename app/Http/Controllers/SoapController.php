<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Artisaninweb\SoapWrapper\Facades\SoapWrapper;

class SoapController{

    private $result;
    public function LogOnInsideWithOTP(Request $request)
    {
        try {
            // Add a new service to the wrapper
            SoapWrapper::add(function ($service) {
                $service
                    ->name('LogOn')
                    ->wsdl('http://accountotp.fpt.net/Service.asmx?WSDL');
            });

            $info = $request->all();
            if(empty($info['otp'])){
                $info['otp'] = 1;
            }
            $data = [
                'UserName'      => $info['name'],
                'Password'      => $info['password'],
                'clientOTP'		=> $info['otp'],
            ];

            // Using the added service
            SoapWrapper::service('LogOn', function ($service) use ($data) {
                $this->result = $service->call('LogOn', [$data])->LogOnResult;
            });
            Log::info($this->result);
            return $this->result;
        }  catch (\SoapFault $e){
            return false;
        }
    }

    public function LogOnInside(Request $request)
    {
        try {
            // Add a new service to the wrapper
            SoapWrapper::add(function ($service) {
                $service
                    ->name('LogOn')
                    ->wsdl('http://account.fpt.net/Service.asmx?WSDL');
            });
            $info = $request->all();
            $data = [
                'UserName'      => $info['email'],
                'Password'      => $info['password'],
            ];        
            // Using the added service
            SoapWrapper::service('LogOn', function ($service) use ($data) {
                $this->result = $service->call('LogOn', [$data])->LogOnResult;
            });
            Log::info($this->result);
            return $this->result;
        }  catch (\SoapFault $e){
            
        }
    }

}