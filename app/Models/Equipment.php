<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'property_no', 'old_property_no', 'serial_number', 'qr_code',
        'item', 'unit', 'brand_manufacturer', 'model', 'item_description', 'specifications',
        'is_dcp', 'dcp_package', 'dcp_year',
        'acquisition_cost', 'category', 'classification', 'estimated_useful_life', 'gl_sl_code', 'uacs_code',
        'mode_acquisition', 'source_acquisition', 'donor', 'source_funds', 'allotment_class', 'received_date', 'pmp_reference',
        'transaction_type', 'supporting_doc_type', 'supporting_doc_no', 
        'accountable_officer_id', 'accountable_date', 'custodian_id', 'custodian_date',
        'new_accountable_id', 'new_accountable_date', 'new_supporting_doc_type', 'new_supporting_doc_no',
        'supplier', 'supplier_contact', 'under_warranty', 'warranty_end_date',
        'equipment_location', 'is_functional', 'equipment_condition', 'disposition_status', 'remarks',
        'school_id', 'created_by'
    ];

    // --- RELATIONSHIPS ---

    public function school() {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function custodian() {
        return $this->belongsTo(Employee::class, 'custodian_id');
    }

    public function newAccountableOfficer() {
        return $this->belongsTo(Employee::class, 'new_accountable_id');
    }

    public function officer()
    {
        return $this->belongsTo(Employee::class, 'accountable_officer_id');
    }
}