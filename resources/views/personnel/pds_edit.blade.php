@extends('layouts.app')
@section('title', 'Edit PDS')

@section('content')
@php
    $isPersonnelEditor = Auth::user()?->hasRole('personnel') ?? false;

    $mainSections = [
        'I. Personal Information' => [
            ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text'],
            ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text'],
            ['name' => 'middle_name', 'label' => 'Middle Name', 'type' => 'text'],
            ['name' => 'extension_name', 'label' => 'Extension Name', 'type' => 'text'],
            ['name' => 'birth_date', 'label' => 'Birth Date', 'type' => 'date'],
            ['name' => 'birth_place', 'label' => 'Birth Place', 'type' => 'text'],
            ['name' => 'birth_sex', 'label' => 'Sex', 'type' => 'text'],
            ['name' => 'civil_status', 'label' => 'Civil Status', 'type' => 'text'],
            ['name' => 'height', 'label' => 'Height', 'type' => 'number'],
            ['name' => 'weight', 'label' => 'Weight', 'type' => 'number'],
            ['name' => 'blood_type', 'label' => 'Blood Type', 'type' => 'text'],
            ['name' => 'umid_id_number', 'label' => 'GSIS/UMID Number', 'type' => 'text'],
            ['name' => 'pagibig_number', 'label' => 'Pag-IBIG Number', 'type' => 'text'],
            ['name' => 'philhealth_number', 'label' => 'PhilHealth Number', 'type' => 'text'],
            ['name' => 'sss_number', 'label' => 'SSS Number', 'type' => 'text'],
            ['name' => 'philsys_number', 'label' => 'PhilSys Number', 'type' => 'text'],
            ['name' => 'tin_number', 'label' => 'TIN Number', 'type' => 'text'],
            ['name' => 'agency_employee_number', 'label' => 'Agency Employee Number', 'type' => 'text'],
            ['name' => 'citizenship_type', 'label' => 'Citizenship Type', 'type' => 'text'],
            ['name' => 'citizenship_mode', 'label' => 'Citizenship Mode', 'type' => 'text'],
            ['name' => 'dual_citizenship_country', 'label' => 'Dual Citizenship Country', 'type' => 'text'],
            ['name' => 'dual_citizenship_details', 'label' => 'Dual Citizenship Details', 'type' => 'textarea'],
            ['name' => 'res_house_lot', 'label' => 'Residential House/Lot', 'type' => 'text'],
            ['name' => 'res_street', 'label' => 'Residential Street', 'type' => 'text'],
            ['name' => 'res_subdivision', 'label' => 'Residential Subdivision', 'type' => 'text'],
            ['name' => 'res_barangay', 'label' => 'Residential Barangay', 'type' => 'text'],
            ['name' => 'res_city', 'label' => 'Residential City/Municipality', 'type' => 'text'],
            ['name' => 'res_province', 'label' => 'Residential Province', 'type' => 'text'],
            ['name' => 'res_zipcode', 'label' => 'Residential ZIP Code', 'type' => 'text'],
            ['name' => 'perm_house_lot', 'label' => 'Permanent House/Lot', 'type' => 'text'],
            ['name' => 'perm_street', 'label' => 'Permanent Street', 'type' => 'text'],
            ['name' => 'perm_subdivision', 'label' => 'Permanent Subdivision', 'type' => 'text'],
            ['name' => 'perm_barangay', 'label' => 'Permanent Barangay', 'type' => 'text'],
            ['name' => 'perm_city', 'label' => 'Permanent City/Municipality', 'type' => 'text'],
            ['name' => 'perm_province', 'label' => 'Permanent Province', 'type' => 'text'],
            ['name' => 'perm_zipcode', 'label' => 'Permanent ZIP Code', 'type' => 'text'],
            ['name' => 'residential_address', 'label' => 'Current Residential Address', 'type' => 'textarea'],
            ['name' => 'telephone', 'label' => 'Telephone Number', 'type' => 'text'],
            ['name' => 'mobile', 'label' => 'Mobile Number', 'type' => 'text'],
            ['name' => 'email_address', 'label' => 'Email Address', 'type' => 'email'],
        ],
        'II. Family Background' => [
            ['name' => 'spouse_last_name', 'label' => 'Spouse Last Name', 'type' => 'text'],
            ['name' => 'spouse_first_name', 'label' => 'Spouse First Name', 'type' => 'text'],
            ['name' => 'spouse_middle_name', 'label' => 'Spouse Middle Name', 'type' => 'text'],
            ['name' => 'spouse_extension_name', 'label' => 'Spouse Extension Name', 'type' => 'text'],
            ['name' => 'spouse_occupation', 'label' => 'Spouse Occupation', 'type' => 'text'],
            ['name' => 'spouse_employer', 'label' => 'Spouse Employer/Business Name', 'type' => 'text'],
            ['name' => 'employer_address', 'label' => 'Employer/Business Address', 'type' => 'textarea'],
            ['name' => 'spouse_telephone', 'label' => 'Spouse Telephone', 'type' => 'text'],
            ['name' => 'father_last_name', 'label' => 'Father Last Name', 'type' => 'text'],
            ['name' => 'father_first_name', 'label' => 'Father First Name', 'type' => 'text'],
            ['name' => 'father_middle_name', 'label' => 'Father Middle Name', 'type' => 'text'],
            ['name' => 'father_extension_name', 'label' => 'Father Extension Name', 'type' => 'text'],
            ['name' => 'mother_last_name', 'label' => 'Mother Last Name', 'type' => 'text'],
            ['name' => 'mother_first_name', 'label' => 'Mother First Name', 'type' => 'text'],
            ['name' => 'mother_middle_name', 'label' => 'Mother Middle Name', 'type' => 'text'],
        ],
    ];

    $questionsGroups = [
        'RELATIONSHIP TO AUTHORITY' => [
            [ 'name' => 'related_third_degree', 'label' => 'Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you in the Office, Bureau or Department where you will be appointed, within the third degree?' ],
            [ 'name' => 'related_fourth_degree', 'label' => 'Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you in the Office, Bureau or Department where you will be appointed, within the fourth degree (for Local Government Unit - Career Employees)?' ],
            [ 'name' => 'related_fourth_degree_details', 'label' => 'Related within Fourth Degree Details (If YES):' ],
        ],
        'ADMINISTRATIVE/CRIMINAL CASES' => [
            [ 'name' => 'admin_offense', 'label' => 'Have you ever been found guilty of any administrative offense?' ],
            [ 'name' => 'admin_offense_details', 'label' => 'Administrative Offense Details (If YES):' ],
            [ 'name' => 'criminal_case', 'label' => 'Have you been criminally charged before any court?' ],
            [ 'name' => 'criminal_case_date', 'label' => 'Date Filed (for criminal case):' ],
            [ 'name' => 'criminal_case_status', 'label' => 'Status of Case/s (for criminal case):' ],
        ],
        'CONVICTION/SEPARATION' => [
            [ 'name' => 'convicted', 'label' => 'Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?' ],
            [ 'name' => 'convicted_details', 'label' => 'Conviction Details (If YES):' ],
            [ 'name' => 'separated_service', 'label' => 'Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?' ],
            [ 'name' => 'separated_service_details', 'label' => 'Separation from Service Details (If YES):' ],
        ],
        'ELECTION-RELATED' => [
            [ 'name' => 'election_candidate', 'label' => 'Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?' ],
            [ 'name' => 'election_candidate_details', 'label' => 'Election Candidate Details (If YES):' ],
            [ 'name' => 'election_resigned', 'label' => 'Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?' ],
            [ 'name' => 'election_resigned_details', 'label' => 'Election Resigned Details (If YES):' ],
        ],
        'RESIDENCY/STATUS' => [
            [ 'name' => 'immigrant', 'label' => 'Have you acquired the status of an immigrant or permanent resident of another country?' ],
            [ 'name' => 'immigrant_details', 'label' => 'Immigrant/Permanent Resident Details (Country) (If YES):' ],
        ],
        'SPECIAL GROUPS' => [
            [ 'name' => 'indigenous', 'label' => 'Are you a member of any indigenous group?' ],
            [ 'name' => 'indigenous_details', 'label' => 'Indigenous Group Details (If YES):' ],
            [ 'name' => 'pwd', 'label' => 'Are you a person with disability?' ],
            [ 'name' => 'pwd_details', 'label' => 'PWD ID Details (If YES):' ],
            [ 'name' => 'solo_parent', 'label' => 'Are you a solo parent?' ],
            [ 'name' => 'solo_parent_details', 'label' => 'Solo Parent ID Details (If YES):' ],
        ],
    ];

    $governmentIdFields = [
        ['name' => 'issued_id', 'label' => 'Government Issued ID', 'type' => 'text'],
        ['name' => 'id_number', 'label' => 'ID Number', 'type' => 'text'],
        ['name' => 'issue_date', 'label' => 'Date of Issuance', 'type' => 'date'],
        ['name' => 'issue_place', 'label' => 'Place of Issuance', 'type' => 'text'],
    ];

    $valueFromMain = function ($field) use ($pds) {
        return old($field, optional($pds)->{$field} ?? '');
    };
@endphp

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-user-edit mr-2 text-primary"></i> Edit PDS</h3>
            <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Profile
            </a>
        </div>

        <div class="card-body">
            @if($isPersonnelEditor && $pendingSubmission)
                <div class="alert alert-warning">
                    You currently have a pending PDS request (#{{ $pendingSubmission->id }}). Submitting again will create a new request.
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('personnel.pds.edit.store', $personnel) }}" method="POST">
                @csrf

                @foreach($mainSections as $sectionTitle => $fields)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">{{ $sectionTitle }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($fields as $field)
                                    @php
                                        $type = $field['type'] ?? 'text';
                                        $name = $field['name'];
                                        $value = $valueFromMain($name);
                                    @endphp
                                    <div class="{{ $type === 'textarea' ? 'col-md-12' : 'col-md-4' }} mb-3">
                                        <label class="form-control-label">{{ $field['label'] }}</label>
                                        @if($type === 'textarea')
                                            <textarea name="{{ $name }}" class="form-control" rows="2">{{ $value }}</textarea>
                                        @elseif($type === 'boolean')
                                            <select name="{{ $name }}" class="form-control">
                                                <option value="" {{ $value === '' || $value === null ? 'selected' : '' }}>-</option>
                                                <option value="1" {{ (string) $value === '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ (string) $value === '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        @else
                                            <input type="{{ $type }}" name="{{ $name }}" class="form-control" value="{{ $value }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($sectionTitle === 'II. Family Background')
                        @include('personnel.partials.pds_repeater_edit', [
                            'key' => 'children',
                            'title' => 'Children',
                            'fields' => [
                                ['name' => 'child_name', 'label' => 'Name', 'type' => 'text'],
                                ['name' => 'birth_date', 'label' => 'Birth Date', 'type' => 'date'],
                            ],
                            'rows' => $currentRows['children'] ?? [],
                        ])
                    @endif
                @endforeach

                @include('personnel.partials.pds_repeater_edit', [
                    'key' => 'education',
                    'title' => 'III. Educational Background',
                    'fields' => [
                        ['name' => 'level', 'label' => 'Level', 'type' => 'text'],
                        ['name' => 'school_name', 'label' => 'School Name', 'type' => 'text'],
                        ['name' => 'degree', 'label' => 'Degree', 'type' => 'text'],
                        ['name' => 'from_year', 'label' => 'From Year', 'type' => 'number'],
                        ['name' => 'to_year', 'label' => 'To Year', 'type' => 'number'],
                        ['name' => 'highest_level_units', 'label' => 'Highest Level / Units', 'type' => 'text'],
                        ['name' => 'year_graduated', 'label' => 'Year Graduated', 'type' => 'number'],
                        ['name' => 'honors', 'label' => 'Honors', 'type' => 'text'],
                    ],
                    'rows' => $currentRows['education'] ?? [],
                ])

                @include('personnel.partials.pds_repeater_edit', [
                    'key' => 'eligibility',
                    'title' => 'IV. Civil Service Eligibility',
                    'fields' => [
                        ['name' => 'eligibility', 'label' => 'Eligibility', 'type' => 'text'],
                        ['name' => 'rating', 'label' => 'Rating', 'type' => 'text'],
                        ['name' => 'exam_date', 'label' => 'Exam Date', 'type' => 'date'],
                        ['name' => 'exam_place', 'label' => 'Exam Place', 'type' => 'text'],
                        ['name' => 'license_number', 'label' => 'License Number', 'type' => 'text'],
                        ['name' => 'license_valid_until', 'label' => 'License Valid Until', 'type' => 'date'],
                    ],
                    'rows' => $currentRows['eligibility'] ?? [],
                ])

                @include('personnel.partials.pds_repeater_edit', [
                    'key' => 'work_experience',
                    'title' => 'V. Work Experience',
                    'fields' => [
                        ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                        ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                        ['name' => 'position', 'label' => 'Position', 'type' => 'text'],
                        ['name' => 'company', 'label' => 'Company', 'type' => 'text'],
                        ['name' => 'appointment_status', 'label' => 'Appointment Status', 'type' => 'text'],
                        ['name' => 'is_government', 'label' => 'Government Service', 'type' => 'boolean'],
                    ],
                    'rows' => $currentRows['work_experience'] ?? [],
                ])

                @include('personnel.partials.pds_repeater_edit', [
                    'key' => 'voluntary_work',
                    'title' => 'VI. Voluntary Work or Involvement in Civic / Non-Government / People / Voluntary Organization/s',
                    'fields' => [
                        ['name' => 'organization_name', 'label' => 'Organization Name', 'type' => 'text'],
                        ['name' => 'organization_address', 'label' => 'Organization Address', 'type' => 'text'],
                        ['name' => 'from_date', 'label' => 'From Date', 'type' => 'date'],
                        ['name' => 'to_date', 'label' => 'To Date', 'type' => 'date'],
                        ['name' => 'number_of_hours', 'label' => 'Number of Hours', 'type' => 'number'],
                        ['name' => 'position', 'label' => 'Position / Nature of Work', 'type' => 'text'],
                    ],
                    'rows' => $currentRows['voluntary_work'] ?? [],
                ])

                <div class="alert alert-info">
                    Training records are managed in the separate Training tab and are not part of this PDS edit workflow.
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">VIII. Other Information (Skills, Distinctions, Memberships)</h5>
                    </div>
                    <div class="card-body">
                        @include('personnel.partials.pds_repeater_edit', [
                            'key' => 'skills',
                            'title' => 'Skills',
                            'fields' => [
                                ['name' => 'skill', 'label' => 'Skill', 'type' => 'text'],
                            ],
                            'rows' => $currentRows['skills'] ?? [],
                        ])

                        @include('personnel.partials.pds_repeater_edit', [
                            'key' => 'distinctions',
                            'title' => 'Distinctions',
                            'fields' => [
                                ['name' => 'distinction', 'label' => 'Distinction', 'type' => 'text'],
                            ],
                            'rows' => $currentRows['distinctions'] ?? [],
                        ])

                        @include('personnel.partials.pds_repeater_edit', [
                            'key' => 'memberships',
                            'title' => 'Memberships',
                            'fields' => [
                                ['name' => 'membership', 'label' => 'Membership', 'type' => 'text'],
                            ],
                            'rows' => $currentRows['memberships'] ?? [],
                        ])
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Questions</h5></div>
                    <div class="card-body">
                        @foreach($questionsGroups as $groupTitle => $fields)
                            <div class="mb-2 mt-3"><strong>{{ $groupTitle }}</strong></div>
                            <div class="row">
                                @foreach($fields as $field)
                                    @php
                                        $type = 'text';
                                        // Map to type if needed (for now, use textarea for *_details, boolean for yes/no, else text)
                                        if(Str::endsWith($field['name'], '_details')) {
                                            $type = 'textarea';
                                        } elseif(in_array($field['name'], [
                                            'related_third_degree', 'related_fourth_degree', 'admin_offense', 'criminal_case', 'convicted',
                                            'separated_service', 'election_candidate', 'election_resigned', 'immigrant', 'indigenous', 'pwd', 'solo_parent'
                                        ])) {
                                            $type = 'boolean';
                                        }
                                        $name = $field['name'];
                                        $value = $valueFromMain($name);
                                    @endphp
                                    <div class="{{ $type === 'textarea' ? 'col-md-12' : 'col-md-4' }} mb-3">
                                        <label class="form-control-label">{{ $field['label'] }}</label>
                                        @if($type === 'textarea')
                                            <textarea name="{{ $name }}" class="form-control" rows="2">{{ $value }}</textarea>
                                        @elseif($type === 'boolean')
                                            <select name="{{ $name }}" class="form-control">
                                                <option value="" {{ $value === '' || $value === null ? 'selected' : '' }}>-</option>
                                                <option value="1" {{ (string) $value === '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ (string) $value === '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        @else
                                            <input type="text" name="{{ $name }}" class="form-control" value="{{ $value }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                @include('personnel.partials.pds_repeater_edit', [
                    'key' => 'references',
                    'title' => 'References',
                    'fields' => [
                        ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                        ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                        ['name' => 'contact', 'label' => 'Contact', 'type' => 'text'],
                    ],
                    'rows' => $currentRows['references'] ?? [],
                ])

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Government ID Issuance</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($governmentIdFields as $field)
                                @php
                                    $type = $field['type'] ?? 'text';
                                    $name = $field['name'];
                                    $value = $valueFromMain($name);
                                @endphp
                                <div class="{{ $type === 'textarea' ? 'col-md-12' : 'col-md-4' }} mb-3">
                                    <label class="form-control-label">{{ $field['label'] }}</label>
                                    <input type="{{ $type }}" name="{{ $name }}" class="form-control" value="{{ $value }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i> {{ $isPersonnelEditor ? 'Submit for Approval' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addPdsRow(key) {
    const template = document.getElementById('tpl-' + key);
    const container = document.getElementById('rows-' + key);
    if (!template || !container) {
        return;
    }

    const index = container.querySelectorAll('.pds-row').length;
    const html = template.innerHTML.replace(/__INDEX__/g, String(index));
    container.insertAdjacentHTML('beforeend', html);
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-remove-row]');
    if (!btn) {
        return;
    }

    const row = btn.closest('.pds-row');
    if (row) {
        row.remove();
    }
});
</script>
@endsection
