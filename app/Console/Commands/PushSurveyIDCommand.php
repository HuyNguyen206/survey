<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Api\ApiHelper;
use Illuminate\Support\Facades\Redis;

class PushSurveyIDCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PushSurveyID';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Job send mail by SurveyID';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for($i = 0; $i <= 4; $i++){
            $redis = Redis::exists('pushNotificationID');
            if ($redis) {
                $apiHelp = new ApiHelper();
                $paramCheck['sectionId'] = Redis::rpop('pushNotificationID');
                $resCheck = $apiHelp->checkSendMail($paramCheck);
                if ($resCheck['status']) {
                    $apiHelp->prepareSendMail($paramCheck, $resCheck);
                }
            }
        }
    }
}
