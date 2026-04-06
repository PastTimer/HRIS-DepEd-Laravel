<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsChild extends Model
{
	protected $table = 'pds_children';

	protected $guarded = [];

	public function personnel()
	{
		return $this->belongsTo(Personnel::class, 'personnel_id');
	}
}
