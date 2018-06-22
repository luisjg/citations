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
	 * Query scope to limit returned results only to API keys that are actually
	 * active.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeWhereIsActive($query) {
		return $query->where('active', '1');
	}

	/**
	 * Query scope to limit returned results only to the given API key value.
	 *
	 * @param Builder $query
	 * @param string $key The value of the API key
	 *
	 * @return Builder
	 */
	public function scopeWhereKeyValue($query, $key) {
		return $query->where('key', $key);
	}

	/**
	 * Returns whether any of the scopes associated with this API key grant the
	 * given permission name to the key.
	 *
	 * @param string $permission Name of the permission to check
	 * @return bool
	 */
	public function hasPermission($permission) {
		if(!isset($this->scopes)) {
			$this->load('scopes');
		}

		foreach($this->scopes as $scope) {
			if($scope->hasPermission($permission) || $scope->hasAllPermissions()) {
				return true;
			}
		}

		return false;
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