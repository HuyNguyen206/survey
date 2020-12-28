<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\Api;
use Illuminate\Console\Command;

class ResendMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ResendMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re Job send mail';

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
        $API = new Api();
        $API->sendNotificationAgain();
    }
}
