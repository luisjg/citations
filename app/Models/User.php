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

	/**
	 * Query scope to filter records by the members ID of an associated
	 * individual.
	 *
	 * @param string $membersId The ID of the member
	 * @return Builder
	 */
	public function scopeWhereMembersId($query, $membersId) {
		return $query->where('user_id', $membersId);
	}
}