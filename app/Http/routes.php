<?php

/*
  |--------------------------------------------------------------------------
  | Routes File
  |--------------------------------------------------------------------------
  |
  | Here is where you will register all of the routes in an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */
/*
  Start
  Route test
 */
Route::get('/test/getDataSummaryCsat/{fromDay}/{toDay}', 'Test\TestController@getCsatSummary');
Route::get('/test/getDataSummaryNps/{fromDay}/{toDay}', 'Test\TestController@getNpsSummary');
//Route::get('/test-info/{contract}/{type}/{code}', 'Test\TestController@testInfo');
Route::get('/test-info-cl/{listIdC}/', 'Test\TestController@testInfoChecklist');
Route::get('/test-info-pcl/{listIdPC}/', 'Test\TestController@testInfoPreChecklist');
Route::get('/test-info-fd/{listIdFD}/', 'Test\TestController@testInfoFD');
Route::get('/test/updateDateCL', 'Test\TestController@UpdateDateCL');
Route::get('/test/UpdateAppointmentTimerPCL', 'Test\TestController@UpdateAppointmentTimerPCL');
Route::get('/test/UpdateInputTimeCL', 'Test\TestController@UpdateInputTimeCL');
Route::get('/test/UpdateActionProcessPCL', 'Test\TestController@UpdateActionProcessPCL');
Route::get('/test/updateObijId', 'Test\TestController@updateObijId');

Route::get('/test/updateAllDate', 'Test\TestController@updateAllDate');
Route::get('/test/notifyNewPhoneNumber', 'Test\TestController@notifyNewPhoneNumber');


Route::post('/test/get-info-transaction', 'Test\TestController@getInfoContractQGD');
Route::post('/test/save-info-transaction', 'Test\TestController@saveInfoTransaction');
Route::post('/test/save-info-transaction-counter', 'Test\TestController@saveInfoTransactionCounter');

Route::get('/test/', 'Test\TestController@test');
Route::get('/test/testUpdateInvalidCase', 'Test\TestController@testUpdateInvalidCase');
Route::get('/test/getDataAllTypeTransaction', 'Test\TestController@getDataAllTypeTransaction');
Route::get('/test/exportTableHTMLCSATNPS', 'Test\TestController@exportTableHTMLCSATNPS');

Route::get('/test/getTransactionCount', 'Test\TestController@getTransactionCount');
Route::get('/test/fixInvalidBranchCodeSurvey', 'Test\TestController@fixInvalidBranchCodeSurvey');
Route::get('/view', 'Test\TestController@index');
//
//Route::get('/redis', function () {
//    print_r(app()->make('redis'));
//});
//Route::resource('account/search', 'Account\AccountController@search');
//Route::get('/apiisc/test', 'ApiiscController@test');
//Route::resource('account/search','Account\AccountController');
//API fpt.vn
/*
  End
  Route test
 */

/*
  Start
  Cron update dữ liêu checklist, prechecklist, chuyển tiếp phòng ban
 */
Route::group(['prefix' => 'cron'], function () {
//    Route::get('update-checklist-prechecklist-fowarddepart', 'Cron\UpdateChecklistFromISC@updateCL_PCL_FW');
    Route::get('update-checklist', 'Cron\UpdateChecklistFromISC@updateChecklist');
    Route::get('update-prechecklist', 'Cron\UpdateChecklistFromISC@updatePrechecklist');
    Route::get('update-foward-department', 'Cron\UpdateChecklistFromISC@updateFowardDepartment');
    Route::get('insert-cl-data', 'Cron\UpdateChecklistFromISC@insertCLData');

    Route::get('update-foward-department-at-once', 'Cron\UpdateChecklistFromISC@updateFDAtOnce');
    Route::get('update-prechecklist-at-once', 'Cron\UpdateChecklistFromISC@updatePrechecklistAtOnce');
    Route::get('updateChecklistAtOnce', 'Cron\UpdateChecklistFromISC@updateChecklistAtOnce');
});
/*
  End
  Cron update dữ liêu checklist, prechecklist, chuyển tiếp phòng ban
 */

/*
  Start
  Api xử lý thông tin giao dịch tại quầy, email, thu cước
 */
//Route::group(['prefix' => 'api/survey'], function () {
//    Route::post('get-info-transaction', 'Api\ApiTransactionController@getInfoContractQGD');
//    Route::post('save-info-transaction', 'Api\ApiTransactionController@saveInfoTransaction');
//    Route::post('save-info-transaction-counter', 'Api\ApiTransactionController@saveInfoTransactionCounter');
//    Route::post('save-info-transaction-tablet', 'Api\ApiTransactionController@saveInfoTransactionTablet');
//    Route::post('save-info-hifpt', 'Api\ApiTransactionController@saveInfoHiFPT');
//    // Mở test
//    Route::post('save-info-transaction-tablet-only-test', 'Api\Api@saveInfoTransactionTabletOnlyTest');
//});
/*
  End
  Api xử lý thông tin giao dịch tại quầy, email, thu cước
 */

//Route::group(['prefix' => 'api/v1'], function () {
//    Route::post('get-survey', 'Api\Api@getResultSurveys');
//    Route::post('save-survey', 'Api\Api@saveResultSurveys');
//    Route::post('get-salary-IBB', 'Api\Api@getInfoSalaryIBB');
//    Route::post('get-salary-TinPNC', 'Api\Api@getInfoSalaryTinPNC');
//    Route::post('confirm-notification', 'Api\Api@saveReponseAcceptInfo');
//    Route::get('resend-notification/{num}', 'Api\Api@sendNotificationAgain');
//
//    Route::get('insert-info-report-top', 'Api\Api@transferToReportByInsertTop');
//    Route::get('insert-info-report-middle', 'Api\Api@transferToReportByInsertMiddle');
//
//    Route::get('update-info-report', 'Api\Api@transferToReportByUpdate');
//    Route::get('update-info-report-now', 'Api\Api@transferToReportByUpdateNow');
//
////    Route::get('get-survey-hifpt', 'Api\Api@getInfoQuestionsSurveyApp');
//    Route::post('save-survey-hifpt', 'Api\Api@insertSurveyApp');
//    Route::get('get-survey-fptvn/{input}', 'Api\Api@getInfoQuestionsSurveyFPTVN');
//    Route::post('save-survey-fptvn', 'Api\Api@insertSurveyFptvn');
//
//    //
//    Route::post('insert-one-record', 'Api\Api@insertOneRecord');
//    Route::post('update-one-record', 'Api\Api@updateOneRecord');
//    Route::post('update-multi-record', 'Api\Api@updateMultiRecordReports');
//
//
//    Route::post('rating-checklist-hifpt', 'Api\Api@ratingChecklistHifpt');
//    Route::get('fix-missed-surveys', 'Api\Api@fixMissedSurveys');
//    Route::get('fix-missed-surveys-time/{dayFrom}/{dayTo}', 'Api\Api@fixMissedSurveysTime');
//    //Thông tin người liên hệ
//    Route::post('get-contact', 'Api\ApiTransactionController@getContact');
//    Route::post('add-contact', 'Api\ApiTransactionController@addContact');
//
//    Route::post('generate-link-email-survey', 'Api\Api@generateLinkEmailSurvey');
//});


/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | This route group applies the "web" middleware group to every route
  | it contains. The "web" middleware group is defined in your HTTP
  | kernel and includes session state, CSRF protection, and more.
  |
 */



Route::group(['middleware' => 'web'], function () {
    Route::get('/Falseinside/{input}', 'Users@falseInside');
//    Route::get('confirm-notification', 'Notification@confirmView');
//    Route::post('confirm', 'Notification@confirm');
//    Route::get('get-push-notification', 'Api\Api@getPushSurveyId');
//    Route::post('get-voice-records-ajax', 'Records\VoiceRecords@getVoiceRecordsAjax');

    Route::group(['middleware' => 'beforeLogin'], function () {
        Route::auth();
    });

    Route::group(['middleware' => ['beforeAction', 'languageSwitch']], function() {
        Route::get('/', 'HistoryController@history');
        Route::post('/', 'HistoryController@history');

        Route::get('lang/{locale}', 'Surveys\SurveysController@setLocale');

        Route::get('/survey/{contractNum}/{type}/{code}', 'Surveys\SurveysController@checkSurvey');
        Route::post('/editSurvey', 'Surveys\SurveysController@editSurvey');
        Route::post('/createSurvey', 'Surveys\SurveysController@createSurvey');
        Route::get('/success/{sectionID}','Surveys\SurveysController@successSurvey');
//        Route::get('/hiFPT/{contractNum}/{type}/{code}', 'Surveys\SurveysController@viewSurveyHiFPT');
//        Route::post('surveys/updateHiFPT', 'Surveys\SurveysController@updateHiFPT');
//        Route::post('/account/save', 'Account\AccountController@save');
//        Route::resource('/search/{contractNum}/{type}/{code}', 'Account\AccountController@search');
//        Route::get('/search/{contractNum}/{type}/{code}', 'Account\AccountController@search');
//        Route::post('surveys/edit_survey_frontend', 'Surveys\SurveysController@edit_survey_frontend');
//        Route::post('history/detail_survey_frontend', 'History@detail_survey_frontend');
//        Route::post('surveys/getHistoryFrontend', 'Surveys\SurveysController@getHistoryFrontend');
//        Route::post('surveys/update', 'Surveys\SurveysController@update');
//        Route::post('surveys/complete', 'Surveys\SurveysController@complete');
//        Route::post('surveys/addContact', 'Surveys\SurveysController@addContact');
//        Route::post('surveys/getContact', 'Surveys\SurveysController@getContact');
//        Route::resource('account', 'Account\AccountController');
        /*
          Start
          Xử lý thông tin checklist, prechecklist ở survey
         */
        Route::post('checklist/getNameUser', 'Checklist\CheckListController@getNameUser');
        Route::post('checklist/getCheckList', 'Checklist\CheckListController@getCheckList');
        Route::post('checklist/createPCL', 'Checklist\CheckListController@createPCL');
        Route::post('checklist/createCL', 'Checklist\CheckListController@createCL');
        Route::post('checklist/getPreCheckList', 'Checklist\CheckListController@getPreCheckList');
        Route::post('checklist/getDateInfo', 'Checklist\CheckListController@getDateInfo');
        Route::post('checklist/checkRemainPTC', 'Checklist\CheckListController@checkRemainPTC');
        Route::post('checklist/getOwnerType', 'Checklist\CheckListController@getOwnerType');
        Route::post('checklist/supportListRemainCheck', 'Checklist\CheckListController@supportListRemainCheck');
        Route::post('checklist/checkRulePhysical', 'Checklist\CheckListController@checkRulePhysical');
        Route::post('checklist/checkCreateCl', 'Checklist\CheckListController@checkCreateCl');
        Route::post('checklist/preSupportListCheck', 'Checklist\CheckListController@preSupportListCheck');
        Route::post('checklist/SupportListCheck', 'Checklist\CheckListController@SupportListCheck');
        Route::get('checklist/GetFirstStatusName', 'Checklist\CheckListController@GetFirstStatusName');
        Route::post('checklist/getGroupPoint', 'Checklist\CheckListController@getGroupPoint');
        /*
          End
          Xử lý thông tin checklist, prechecklist ở survey
         */
        
        /*
          Start
          Xử lý thông tin chuyển phòng ban ở survey
         */
            Route::post('forward/forwardDepartment', 'Checklist\ForwardDepartmentController@forwardDepartment');
         /*
          End
          Xử lý thông tin chuyển phòng ban ở survey
         */
 
//        Route::post('surveys/getContactByPhone', 'Surveys\SurveysController@getContactByPhone');
        /*
          Start
          Xử lý các function ở survey
         */
//        Route::post('function/getListBill', 'FunctionController@getListBill');
//        Route::post('function/getInfoConnect', 'FunctionController@getInfoConnect');
//        Route::post('function/getPromoInfo', 'FunctionController@getPromoInfo');
//        Route::post('function/getIptvStatus', 'FunctionController@getIptvStatus');
//        Route::post('function/GetInfoBox', 'FunctionController@GetInfoBox');
//        Route::post('function/GetInfoIptv', 'FunctionController@GetInfoIptv');
//        Route::post('function/GetInfoDeploy', 'FunctionController@GetInfoDeploy');
//        Route::post('function/getInfoPtv', 'FunctionController@getInfoPtv');
//        Route::post('function/GetListMacService', 'FunctionController@GetListMacService');
        /*
          End
          Xử lý các function ở survey
         */
    });

    Route::group(['middleware' => 'beforeError'], function() {
        Route::get('/error/auth', 'Error@auth');
        Route::delete('error/auth', 'Error@auth');
    });



//	Route::group(['middleware' => 'CheckAjaxLogin'], function() {
//	 });
});
//Route::controller('account', 'Account\AccountController');
//Route::controller('surveys', 'Surveys\SurveysController');
//Route::resource('surveys', 'Surveys\SurveysController@index');
