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

	public function voluntary_works()
	{
		return $this->hasMany(PdsVoluntaryWork::class, 'personnel_id', 'personnel_id');
	}

	public function skills()
	{
		return $this->hasMany(PdsSkill::class, 'personnel_id', 'personnel_id');
	}

	public function distinctions()
	{
		return $this->hasMany(PdsDistinction::class, 'personnel_id', 'personnel_id');
	}

	public function memberships()
	{
		return $this->hasMany(PdsMembership::class, 'personnel_id', 'personnel_id');
	}
}
