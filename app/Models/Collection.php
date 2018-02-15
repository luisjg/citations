<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
	protected $table = "citations.collections";
	protected $primaryKey = "citation_id";
	public $incrementing = false;

	protected $hidden = ['citation_id', 'created_at', 'updated_at'];
}