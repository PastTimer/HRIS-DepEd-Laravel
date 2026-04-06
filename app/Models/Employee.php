<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 'name_ext', 
        'gender', 'date_of_birth', 'place_of_birth', 'civil_status', 'blood_type',
        'employee_id', 'position_id', 'item_no', 'step', 'last_step', 'sg', 'employee_type',
        'school_id', 'deployed_school_id',
        'gsis_no', 'pagibig_no', 'philhealth_no', 'sss_no', 'tin_no',
        'contact_no', 'email_address', 'address',
        'is_active', 'photo_path'
    ];

    public function school() { return $this->belongsTo(School::class, 'school_id'); }
    public function deployedSchool() { return $this->belongsTo(School::class, 'deployed_school_id'); }
    public function position() { return $this->belongsTo(Position::class); }
    public function equipment() {return $this->hasMany(Equipment::class, 'accountable_officer_id');}
    public function trainings() {return $this->belongsToMany(Training::class, 'employee_training');}
    public function specialOrders() {return $this->belongsToMany(SpecialOrder::class, 'employee_specialorder', 'employee_id', 'specialorder_id');}
}