<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsMain extends Model
{
	protected $table = 'pds_main';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}

	public function submission()
	{
		return $this->belongsTo(PdsSubmission::class, 'submission_id');
	}
}
