<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
	protected $table = "keys";
	protected $primaryKey = "key_id";

	/**
	 * Returns a BelongsToMany instance representing the set of scopes that this
	 * key has been authorized to use.
	 *
	 * @return BelongsToMany
	 */
	public function scopes() {
		return $this->belongsToMany('App\ApiScope', 'key_scope', 'key_id', 'scope');
	}

	/**
	 * Returns whether this key is active for this service.
	 *
	 * @return bool
	 */
	public function isActive() {
		return (bool)$this->active;
	}
}