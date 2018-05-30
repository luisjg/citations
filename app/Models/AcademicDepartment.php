<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcademicDepartment extends Model
{
	protected $table = "nemo.academicDepartments";
	protected $primaryKey = "entities_id";
	public $incrementing = false;

	public function users() {
		return $this->belongsToMany('App\User', 'nemo.memberships', 'parent_entities_id', 'individuals_id');
	}

	/**
	 * Query scope to filter records by the department ID and without having
	 * to prepend the "academic_departments:" collection manually.
	 *
	 * @param int $id The ID of the department
	 * @return Builder
	 */
	public function scopeWhereDepartmentId($query, $id) {
		return $query->where("entities_id", "academic_departments:{$id}");
	}
}