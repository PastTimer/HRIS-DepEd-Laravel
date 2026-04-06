<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = 'personnel';

    protected $fillable = [
        'position_id',
        'assigned_school_id',
        'deployed_school_id',
        'profile_photo',
        'is_active',
        'emp_id',
        'item_number',
        'current_step',
        'last_step_increment_date',
        'salary_grade',
        'employee_type',
    ];

    public function school() { return $this->belongsTo(School::class, 'assigned_school_id'); }
    public function deployedSchool() { return $this->belongsTo(School::class, 'deployed_school_id'); }
    public function position() { return $this->belongsTo(Position::class); }
    public function pdsMain() { return $this->hasOne(PdsMain::class, 'personnel_id'); }
    public function pdsSubmissions() { return $this->hasMany(PdsSubmission::class, 'personnel_id'); }
    public function pdsChildren() { return $this->hasMany(PdsChild::class, 'personnel_id'); }
    public function pdsEducation() { return $this->hasMany(PdsEducation::class, 'personnel_id'); }
    public function pdsEligibility() { return $this->hasMany(PdsEligibility::class, 'personnel_id'); }
    public function pdsWorkExperience() { return $this->hasMany(PdsWorkExperience::class, 'personnel_id'); }
    public function pdsTraining() { return $this->hasMany(PdsTraining::class, 'personnel_id'); }
    public function pdsReferences() { return $this->hasMany(PdsReference::class, 'personnel_id'); }
    public function equipment() {return $this->hasMany(Equipment::class, 'accountable_officer_id');}
    public function trainings() {
        return $this->belongsToMany(Training::class, 'personnel_training', 'personnel_id', 'training_id');
    }
    public function specialOrders() {
        return $this->belongsToMany(SpecialOrder::class, 'personnel_specialorder', 'personnel_id', 'specialorder_id');
    }

    public function users() {
        return $this->hasMany(User::class, 'personnel_id');
    }
}