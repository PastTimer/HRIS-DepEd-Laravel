<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IspSpeedtest extends Model
{
    protected $table = 'isp_speedtests';

    protected $fillable = [
        'isp_id',
        'test_date',
        'download_mbps',
        'upload_mbps',
        'ping_ms',
        'tested_by',
        'remarks_speed',
    ];

    protected $casts = [
        'test_date' => 'datetime',
        'download_mbps' => 'decimal:2',
        'upload_mbps' => 'decimal:2',
        'ping_ms' => 'integer',
    ];

    public function isp()
    {
        return $this->belongsTo(IspInventory::class, 'isp_id');
    }
}
