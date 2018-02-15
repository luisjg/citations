<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CitationType extends Model
{
	protected $table = "citations.citation_types";
	protected $primaryKey = "citation_type";
	public $incrementing = false;

	protected $hidden = ['created_at', 'updated_at'];
}