<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsWorkExperience extends Model
{
	protected $table = 'pds_work_experience';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}
}
