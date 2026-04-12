@extends('layouts.app')
@section('title', 'Personnel Profile')

@section('content')
@php
    $user = auth()->user();
    $pds = $personnel->pdsMain;
    $lastName = $pds->last_name ?? '--';
    $firstName = $pds->first_name ?? '';
    $gender = $pds?->birth_sex ? ucfirst(strtolower($pds->birth_sex)) : '--';
    $civilStatus = $pds?->civil_status ? ucfirst(strtolower($pds->civil_status)) : null;
    $canManageServiceRecords = auth()->user()?->hasAnyRole(['admin', 'school']);
    $canExportServiceRecords = auth()->user()?->hasAnyRole(['admin', 'school', 'encoding_officer', 'personnel']);
    $showBackToList = !($user && $user->hasRole('personnel'));
    $canEditPds = $user && (
        $user->hasAnyRole(['admin', 'school']) ||
        ($user->hasRole('personnel') && (int) $user->personnel_id === (int) $personnel->id)
    );
@endphp
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-4">
            <div class="card card-profile shadow mb-4">
                <div class="card-body pt-5 text-center">
                    <div class="mb-4">
                        <img src="{{ $personnel->profile_photo ? asset('storage/' . $personnel->profile_photo) : asset('assets/img/brand/boss.jpg') }}" class="rounded-circle border shadow-sm" width="140" style="height: 140px; object-fit: cover;">
                    </div>
                    <h2 class="mb-0 text-dark">{{ $lastName }}, {{ $firstName }}</h2>
                    <p class="text-muted mb-3">{{ $personnel->position->title ?? '--' }}</p>
                    
                    <div class="badge badge-pill badge-primary mb-4 px-4 py-2">
                        {{ $personnel->employee_type ?? '--' }}
                    </div>

                    <div class="row text-left mt-2">
                        <div class="col-12 mb-2">
                            <small class="text-uppercase text-muted font-weight-bold">Station</small>
                            <div class="h5">{{ $personnel->school->name ?? 'Unassigned' }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-uppercase text-muted font-weight-bold">Gender</small>
                            <div class="h5">{{ $gender }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-uppercase text-muted font-weight-bold">Civil Status</small>
                            <div class="h5">{{ $civilStatus ?? '---' }}</div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        @if($showBackToList)
                            <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
                        @endif
                        <a href="{{ route('personnel.pds.export', $personnel->id) }}" class="btn btn-sm btn-primary">Export PDS PDF</a>
                        @if($personnel->is_active)
                            <span class="text-success font-weight-bold"><i class="fas fa-circle mr-1"></i> Active</span>
                        @else
                            <span class="text-danger font-weight-bold"><i class="fas fa-circle mr-1"></i> Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-tabs nav-fill" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-3" data-toggle="tab" href="#pds">
                                <i class="ni ni-single-02 mr-2"></i> PDS Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-toggle="tab" href="#inventory">
                                <i class="ni ni-archive-2 mr-2"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-toggle="tab" href="#service-records">
                                <i class="ni ni-badge mr-2"></i> Service Records
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-toggle="tab" href="#history">
                                <i class="ni ni-hat-3 mr-2"></i> History
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body" style="min-height: 500px;">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pds">
                            @php
                                $sectionMain = [
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
                            @endphp

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">PDS Information</h4>
                                @if($canEditPds)
                                    <a href="{{ route('personnel.pds.edit', $personnel) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                @endif
                            </div>

                            @foreach($sectionMain as $sectionTitle => $fields)
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-white"><h5 class="mb-0">{{ $sectionTitle }}</h5></div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($fields as $field)
                                                @php
                                                    $boolFields = [
                                                        'related_third_degree', 'related_fourth_degree', 'admin_offense', 'criminal_case', 'convicted',
                                                        'separated_service', 'election_candidate', 'election_resigned', 'immigrant', 'indigenous', 'pwd', 'solo_parent'
                                                    ];
                                                    $value = optional($pds)->{$field['name']} ?? null;
                                                @endphp
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-control-label text-muted">{{ $field['label'] }}</label>
                                                    <div class="font-weight-bold">
                                                        @if(in_array($field['name'], $boolFields, true))
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

                                @if($sectionTitle === 'II. Family Background')
                                    @include('personnel.partials.pds_repeater_readonly', [
                                        'title' => 'Children',
                                        'fields' => [
                                            ['name' => 'child_name', 'label' => 'Name', 'type' => 'text'],
                                            ['name' => 'birth_date', 'label' => 'Birth Date', 'type' => 'date'],
                                        ],
                                        'rows' => $personnel->pdsChildren->map(function ($row) {
                                            return [
                                                'child_name' => $row->child_name,
                                                'birth_date' => $row->birth_date,
                                            ];
                                        })->values()->all(),
                                    ])
                                @endif
                            @endforeach

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
                                'rows' => $personnel->pdsEducation->map(function ($row) {
                                    return [
                                        'level' => $row->level,
                                        'school_name' => $row->school_name,
                                        'degree' => $row->degree,
                                        'from_year' => $row->from_year,
                                        'to_year' => $row->to_year,
                                        'highest_level_units' => $row->highest_level_units,
                                        'year_graduated' => $row->year_graduated,
                                        'honors' => $row->honors,
                                    ];
                                })->values()->all(),
                            ])

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
                                'rows' => $personnel->pdsEligibility->map(function ($row) {
                                    return [
                                        'eligibility' => $row->eligibility,
                                        'rating' => $row->rating,
                                        'exam_date' => $row->exam_date,
                                        'exam_place' => $row->exam_place,
                                        'license_number' => $row->license_number,
                                        'license_valid_until' => $row->license_valid_until,
                                    ];
                                })->values()->all(),
                            ])

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
                                'rows' => $personnel->pdsWorkExperience->map(function ($row) {
                                    return [
                                        'start_date' => $row->start_date,
                                        'end_date' => $row->end_date,
                                        'position' => $row->position,
                                        'company' => $row->company,
                                        'appointment_status' => $row->appointment_status,
                                        'is_government' => $row->is_government,
                                    ];
                                })->values()->all(),
                            ])

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
                                'rows' => $personnel->pdsVoluntaryWork->map(function ($row) {
                                    return [
                                        'organization_name' => $row->organization_name,
                                        'organization_address' => $row->organization_address,
                                        'from_date' => $row->from_date,
                                        'to_date' => $row->to_date,
                                        'number_of_hours' => $row->number_of_hours,
                                        'position' => $row->position,
                                    ];
                                })->values()->all(),
                            ])

                            @include('personnel.partials.pds_repeater_readonly', [
                                'title' => 'VII. Learning and Development (L & D) Interventions/Training Program Attended',
                                'fields' => [
                                    ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                                    ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                                    ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                                    ['name' => 'hours', 'label' => 'Hours', 'type' => 'number'],
                                    ['name' => 'type', 'label' => 'Type', 'type' => 'text'],
                                    ['name' => 'sponsor', 'label' => 'Sponsor', 'type' => 'text'],
                                ],
                                'rows' => $personnel->pdsTraining->map(function ($row) {
                                    return [
                                        'title' => $row->title,
                                        'start_date' => $row->start_date,
                                        'end_date' => $row->end_date,
                                        'hours' => $row->hours,
                                        'type' => $row->type,
                                        'sponsor' => $row->sponsor,
                                    ];
                                })->values()->all(),
                            ])

                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white"><h5 class="mb-0">VIII. Other Information (Skills, Distinctions, Memberships)</h5></div>
                                <div class="card-body">
                                    @include('personnel.partials.pds_repeater_readonly', [
                                        'title' => 'Skills',
                                        'fields' => [
                                            ['name' => 'skill', 'label' => 'Skill', 'type' => 'text'],
                                        ],
                                        'rows' => $personnel->pdsSkills->map(function ($row) {
                                            return [
                                                'skill' => $row->skill,
                                            ];
                                        })->values()->all(),
                                    ])

                                    @include('personnel.partials.pds_repeater_readonly', [
                                        'title' => 'Distinctions',
                                        'fields' => [
                                            ['name' => 'distinction', 'label' => 'Distinction', 'type' => 'text'],
                                        ],
                                        'rows' => $personnel->pdsDistinctions->map(function ($row) {
                                            return [
                                                'distinction' => $row->distinction,
                                            ];
                                        })->values()->all(),
                                    ])

                                    @include('personnel.partials.pds_repeater_readonly', [
                                        'title' => 'Memberships',
                                        'fields' => [
                                            ['name' => 'membership', 'label' => 'Membership', 'type' => 'text'],
                                        ],
                                        'rows' => $personnel->pdsMemberships->map(function ($row) {
                                            return [
                                                'membership' => $row->membership,
                                            ];
                                        })->values()->all(),
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
                                                    if(Str::endsWith($field['name'], '_details')) {
                                                        $type = 'textarea';
                                                    } elseif(in_array($field['name'], [
                                                        'related_third_degree', 'related_fourth_degree', 'admin_offense', 'criminal_case', 'convicted',
                                                        'separated_service', 'election_candidate', 'election_resigned', 'immigrant', 'indigenous', 'pwd', 'solo_parent'
                                                    ])) {
                                                        $type = 'boolean';
                                                    }
                                                    $name = $field['name'];
                                                    $value = optional($pds)->{$name} ?? null;
                                                @endphp
                                                <div class="{{ $type === 'textarea' ? 'col-md-12' : 'col-md-4' }} mb-3">
                                                    <label class="form-control-label text-muted">{{ $field['label'] }}</label>
                                                    <div class="font-weight-bold">
                                                        @if($type === 'boolean')
                                                            @if($value === null || $value === '')
                                                                --
                                                            @else
                                                                {{ (string) $value === '1' ? 'Yes' : 'No' }}
                                                            @endif
                                                        @elseif($type === 'textarea')
                                                            {{ $value !== null && $value !== '' ? $value : '--' }}
                                                        @else
                                                            {{ $value !== null && $value !== '' ? $value : '--' }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @include('personnel.partials.pds_repeater_readonly', [
                                'title' => 'References',
                                'fields' => [
                                    ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                                    ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                                    ['name' => 'contact', 'label' => 'Contact', 'type' => 'text'],
                                ],
                                'rows' => $personnel->pdsReferences->map(function ($row) {
                                    return [
                                        'name' => $row->name,
                                        'address' => $row->address,
                                        'contact' => $row->contact,
                                    ];
                                })->values()->all(),
                            ])

                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white"><h5 class="mb-0">Government ID Issuance</h5></div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($governmentIdFields as $field)
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
                        </div>

                        <div class="tab-pane fade" id="inventory">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">Assigned Assets</h4>
                                <span class="badge badge-info">{{ $personnel->equipment->count() }} Items</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Property No</th>
                                            <th>Item Name</th>
                                            <th>Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($personnel->equipment as $item)
                                        <tr>
                                            <td class="font-weight-bold">{{ $item->property_no }}</td>
                                            <td>{{ $item->item }}<br><small class="text-muted">{{ $item->brand_manufacturer }}</small></td>
                                            <td><span class="badge badge-success">{{ $item->equipment_condition }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-5 text-muted">No equipment accountability found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="service-records">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Service Records</h4>
                                <div>
                                    @if($canManageServiceRecords)
                                        <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#addServiceRecordModal">
                                            <i class="fas fa-plus"></i> Add Record
                                        </button>
                                    @endif
                                    @if($canExportServiceRecords)
                                        <a href="{{ route('service-records.export.xlsx-format', $personnel->id) }}" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel"></i> Export
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Add Service Record Modal -->
                            @if($canManageServiceRecords)
                            <div class="modal fade" id="addServiceRecordModal" tabindex="-1" role="dialog" aria-labelledby="addServiceRecordModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="addServiceRecordModalLabel">Add Service Record</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <form action="{{ route('service-records.store', $personnel->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                      <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Date From <span class="text-danger">*</span></label>
                                            <input type="date" name="date_from" class="form-control" value="{{ old('date_from', now()->toDateString()) }}" required>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Date To</label>
                                            <input type="date" name="date_to" class="form-control" value="{{ old('date_to') }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Designation <span class="text-danger">*</span></label>
                                            <select name="position_id" class="form-control" required>
                                                <option value="" disabled {{ old('position_id') ? '' : 'selected' }}>Select Position</option>
                                                @foreach($positions as $position)
                                                    <option value="{{ $position->id }}" {{ (string) old('position_id', $personnel->position_id) === (string) $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Status <span class="text-danger">*</span></label>
                                            <select name="status" class="form-control" required>
                                                @foreach($employeeTypes as $type)
                                                    <option value="{{ $type }}" {{ old('status', $personnel->employee_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                      </div>
                                      <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Station <span class="text-danger">*</span></label>
                                            <select name="school_id" class="form-control" required>
                                                <option value="" disabled {{ old('school_id') ? '' : 'selected' }}>Select School</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}" {{ (string) old('school_id', $personnel->deployed_school_id ?? $personnel->assigned_school_id) === (string) $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Salary</label>
                                            <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $personnel->salary_actual) }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Branch</label>
                                            <input type="text" name="branch" class="form-control" value="{{ old('branch', $personnel->branch) }}">
                                        </div>
                                        <div class="col-md-2 form-group">
                                            <label>LV/Abs WO Pay</label>
                                            <input type="text" name="lv_abs_wo_pay" class="form-control" value="{{ old('lv_abs_wo_pay') }}">
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                      <button type="submit" class="btn btn-primary">Add Record</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-items-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date From</th>
                                            <th>Date To</th>
                                            <th>Designation</th>
                                            <th>Status</th>
                                            <th>Salary</th>
                                            <th>Station</th>
                                            <th>Branch</th>
                                            <th>LV/Abs WO Pay</th>
                                            @if($canManageServiceRecords)
                                                <th style="width: 180px;">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($personnel->serviceRecords->sortByDesc('date_from') as $record)
                                            <tr>
                                                <td>{{ $record->date_from }}</td>
                                                <td>{{ $record->date_to ?? '---' }}</td>
                                                <td>{{ $record->position->title ?? '--' }}</td>
                                                <td>{{ $record->status }}</td>
                                                <td>{{ $record->salary }}</td>
                                                <td>{{ $record->school->name ?? '--' }}</td>
                                                <td>{{ $record->branch ?? '---' }}</td>
                                                <td>{{ $record->lv_abs_wo_pay ?? '---' }}</td>
                                                @if($canManageServiceRecords)
                                                <td>
                                                    <button class="btn btn-sm btn-warning" type="button" data-toggle="modal" data-target="#editServiceRecordModal-{{ $record->id }}">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('service-records.destroy', [$personnel->id, $record->id]) }}" method="POST" class="d-inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service record?')">Delete</button>
                                                    </form>
                                                </td>
                                                @endif
                                            </tr>

                                            <!-- Edit Service Record Modal -->
                                            @if($canManageServiceRecords)
                                            <div class="modal fade" id="editServiceRecordModal-{{ $record->id }}" tabindex="-1" role="dialog" aria-labelledby="editServiceRecordModalLabel-{{ $record->id }}" aria-hidden="true">
                                              <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h5 class="modal-title" id="editServiceRecordModalLabel-{{ $record->id }}">Edit Service Record</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true">&times;</span>
                                                    </button>
                                                  </div>
                                                  <form action="{{ route('service-records.update', [$personnel->id, $record->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                      <div class="row">
                                                        <div class="col-md-3 form-group">
                                                            <label>Date From <span class="text-danger">*</span></label>
                                                            <input type="date" name="date_from" class="form-control" value="{{ $record->date_from }}" required>
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Date To</label>
                                                            <input type="date" name="date_to" class="form-control" value="{{ $record->date_to }}">
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Designation <span class="text-danger">*</span></label>
                                                            <select name="position_id" class="form-control" required>
                                                                @foreach($positions as $position)
                                                                    <option value="{{ $position->id }}" {{ (string) $record->position_id === (string) $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Status <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-control" required>
                                                                @foreach($employeeTypes as $type)
                                                                    <option value="{{ $type }}" {{ $record->status === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                      </div>
                                                      <div class="row">
                                                        <div class="col-md-4 form-group">
                                                            <label>Station <span class="text-danger">*</span></label>
                                                            <select name="school_id" class="form-control" required>
                                                                @foreach($schools as $school)
                                                                    <option value="{{ $school->id }}" {{ (string) $record->school_id === (string) $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Salary</label>
                                                            <input type="number" step="0.01" name="salary" class="form-control" value="{{ $record->salary }}">
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Branch</label>
                                                            <input type="text" name="branch" class="form-control" value="{{ $record->branch }}">
                                                        </div>
                                                        <div class="col-md-2 form-group">
                                                            <label>LV/Abs WO Pay</label>
                                                            <input type="text" name="lv_abs_wo_pay" class="form-control" value="{{ $record->lv_abs_wo_pay }}">
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                      <button type="submit" class="btn btn-success">Save Changes</button>
                                                    </div>
                                                  </form>
                                                </div>
                                              </div>
                                            </div>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">No service records yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="history">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h4 class="mb-3 text-primary"><i class="ni ni-books mr-2"></i> Seminar History</h4>
                                    <div class="timeline timeline-one-side">
                                        @forelse($personnel->pdsTraining as $tr)
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step badge-success"><i class="ni ni-check-bold"></i></span>
                                            <div class="timeline-content">
                                                <small class="text-muted font-weight-bold">{{ $tr->start_date }}</small>
                                                <h5 class="mt-1 mb-0">{{ $tr->title }}</h5>
                                                <p class="text-sm mt-1 mb-0 text-muted">{{ $tr->hours }} Hours</p>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted small">No training records found.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h4 class="mb-3 text-danger"><i class="ni ni-paper-diploma mr-2"></i> Special Orders</h4>
                                    <div class="list-group list-group-flush">
                                        @php
                                            $approvedSOs = $personnel->specialOrders->where('status', 'Approved');
                                        @endphp
                                        @forelse($approvedSOs as $so)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h5 class="mb-0">SO #{{ $so->so_number ?? $so->so_no }}-{{ $so->series_year }}</h5>
                                                    <small class="text-muted">{{ Str::limit($so->title, 40) }}</small>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="badge badge-pill badge-primary">{{ $so->type->name ?? ($so->type ?? '--') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted small">No special orders found.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.location.hash) {
            var trigger = document.querySelector('a.nav-link[href="' + window.location.hash + '"]');
            if (trigger && window.jQuery) {
                window.jQuery(trigger).tab('show');
            }
        }
    })();
</script>
@endsection