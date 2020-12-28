<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveySections;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class HistoryController extends Controller {
    public function history(Request $request){
        $dataPage = [];
        $currentPage = 0;
        $recordPerPage = 50;
        $infoSurvey = $condition = null;
        $modelSurveySections = new SurveySections();

        if ($request->isMethod('post') || (isset($request->page) && Session::has('condition'))) {//click vào nút tìm
            if ($request->isMethod('post')){
                Session::forget('condition');
            }

            if (Session::has('condition')) {
                $condition = Session::get('condition');
            } else {
                $condition = $this->attachCondition($condition, $request);
                $condition['recordPerPage'] = $recordPerPage;
                //nếu tìm kiếm theo HĐ thì bỏ hết các đk tìm kiếm khác, trừ đk triển khai hoặc bảo trì.
                if (!empty($condition['contractNum'])) {
                    $arrayKeep = ['contractNum', 'type', 'recordPerPage'];
                    foreach($condition as $key => $val){
                        if(!in_array($key, $arrayKeep)){
                            $condition[$key] = '';
                        }
                    }
                }
                Session::put('condition', $condition);
            }
            $currentPage = !empty($request->page) ? intval($request->page - 1) : 0;
            $count = $modelSurveySections->countListSurvey($condition);
            $infoSurvey = $modelSurveySections->searchListSurvey($condition, $currentPage);
            $infoSurvey = new LengthAwarePaginator($infoSurvey, $count, $recordPerPage, $request->page, [
                'path' => $request->url(),
                'query' => $request->query()
            ]);
            $dataPage = $this->repairDataForViewHistoryIndex($infoSurvey, $condition);
            //gán lại giá trị cho tìm kiếm
            if (Session::has('condition')) {
                $condition = Session::get('condition');
            } else {
                $condition = $this->attachCondition($condition, $request);
                $condition['recordPerPage'] = $recordPerPage;
            }
        }

        $data = [
            'modelSurveySections' => $infoSurvey,
            'searchCondition' => $condition,
            'dataPage' => $dataPage,
            'currentPage' => $currentPage,
        ];
        return view('surveys.history', $data);
    }

    private function attachCondition($condition, $request) {
        $user = Auth::user();
        $condition['surveyFrom'] = !empty($request->surveyFrom) ? date('Y-m-d 00:00:00', strtotime($request->surveyFrom)) : date('Y-m-d 00:00:00');
        $condition['surveyTo'] = !empty($request->surveyTo) ? date('Y-m-d 23:59:59', strtotime($request->surveyTo)) : date('Y-m-d 23:59:59');
        $condition['surveyFromInt'] = !empty($request->surveyFrom) ? strtotime($request->surveyFrom) : strtotime(date('Y-m-d 00:00:00'));
        $condition['surveyToInt'] = !empty($request->surveyTo) ? strtotime($request->surveyTo . '  23:59:59') : strtotime(date('Y-m-d 23:59:59'));
        $condition['surveyType'] = !empty($request->surveyType) ? $request->surveyType : '';
        $condition['surveyUser'] = $user->name;
        $condition['sectionConnected'] = !empty($request->sectionConnected) ? $request->sectionConnected : '';
        return $condition;
    }
    private function repairDataForViewHistoryIndex($infoSurvey, $condition) {
        $data = [];
        $controller = 'history';

        $arrayAction = [
            0 => trans($controller.'.'.'NotYetDoAnything'),
            1 => trans($controller.'.'.'NotYetDoAnything'),
            2 => trans($controller.'.'.'CreateChecklist'),
            3 => trans($controller.'.'.'CreatePrechecklist'),
            4 => trans($controller.'.'.'CreateChecklist').' INDO',
            5 => trans($controller.'.'.'SendToDepartment'),
        ];
        $arrayConnect = [
            0 => trans($controller.'.'.'NoNeedContact'),
            1 => trans($controller.'.'.'CannotContact'),
            2 => trans($controller.'.'.'MeetCustomerCustomerDeclinedToTakeSurvey'),
            3 => trans($controller.'.'.'DidntMeetUser'),
            4 => trans($controller.'.'.'MeetUser'),
        ];
        $surveyTitle = [
            1 => trans($controller.'.'.'AfterActive'),
            2 => trans($controller.'.'.'AfterChecklist'),
        ];

        foreach ($infoSurvey as $index => $surveySection) {
            $surveySectionArray = (array)$surveySection;
            foreach($surveySectionArray as $key => $value){
                switch($key){
                    case 'section_connected':
                        $surveySectionArray[$key] = $arrayConnect[$value];
                        break;
                    case 'section_survey_id':
                        $surveySectionArray['section_survey_name'] = $surveyTitle[$value];
                        break;
                    case 'section_action':
                        $surveySectionArray[$key] = $arrayAction[$value];
                        break;
                    default:
                }
            }
            $data[] = $surveySectionArray;
        }
        return $data;
    }
}
