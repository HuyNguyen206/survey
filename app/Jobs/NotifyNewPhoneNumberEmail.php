<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyNewPhoneNumberEmail extends Job implements ShouldQueue {

    use InteractsWithQueue,
        SerializesModels;

    protected $contactInfo;
    protected $dateCompleteSurvey;
    protected $typeSurvey;
    protected $contractNumber;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($contactInfo, $dateCompleteSurvey, $typeSurvey, $contractNumber) {
        $this->contactInfo = $contactInfo;
        $this->dateCompleteSurvey = $dateCompleteSurvey;
        $this->typeSurvey = $typeSurvey;
        $this->contractNumber = $contractNumber;
//
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
//
//        $test = false;
        $typeSurveyMap = [1 => 'Sau triển khai', 2 => 'Sau bảo trì', 6 => 'Sau triển khai Telesale', 9 => 'Sau Triển khai Sale tại quầy', 10 => 'Sau Triển khai Swap'];
        $relationshipMap = [1 => 'Ba mẹ', 2 => 'Anh chị em', 3 => 'Bạn bè', 4 => 'Chủ hợp đồng', 5 => 'Khác'];
        $contactInfo = $this->contactInfo;
        $contactInfo['relationship'] = $relationshipMap[$contactInfo['relationship']];
        $dateCompleteSurvey = $this->dateCompleteSurvey;
        $typeSurvey = $typeSurveyMap[$this->typeSurvey];
        $contractNumber = $this->contractNumber;
//        if ($test) {
//            $mailTo = ['huydp2@fpt.com.vn'];
//            $cc = ['huynl2@fpt.com.vn'];
//        } else {
            $mailTo = ['anhptv3@fpt.com.vn', 'hiengtt@fpt.com.vn', 'tungdt21@fpt.com.vn', 'uyentttt@fpt.com.vn'];
            $cc = ['Hoadtt11@fpt.com.vn', 'huydp2@fpt.com.vn', 'huynl2@fpt.com.vn'];
//        }

        Mail::send('emails.notifyNewPhoneNumberEmail', ['contactInfo'=>$contactInfo, 'dateCompleteSurvey'=> $dateCompleteSurvey, 'typeSurvey'=> $typeSurvey, 'contractNumber'=> $contractNumber], function ($message) use($mailTo, $cc, $typeSurvey, $contractNumber) {
            $message->from('rad.support@fpt.com.vn', 'Support');
            $message->to($mailTo);
            $message->cc($cc);
            $message->subject('[CEM-Warning] Số điện thoại liên hệ không có trên hệ thống Inside');
        });
    }

}
