<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListEmailSir extends Model
{
    protected $table = 'list_mail_sir';
    protected $fillable = ['Email', 'Account'];

    public function insert($param){
        $result = DB::table($this->table)->insert($param);
        return $result;
    }

    public function getListEmail($params){
        $result = DB::table($this->table)
            ->where(function($query) use ($params) {
                if (!empty($params['team'])) {
                    $query->whereRaw('AccountInside like "%'.$params['team'].'%"');
                }
            })
            ->select('Email', 'AccountConfirm', 'AccountInside')
            ->first();
        return $result;
    }
}