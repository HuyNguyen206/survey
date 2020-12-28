<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Zone extends Model
{
    public $connection = null;
    public $no_error = 0;

    protected $table = 'outbound_zone';

    protected $fillable = array('zone_name');
}