<?php 

namespace App\Component;

use App\Models\Role;
use App\Models\SurveySections;
use App\Models\SurveyResult;
use Illuminate\Support\Facades\Auth;
use Exception;


Class ExtraFunction{
	public static function customSubString($string, $lenght) {
		if (strlen($string) >= $lenght) {
			return mb_substr($string, 0, $lenght - 3, "utf-8") . '...';
		} else {
			return $string;
		}
    }
	
	public static function checkCanAction($level){
		$role = Role::getAllRoleById(Auth::user()->id);
		if($role['0']->level >= $level && $role['0']->level != '1'){
			return false;
		}
		return true;
	}
	
	public function getHeader()
    {
        return array(
            'Content-Type: application/json'
        );
    }
	
	public function response($status, $error, $msg){
		return [
			'status' => $status,
			'error' => $error,
			'msg' => $msg,
		];
	}
	
	public function sendRequest($uri, $headers = null, $method = 'GET', $params = null) {
		$ch = curl_init();
		if (strtoupper($method) == 'POST') {
			if (!empty($params)) {
				$params = json_encode($params);
			} else {
				return $this->response('fail', true, 'Missing params');
			}
		}

		if (strtoupper($method) == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		} else if ($method == 'DELETE') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}

		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//		curl_setopt($ch, CURLOPT_PROXY, "");

		$result = curl_exec($ch);
		if (curl_error($ch) != 0) {
			return $this->response('fail', true, 'Cannot connect to server host');
		}
		$res = curl_getinfo($ch);
		if ($res['http_code'] != 200) {
			return $this->response('fail', true, 'Something happen');
		}
		return $this->response('success', false, json_decode($result, 1));

	}
	
	public function getFullQAWithKeyIsQuestionKey($surveyID){
	    $modelSurveySection = new SurveySections();
        $resultQues = $modelSurveySection->getFullQA($surveyID);
        $QA = [];
        foreach ($resultQues as $que) {
            $que = (array)$que;
            if(!isset($QA[$que['question_id']])){
                $QA[$que['question_id']]['questionKey'] = $que['question_key'];
                $QA[$que['question_id']]['questionAlias'] = $que['question_alias'];
                $QA[$que['question_id']]['seeMore'] = $this->getDetailSeeMore($que['question_alias']);
                $QA[$que['question_id']]['answers'] = [];
            }
            $QA[$que['question_id']]['answers'][$que['answer_id']] = [
                'answerKey' => $que['answers_key'],
                'answerPoint' => $que['answers_point'],
            ];
        }
        return $QA;
    }

    public function getFullQAOfQuestionOtherNPS($surveyID){
        $modelSurveySection = new SurveySections();
        $resultAns = $modelSurveySection->getAnswerOfNPSOther();
        $resultQues = $modelSurveySection->getQuestionOfNPSOther($surveyID);
        $QA = [];
        foreach ($resultQues as $que) {
            $que = (array)$que;
            if(!isset($QA[$que['question_id']])){
                $QA[$que['question_id']]['questionKey'] = $que['question_key'];
                $QA[$que['question_id']]['questionAlias'] = $que['question_alias'];
                $QA[$que['question_id']]['seeMore'] = $this->getDetailSeeMore($que['question_alias']);
                $QA[$que['question_id']]['answersGroup'] = [];
            }
            foreach($resultAns as $ans){
                $ans = (array)$ans;
                if(!isset($QA[$que['question_id']]['answersGroup'][$ans['answers_group_id']])){
                    $QA[$que['question_id']]['answersGroup'][$ans['answers_group_id']] = [
                        'answerGroupKey' => $ans['answers_group_key'],
                        'answers' => [],
                    ];
                }
                $QA[$que['question_id']]['answersGroup'][$ans['answers_group_id']]['answers'][$ans['answer_id']] = [
                    'answerKey' => $ans['answers_key'],
                    'answerPoint' => $ans['answers_point'],
                ];
            }
        }
        return $QA;
    }

    public function getFullAnswerWithKeyIsGroupAnswer(){
        $modelSurveySection = new SurveySections();
        $resultAns = $modelSurveySection->getAnswer();
        $QA = [];
        foreach ($resultAns as $ans) {
            $ans = (array)$ans;
            $temp = [];
            $temp['answerPoint'] = $ans['answers_point'];
            $temp['answerKey'] = $ans['answers_key'];
            $QA[$ans['answer_group']][$ans['answer_id']] = $temp;
        }
        return $QA;
    }

    public function getDetailAnswerOfCustomer($sectionID){
        $modelSurveyResult = new SurveyResult();
        $resultAns = $modelSurveyResult->getDetailSurvey($sectionID);
        $QA = [];
        foreach ($resultAns as $ans) {
            $ans = (array)$ans;
            //Trường hợp một câu hỏi có nhiều câu trả lời
            if(isset($QA[$ans['survey_result_question_id']])){
                $tempQA = $QA[$ans['survey_result_question_id']]['survey_result_answer_id'];
                if(!is_array($tempQA)){
                    $QA[$ans['survey_result_question_id']]['survey_result_answer_id'] = [$tempQA];
                }
                $QA[$ans['survey_result_question_id']]['survey_result_answer_id'][] = $ans['survey_result_answer_id'];
            }else{
                $QA[$ans['survey_result_question_id']] = $ans;
            }
        }
        return $QA;
    }

    public function getDetailSeeMore($alias){
        $transFile = 'more.';
        $internet = trans($transFile.'CS').': '.trans($transFile.'AfterOurTechnicianGoToCheckForYouDoYouSatisfyWithIt').'?&#10;';
        $internet .= trans($transFile.'CS').': '.trans($transFile.'DoesYourConnectionStable').'?&#10;';
        $internet .= trans($transFile.'CS').': '.trans($transFile.'DoesTheSpeedSuitableWithYourPackage').'?&#10;';
        $internet .= trans($transFile.'CS').': '.trans($transFile.'DoYouNeedAnySupportOrRequest').'?&#10;';

        $tech = trans($transFile.'CS').': '.trans($transFile.'DoesTechnicianComeOnTime').'?&#10;';
        $tech .= trans($transFile.'CS').': '.trans($transFile.'DoesHeOfficious').'?&#10;';
        $tech .= trans($transFile.'CS').': '.trans($transFile.'DoesTechnicalStaffConsultYou').'?&#10;';
        $tech .= trans($transFile.'CS').': '.trans($transFile.'DoesHeGuideYouHowToUse').'?&#10;';

        $sale = trans($transFile.'CS').': '.trans($transFile.'DoYouSatisfyWithOurSalesman').'?&#10;';
        $sale .= trans($transFile.'CS').': '.trans($transFile.'DoesHeConsultYouAndGuideYouInDetail').'?&#10;';
        $sale .= trans($transFile.'Customer').': '.trans($transFile.'Yes').'&#10;';
        $sale .= trans($transFile.'CS').': '.trans($transFile.'MoveToQuestionNumber5').'&#10;';
        $sale .= trans($transFile.'Customer').': '.trans($transFile.'NotClear').'&#10;';
        $sale .= trans($transFile.'CS').': '.trans($transFile.'ProvideCustomerPackageSpeedPromotion').'&#10;';

        $nps = trans($transFile.'CS').': '.trans($transFile.'ThisIsASurveyQuestionThePurposeIsToEvaluateYourSupportWithOurOpennetService').'&#10;';
        $nps .= trans($transFile.'CS').': '.trans($transFile.'IfYouGiveUs9PointWhichMeansYouReallySupportWithOurService').'&#10;';
        $nps .= trans($transFile.'CS').': '.trans($transFile.'IfYouGiveUs0PointWhichMeansYouAreTotallyNotSupportWithOurService').'&#10;';

        $opinion = trans($transFile.'CS').': '.trans($transFile.'DoYouHaveAnyComplainAboutOurService').'? &#10;';
        $opinion .= trans($transFile.'CS').': '.trans($transFile.'CouldYouPleaseGiveSomeCommentAboutOurCompany').'? &#10;';
        $opinion .= trans($transFile.'CS').': '.trans($transFile.'WhichPointWeShouldImproveMoreTheGetTheHighestScore').'? &#10;';
        $opinion .= trans($transFile.'CS').': '.trans($transFile.'ShouldWeImproveMoreAboutServiceInternetOrStaff').'? &#10;';

        $arraySeeMore = [
            '1' => $sale,
            '3' => $tech,
            '4' => $tech,
            '5' => $internet,
            '9' => $opinion,
            '10' => $nps,
        ];
        $result = $arraySeeMore[$alias];
        return $result;
    }
}   