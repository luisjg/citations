<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
	protected $table = "citations.documents";
	protected $primaryKey = "citation_id";
	public $incrementing = false;

	protected $fillable = [
		'citation_id',
		'doi',
		'handle',
		'url',
	];

	protected $hidden = ['citation_id', 'created_at', 'updated_at'];
}