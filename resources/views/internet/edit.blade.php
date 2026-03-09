@extends('layouts.app')
@section('title', 'Complete Internet Profile')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0"><i class="fas fa-network-wired mr-2 text-primary"></i> Connectivity Profile: {{ $school->name }}</h3>
                <small class="text-muted">Station ID: {{ $school->school_id }}</small>
            </div>
            <a href="/internet" class="btn btn-sm btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card-body bg-secondary">
            <style>
                .survey-section { background: white; padding: 25px; margin-bottom: 25px; border-radius: 8px; border-left: 5px solid #5e72e4; box-shadow: 0 0 2rem 0 rgba(136, 152, 170, .15); }
                .survey-q { font-weight: 600; margin-top: 15px; margin-bottom: 8px; color: #32325d; font-size: 0.9rem; display: block; }
                .survey-sub { margin-left: 20px; }
                .label-caps { font-size: 0.65rem; text-transform: uppercase; letter-spacing: .025em; color: #8898aa; font-weight: 700; }
            </style>

            <form method="POST" action="/internet/{{ $school->id }}">
                @csrf
                @method('PUT')  
                <div class="survey-section">
                    <h6 class="heading-small text-muted mb-4">I. Connectivity Availability & Signals</h6>
                    <div class="row">
                        <div class="col-lg-6">
                            <label class="survey-q">1. Are there any internet service provider(s) available in the area?</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="availYes" name="is_provider_available" value="Yes" class="custom-control-input" {{ ($profile->is_provider_available ?? '') == 'Yes' ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="availYes">Yes</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="availNo" name="is_provider_available" value="No" class="custom-control-input" {{ ($profile->is_provider_available ?? '') == 'No' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="availNo">No</label>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="survey-q">2. What internet service provider(s) are available?</label>
                            @php $saved_av = explode(',', $profile->available_providers ?? ''); @endphp
                            @foreach(['PLDT', 'Globe', 'Smart', 'SkyCable', 'Converge', 'Starlink', 'DITO', 'Others'] as $p)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="available_providers[]" value="{{ $p }}" class="custom-control-input others-trigger" id="av_{{ $p }}" {{ in_array($p, $saved_av) ? 'checked' : '' }} data-target="av_others_input">
                                <label class="custom-control-label" for="av_{{ $p }}">{{ $p }}</label>
                            </div>
                            @endforeach
                            <input type="text" id="av_others_input" name="available_providers_others" class="form-control form-control-sm mt-2 w-50" style="display:none" placeholder="Please specify...">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <label class="survey-q">3. Mobile network signals available?</label>
                            @php $saved_sigs = explode(',', $profile->mobile_signals ?? ''); @endphp
                            @foreach(['3G', 'LTE', '5G', 'No Signal'] as $sg)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="mobile_signals[]" value="{{ $sg }}" class="custom-control-input" id="sg_{{ $sg }}" {{ in_array($sg, $saved_sigs) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sg_{{ $sg }}">{{ $sg }}</label>
                            </div>
                            @endforeach
                        </div>
                        <div class="col-md-4">
                            <label class="survey-q">4. Area has Mobile Data Connectivity?</label>
                            <select name="has_mobile_data" class="form-control form-control-sm">
                                <option value="Yes" {{ ($profile->has_mobile_data ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ ($profile->has_mobile_data ?? '') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="survey-q">5. Quality of Mobile Data</label>
                            <input type="text" name="mobile_data_quality" class="form-control form-control-sm" value="{{ $profile->mobile_data_quality ?? '' }}" placeholder="e.g. Good, Intermittent">
                        </div>
                    </div>
                </div>

                <div class="survey-section">
                    <h6 class="heading-small text-muted mb-4">II. Subscription & Monthly Cost</h6>
                    <div class="row">
                        <div class="col-lg-6">
                            <label class="survey-q">6. Do you subscribe to any ISP?</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="subYes" name="has_subscription" value="Yes" class="custom-control-input" {{ ($profile->total_isps ?? 0) > 0 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="subYes">Yes</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="subNo" name="has_subscription" value="No" class="custom-control-input" {{ ($profile->total_isps ?? 0) == 0 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="subNo">No</label>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="survey-q">7. Specify subscribed provider(s)</label>
                            @php $saved_sub = explode(',', $profile->subscribed_providers ?? ''); @endphp
                            @foreach(['PLDT', 'Globe', 'Smart', 'Starlink', 'Converge', 'Others'] as $sp)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="subscribed_providers[]" value="{{ $sp }}" class="custom-control-input others-trigger" id="sub_{{ $sp }}" {{ in_array($sp, $saved_sub) ? 'checked' : '' }} data-target="sub_others_input">
                                <label class="custom-control-label" for="sub_{{ $sp }}">{{ $sp }}</label>
                            </div>
                            @endforeach
                            <input type="text" id="sub_others_input" name="subscribed_providers_others" class="form-control form-control-sm mt-2 w-50" style="display:none" placeholder="Specify others...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="survey-q">8. Total ISPs Subscribed</label>
                            <input type="number" name="total_isps" class="form-control form-control-sm" value="{{ $profile->total_isps ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="survey-q">9. Total Cost per Month</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">₱</span></div>
                                <input type="number" step="0.01" name="total_cost" class="form-control" value="{{ $profile->total_cost ?? 0.00 }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="survey-section">
                    <h6 class="heading-small text-muted mb-4">III. Purpose, Usage & Coverage</h6>
                    <div class="row">
                        <div class="col-lg-12">
                            <label class="survey-q">10. Purpose of Subscription</label>
                            @php $saved_purp = explode(',', $profile->subscription_purpose ?? ''); @endphp
                            @foreach(['Administrative use', 'Classroom instruction', 'Both admin and classroom'] as $purp)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="subscription_purpose[]" value="{{ $purp }}" class="custom-control-input" id="pp_{{ $loop->index }}" {{ in_array($purp, $saved_purp) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="pp_{{ $loop->index }}">{{ $purp }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <label class="label-caps">11. Admin Rooms</label>
                            <input type="number" name="rooms_admin_use" class="form-control form-control-sm" value="{{ $profile->rooms_admin_use ?? 0 }}">
                        </div>
                        <div class="col-md-3">
                            <label class="label-caps">12. Classrooms</label>
                            <input type="number" name="rooms_classroom_use" class="form-control form-control-sm" value="{{ $profile->rooms_classroom_use ?? 0 }}">
                        </div>
                        <div class="col-md-3">
                            <label class="label-caps">13. Other Rooms</label>
                            <input type="number" name="rooms_other_use" class="form-control form-control-sm" value="{{ $profile->rooms_other_use ?? 0 }}">
                        </div>
                        <div class="col-md-3">
                            <label class="label-caps">14. Rooms Covered</label>
                            <input type="number" name="rooms_covered" class="form-control form-control-sm" value="{{ $profile->rooms_covered ?? 0 }}">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="survey-q">15. Total Access Points</label>
                            <input type="number" name="access_points" class="form-control form-control-sm" value="{{ $profile->access_points ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="survey-q">16. Challenges (Insufficient Bandwidth)</label>
                            <textarea name="insufficient_bandwidth_reason" class="form-control form-control-sm" rows="1">{{ $profile->insufficient_bandwidth_reason ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <label class="survey-q">17. Coverage Area</label>
                            @php $saved_cov = explode(',', $profile->coverage_areas ?? ''); @endphp
                            @foreach(['Building/Office-wide', 'School-wide', 'Faculty area', 'ICT Room', 'Library'] as $area)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="coverage_areas[]" value="{{ $area }}" class="custom-control-input" id="area_{{ $loop->index }}" {{ in_array($area, $saved_cov) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="area_{{ $loop->index }}">{{ $area }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="survey-section">
                    <h6 class="heading-small text-muted mb-4">IV. DICT Free Wi-Fi & Power Source</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="survey-q">18. Recipient of DICT Wi-Fi?</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="dictYes" name="is_dict_recipient" value="Yes" class="custom-control-input" {{ ($profile->is_dict_recipient ?? '') == 'Yes' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="dictYes">Yes</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="dictNo" name="is_dict_recipient" value="No" class="custom-control-input" {{ ($profile->is_dict_recipient ?? '') == 'No' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="dictNo">No</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="survey-q">19. DICT Rating</label>
                            <input type="text" name="dict_rating" class="form-control form-control-sm" value="{{ $profile->dict_rating ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="survey-q">20. Sufficient Bandwidth?</label>
                            <select name="has_sufficient_bandwidth" class="form-control form-control-sm">
                                <option value="Yes" {{ ($profile->has_sufficient_bandwidth ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ ($profile->has_sufficient_bandwidth ?? '') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="survey-q">21. If No Subscription, Why?</label>
                            <textarea name="no_subscription_reason" class="form-control form-control-sm" rows="1">{{ $profile->no_subscription_reason ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="survey-q">22. Has Electricity?</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="elecYes" name="has_electricity" value="Yes" class="custom-control-input" {{ ($profile->has_electricity ?? '') == 'Yes' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="elecYes">Yes</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="elecNo" name="has_electricity" value="No" class="custom-control-input" {{ ($profile->has_electricity ?? '') == 'No' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="elecNo">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="survey-q">23. Sources of Electricity</label>
                            @php $saved_elec = explode(',', $profile->electricity_sources ?? ''); @endphp
                            @foreach(['Grid', 'Generator', 'Solar', 'Others'] as $el)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="electricity_sources[]" value="{{ $el }}" class="custom-control-input others-trigger" id="ee_{{ $el }}" {{ in_array($el, $saved_elec) ? 'checked' : '' }} data-target="ee_others_input">
                                <label class="custom-control-label" for="ee_{{ $el }}">{{ $el }}</label>
                            </div>
                            @endforeach
                            <input type="text" id="ee_others_input" name="electricity_sources_others" class="form-control form-control-sm mt-2 w-50" style="display:none" placeholder="Specify others...">
                        </div>
                        <div class="col-md-3">
                            <label class="survey-q">24. Powered by Solar?</label>
                            <select name="is_solar_powered" class="form-control form-control-sm">
                                <option value="Yes" {{ ($profile->is_solar_powered ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ ($profile->is_solar_powered ?? '') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="survey-q">25. Frequent Brownouts?</label>
                            <select name="frequent_brownouts" class="form-control form-control-sm">
                                <option value="Yes" {{ ($profile->frequent_brownouts ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ ($profile->frequent_brownouts ?? '') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-right py-4 mb-5">
                    <button type="submit" class="btn btn-primary btn-lg shadow px-5">
                        <i class="fas fa-save mr-2"></i> Save Connectivity Profile
                    </button>
                </div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Toggle "Others" input fields based on legacy checkbox names
                    document.querySelectorAll('.others-trigger').forEach(checkbox => {
                        const toggleInput = () => {
                            const targetId = checkbox.dataset.target;
                            const targetInput = document.getElementById(targetId);
                            if (checkbox.checked && checkbox.value === 'Others') {
                                targetInput.style.display = 'inline-block';
                                targetInput.required = true;
                            } else if (checkbox.value === 'Others') {
                                targetInput.style.display = 'none';
                                targetInput.value = '';
                                targetInput.required = false;
                            }
                        };
                        
                        checkbox.addEventListener('change', toggleInput);
                        toggleInput(); // Run on load
                    });
                });
            </script>
        </div>
    </div>
</div>
@endsection