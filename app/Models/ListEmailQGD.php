<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListEmailQGD extends Model
{
    protected $table = 'list_mail_qgd';
    protected $fillable = ['email_list_to', 'email_list_cc', 'summary_branches_id'];

    public function insertByArray($param){
        $result = DB::table($this->table)->insert($param);
        return $result;
    }

    public function getListEmail($params){
        $result = DB::table($this->table.' as lmq')
            ->join('summary_branches as sb','sb.branch_id','=','lmq.summary_branches_id')
            ->where(function($query) use ($params) {
                if (!empty($params['location_id'])) {
                    $query->where('sb.isc_location_id', '=',$params['location_id']);
                }
                if (!empty($params['branch_code'])) {
                    $query->where('sb.isc_branch_code', '=',$params['branch_code']);
                }
            })
            ->select('lmq.email_list_to', 'lmq.email_list_cc', 'sb.branch_id')
            ->first();
        return $result;
    }
}