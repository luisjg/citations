<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublishedMetadata extends Model
{
	protected $table = "citations.published_metadata";
	protected $primaryKey = "citation_id";
	public $incrementing = false;

	protected $hidden = ['citation_id', 'created_at', 'updated_at'];
}