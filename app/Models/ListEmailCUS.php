<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListEmailCUS extends Model
{
    protected $table = 'list_mail_cus';
    protected $fillable = ['email_list', 'summary_branches_id'];

    public function insert($param){
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
            ->select('lmq.email_list', 'sb.branch_id')
            ->first();
        return $result;
    }
}