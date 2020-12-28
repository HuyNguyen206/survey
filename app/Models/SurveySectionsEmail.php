<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;
use App\Models\User;
use App\Models\Apiisc;
use Exception;

class SurveySectionsEmail extends Model {

//class SurveySections extends Eloquent {
//    use \Venturecraft\Revisionable\RevisionableTrait;
//    protected $revisionEnabled = true;
//    protected $revisionCleanup = true; //Remove old revisions (works only when used with $historyLimit)
//    protected $historyLimit = 500; //Maintain a maximum of 500 changes at any point of time, while cleaning up old revisions.
    protected $table = 'outbound_survey_sections_email';
    protected $primaryKey = 'id';
    public $timestamps = false;

   
}
