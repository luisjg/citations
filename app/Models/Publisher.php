<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
	protected $table = "citations.publishers";
	protected $primaryKey = "citation_id";
	public $incrementing = false;

	protected $fillable = [
		'citation_id',
		'institution',
		'organization',
		'publisher',
		'school',
		'address',
	];

	protected $hidden = ['citation_id', 'created_at', 'updated_at'];
}