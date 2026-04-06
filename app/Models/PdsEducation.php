<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsEducation extends Model
{
	protected $table = 'pds_education';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}
}
