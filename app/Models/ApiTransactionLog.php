<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ApiTransactionLog extends Model {

    protected $table = 'api_transaction_log';
    public $timestamps = true;
    protected $fillable = [
        'survey_id', 'source', 'input', 'time_call'];

    public function insertApiLog($param) {
        $res = DB::table($this->table)
            ->insert($param);
        return $res;
    }
}
