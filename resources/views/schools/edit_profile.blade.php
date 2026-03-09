@extends('layouts.app')
@section('title', 'Edit School Profile')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header border-0 bg-white d-flex justify-content-between">
            <h3 class="mb-0">Edit Stakeholder / School Profile: {{ $school->name }}</h3>
            <a href="/schools/{{ $school->id }}" class="btn btn-sm btn-secondary">Cancel</a>
        </div>
        <div class="card-body bg-secondary">
            <form method="POST" action="/schools/{{ $school->id }}/profile/update">
                @csrf
                
                <h6 class="heading-small text-muted mb-4">General Information</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">School ID (System Reference)</label>
                            <input type="text" class="form-control bg-white" value="{{ $school->school_id }}" readonly>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Governance Level</label>
                            <input type="text" name="governance_level" class="form-control" value="{{ $profile->governance_level ?? '' }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">School Name (Official)</label>
                            <input type="text" name="school_name" class="form-control" value="{{ $profile->school_name ?? $school->name }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 form-group"><label class="form-control-label">RO</label><input type="text" name="ro" class="form-control" value="{{ $profile->ro ?? '' }}"></div>
                        <div class="col-lg-4 form-group"><label class="form-control-label">SDO</label><input type="text" name="sdo" class="form-control" value="{{ $profile->sdo ?? '' }}"></div>
                        <div class="col-lg-4 form-group"><label class="form-control-label">School District</label><input type="text" name="school_district" class="form-control" value="{{ $profile->school_district ?? $school->district }}"></div>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Address & PSGC</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-md-12 form-group"><label class="form-control-label">Address (Street)</label><input type="text" name="address_street" class="form-control" value="{{ $profile->address_street ?? '' }}"></div>
                        <div class="col-lg-3 form-group"><label class="form-control-label">Barangay</label><input type="text" name="address_barangay" class="form-control" value="{{ $profile->address_barangay ?? '' }}"></div>
                        <div class="col-lg-3 form-group"><label class="form-control-label">City/Municipality</label><input type="text" name="address_city" class="form-control" value="{{ $profile->address_city ?? '' }}"></div>
                        <div class="col-lg-3 form-group"><label class="form-control-label">Province</label><input type="text" name="address_province" class="form-control" value="{{ $profile->address_province ?? '' }}"></div>
                        <div class="col-lg-3 form-group"><label class="form-control-label">PSGC</label><input type="text" name="psgc" class="form-control" value="{{ $profile->psgc ?? '' }}"></div>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Contact & Personnel</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-4 form-group"><label class="form-control-label">Mobile 1</label><input type="text" name="contact_mobile1" class="form-control" value="{{ $profile->contact_mobile1 ?? '' }}"></div>
                        <div class="col-lg-4 form-group"><label class="form-control-label">Mobile 2</label><input type="text" name="contact_mobile2" class="form-control" value="{{ $profile->contact_mobile2 ?? '' }}"></div>
                        <div class="col-lg-4 form-group"><label class="form-control-label">Landline</label><input type="text" name="contact_landline" class="form-control" value="{{ $profile->contact_landline ?? '' }}"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <label class="form-control-label text-primary">School Head Info</label>
                            <input type="text" name="head_name" class="form-control mb-2" value="{{ $profile->head_name ?? '' }}" placeholder="Name">
                            <input type="text" name="head_position" class="form-control mb-2" value="{{ $profile->head_position ?? '' }}" placeholder="Position">
                            <input type="email" name="head_email" class="form-control" value="{{ $profile->head_email ?? '' }}" placeholder="Email">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-control-label text-primary">Admin / Inventory Clerk</label>
                            <input type="text" name="admin_name" class="form-control mb-2" value="{{ $profile->admin_name ?? '' }}" placeholder="Name">
                            <input type="text" name="admin_mobile" class="form-control" value="{{ $profile->admin_mobile ?? '' }}" placeholder="Contact No">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Nearby Institutions & Access</h6>
                <div class="pl-lg-4">
                    <div class="form-group">
                        <label class="form-control-label mb-3">Check Nearby Institutions:</label><br>
                        @php 
                            $inst_opts = ['Provincial/City/Municipal Hall', 'Barangay Hall', 'Police Station', 'Health Center', 'Fire Station', 'Hospital', 'Church/Mosque', 'Market', 'Mall']; 
                            $saved_inst = $profile->nearby_institutions ?? '';
                        @endphp
                        @foreach($inst_opts as $opt)
                            <div class="custom-control custom-checkbox custom-control-inline mb-2" style="width: 250px;">
                                <input type="checkbox" name="nearby_institutions[]" value="{{ $opt }}" class="custom-control-input" id="nb_{{ md5($opt) }}" 
                                    {{ str_contains($saved_inst, $opt) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="nb_{{ md5($opt) }}">{{ $opt }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-group mt-4">
                        <label class="form-control-label mb-3">Access Paths:</label><br>
                        @php 
                            $path_opts = ['Paved', 'Dirt', 'Waterway']; 
                            $saved_paths = $profile->access_paths ?? '';
                        @endphp
                        @foreach($path_opts as $opt)
                            <div class="custom-control custom-checkbox custom-control-inline mb-2">
                                <input type="checkbox" name="access_paths[]" value="{{ $opt }}" class="custom-control-input" id="path_{{ md5($opt) }}" 
                                    {{ str_contains($saved_paths, $opt) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="path_{{ md5($opt) }}">{{ $opt }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Notes & Geo Data</h6>
                <div class="pl-lg-4">
                    <div class="row mb-3">
                        <div class="col-lg-4"><label class="form-control-label">Latitude</label><input type="text" name="coordinates_lat" class="form-control" value="{{ $profile->coordinates_lat ?? '' }}"></div>
                        <div class="col-lg-4"><label class="form-control-label">Longitude</label><input type="text" name="coordinates_long" class="form-control" value="{{ $profile->coordinates_long ?? '' }}"></div>
                        <div class="col-lg-4"><label class="form-control-label">Travel Time (min)</label><input type="number" name="travel_time_min" class="form-control" value="{{ $profile->travel_time_min ?? '' }}"></div>
                    </div>
                    <div class="form-group"><label class="form-control-label">Recent Developments</label><textarea name="recent_developments" class="form-control" rows="3">{{ $profile->recent_developments ?? '' }}</textarea></div>
                    <div class="form-group"><label class="form-control-label">General Notes</label><textarea name="notes" class="form-control" rows="2">{{ $profile->notes ?? '' }}</textarea></div>
                </div>

                <div class="text-center py-4">
                    <button type="submit" class="btn btn-primary btn-lg px-6">Save Stakeholder Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection