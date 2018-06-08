<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiPermission extends Model
{
	protected $table = "permissions";
	protected $primaryKey = "system_name";
	public $incrementing = false;
}