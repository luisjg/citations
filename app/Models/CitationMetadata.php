<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CitationMetadata extends Model
{
	protected $table = "citations.citation_metadata";
	protected $primaryKey = "citation_id";
	public $incrementing = false;

	protected $fillable = [
		'citation_id',
		'title',
		'abstract',
		'book_title',
		'journal',
	];

	protected $hidden = ['citation_id', 'created_at', 'updated_at'];
}