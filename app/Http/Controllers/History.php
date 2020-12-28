<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests;
use App\Models\SurveySections;
use App\Models\Location;
use DB;

class History extends Controller {

    public function detail_survey_frontend(Request $request) {
//        DB::enableQueryLog();
        $modelDetailSurveyResult = new SurveySections();
        $detail = $modelDetailSurveyResult->getAllDetailSurveyInfo($request->surveyID);
//        dump(DB::getQueryLog());die;
        $survey = SurveySections::find($request->surveyID);
        $connected = $survey->section_connected;
        $contactPhone = $survey->section_contact_phone;
        $mainNote = $survey->section_note;
//            var_dump($detail);die;
        return view('report_history/detailSurveyFrontend', ['detail' => $detail, 'contract' => $request->contractNum, 'connected' => $connected, 'contactPhone' => $contactPhone,'mainNote'=>$mainNote])->render();
//        }
//        exit();
    }

}
