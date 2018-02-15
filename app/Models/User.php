<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $table = "citations_ws.users";
	protected $primaryKey = "user_id";
	public $incrementing = false;

	public function facultyUrl() {
		return $this->hasOne('App\FacultyUrl', 'user_id');
	}
}