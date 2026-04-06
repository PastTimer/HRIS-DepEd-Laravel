<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsEligibility extends Model
{
	protected $table = 'pds_eligibility';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}
}
