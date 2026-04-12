@extends('layouts.app')
@section('title', 'PDS Edit Request Details')

@section('content')
@php
    $isReviewer = Auth::user() && Auth::user()->hasAnyRole(['admin', 'school']);
    $isPending = $submission->status === 'SUBMITTED';
    $canDeleteByPersonnel = Auth::user()?->hasRole('personnel')
        && (int) Auth::id() === (int) ($submission->submitted_by ?? 0)
        && (int) (Auth::user()?->personnel_id ?? 0) === (int) $submission->personnel_id
        && $isPending;
    $pds = $submission->pdsMainSnapshot;
    $changedMainFields = $changedMainFields ?? [];
    $changedSections = $changedSections ?? [];
    $isChangedSection = fn (string $key) => in_array($key, $changedSections, true);
    $isChangedMainField = fn (string $key) => in_array($key, $changedMainFields, true);

    $mainSections = [
        'I. Personal Information' => [
            ['name' => 'last_name', 'label' => 'Last Name'],
            ['name' => 'first_name', 'label' => 'First Name'],
            ['name' => 'middle_name', 'label' => 'Middle Name'],
            ['name' => 'extension_name', 'label' => 'Extension Name'],
            ['name' => 'birth_date', 'label' => 'Birth Date'],
            ['name' => 'birth_place', 'label' => 'Birth Place'],
            ['name' => 'birth_sex', 'label' => 'Sex'],
            ['name' => 'civil_status', 'label' => 'Civil Status'],
            ['name' => 'height', 'label' => 'Height'],
            ['name' => 'weight', 'label' => 'Weight'],
            ['name' => 'blood_type', 'label' => 'Blood Type'],
            ['name' => 'umid_id_number', 'label' => 'GSIS/UMID Number'],
            ['name' => 'pagibig_number', 'label' => 'Pag-IBIG Number'],
            ['name' => 'philhealth_number', 'label' => 'PhilHealth Number'],
            ['name' => 'sss_number', 'label' => 'SSS Number'],
            ['name' => 'philsys_number', 'label' => 'PhilSys Number'],
            ['name' => 'tin_number', 'label' => 'TIN Number'],
            ['name' => 'agency_employee_number', 'label' => 'Agency Employee Number'],
            ['name' => 'citizenship_type', 'label' => 'Citizenship Type'],
            ['name' => 'citizenship_mode', 'label' => 'Citizenship Mode'],
            ['name' => 'dual_citizenship_country', 'label' => 'Dual Citizenship Country'],
            ['name' => 'dual_citizenship_details', 'label' => 'Dual Citizenship Details'],
            ['name' => 'res_house_lot', 'label' => 'Residential House/Lot'],
            ['name' => 'res_street', 'label' => 'Residential Street'],
            ['name' => 'res_subdivision', 'label' => 'Residential Subdivision'],
            ['name' => 'res_barangay', 'label' => 'Residential Barangay'],
            ['name' => 'res_city', 'label' => 'Residential City/Municipality'],
            ['name' => 'res_province', 'label' => 'Residential Province'],
            ['name' => 'res_zipcode', 'label' => 'Residential ZIP Code'],
            ['name' => 'perm_house_lot', 'label' => 'Permanent House/Lot'],
            ['name' => 'perm_street', 'label' => 'Permanent Street'],
            ['name' => 'perm_subdivision', 'label' => 'Permanent Subdivision'],
            ['name' => 'perm_barangay', 'label' => 'Permanent Barangay'],
            ['name' => 'perm_city', 'label' => 'Permanent City/Municipality'],
            ['name' => 'perm_province', 'label' => 'Permanent Province'],
            ['name' => 'perm_zipcode', 'label' => 'Permanent ZIP Code'],
            ['name' => 'residential_address', 'label' => 'Current Residential Address'],
            ['name' => 'telephone', 'label' => 'Telephone Number'],
            ['name' => 'mobile', 'label' => 'Mobile Number'],
            ['name' => 'email_address', 'label' => 'Email Address'],
        ],
        'II. Family Background' => [
            ['name' => 'spouse_last_name', 'label' => 'Spouse Last Name'],
            ['name' => 'spouse_first_name', 'label' => 'Spouse First Name'],
            ['name' => 'spouse_middle_name', 'label' => 'Spouse Middle Name'],
            ['name' => 'spouse_extension_name', 'label' => 'Spouse Extension Name'],
            ['name' => 'spouse_occupation', 'label' => 'Spouse Occupation'],
            ['name' => 'spouse_employer', 'label' => 'Spouse Employer/Business Name'],
            ['name' => 'employer_address', 'label' => 'Employer/Business Address'],
            ['name' => 'spouse_telephone', 'label' => 'Spouse Telephone'],
            ['name' => 'father_last_name', 'label' => 'Father Last Name'],
            ['name' => 'father_first_name', 'label' => 'Father First Name'],
            ['name' => 'father_middle_name', 'label' => 'Father Middle Name'],
            ['name' => 'father_extension_name', 'label' => 'Father Extension Name'],
            ['name' => 'mother_last_name', 'label' => 'Mother Last Name'],
            ['name' => 'mother_first_name', 'label' => 'Mother First Name'],
            ['name' => 'mother_middle_name', 'label' => 'Mother Middle Name'],
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
        ['name' => 'issued_id', 'label' => 'Government Issued ID'],
        ['name' => 'id_number', 'label' => 'ID Number'],
        ['name' => 'issue_date', 'label' => 'Date of Issuance'],
        ['name' => 'issue_place', 'label' => 'Place of Issuance'],
    ];

    $toRows = function ($collection, $fields) {
        $names = collect($fields)->pluck('name')->all();
        return $collection->map(function ($item) use ($names) {
            return \Illuminate\Support\Arr::only($item->toArray(), $names);
        })->values()->all();
    };

    $hasAnyChanges = !empty($changedMainFields) || !empty($changedSections);
@endphp

<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-file-signature mr-2 text-primary"></i> PDS Edit Request #{{ $submission->id }}</h3>
            <div class="d-flex align-items-center">
                @if($canDeleteByPersonnel)
                    <form action="{{ route('pds.requests.destroy', $submission) }}" method="POST" class="mr-2" onsubmit="return confirm('Delete this submitted PDS request? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash mr-1"></i> Delete Request
                        </button>
                    </form>
                @endif
                <a href="{{ route('pds.requests.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Requests
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4"><strong>Status:</strong> {{ $submission->status }}</div>
                <div class="col-md-4"><strong>Submitted By:</strong> {{ $submission->submitter->username ?? '--' }}</div>
                <div class="col-md-4"><strong>Submitted At:</strong> {{ optional($submission->submitted_at)->format('Y-m-d H:i') }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Reviewed By:</strong> {{ $submission->reviewer->username ?? '--' }}</div>
                <div class="col-md-6"><strong>Review Remarks:</strong> {{ $submission->review_remarks ?: '--' }}</div>
            </div>

            @if(!$hasAnyChanges)
                <div class="alert alert-secondary">No changed fields were captured for this request.</div>
            @endif

            @foreach($mainSections as $sectionTitle => $fields)
                @php
                    $filteredFields = collect($fields)->filter(function ($field) use ($isChangedMainField) {
                        return $isChangedMainField($field['name']);
                    })->values()->all();
                @endphp
                @if(empty($filteredFields))
                    @continue
                @endif
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ $sectionTitle }}</h5></div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($filteredFields as $field)
                                @php
                                    $value = optional($pds)->{$field['name']} ?? null;
                                    $isBool = in_array($field['name'], [
                                        'related_third_degree', 'related_fourth_degree', 'admin_offense', 'criminal_case',
                                        'convicted', 'separated_service', 'election_candidate', 'election_resigned',
                                        'immigrant', 'indigenous', 'pwd', 'solo_parent'
                                    ], true);
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <label class="form-control-label text-muted">{{ $field['label'] }}</label>
                                    <div class="font-weight-bold">
                                        @if($isBool)
                                            @if($value === null || $value === '')
                                                --
                                            @else
                                                {{ (string) $value === '1' ? 'Yes' : 'No' }}
                                            @endif
                                        @else
                                            {{ $value !== null && $value !== '' ? $value : '--' }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($sectionTitle === 'II. Family Background' && $isChangedSection('children'))
                    @include('personnel.partials.pds_repeater_readonly', [
                        'title' => 'Children',
                        'fields' => [
                            ['name' => 'child_name', 'label' => 'Name', 'type' => 'text'],
                            ['name' => 'birth_date', 'label' => 'Birth Date', 'type' => 'date'],
                        ],
                        'rows' => $toRows($submission->childrenSnapshots, [
                            ['name' => 'child_name'],
                            ['name' => 'birth_date'],
                        ]),
                    ])
                @endif
            @endforeach

            @if($isChangedSection('education'))
            @include('personnel.partials.pds_repeater_readonly', [
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
                'rows' => $toRows($submission->educationSnapshots, [
                    ['name' => 'level'], ['name' => 'school_name'], ['name' => 'degree'], ['name' => 'from_year'], ['name' => 'to_year'],
                    ['name' => 'highest_level_units'], ['name' => 'year_graduated'], ['name' => 'honors'],
                ]),
            ])
            @endif

            @if($isChangedSection('eligibility'))
            @include('personnel.partials.pds_repeater_readonly', [
                'title' => 'IV. Civil Service Eligibility',
                'fields' => [
                    ['name' => 'eligibility', 'label' => 'Eligibility', 'type' => 'text'],
                    ['name' => 'rating', 'label' => 'Rating', 'type' => 'text'],
                    ['name' => 'exam_date', 'label' => 'Exam Date', 'type' => 'date'],
                    ['name' => 'exam_place', 'label' => 'Exam Place', 'type' => 'text'],
                    ['name' => 'license_number', 'label' => 'License Number', 'type' => 'text'],
                    ['name' => 'license_valid_until', 'label' => 'License Valid Until', 'type' => 'date'],
                ],
                'rows' => $toRows($submission->eligibilitySnapshots, [
                    ['name' => 'eligibility'], ['name' => 'rating'], ['name' => 'exam_date'], ['name' => 'exam_place'], ['name' => 'license_number'], ['name' => 'license_valid_until'],
                ]),
            ])
            @endif

            @if($isChangedSection('work_experience'))
            @include('personnel.partials.pds_repeater_readonly', [
                'title' => 'V. Work Experience',
                'fields' => [
                    ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                    ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                    ['name' => 'position', 'label' => 'Position', 'type' => 'text'],
                    ['name' => 'company', 'label' => 'Company', 'type' => 'text'],
                    ['name' => 'appointment_status', 'label' => 'Appointment Status', 'type' => 'text'],
                    ['name' => 'is_government', 'label' => 'Government Service', 'type' => 'boolean'],
                ],
                'rows' => $toRows($submission->workExperienceSnapshots, [
                    ['name' => 'start_date'], ['name' => 'end_date'], ['name' => 'position'], ['name' => 'company'], ['name' => 'appointment_status'], ['name' => 'is_government'],
                ]),
            ])
            @endif

            @if($isChangedSection('voluntary_work'))
            @include('personnel.partials.pds_repeater_readonly', [
                'title' => 'VI. Voluntary Work or Involvement in Civic / Non-Government / People / Voluntary Organization/s',
                'fields' => [
                    ['name' => 'organization_name', 'label' => 'Organization Name', 'type' => 'text'],
                    ['name' => 'organization_address', 'label' => 'Organization Address', 'type' => 'text'],
                    ['name' => 'from_date', 'label' => 'From Date', 'type' => 'date'],
                    ['name' => 'to_date', 'label' => 'To Date', 'type' => 'date'],
                    ['name' => 'number_of_hours', 'label' => 'Number of Hours', 'type' => 'number'],
                    ['name' => 'position', 'label' => 'Position / Nature of Work', 'type' => 'text'],
                ],
                'rows' => $toRows($submission->voluntaryWorkSnapshots, [
                    ['name' => 'organization_name'], ['name' => 'organization_address'], ['name' => 'from_date'], ['name' => 'to_date'], ['name' => 'number_of_hours'], ['name' => 'position'],
                ]),
            ])
            @endif

            @if($isChangedSection('skills') || $isChangedSection('distinctions') || $isChangedSection('memberships'))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">VIII. Other Information (Skills, Distinctions, Memberships)</h5></div>
                <div class="card-body">
                    @if($isChangedSection('skills'))
                    @include('personnel.partials.pds_repeater_readonly', [
                        'title' => 'Skills',
                        'fields' => [
                            ['name' => 'skill', 'label' => 'Skill', 'type' => 'text'],
                        ],
                        'rows' => $toRows($submission->skillsSnapshots, [
                            ['name' => 'skill'],
                        ]),
                    ])
                    @endif

                    @if($isChangedSection('distinctions'))
                    @include('personnel.partials.pds_repeater_readonly', [
                        'title' => 'Distinctions',
                        'fields' => [
                            ['name' => 'distinction', 'label' => 'Distinction', 'type' => 'text'],
                        ],
                        'rows' => $toRows($submission->distinctionsSnapshots, [
                            ['name' => 'distinction'],
                        ]),
                    ])
                    @endif

                    @if($isChangedSection('memberships'))
                    @include('personnel.partials.pds_repeater_readonly', [
                        'title' => 'Memberships',
                        'fields' => [
                            ['name' => 'membership', 'label' => 'Membership', 'type' => 'text'],
                        ],
                        'rows' => $toRows($submission->membershipsSnapshots, [
                            ['name' => 'membership'],
                        ]),
                    ])
                    @endif
                </div>
            </div>
            @endif

            @php
                $anyQuestionsChanged = collect($questionsGroups)
                    ->flatten(1)
                    ->contains(fn ($field) => $isChangedMainField($field['name']));
            @endphp
            @if($anyQuestionsChanged)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Questions</h5></div>
                <div class="card-body">
                    @foreach($questionsGroups as $groupTitle => $fields)
                        @php
                            $groupChanged = collect($fields)->contains(fn ($field) => $isChangedMainField($field['name']));
                        @endphp
                        @if($groupChanged)
                            <div class="mb-2 mt-3"><strong>{{ $groupTitle }}</strong></div>
                            <div class="row">
                                @foreach($fields as $field)
                                    @continue(!$isChangedMainField($field['name']))
                                    @php
                                        $value = optional($pds)->{$field['name']} ?? null;
                                        $isBool = in_array($field['name'], [
                                            'related_third_degree', 'related_fourth_degree', 'admin_offense', 'criminal_case',
                                            'convicted', 'separated_service', 'election_candidate', 'election_resigned',
                                            'immigrant', 'indigenous', 'pwd', 'solo_parent'
                                        ], true);
                                    @endphp
                                    <div class="col-md-4 mb-3">
                                        <label class="form-control-label text-muted">{{ $field['label'] }}</label>
                                        <div class="font-weight-bold">
                                            @if($isBool)
                                                @if($value === null || $value === '')
                                                    --
                                                @else
                                                    {{ (string) $value === '1' ? 'Yes' : 'No' }}
                                                @endif
                                            @else
                                                {{ $value !== null && $value !== '' ? $value : '--' }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if($isChangedSection('references'))
            @include('personnel.partials.pds_repeater_readonly', [
                'title' => 'References',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                    ['name' => 'contact', 'label' => 'Contact', 'type' => 'text'],
                ],
                'rows' => $toRows($submission->referencesSnapshots, [
                    ['name' => 'name'], ['name' => 'address'], ['name' => 'contact'],
                ]),
            ])
            @endif

            @if(collect($governmentIdFields)->contains(fn ($field) => $isChangedMainField($field['name'])))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Government ID Issuance</h5></div>
                <div class="card-body">
                    <div class="row">
                        @foreach($governmentIdFields as $field)
                            @continue(!$isChangedMainField($field['name']))
                            @php
                                $value = optional($pds)->{$field['name']} ?? null;
                            @endphp
                            <div class="col-md-4 mb-3">
                                <label class="form-control-label text-muted">{{ $field['label'] }}</label>
                                <div class="font-weight-bold">{{ $value !== null && $value !== '' ? $value : '--' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($isReviewer && $isPending)
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Review Decision</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('pds.requests.approve', $submission) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label>Remarks (Optional)</label>
                                        <textarea name="review_remarks" class="form-control" rows="3" placeholder="Approval notes (optional)"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check mr-1"></i> Approve and Apply Changes
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('pds.requests.reject', $submission) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label>Reason / Remarks (Optional)</label>
                                        <textarea name="review_remarks" class="form-control" rows="3" placeholder="Reason for rejection (optional)"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times mr-1"></i> Reject Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
