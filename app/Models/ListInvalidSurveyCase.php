<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ListInvalidSurveyCase extends Model {

    protected $table = 'list_invalid_survey_case';
    public $timestamps = true;
    protected $fillable = [
        'section_id', 'contract_number', 'section_code', 'survey_id', 'sub_parent_desc', 'sale_branch_cole', 'location_id', 'support', 'sub_support', 'user_name',
        'type_error', 'created_at', 'updated_at', 'updated_date_on_survey'];


}
