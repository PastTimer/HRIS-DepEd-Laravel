<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsSubmission extends Model
{
	protected $table = 'pds_submissions';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}
}
