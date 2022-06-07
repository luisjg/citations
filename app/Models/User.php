<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $table = "users";
	protected $primaryKey = "user_id";
	public $incrementing = false;

	protected $hidden = ['user_id', 'scopus_id'];

	public function citations() {
		return $this->belongsToMany('App\Citation', 'nemo.memberships', 'individuals_id', 'parent_entities_id')
			->withPivot('role_position')
			->withPivot('precedence');
	}

	public function facultyUrl() {
		return $this->hasOne('App\FacultyUrl', 'user_id');
	}

	/**
	 * Query scope to filter records by the Scopus author ID of an associated
	 * individual.
	 *
	 * @param string $authorId The author ID of the member
	 * @return Builder
	 */
	public function scopeWhereAuthorId($query, $authorId) {
		return $query->where('scopus_id', $authorId);
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

	/**
	 * Query scope to filter records by the ORCID of an associated individual.
	 *
	 * @param string $orcid The ORCID of the member
	 * @return Builder
	 */
	public function scopeWhereOrcid($query, $orcid) {
		return $query->where('orcid', $orcid);
	}

	/**
	 * Returns the formatted name of the document creator in Scopus. This will
	 * be used for generating collaboration strings dynamically.
	 *
	 * @example "Edmunds P."
	 * @return string
	 */
	public function getScopusFormattedNameAttribute() {
		return $this->last_name . ' ' . $this->first_name[0] . '.';
	}
}
