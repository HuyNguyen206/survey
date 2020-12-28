<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Location extends Model {

    protected $table = 'location';
    protected $primaryKey = 'id';
	
    public function getAllLocation(){
        $result = DB::table('location AS l')
                ->leftJoin('location_branches AS lb', 'l.id', '=', 'lb.location_id')
                ->select('l.id', 'l.name', 'region', 'branchcode')
                ->orderBy(DB::raw('region, name, branchcode'))
                ->get();
        return $result;
    }
    ///Lay chi nhanh theo tung vung
    public function getBranchLocation($region) {
        $region_detail = array();
        $region = explode(",", $region);
        foreach ($region as $detail) {
            array_push($region_detail, "Vùng " . $detail);
        }
        $result = DB::table('location')
            ->whereIn('region', $region_detail)
            ->select('id', 'name', 'region')
            ->orderBy('region', 'ASC')
            ->get();
        return $result;
    }
    ///Lay chi nhanh theo tung vung
    public function getBranchLocationPlus($region) {
        $region_detail = array();
        $region = explode(",", $region);
        foreach ($region as $detail) {
            array_push($region_detail, "Vùng " . $detail);
        }
        $result = DB::table('location AS l')
            ->leftJoin('location_branches AS lb', 'l.id', '=', 'lb.location_id')
            ->whereIn('region', $region_detail)
            ->select('l.id', 'l.name', 'region', 'branchcode')
            ->orderBy(DB::raw('region, branchcode'), 'ASC')
            ->get();
        return $result;
    }
}
