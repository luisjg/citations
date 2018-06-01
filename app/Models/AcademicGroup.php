<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcademicGroup extends Model
{
	protected $table = "nemo.academicGroups";
	protected $primaryKey = "entities_id";
	public $incrementing = false;

	/**
	 * Returns a HasMany instance representing all academic departments where
	 * this academic group is the parent.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function departments() {
		return $this->hasMany('App\AcademicDepartment', 'parent_entities_id', 'entities_id');
	}

	/**
	 * Query scope to filter records by the group ID and without having
	 * to prepend the "academic_departments:" collection manually.
	 *
	 * @param int $id The ID of the group
	 * @return Builder
	 */
	public function scopeWhereGroupId($query, $id) {
		return $query->where("entities_id", "academic_groups:{$id}");
	}
}