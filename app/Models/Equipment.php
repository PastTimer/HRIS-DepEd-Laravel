<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    // Hardware connections
    public function school() { return $this->belongsTo(School::class); }
    public function itemType() { return $this->belongsTo(ItemType::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function dcpPackage() { return $this->belongsTo(DcpPackage::class); }

    // Personnel connections
    public function accountableOfficer() { 
        return $this->belongsTo(Employee::class, 'accountable_officer_id'); 
    }
    public function custodian() { 
        return $this->belongsTo(Employee::class, 'custodian_id'); 
    }
}