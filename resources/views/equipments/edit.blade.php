@extends('layouts.app')
@section('title', 'Edit Equipment')
@section('content')
<style>
    .form-section { background: #f7f8fa; padding: 20px; margin-bottom: 25px; border-radius: 5px; border-left: 4px solid #0473B4; }
    .form-section h5 { color: #2F557A; margin-bottom: 20px; text-transform: uppercase; font-weight: bold; }
    .required::after { content: " *"; color: red; }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 col-lg-12 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white">
                    <h2 class="mb-0 text-primary"><i class="ni ni-laptop mr-2"></i> EDIT EQUIPMENT</h2>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="/equipment/{{ $equipment->id }}">
                        @csrf
                        @method('PUT')

                        <div class="form-section shadow-sm">
                            <h5>Core Identification</h5>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label required">Property No.</label>
                                    <input type="text" name="property_no" class="form-control @error('property_no') is-invalid @enderror" value="{{ old('property_no', $equipment->property_no) }}" required>
                                    <small class="form-text text-muted">Enter the equipment's property number.</small>
                                    @error('property_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Old / Previous Property No.</label>
                                    <input type="text" name="old_property_no" class="form-control" value="{{ old('old_property_no', $equipment->old_property_no) }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Serial Number</label>
                                    <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $equipment->serial_number) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>Equipment Details</h5>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label required">Item (Device Type)</label>
                                    <select name="item" class="form-control @error('item') is-invalid @enderror" required>
                                        <option value="">-- Select Item --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item }}" {{ old('item', $equipment->item) == $item ? 'selected' : '' }}>{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Unit of Measure</label>
                                    <select name="unit" class="form-control">
                                        <option value="">-- Select Unit --</option>
                                        <option value="Piece" {{ old('unit', $equipment->unit) == 'Piece' ? 'selected' : '' }}>Piece</option>
                                        <option value="Set (bundled)" {{ old('unit', $equipment->unit) == 'Set (bundled)' ? 'selected' : '' }}>Set (bundled)</option>
                                        <option value="Lot" {{ old('unit', $equipment->unit) == 'Lot' ? 'selected' : '' }}>Lot</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Brand / Manufacturer</label>
                                    <select name="brand_manufacturer" class="form-control">
                                        <option value="">-- Select Brand --</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand }}" {{ old('brand_manufacturer', $equipment->brand_manufacturer) == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Model</label>
                                    <input type="text" name="model" class="form-control" value="{{ old('model', $equipment->model) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Item Description</label>
                                    <textarea name="item_description" class="form-control" rows="2">{{ old('item_description', $equipment->item_description) }}</textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group mb-3">
                                    <label class="form-control-label">Specifications (Detailed Description)</label>
                                    <textarea name="specifications" class="form-control" rows="3">{{ old('specifications', $equipment->specifications) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>DCP Information</h5>
                            <div class="row align-items-center">
                                <div class="col-md-4 form-group mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_dcp" name="is_dcp" value="1" onclick="toggleDcpFields()" {{ old('is_dcp', $equipment->is_dcp) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_dcp">DCP Equipment</label>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3" id="dcp_package_div">
                                    <label class="form-control-label">DCP Package</label>
                                    <select name="dcp_package" class="form-control">
                                        <option value="">-- Select Package --</option>
                                        @foreach($packages as $pkg)
                                            <option value="{{ $pkg }}" {{ old('dcp_package', $equipment->dcp_package) == $pkg ? 'selected' : '' }}>{{ $pkg }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3" id="dcp_year_div">
                                    <label class="form-control-label">DCP Year Package</label>
                                    <input type="number" name="dcp_year" class="form-control" min="2000" max="2099" value="{{ old('dcp_year', $equipment->dcp_year) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>Financial Information</h5>
                            <div class="row">
                                <div class="col-md-3 form-group mb-3">
                                    <label class="form-control-label">Acquisition Cost (Value)</label>
                                    <input type="number" step="0.01" name="acquisition_cost" id="acquisition_cost" class="form-control" value="{{ old('acquisition_cost', $equipment->acquisition_cost) }}" onchange="updateCategory()">
                                </div>
                                <div class="col-md-3 form-group mb-3">
                                    <label class="form-control-label">Category</label>
                                    <input type="text" id="category_field" class="form-control" readonly value="{{ $equipment->category ?? 'Low-value' }}">
                                </div>
                                <div class="col-md-3 form-group mb-3">
                                    <label class="form-control-label">Classification</label>
                                    <select name="classification" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="Machinery and Equipment" {{ old('classification', $equipment->classification) == 'Machinery and Equipment' ? 'selected' : '' }}>Machinery and Equipment</option>
                                        <option value="Office, ICT Equipment, Furniture And Fixtures" {{ old('classification', $equipment->classification) == 'Office, ICT Equipment, Furniture And Fixtures' ? 'selected' : '' }}>Office, ICT Equipment, Furniture And Fixtures</option>
                                        <option value="Other Property, Plant And Equipment" {{ old('classification', $equipment->classification) == 'Other Property, Plant And Equipment' ? 'selected' : '' }}>Other Property, Plant And Equipment</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group mb-3">
                                    <label class="form-control-label">Estimated Useful Life</label>
                                    <input type="number" name="estimated_useful_life" class="form-control" value="{{ old('estimated_useful_life', $equipment->estimated_useful_life) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">GL-SL Code (NGAS Code)</label>
                                    <input type="text" name="gl_sl_code" class="form-control" value="{{ old('gl_sl_code', $equipment->gl_sl_code) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">UACS</label>
                                    <input type="text" name="uacs_code" class="form-control" value="{{ old('uacs_code', $equipment->uacs_code) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>Acquisition Details</h5>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Mode of Acquisition</label>
                                    <select name="mode_acquisition" id="mode_acquisition" class="form-control" onchange="toggleDonor()">
                                        <option value="">-- Select --</option>
                                        <option value="DepEd Purchase" {{ old('mode_acquisition', $equipment->mode_acquisition) == 'DepEd Purchase' ? 'selected' : '' }}>DepEd Purchase</option>
                                        <option value="Donation" {{ old('mode_acquisition', $equipment->mode_acquisition) == 'Donation' ? 'selected' : '' }}>Donation</option>
                                        <option value="Grant" {{ old('mode_acquisition', $equipment->mode_acquisition) == 'Grant' ? 'selected' : '' }}>Grant</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Source of Acquisition</label>
                                    <select name="source_acquisition" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Central Office', 'Regional Office', 'SDO', 'School', 'Local Government Unit (LGU)', 'Private Corporation', 'National Government Agency (NGA)', 'Parent-Teacher Association (PTA)'] as $source)
                                            <option value="{{ $source }}" {{ old('source_acquisition', $equipment->source_acquisition) == $source ? 'selected' : '' }}>{{ $source }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3" id="donor_div" style="display:none;">
                                    <label class="form-control-label">Donor</label>
                                    <input type="text" name="donor" class="form-control" value="{{ old('donor', $equipment->donor) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Source of Funds</label>
                                    <select name="source_funds" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Program Support Funds (PSF)', 'General Fund (GF)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)', 'School Education Fund (SEF)'] as $fund)
                                            <option value="{{ $fund }}" {{ old('source_funds', $equipment->source_funds) == $fund ? 'selected' : '' }}>{{ $fund }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Allotment Class</label>
                                    <select name="allotment_class" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="Personal Services (PS)" {{ old('allotment_class', $equipment->allotment_class) == 'Personal Services (PS)' ? 'selected' : '' }}>Personal Services (PS)</option>
                                        <option value="MOOE" {{ old('allotment_class', $equipment->allotment_class) == 'MOOE' ? 'selected' : '' }}>MOOE</option>
                                        <option value="Capital Outlay (CO)" {{ old('allotment_class', $equipment->allotment_class) == 'Capital Outlay (CO)' ? 'selected' : '' }}>Capital Outlay (CO)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Acquisition / Received Date</label>
                                    <input type="date" name="received_date" class="form-control" value="{{ old('received_date', $equipment->received_date) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>Initial Transaction & Accountability</h5>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Transaction Type</label>
                                    <select name="transaction_type" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Beginning Inventory', 'Delivery', 'Inspection', 'Issuance/Transfer', 'Return', 'Disposal', 'Stock Position'] as $type)
                                            <option value="{{ $type }}" {{ old('transaction_type', $equipment->transaction_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8 form-group mb-3">
                                    <label class="form-control-label">Supporting Documents (Initial)</label>
                                    <select name="supporting_doc_type" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Sales Invoice (SI)', 'Official Receipt (OR)', 'Delivery Receipt (DR)', 'Inspection Acceptance Report (IAR)', 'Report of Receipt and Stock Position (RRSP)', 'Property Acknowledgment Receipt (PAR)', 'Inventory Custodian Slip (ICS)', 'Return and Receipt of Property/Equipment (RRPE)', 'Waste Material Report (WMR)'] as $doc)
                                            <option value="{{ $doc }}" {{ old('supporting_doc_type', $equipment->supporting_doc_type) == $doc ? 'selected' : '' }}>{{ $doc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Document No.</label>
                                    <input type="text" name="supporting_doc_no" class="form-control" value="{{ old('supporting_doc_no', $equipment->supporting_doc_no) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Accountable Officer</label>
                                    <select name="accountable_officer_id" class="form-control">
                                        <option value="">-- Select Personnel --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ old('accountable_officer_id', $equipment->accountable_officer_id) == $emp->id ? 'selected' : '' }}>{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Date Assigned to Officer</label>
                                    <input type="date" name="accountable_date" class="form-control" value="{{ old('accountable_date', $equipment->accountable_date) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Custodian / End User</label>
                                    <select name="custodian_id" class="form-control">
                                        <option value="">-- Select Personnel --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ old('custodian_id', $equipment->custodian_id) == $emp->id ? 'selected' : '' }}>{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Date Assigned to Custodian</label>
                                    <input type="date" name="custodian_date" class="form-control" value="{{ old('custodian_date', $equipment->custodian_date) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm border-warning">
                            <h5 class="text-warning"><i class="fas fa-exchange-alt mr-2"></i> Movement Tracking</h5>
                            <p class="text-muted small">Update this section if the equipment is being transferred to a new employee or disposed of.</p>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Received by (New Accountable Officer / Custodian)</label>
                                    <select name="new_accountable_id" class="form-control">
                                        <option value="">-- Select Personnel --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ old('new_accountable_id', $equipment->new_accountable_id) == $emp->id ? 'selected' : '' }}>{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Date received by New Personnel</label>
                                    <input type="date" name="new_accountable_date" class="form-control" value="{{ old('new_accountable_date', $equipment->new_accountable_date) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8 form-group mb-3">
                                    <label class="form-control-label">Supporting Documents for Movement</label>
                                    <select name="new_supporting_doc_type" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Property Acknowledgment Receipt (PAR)', 'Inventory Custodian Slip (ICS)', 'Report of Receipt and Stock Position (RRSP)', 'Return and Receipt of Property/Equipment (RRPE)', 'Waste Material Report (WMR)'] as $doc)
                                            <option value="{{ $doc }}" {{ old('new_supporting_doc_type', $equipment->new_supporting_doc_type) == $doc ? 'selected' : '' }}>{{ $doc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Document No.</label>
                                    <input type="text" name="new_supporting_doc_no" class="form-control" value="{{ old('new_supporting_doc_no', $equipment->new_supporting_doc_no) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>Supplier & Warranty</h5>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Supplier / Distributor</label>
                                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $equipment->supplier) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Supplier Contact</label>
                                    <input type="text" name="supplier_contact" class="form-control" value="{{ old('supplier_contact', $equipment->supplier_contact) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <div class="custom-control custom-checkbox mt-4">
                                        <input type="checkbox" class="custom-control-input" id="under_warranty" name="under_warranty" value="1" onclick="toggleWarrantyDate()" {{ old('under_warranty', $equipment->under_warranty) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="under_warranty">Under Warranty</label>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3" id="warranty_date_div" style="display:none;">
                                    <label class="form-control-label">End of Warranty</label>
                                    <input type="date" name="warranty_end_date" class="form-control" value="{{ old('warranty_end_date', $equipment->warranty_end_date) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>Status & Condition</h5>
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Equipment Location</label>
                                    <input type="text" name="equipment_location" class="form-control" value="{{ old('equipment_location', $equipment->equipment_location) }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <div class="custom-control custom-checkbox mt-4">
                                        <input type="checkbox" class="custom-control-input" id="is_functional" name="is_functional" value="1" {{ old('is_functional', $equipment->is_functional) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_functional">Functional</label>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-control-label">Equipment Condition (COA Classification)</label>
                                    <select name="equipment_condition" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Serviceable', 'For Repair', 'Unserviceable', 'Not Applicable'] as $cond)
                                            <option value="{{ $cond }}" {{ old('equipment_condition', $equipment->equipment_condition) == $cond ? 'selected' : '' }}>{{ $cond }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Accountability / Disposition Status</label>
                                    <select name="disposition_status" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach(['Normal', 'Transferred', 'Stolen', 'Lost', 'Damaged due to calamity', 'For Disposal'] as $disp)
                                            <option value="{{ $disp }}" {{ old('disposition_status', $equipment->disposition_status) == $disp ? 'selected' : '' }}>{{ $disp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-control-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $equipment->remarks) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section shadow-sm">
                            <h5>School Association</h5>
                            <div class="row">
                                <div class="col-md-12 form-group mb-3">
                                    <label class="form-control-label required">School</label>
                                    @if(Auth::user()->role === 'school')
                                        @php $userSchool = $schools->where('name', Auth::user()->access_level)->first(); @endphp
                                        <input type="hidden" name="school_id" value="{{ $userSchool->id ?? '' }}">
                                        <input type="text" class="form-control" value="{{ Auth::user()->access_level }}" readonly>
                                    @else
                                        <select name="school_id" class="form-control @error('school_id') is-invalid @enderror" required>
                                            <option value="">-- Select School --</option>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}" {{ old('school_id', $equipment->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center mt-4 mb-3">
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="ni ni-check-bold mr-2"></i> Update Equipment
                                </button>
                                <a href="/equipment" class="btn btn-secondary btn-lg px-5 ml-3">
                                    <i class="ni ni-bold-left mr-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDcpFields() {
        var isDcp = document.getElementById('is_dcp').checked;
        document.getElementById('dcp_package_div').style.display = isDcp ? 'block' : 'none';
        document.getElementById('dcp_year_div').style.display = isDcp ? 'block' : 'none';
    }
    
    function updateCategory() {
        var cost = parseFloat(document.getElementById('acquisition_cost').value);
        var field = document.getElementById('category_field');
        if (!isNaN(cost)) {
            if (cost >= 50000) {
                field.value = 'High-value';
                field.style.backgroundColor = '#28a745';
                field.style.color = 'white';
            } else {
                field.value = 'Low-value';
                field.style.backgroundColor = '#ffc107';
                field.style.color = 'black';
            }
        } else {
            field.value = 'Low-value';
            field.style.backgroundColor = '';
            field.style.color = '';
        }
    }
    
    function toggleDonor() {
        var mode = document.getElementById('mode_acquisition').value;
        var donorDiv = document.getElementById('donor_div');
        donorDiv.style.display = (mode === 'Donation' || mode === 'Grant') ? 'block' : 'none';
    }
    
    function toggleWarrantyDate() {
        var isWarranty = document.getElementById('under_warranty').checked;
        document.getElementById('warranty_date_div').style.display = isWarranty ? 'block' : 'none';
    }

    // Run on load to set proper visibility based on existing database values
    document.addEventListener('DOMContentLoaded', function() {
        toggleDcpFields();
        updateCategory();
        toggleDonor();
        toggleWarrantyDate();
    });
</script>
@endsection