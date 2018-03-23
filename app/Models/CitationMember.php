<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CitationMember extends Model
{
	protected $table = "nemo.memberships";
	protected $primaryKey = "individuals_id";
	public $incrementing = false;

	/**
	 * Query scope to filter records by the citation ID and without having
	 * to prepend the "citations:" collection manually.
	 *
	 * @param int $id The ID of the citation
	 * @return Builder
	 */
	public function scopeWhereCitationId($query, $id) {
		return $this->where("parent_entities_id", "citations:{$id}");
	}

	/**
	 * Query scope to filter records by the members ID of an associated
	 * individual.
	 *
	 * @param string $membersId The ID of the member
	 * @return Builder
	 */
	public function scopeWhereMembersId($query, $membersId) {
		return $this->where("individuals_id", $membersId);
	}
}