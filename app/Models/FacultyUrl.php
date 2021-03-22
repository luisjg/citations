<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacultyUrl extends Model
{
	protected $table = "bedrock.faculty_urls";
	protected $primaryKey = "user_id";
	public $incrementing = false;
	protected $hidden = [
	    'user_id'
    ];
}
