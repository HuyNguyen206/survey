<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class RecordChannel extends Model {

    protected $table = 'outbound_record_channel';
    protected $primaryKey = 'record_channel_id';
	
    public function getAllRecordChannel(){
        $result = DB::table($this->table)
                ->select('record_channel_id', 'record_channel_name', 'record_channel_code', 'record_channel_key')
                ->get();
        return $result;
    }
}
