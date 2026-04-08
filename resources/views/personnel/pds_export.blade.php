<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ str_replace('\\', '/', public_path('assets/css/pds.css')) }}">
</head>
<body>
@php
    $pds = $personnel->pdsMain;
    $show = fn ($value) => filled($value) ? e($value) : '&nbsp;';
    $showDate = fn ($value) => filled($value) ? e(\Illuminate\Support\Carbon::parse($value)->format('d/m/Y')) : '&nbsp;';
    $showYear = fn ($value) => filled($value) ? e($value) : '&nbsp;';
    $yesNo = fn ($value) => is_null($value) ? '&nbsp;' : ($value ? 'YES' : 'NO');
    $yn = fn ($value) => is_null($value) ? '&nbsp;' : ($value ? 'Y' : 'N');
    $name = trim(collect([
        $pds?->last_name,
        $pds?->first_name,
        $pds?->middle_name,
        $pds?->extension_name,
    ])->filter()->implode(' '));
    $resAddress = collect([
        $pds?->res_house_lot,
        $pds?->res_street,
        $pds?->res_subdivision,
        $pds?->res_barangay,
        $pds?->res_city,
        $pds?->res_province,
        $pds?->res_zipcode,
    ])->filter()->implode(', ');
    $permAddress = collect([
        $pds?->perm_house_lot,
        $pds?->perm_street,
        $pds?->perm_subdivision,
        $pds?->perm_barangay,
        $pds?->perm_city,
        $pds?->perm_province,
        $pds?->perm_zipcode,
    ])->filter()->implode(', ');
    $educationRows = $personnel->pdsEducation->sortBy('from_year')->values();
    $eligibilityRows = $personnel->pdsEligibility->sortBy('exam_date')->values();
    $experienceRows = $personnel->pdsWorkExperience->sortByDesc('start_date')->values();
    $trainingRows = $personnel->pdsTraining->sortBy('start_date')->values();
    $childrenRows = $personnel->pdsChildren->sortBy('birth_date')->values();
    $referenceRows = $personnel->pdsReferences->values();
    $voluntaryRows = collect(range(1, 7));
    $otherRows = collect(range(1, 6));

    // Fixed row counts per page to keep the PDF pages visually filled.
    // Increase/decrease these if DOMPDF pagination shifts after styling tweaks.
    $workExperienceDisplayRows = 16;
    $trainingDisplayRows = 18;
    $referenceBlankRows = 9;

    $citizenshipDetailsHtml = collect([
        $pds?->citizenship_type,
        $pds?->citizenship_mode,
        $pds?->dual_citizenship_details,
    ])->filter()->map(fn ($value) => e($value))->implode('<br>');
@endphp

<div class="page">
    <div class="page-inner">
        <div class="pds-top">
            <table class="pds-meta-table">
                <tr>
                    <td></td>
                    <td class="pds-meta">
                        <b><i>CS Form No. 212</b></i><br>
                        <i>Revised 2025</i>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="pds-main-title">PERSONAL DATA SHEET</td>
                </tr>
                <tr>
                    <td colspan="2" class="pds-notice">
                        <i><b>WARNING: Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.</b></i>
                        <br><br>
                        <i><b>READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEET (PDS) BEFORE ACCOMPLISHING THE PDS FORM.</b></i>
                        <br>
                        Print legibly if accomplished through own handwriting. Tick appropriate boxes (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) and use separate sheet if necessary. Indicate N/A if not applicable. <b>DO NOT ABBREVIATE.</b>
                    </td>
                </tr>
            </table>
        </div>

        <div class="page-title">I. PERSONAL INFORMATION</div>

        <table class="grid row-height-short">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 32%;">
                <col style="width: 18%;">
                <col style="width: 16%;">
                <col style="width: 16%;">
            </colgroup>
            <tr>
                <td class="label-cell">1. Surname</td>
                <td class="value-cell" colspan="4">{!! $show($pds?->last_name) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">2. First Name</td>
                <td class="value-cell" colspan="3">{!! $show($pds?->first_name) !!}</td>
                <td class="value-cell name-ext-cell">
                    <div class="name-ext-label">Name Extension (Jr., Sr.)</div>
                    <div class="name-ext-value">{!! $show($pds?->extension_name) !!}</div>
                </td>
            </tr>
            <tr>
                <td class="label-cell">Middle Name</td>
                <td class="value-cell" colspan="4">{!! $show($pds?->middle_name) !!}</td>
            </tr>

            <tr>
                <td class="label-cell">3. Date of Birth<br><span class="small muted" style="text-transform:none;">(dd/mm/yyyy)</span></td>
                <td class="value-cell">{!! $showDate($pds?->birth_date) !!}</td>
                <td class="label-cell" rowspan="3">
                    16. Citizenship
                    <span class="label-note">If holder of dual citizenship, please indicate the details.</span>
                </td>
                <td class="value-cell" rowspan="3">{!! filled($citizenshipDetailsHtml) ? $citizenshipDetailsHtml : '&nbsp;' !!}</td>
                <td class="value-cell" rowspan="3">
                    <span class="country-hint">Pls. indicate country:</span>
                    {!! $show($pds?->dual_citizenship_country) !!}
                </td>
            </tr>
            <tr>
                <td class="label-cell">4. Place of Birth</td>
                <td class="value-cell">{!! $show($pds?->birth_place) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">5. Sex at Birth</td>
                <td class="value-cell">{!! $show($pds?->birth_sex) !!}</td>
            </tr>

            <tr>
                <td class="label-cell">6. Civil Status</td>
                <td class="value-cell">{!! $show($pds?->civil_status) !!}</td>
                <td class="label-cell address-label-cell" rowspan="3">
                    17. Residential Address
                    <span class="zip-caption">ZIP CODE</span>
                </td>
                <td class="value-cell address-wrap" colspan="2" rowspan="3">
                    <table class="address-table">
                        <tr>
                            <td>
                                <span class="field-value">{!! $show($pds?->res_house_lot) !!}</span>
                                <span class="field-hint">House/Block/Lot No.</span>
                            </td>
                            <td>
                                <span class="field-value">{!! $show($pds?->res_street) !!}</span>
                                <span class="field-hint">Street</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="field-value">{!! $show($pds?->res_subdivision) !!}</span>
                                <span class="field-hint">Subdivision/Village</span>
                            </td>
                            <td>
                                <span class="field-value">{!! $show($pds?->res_barangay) !!}</span>
                                <span class="field-hint">Barangay</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="field-value">{!! $show($pds?->res_city) !!}</span>
                                <span class="field-hint">City/Municipality</span>
                            </td>
                            <td>
                                <span class="field-value">{!! $show($pds?->res_province) !!}</span>
                                <span class="field-hint">Province</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><span class="field-value">{!! $show($pds?->res_zipcode) !!}</span></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="label-cell">7. Height (m)</td>
                <td class="value-cell">{!! $show($pds?->height) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">8. Weight (kg)</td>
                <td class="value-cell">{!! $show($pds?->weight) !!}</td>
            </tr>

            <tr>
                <td class="label-cell">9. Blood Type</td>
                <td class="value-cell">{!! $show($pds?->blood_type) !!}</td>
                <td class="label-cell address-label-cell" rowspan="4">
                    18. Permanent Address
                    <span class="zip-caption">ZIP CODE</span>
                </td>
                <td class="value-cell address-wrap" colspan="2" rowspan="4">
                    <table class="address-table">
                        <tr>
                            <td>
                                <span class="field-value">{!! $show($pds?->perm_house_lot) !!}</span>
                                <span class="field-hint">House/Block/Lot No.</span>
                            </td>
                            <td>
                                <span class="field-value">{!! $show($pds?->perm_street) !!}</span>
                                <span class="field-hint">Street</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="field-value">{!! $show($pds?->perm_subdivision) !!}</span>
                                <span class="field-hint">Subdivision/Village</span>
                            </td>
                            <td>
                                <span class="field-value">{!! $show($pds?->perm_barangay) !!}</span>
                                <span class="field-hint">Barangay</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="field-value">{!! $show($pds?->perm_city) !!}</span>
                                <span class="field-hint">City/Municipality</span>
                            </td>
                            <td>
                                <span class="field-value">{!! $show($pds?->perm_province) !!}</span>
                                <span class="field-hint">Province</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><span class="field-value">{!! $show($pds?->perm_zipcode) !!}</span></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="label-cell">10. UMID ID No.</td>
                <td class="value-cell">{!! $show($pds?->umid_id_number) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">11. PAG-IBIG ID No.</td>
                <td class="value-cell">{!! $show($pds?->pagibig_number) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">12. PhilHealth No.</td>
                <td class="value-cell">{!! $show($pds?->philhealth_number) !!}</td>
            </tr>

            <tr>
                <td class="label-cell">13. PhilSys Number (PSN)</td>
                <td class="value-cell">{!! $show($pds?->philsys_number) !!}</td>
                <td class="label-cell">19. Telephone No.</td>
                <td class="value-cell" colspan="2">{!! $show($pds?->telephone) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">14. TIN No.</td>
                <td class="value-cell">{!! $show($pds?->tin_number) !!}</td>
                <td class="label-cell">20. Mobile No.</td>
                <td class="value-cell" colspan="2">{!! $show($pds?->mobile) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">15. Agency Employee No.</td>
                <td class="value-cell">{!! $show($pds?->agency_employee_number) !!}</td>
                <td class="label-cell">21. E-mail Address (if any)</td>
                <td class="value-cell" colspan="2">{!! $show($pds?->email_address) !!}</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <div class="page-title">II. FAMILY BACKGROUND</div>
        @php
            $familyChildren = $childrenRows->take(6)->values();
        @endphp
        <table class="grid row-height-short">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 42%;">
                <col style="width: 25%;">
                <col style="width: 15%;">
            </colgroup>
            <tr>
                <td class="label-cell">22. Spouse's Surname</td>
                <td class="value-cell">{!! $show($pds?->spouse_last_name) !!}</td>
                <td class="label-cell">23. Name of Children <span class="small muted" style="text-transform:none;">(Write full name and list all)</span></td>
                <td class="label-cell">Date of Birth <span class="small muted" style="text-transform:none;">(dd/mm/yyyy)</span></td>
            </tr>
            <tr>
                <td class="label-cell">First Name</td>
                <td class="value-cell" style="padding:0;">
                    <table class="split-table">
                        <tr>
                            <td style="padding:2px 4px;">{!! $show($pds?->spouse_first_name) !!}</td>
                            <td class="name-ext-cell" style="width: 32%;">
                                <div class="name-ext-label">Name Extension (Jr., Sr.)</div>
                                <div class="name-ext-value">{!! $show($pds?->spouse_extension_name) !!}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="value-cell">{!! $show(optional($familyChildren->get(0))->child_name) !!}</td>
                <td class="value-cell">{!! $showDate(optional($familyChildren->get(0))->birth_date) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Middle Name</td>
                <td class="value-cell">{!! $show($pds?->spouse_middle_name) !!}</td>
                <td class="value-cell">{!! $show(optional($familyChildren->get(1))->child_name) !!}</td>
                <td class="value-cell">{!! $showDate(optional($familyChildren->get(1))->birth_date) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Occupation</td>
                <td class="value-cell">{!! $show($pds?->spouse_occupation) !!}</td>
                <td class="value-cell">{!! $show(optional($familyChildren->get(2))->child_name) !!}</td>
                <td class="value-cell">{!! $showDate(optional($familyChildren->get(2))->birth_date) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Employer/Business Name</td>
                <td class="value-cell">{!! $show($pds?->spouse_employer) !!}</td>
                <td class="value-cell">{!! $show(optional($familyChildren->get(3))->child_name) !!}</td>
                <td class="value-cell">{!! $showDate(optional($familyChildren->get(3))->birth_date) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Business Address</td>
                <td class="value-cell">{!! $show($pds?->employer_address) !!}</td>
                <td class="value-cell">{!! $show(optional($familyChildren->get(4))->child_name) !!}</td>
                <td class="value-cell">{!! $showDate(optional($familyChildren->get(4))->birth_date) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Telephone No.</td>
                <td class="value-cell">{!! $show($pds?->spouse_telephone) !!}</td>
                <td class="value-cell">{!! $show(optional($familyChildren->get(5))->child_name) !!}</td>
                <td class="value-cell">{!! $showDate(optional($familyChildren->get(5))->birth_date) !!}</td>
            </tr>

            <tr>
                <td class="label-cell">24. Father's Surname</td>
                <td class="value-cell">{!! $show($pds?->father_last_name) !!}</td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
            </tr>
            <tr>
                <td class="label-cell">First Name</td>
                <td class="value-cell" style="padding:0;">
                    <table class="split-table">
                        <tr>
                            <td style="padding:2px 4px;">{!! $show($pds?->father_first_name) !!}</td>
                            <td class="name-ext-cell" style="width: 32%;">
                                <div class="name-ext-label">Name Extension (Jr., Sr.)</div>
                                <div class="name-ext-value">{!! $show($pds?->father_extension_name) !!}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
            </tr>
            <tr>
                <td class="label-cell">Middle Name</td>
                <td class="value-cell">{!! $show($pds?->father_middle_name) !!}</td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
            </tr>

            <tr>
                <td class="label-cell">25. Mother's Maiden Name</td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
            </tr>
            <tr>
                <td class="label-cell">Surname</td>
                <td class="value-cell">{!! $show($pds?->mother_last_name) !!}</td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
            </tr>
            <tr>
                <td class="label-cell">First Name</td>
                <td class="value-cell">{!! $show($pds?->mother_first_name) !!}</td>
                <td class="value-cell">&nbsp;</td>
                <td class="value-cell">&nbsp;</td>
            </tr>
            <tr>
                <td class="label-cell">Middle Name</td>
                <td class="value-cell">{!! $show($pds?->mother_middle_name) !!}</td>
                <td class="value-cell note-continue" colspan="2">(Continue on separate sheet if necessary)</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <div class="page-title">III. EDUCATIONAL BACKGROUND</div>
        @php
            $educationByLevel = $educationRows
                ->filter(fn ($row) => filled($row->level))
                ->keyBy(fn ($row) => strtoupper(trim($row->level)));
            $educationLevels = [
                'ELEMENTARY' => 'ELEMENTARY',
                'SECONDARY' => 'SECONDARY',
                'VOCATIONAL / TRADE COURSE' => 'VOCATIONAL /<br>TRADE COURSE',
                'COLLEGE' => 'COLLEGE',
                'GRADUATE STUDIES' => 'GRADUATE STUDIES',
            ];
        @endphp
        <table class="subtable row-height-short">
            <colgroup>
                <col style="width: 16%;">
                <col style="width: 23%;">
                <col style="width: 22%;">
                <col style="width: 7%;">
                <col style="width: 7%;">
                <col style="width: 9%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
            </colgroup>
            <tr>
                <th class="label-cell center" rowspan="2">26.<br>Level</th>
                <th class="label-cell center" rowspan="2">Name of School<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
                <th class="label-cell center" rowspan="2">Basic Education/Degree/Course<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
                <th class="label-cell center" colspan="2">Period of Attendance</th>
                <th class="label-cell center" rowspan="2">Highest Level/<br>Units Earned<br><span class="small muted" style="text-transform:none;">(if not graduated)</span></th>
                <th class="label-cell center" rowspan="2">Year Graduated</th>
                <th class="label-cell center" rowspan="2">Scholarship/<br>Academic Honors Received</th>
            </tr>
            <tr>
                <th class="label-cell center">From</th>
                <th class="label-cell center">To</th>
            </tr>
            @foreach($educationLevels as $levelKey => $levelLabelHtml)
                @php
                    $education = $educationByLevel->get($levelKey);
                @endphp
                <tr>
                    <td class="label-cell" style="font-weight:700;">{!! $levelLabelHtml !!}</td>
                    <td class="value-cell">{!! $show($education?->school_name) !!}</td>
                    <td class="value-cell">{!! $show($education?->degree) !!}</td>
                    <td class="value-cell center">{!! $showYear($education?->from_year) !!}</td>
                    <td class="value-cell center">{!! $showYear($education?->to_year) !!}</td>
                    <td class="value-cell">{!! $show($education?->highest_level) !!}</td>
                    <td class="value-cell center">{!! $showYear($education?->to_year) !!}</td>
                    <td class="value-cell">{!! $show($education?->honors) !!}</td>
                </tr>
            @endforeach
            <tr>
                <td class="note-continue" colspan="8">(Continue on separate sheet if necessary)</td>
            </tr>
        </table>

        <div class="signature-strip">
            <div class="signature-strip-cell signature-strip-label" style="width: 18%;">Signature</div>
            <div class="signature-strip-cell signature-strip-value" style="width: 52%;">
                <span class="signature-line">&nbsp;</span>
                <div class="signature-note">(wet signature/e-signature/digital certificate)</div>
            </div>
            <div class="signature-strip-cell signature-strip-label" style="width: 10%;">Date</div>
            <div class="signature-strip-cell signature-strip-value" style="width: 20%;">
                <span class="signature-line">&nbsp;</span>
            </div>
        </div>
    </div>
</div>

<div class="page">
    <div class="page-inner">

        <div class="page-title">IV. CIVIL SERVICE ELIGIBILITY</div>
        <table class="subtable row-height-short">
            <colgroup>
                <col style="width: 38%;">
                <col style="width: 12%;">
                <col style="width: 16%;">
                <col style="width: 16%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
            </colgroup>
            <tr>
                <th class="label-cell center" rowspan="2">27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL</th>
                <th class="label-cell center" rowspan="2">RATING (If Applicable)</th>
                <th class="label-cell center" rowspan="2">DATE OF EXAMINATION / CONFERMENT</th>
                <th class="label-cell center" rowspan="2">PLACE OF EXAMINATION / CONFERMENT</th>
                <th class="label-cell center" colspan="2">LICENSE (if applicable)</th>
            </tr>
            <tr>
                <th class="label-cell center">NUMBER</th>
                <th class="label-cell center">Valid Until</th>
            </tr>
            @foreach($eligibilityRows->take(7) as $eligibility)
                <tr>
                    <td class="value-cell">{!! $show($eligibility->eligibility) !!}</td>
                    <td class="value-cell">{!! $show($eligibility->rating) !!}</td>
                    <td class="value-cell">{!! $showDate($eligibility->exam_date) !!}</td>
                    <td class="value-cell">{!! $show($eligibility->exam_place) !!}</td>
                    <td class="value-cell">{!! $show($eligibility->license_number) !!}</td>
                    <td class="value-cell">{!! $showDate($eligibility->license_valid_until) !!}</td>
                </tr>
            @endforeach
            @for ($index = $eligibilityRows->take(7)->count(); $index < 7; $index++)
                <tr>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                </tr>
            @endfor
            <tr>
                <td class="note-continue" colspan="6">(Continue on separate sheet if necessary)</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <div class="page-title">
            V. WORK EXPERIENCE
            <span class="section-title-note">(Include private employment. Start from your recent work.) Description of duties should be indicated in the attached Work Experience Sheet.</span>
        </div>
        <table class="subtable row-height-short">
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 28%;">
                <col style="width: 30%;">
                <col style="width: 16%;">
                <col style="width: 10%;">
            </colgroup>
            <tr>
                <th class="label-cell center" colspan="2">
                    28. INCLUSIVE DATES
                    <br><span class="small muted" style="text-transform:none;">(dd/mm/yyyy)</span>
                </th>
                <th class="label-cell center" rowspan="2">POSITION TITLE<br><span class="small muted" style="text-transform:none;">(Write in full/Do not abbreviate)</span></th>
                <th class="label-cell center" rowspan="2">DEPARTMENT / AGENCY / OFFICE / COMPANY<br><span class="small muted" style="text-transform:none;">(Write in full/Do not abbreviate)</span></th>
                <th class="label-cell center" rowspan="2">STATUS OF APPOINTMENT</th>
                <th class="label-cell center" rowspan="2">GOV'T SERVICE<br><span class="small muted" style="text-transform:none;">(Y / N)</span></th>
            </tr>
            <tr>
                <th class="label-cell center">From</th>
                <th class="label-cell center">To</th>
            </tr>
            @foreach($experienceRows->take($workExperienceDisplayRows) as $experience)
                <tr>
                    <td class="value-cell">{!! $showDate($experience->start_date) !!}</td>
                    <td class="value-cell">{!! $showDate($experience->end_date) !!}</td>
                    <td class="value-cell">{!! $show($experience->position) !!}</td>
                    <td class="value-cell">{!! $show($experience->company) !!}</td>
                    <td class="value-cell">{!! $show($experience->appointment_status) !!}</td>
                    <td class="value-cell center">{!! $yn($experience->is_government ?? null) !!}</td>
                </tr>
            @endforeach
            @for ($index = $experienceRows->take($workExperienceDisplayRows)->count(); $index < $workExperienceDisplayRows; $index++)
                <tr>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                </tr>
            @endfor
        </table>

        <div class="signature-strip">
            <div class="signature-strip-cell signature-strip-label" style="width: 25%;">SIGNATURE</div>
            <div class="signature-strip-cell signature-strip-value" style="width: 40%;">
                <span class="signature-line">&nbsp;</span>
                <div class="signature-note">(wet signature/e-signature/digital certificate)</div>
            </div>
            <div class="signature-strip-cell signature-strip-label" style="width: 15%;">DATE</div>
            <div class="signature-strip-cell signature-strip-value" style="width: 20%;">
                <span class="signature-line">&nbsp;</span>
            </div>
        </div>
    </div>
</div>

<div class="page">
    <div class="page-inner">

        <div class="page-title">VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE / VOLUNTARY ORGANIZATIONS</div>
        <table class="subtable row-height-short">
            <colgroup>
                <col style="width: 44%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 30%;">
            </colgroup>
            <tr>
                <th class="label-cell center" rowspan="2">29.<br>NAME &amp; ADDRESS OF ORGANIZATION<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
                <th class="label-cell center" colspan="2">INCLUSIVE DATES<br><span class="small muted" style="text-transform:none;">(dd/mm/yyyy)</span></th>
                <th class="label-cell center" rowspan="2">NUMBER OF HOURS</th>
                <th class="label-cell center" rowspan="2">POSITION / NATURE OF WORK</th>
            </tr>
            <tr>
                <th class="label-cell center">From</th>
                <th class="label-cell center">To</th>
            </tr>
            @foreach($voluntaryRows as $rowNumber)
                <tr>
                    <td class="value-cell">
                        @php
                            $vw = $pds->voluntary_works->get($rowNumber - 1);
                            $org = $vw->organization_name ?? $vw->organization ?? null;
                            $addr = $vw->organization_address ?? null;
                        @endphp
                        {!! $show(trim(($org ? $org : '') . ($org && $addr ? ', ' : '') . ($addr ? $addr : ''))) !!}
                    </td>
                    <td class="value-cell">{!! $showDate($vw->from_date ?? null) !!}</td>
                    <td class="value-cell">{!! $showDate($vw->to_date ?? null) !!}</td>
                    <td class="value-cell">{!! $show($vw->number_of_hours ?? null) !!}</td>
                    <td class="value-cell">{!! $show($vw->position ?? null) !!}</td>
                </tr>
            @endforeach
            <tr>
                <td class="note-continue" colspan="5">(Continue on separate sheet if necessary)</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <div class="page-title">VII. LEARNING AND DEVELOPMENT (L&amp;D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED</div>
        <table class="subtable row-height-short">
            <colgroup>
                <col style="width: 44%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 20%;">
            </colgroup>
            <tr>
                <th class="label-cell center" rowspan="2">30.<br>TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
                <th class="label-cell center" colspan="2">INCLUSIVE DATES OF ATTENDANCE<br><span class="small muted" style="text-transform:none;">(dd/mm/yyyy)</span></th>
                <th class="label-cell center" rowspan="2">NUMBER OF HOURS</th>
                <th class="label-cell center" rowspan="2">Type of L&amp;D<br><span class="small muted" style="text-transform:none;">(Managerial/ Supervisory/Technical/etc)</span></th>
                <th class="label-cell center" rowspan="2">CONDUCTED/ SPONSORED BY<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
            </tr>
            <tr>
                <th class="label-cell center">From</th>
                <th class="label-cell center">To</th>
            </tr>
            @foreach($trainingRows->take($trainingDisplayRows) as $training)
                <tr>
                    <td class="value-cell">{!! $show($training->title) !!}</td>
                    <td class="value-cell">{!! $showDate($training->start_date) !!}</td>
                    <td class="value-cell">{!! $showDate($training->end_date) !!}</td>
                    <td class="value-cell">{!! $show($training->hours) !!}</td>
                    <td class="value-cell">{!! $show($training->type) !!}</td>
                    <td class="value-cell">{!! $show($training->sponsor) !!}</td>
                </tr>
            @endforeach
            @for ($index = $trainingRows->take($trainingDisplayRows)->count(); $index < $trainingDisplayRows; $index++)
                <tr>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                    <td class="value-cell">&nbsp;</td>
                </tr>
            @endfor
        </table>

        <div class="spacer"></div>

        <div class="page-title">VIII. OTHER INFORMATION</div>
        <table class="subtable row-height-short">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 50%;">
                <col style="width: 25%;">
            </colgroup>
            <tr>
                <th class="label-cell center">31.<br>SPECIAL SKILLS and HOBBIES</th>
                <th class="label-cell center">32.<br>NON-ACADEMIC DISTINCTIONS / RECOGNITION<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
                <th class="label-cell center">33.<br>MEMBERSHIP IN ASSOCIATION/ORGANIZATION<br><span class="small muted" style="text-transform:none;">(Write in full)</span></th>
            </tr>
            @foreach($otherRows as $rowNumber)
                <tr>
                    <td class="value-cell">{!! $show($pds->skills->get($rowNumber - 1)->skill ?? null) !!}</td>
                    <td class="value-cell">{!! $show($pds->distinctions->get($rowNumber - 1)->distinction ?? null) !!}</td>
                    <td class="value-cell">{!! $show($pds->memberships->get($rowNumber - 1)->membership ?? null) !!}</td>
                </tr>
            @endforeach
            <tr>
                <td class="note-continue" colspan="3">(Continue on separate sheet if necessary)</td>
            </tr>
        </table>

        <div class="signature-strip">
            <div class="signature-strip-cell signature-strip-label" style="width: 25%;">SIGNATURE</div>
            <div class="signature-strip-cell signature-strip-value" style="width: 40%;">
                <span class="signature-line">&nbsp;</span>
                <div class="signature-note">(wet signature/e-signature/digital certificate)</div>
            </div>
            <div class="signature-strip-cell signature-strip-label" style="width: 15%;">DATE</div>
            <div class="signature-strip-cell signature-strip-value" style="width: 20%;">
                <span class="signature-line">&nbsp;</span>
            </div>
        </div>
    </div>
</div>

<div class="page">
    <div class="page-inner">

        <table class="grid">
            <colgroup>
                <col style="width: 70%;">
                <col style="width: 30%;">
            </colgroup>

            @php
                $cb = fn($checked) => $checked === null ? '☐' : ($checked ? '☑' : '☐');
                $cbNo = fn($checked) => $checked === null ? '☐' : ($checked ? '☐' : '☑');
            @endphp

            <!-- 34 -->
            <tr style="height: 72px;">
                <td class="question-cell">
                    <b>34.</b> Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you in the Office, Bureau or Department where you will be appointed,
                    <br><span class="small">a.</span> within the third degree?
                    <br><span class="small">b.</span> within the fourth degree (for Local Government Unit - Career Employees)?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->related_third_degree) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->related_third_degree) !!} NO</span>
                    <span style="margin-left:8px;">{!! $cb($pds?->related_fourth_degree) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->related_fourth_degree) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->related_fourth_degree_details) !!}
                </td>
            </tr>

            <!-- 35 -->
            <tr style="height: 48px;">
                <td class="question-cell">
                    <b>35.a</b> Have you ever been found guilty of any administrative offense?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->admin_offense) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->admin_offense) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->admin_offense_details) !!}
                </td>
            </tr>
            <tr style="height: 62px;">
                <td class="question-cell">
                    <b>35.b</b> Have you been criminally charged before any court?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->criminal_case) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->criminal_case) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->criminal_case_details) !!}
                    <div class="details-label">Date Filed:</div>
                    {!! $show($pds?->criminal_case_date_filed) !!}
                    <div class="details-label">Status of Case/s:</div>
                    {!! $show($pds?->criminal_case_status) !!}
                </td>
            </tr>

            <!-- 36 -->
            <tr style="height: 48px;">
                <td class="question-cell">
                    <b>36.</b> Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->convicted) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->convicted) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->convicted_details) !!}
                </td>
            </tr>

            <!-- 37 -->
            <tr style="height: 58px;">
                <td class="question-cell">
                    <b>37.</b> Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->separated_service) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->separated_service) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->separated_service_details) !!}
                </td>
            </tr>

            <!-- 38 -->
            <tr style="height: 62px;">
                <td class="question-cell">
                    <b>38.a</b> Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->election_candidate) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->election_candidate) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->election_candidate_details) !!}
                </td>
            </tr>
            <tr style="height: 62px;">
                <td class="question-cell">
                    <b>38.b</b> Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->election_resigned) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->election_resigned) !!} NO</span>
                    <div class="details-label">If YES, give details:</div>
                    {!! $show($pds?->election_resigned_details) !!}
                </td>
            </tr>

            <!-- 39 -->
            <tr style="height: 48px;">
                <td class="question-cell">
                    <b>39.</b> Have you acquired the status of an immigrant or permanent resident of another country?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->immigrant) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->immigrant) !!} NO</span>
                    <div class="details-label">If YES, give details (country):</div>
                    {!! $show($pds?->immigrant_details) !!}
                </td>
            </tr>

            <!-- 40 -->
            <tr style="height: 78px;">
                <td class="question-cell">
                    <b>40.</b> Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:
                    <br><span class="small">a.</span> Are you a member of any indigenous group?
                    <br><span class="small">b.</span> Are you a person with disability?
                    <br><span class="small">c.</span> Are you a solo parent?
                </td>
                <td class="value-cell" style="vertical-align: top;">
                    <span style="margin-left:8px;">{!! $cb($pds?->indigenous) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->indigenous) !!} NO</span>
                    <div class="details-label">If YES, please specify:</div>
                    {!! $show($pds?->indigenous_details) !!}

                    <span style="margin-left:8px;">{!! $cb($pds?->pwd) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->pwd) !!} NO</span>
                    <div class="details-label">If YES, please specify ID No:</div>
                    {!! $show($pds?->pwd_details) !!}

                    <span style="margin-left:8px;">{!! $cb($pds?->solo_parent) !!} YES</span>
                    <span style="margin-left:8px;">{!! $cbNo($pds?->solo_parent) !!} NO</span>
                    <div class="details-label">If YES, please specify ID No:</div>
                    {!! $show($pds?->solo_parent_details) !!}
                </td>
            </tr>
        </table>

        <div class="spacer"></div>

        <table class="grid">
            <colgroup>
                <col style="width: 78%;">
                <col style="width: 22%;">
            </colgroup>
            <tr>
                <td style="padding:0;">
                    <table class="grid" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 40%;">
                            <col style="width: 40%;">
                            <col style="width: 20%;">
                        </colgroup>
                        <tr>
                            <td class="label-cell" colspan="3" style="text-transform:none; font-weight:700;">
                                41.&nbsp; REFERENCES <span class="small muted" style="text-transform:none;">(Person not related by consanguinity or affinity to applicant/appointee)</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell center">NAME</td>
                            <td class="label-cell center">OFFICE / RESIDENTIAL ADDRESS</td>
                            <td class="label-cell center">CONTACT NO. AND/OR EMAIL</td>
                        </tr>
                        @for ($refIndex = 0; $refIndex < $referenceBlankRows; $refIndex++)
                            @php
                                $ref = $referenceRows->get($refIndex);
                            @endphp
                            <tr>
                                <td class="value-cell">{!! $show($ref?->name) !!}</td>
                                <td class="value-cell">{!! $show($ref?->address) !!}</td>
                                <td class="value-cell">{!! $show($ref?->contact) !!}</td>
                            </tr>
                        @endfor
                    </table>
                </td>
                <td style="padding:0;">
                    <div style="border:1px solid #000; height: 78px; box-sizing: border-box; padding: 6px; text-align:center; font-size: 7px; line-height: 1.2;">
                        Passport-sized unfiltered digital picture taken within the last 6 months
                        <br>4.5 cm. x 3.5 cm
                    </div>
                    <div class="label-cell center" style="border-top:0;">PHOTO</div>
                </td>
            </tr>
            <tr style="height: 54px;">
                <td class="question-cell" style="text-transform:none;">
                    42.&nbsp; I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement.
                </td>
                <td class="value-cell">&nbsp;</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <table class="grid">
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 45%;">
                <col style="width: 25%;">
            </colgroup>
            <tr style="height: 66px;">
                <td style="padding:0; vertical-align: top;">
                    <table style="width:100%; border-collapse: collapse; table-layout: fixed;">
                        <tr>
                            <td style="border:0; padding: 4px 6px; font-size: 7px; line-height: 1.15;">
                                Government Issued ID (i.e. Passport, GSIS, SSS, PRC, Driver's License, etc)
                                <br><b>PLEASE INDICATE</b> ID Number and Date of Issuance
                            </td>
                        </tr>
                    </table>
                    <table style="width:100%; border-collapse: collapse; table-layout: fixed;">
                        <tr>
                            <td style="border-top:1px solid #000; border-left:0; border-right:0; border-bottom:0; padding: 2px 6px; font-size: 7px;">
                                Government Issued ID: {!! $show($pds?->issued_id) !!}
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top:1px solid #000; border-left:0; border-right:0; border-bottom:0; padding: 2px 6px; font-size: 7px;">
                                ID/License/Passport No.: {!! $show($pds?->id_number) !!}
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top:1px solid #000; border-left:0; border-right:0; border-bottom:0; padding: 2px 6px; font-size: 7px;">
                                Date/Place of Issuance: {!! $showDate($pds?->issue_date) !!} {!! $show($pds?->issue_place) !!}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding:0; vertical-align: top;">
                    <div style="border: 1px solid #000; border-left:0; border-right:0; height: 40px; box-sizing: border-box; text-align:center; padding-top: 14px;">
                        <div class="signature-note">(wet signature/e-signature/digital certificate)</div>
                    </div>
                    <div style="border-top: 0; border-left:0; border-right:0; border-bottom:1px solid #000; text-align:center; font-size:7px; padding: 2px 0;">Signature (Sign inside the box)</div>
                    <div style="text-align:center; font-size:7px; padding: 2px 0;">Date Accomplished</div>
                </td>
                <td style="padding:0; vertical-align: top;">
                    <div style="border:1px solid #000; height: 52px; box-sizing: border-box;">&nbsp;</div>
                    <div style="border-top: 0; text-align:center; font-size:7px; padding: 2px 0;">Right Thumbmark</div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 4px; font-size: 7px; line-height: 1.15;">
            SUBSCRIBED AND SWORN to before me this <span style="display:inline-block; border-bottom: 1px solid #000; width: 170px;">&nbsp;</span> , affiant exhibiting his/her validly issued government ID as indicated above.
        </div>

        <div style="margin-top: 6px; text-align:center;">
            <div style="display:inline-block; width: 70%; border: 1px solid #000; height: 44px; box-sizing: border-box; padding-top: 14px;">
                <div class="signature-note">(wet signature/e-signature/digital certificate except for notary public)</div>
            </div>
            <div style="display:inline-block; width: 70%; border: 1px solid #000; border-top: 0; font-size: 7px; padding: 2px 0; box-sizing: border-box;">
                Person Administering Oath
            </div>
        </div>
    </div>
</div>
</body>
</html>