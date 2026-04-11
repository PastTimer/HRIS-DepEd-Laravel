<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsTraining extends Model
{
	protected $table = 'pds_training';
	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}

	public function verifier()
	{
		return $this->belongsTo(User::class, 'verified_by');
	}

	// Scope for pending requests
	public function scopePending($query)
	{
		return $query->where('verification_status', 'pending');
	}
}
