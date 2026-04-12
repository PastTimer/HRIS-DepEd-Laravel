<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsSubmission extends Model
{
	protected $table = 'pds_submissions';

	protected $guarded = [];

		/**
		 * The attributes that should be cast to native types.
		 *
		 * @var array
		 */
		protected $casts = [
			'submitted_at' => 'datetime',
			'reviewed_at' => 'datetime',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}

	public function submitter()
	{
		return $this->belongsTo(User::class, 'submitted_by');
	}

	public function reviewer()
	{
		return $this->belongsTo(User::class, 'reviewed_by');
	}

	public function pdsMainSnapshot()
	{
		return $this->hasOne(PdsMain::class, 'submission_id');
	}

	public function childrenSnapshots()
	{
		return $this->hasMany(PdsChild::class, 'submission_id');
	}

	public function educationSnapshots()
	{
		return $this->hasMany(PdsEducation::class, 'submission_id');
	}

	public function eligibilitySnapshots()
	{
		return $this->hasMany(PdsEligibility::class, 'submission_id');
	}

	public function workExperienceSnapshots()
	{
		return $this->hasMany(PdsWorkExperience::class, 'submission_id');
	}

	public function voluntaryWorkSnapshots()
	{
		return $this->hasMany(PdsVoluntaryWork::class, 'submission_id');
	}

	public function trainingSnapshots()
	{
		return $this->hasMany(PdsTraining::class, 'submission_id');
	}

	public function skillsSnapshots()
	{
		return $this->hasMany(PdsSkill::class, 'submission_id');
	}

	public function distinctionsSnapshots()
	{
		return $this->hasMany(PdsDistinction::class, 'submission_id');
	}

	public function membershipsSnapshots()
	{
		return $this->hasMany(PdsMembership::class, 'submission_id');
	}

	public function referencesSnapshots()
	{
		return $this->hasMany(PdsReference::class, 'submission_id');
	}
}
