<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Surveys extends Model
{
    //
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'outbound_surveys';
	
	protected $fillable = array('id', 'name', 'email','contact_number','position');
}