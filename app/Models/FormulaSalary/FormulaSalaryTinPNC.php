<?php
/**
 * Created by PhpStorm.
 * User: Minh Tuan
 * Date: 2017-06-16
 * Time: 2:42 PM
 */
namespace App\Models\FormulaSalary;

use Illuminate\Database\Eloquent\Model;
use DB;

class FormulaSalaryTinPNC extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'formula_salary_tin_pnc';
    protected $fillable = [];
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function getNewestRecord(){
        $result = DB::table($this->table)
            ->select('*')
            ->whereRaw('id in (select max(id) from '.$this->table.')')
        ->first();
        return $result;
    }

    public function getRecordByParam($param){
        $query = DB::table($this->table);
        if(!empty($param['date_start'])){
            $query->where('createdAtInt', '>=', strtotime(date_format($param['date_start'], 'Y-m-d 00:00:00')));
            $query->where('createdAtInt', '<=', strtotime(date_format($param['date_end'], 'Y-m-d 23:59:59')));
        }
        $result = $query->get();
        return $result;
    }
}