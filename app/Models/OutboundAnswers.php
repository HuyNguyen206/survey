<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class OutboundAnswers extends Model {

    protected $table = 'outbound_answers';
    protected $fillable = [
        
    ];

    public function getAnswerByGroup($groupId, $idNotIn = []){
        $result = DB::table($this->table)
                ->select('answer_id','answers_title', 'answer_group', 'answers_key')
                ->whereIn('answer_group',$groupId)
                ->whereNotIn('answer_id', $idNotIn)
                ->orderBy('answer_group','answer_id')
                ->get();
        return $result;
    }

}
