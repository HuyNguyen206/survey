<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SurveySectionsMG extends Eloquent {
	protected $collection = 'survey_sections';
	protected $connection = 'mongodb';
	
	protected $fillable = [
		'section_subsupporter', 'section_supporter', 'salename' ,
		'section_survey_id', 'section_connected', 'section_action',
		'section_user_name', 'section_sub_parent_desc', 'section_location',
		'section_note', 'section_time_completed', 'section_time_start',
		'section_id', 'section_code', 'section_contract_num',
		'section_contact_phone', 'nps', 'caithien_nps', 'csat_kinhdoanh',
		'csatkythuat', 'csatinternet', 'csattruyenhinh',
    ];
}
