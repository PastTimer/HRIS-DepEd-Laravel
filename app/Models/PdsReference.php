<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsReference extends Model
{
	protected $table = 'pds_references';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}
}
