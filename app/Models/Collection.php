<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
	protected $table = "citations.collections";
	protected $primaryKey = "citation_id";
	public $incrementing = false;

	protected $fillable = [
		'citation_id',
		'edition',
		'series',
		'number',
		'volume',
		'chapter',
		'pages',
	];

	protected $hidden = ['citation_id', 'created_at', 'updated_at'];
}