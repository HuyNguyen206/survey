<?php
namespace App;
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;

class BaseModel extends Model {

	public function selectQuery($sql_stmt) {
		return DB::select($sql_stmt);
	}

	public function sqlStatement($sql_stmt) {
		DB::statement($sql_stmt);
	}
}