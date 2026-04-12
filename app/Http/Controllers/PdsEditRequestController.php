<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PdsChild;
use App\Models\PdsDistinction;
use App\Models\PdsEducation;
use App\Models\PdsEligibility;
use App\Models\PdsMain;
use App\Models\PdsMembership;
use App\Models\PdsReference;
use App\Models\PdsSkill;
use App\Models\PdsSubmission;
use App\Models\PdsVoluntaryWork;
use App\Models\PdsWorkExperience;
use App\Models\Personnel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PdsEditRequestController extends Controller
{
    private function schoolScopeId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->hasRole('school') && $user->school_id) {
            return (int) $user->school_id;
        }

        return null;
    }

    private function assertPersonnelScope(Personnel $personnel): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasRole('personnel')) {
            abort_if((int) $user->personnel_id !== (int) $personnel->id, 403);
            return;
        }

        if ($user->hasRole('school')) {
            abort_if((int) $personnel->assigned_school_id !== (int) $user->school_id, 403);
            return;
        }

        abort_unless($user->hasRole('admin'), 403);
    }

    private function assertSubmissionScope(PdsSubmission $submission): void
    {
        $submission->loadMissing('personnel');
        abort_unless($submission->personnel, 404);

        $this->assertPersonnelScope($submission->personnel);
    }

    private function assertCanReviewSubmission(PdsSubmission $submission): void
    {
        $user = Auth::user();
        abort_unless($user && $user->hasAnyRole(['admin', 'school']), 403);
        abort_if(strtoupper((string) $submission->status) !== 'SUBMITTED', 422, 'Only submitted requests can be reviewed.');

        if ($user->hasRole('school')) {
            $submission->loadMissing('personnel:id,assigned_school_id');
            abort_if((int) optional($submission->personnel)->assigned_school_id !== (int) $user->school_id, 403);
        }
    }

    private function assertCanDeleteSubmission(PdsSubmission $submission): void
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('personnel'), 403);

        $submission->loadMissing('personnel');
        abort_unless($submission->personnel, 404);

        abort_if((int) $submission->personnel_id !== (int) $user->personnel_id, 403);
        abort_if((int) ($submission->submitted_by ?? 0) !== (int) $user->id, 403);
        abort_if(strtoupper((string) $submission->status) !== 'SUBMITTED', 422, 'Only submitted requests can be deleted.');
    }

    private function loadLivePds(Personnel $personnel): Personnel
    {
        $personnel->load([
            'pdsMain' => function ($q) {
                $q->whereNull('submission_id');
            },
            'pdsChildren' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsEducation' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsEligibility' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsWorkExperience' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsVoluntaryWork' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsTraining' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsSkills' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsDistinctions' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsMemberships' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsReferences' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
        ]);

        return $personnel;
    }

    private function validationRules(): array
    {
        return [
            // I. Personal Information
            'last_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'extension_name' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'birth_sex' => 'nullable|string|max:10',
            'civil_status' => 'nullable|string|max:50',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'blood_type' => 'nullable|string|max:5',
            'umid_id_number' => 'nullable|string|max:255',
            'pagibig_number' => 'nullable|string|max:255',
            'philhealth_number' => 'nullable|string|max:255',
            'sss_number' => 'nullable|string|max:255',
            'philsys_number' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'agency_employee_number' => 'nullable|string|max:255',
            'citizenship_type' => 'nullable|string|max:20',
            'citizenship_mode' => 'nullable|string|max:20',
            'dual_citizenship_country' => 'nullable|string|max:255',
            'dual_citizenship_details' => 'nullable|string',
            'res_house_lot' => 'nullable|string|max:255',
            'res_street' => 'nullable|string|max:255',
            'res_subdivision' => 'nullable|string|max:255',
            'res_barangay' => 'nullable|string|max:255',
            'res_city' => 'nullable|string|max:255',
            'res_province' => 'nullable|string|max:255',
            'res_zipcode' => 'nullable|string|max:10',
            'perm_house_lot' => 'nullable|string|max:255',
            'perm_street' => 'nullable|string|max:255',
            'perm_subdivision' => 'nullable|string|max:255',
            'perm_barangay' => 'nullable|string|max:255',
            'perm_city' => 'nullable|string|max:255',
            'perm_province' => 'nullable|string|max:255',
            'perm_zipcode' => 'nullable|string|max:10',
            'residential_address' => 'nullable|string',
            'telephone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email_address' => 'nullable|email|max:255',

            // II. Family Background
            'spouse_last_name' => 'nullable|string|max:255',
            'spouse_first_name' => 'nullable|string|max:255',
            'spouse_middle_name' => 'nullable|string|max:255',
            'spouse_extension_name' => 'nullable|string|max:20',
            'spouse_occupation' => 'nullable|string|max:255',
            'spouse_employer' => 'nullable|string|max:255',
            'employer_address' => 'nullable|string',
            'spouse_telephone' => 'nullable|string|max:255',
            'father_last_name' => 'nullable|string|max:255',
            'father_first_name' => 'nullable|string|max:255',
            'father_middle_name' => 'nullable|string|max:255',
            'father_extension_name' => 'nullable|string|max:20',
            'mother_last_name' => 'nullable|string|max:255',
            'mother_first_name' => 'nullable|string|max:255',
            'mother_middle_name' => 'nullable|string|max:255',

            // Questions
            'related_third_degree' => 'nullable|boolean',
            'related_fourth_degree' => 'nullable|boolean',
            'related_fourth_degree_details' => 'nullable|string',
            'admin_offense' => 'nullable|boolean',
            'admin_offense_details' => 'nullable|string',
            'criminal_case' => 'nullable|boolean',
            'criminal_case_date' => 'nullable|date',
            'criminal_case_status' => 'nullable|string',
            'convicted' => 'nullable|boolean',
            'convicted_details' => 'nullable|string',
            'separated_service' => 'nullable|boolean',
            'separated_service_details' => 'nullable|string',
            'election_candidate' => 'nullable|boolean',
            'election_candidate_details' => 'nullable|string',
            'election_resigned' => 'nullable|boolean',
            'election_resigned_details' => 'nullable|string',
            'immigrant' => 'nullable|boolean',
            'immigrant_details' => 'nullable|string',
            'indigenous' => 'nullable|boolean',
            'indigenous_details' => 'nullable|string',
            'pwd' => 'nullable|boolean',
            'pwd_details' => 'nullable|string',
            'solo_parent' => 'nullable|boolean',
            'solo_parent_details' => 'nullable|string',

            // Government ID Issuance
            'issued_id' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'issue_place' => 'nullable|string|max:255',

            // 1:M tables
            'children' => 'nullable|array',
            'children.*.child_name' => 'nullable|string|max:255',
            'children.*.birth_date' => 'nullable|date',

            'education' => 'nullable|array',
            'education.*.level' => 'nullable|string|max:50',
            'education.*.school_name' => 'nullable|string|max:255',
            'education.*.degree' => 'nullable|string|max:255',
            'education.*.from_year' => 'nullable|integer',
            'education.*.to_year' => 'nullable|integer',
            'education.*.highest_level_units' => 'nullable|string|max:255',
            'education.*.year_graduated' => 'nullable|integer',
            'education.*.honors' => 'nullable|string|max:255',

            'eligibility' => 'nullable|array',
            'eligibility.*.eligibility' => 'nullable|string|max:255',
            'eligibility.*.rating' => 'nullable|string|max:255',
            'eligibility.*.exam_date' => 'nullable|date',
            'eligibility.*.exam_place' => 'nullable|string|max:255',
            'eligibility.*.license_number' => 'nullable|string|max:255',
            'eligibility.*.license_valid_until' => 'nullable|date',

            'work_experience' => 'nullable|array',
            'work_experience.*.start_date' => 'nullable|date',
            'work_experience.*.end_date' => 'nullable|date',
            'work_experience.*.position' => 'nullable|string|max:255',
            'work_experience.*.company' => 'nullable|string|max:255',
            'work_experience.*.appointment_status' => 'nullable|string|max:255',
            'work_experience.*.is_government' => 'nullable|boolean',

            'voluntary_work' => 'nullable|array',
            'voluntary_work.*.organization_name' => 'nullable|string|max:255',
            'voluntary_work.*.organization_address' => 'nullable|string|max:255',
            'voluntary_work.*.from_date' => 'nullable|date',
            'voluntary_work.*.to_date' => 'nullable|date',
            'voluntary_work.*.number_of_hours' => 'nullable|integer',
            'voluntary_work.*.position' => 'nullable|string|max:255',

            'skills' => 'nullable|array',
            'skills.*.skill' => 'nullable|string|max:255',

            'distinctions' => 'nullable|array',
            'distinctions.*.distinction' => 'nullable|string|max:255',

            'memberships' => 'nullable|array',
            'memberships.*.membership' => 'nullable|string|max:255',

            'references' => 'nullable|array',
            'references.*.name' => 'nullable|string|max:255',
            'references.*.address' => 'nullable|string',
            'references.*.contact' => 'nullable|string|max:255',
        ];
    }

    private function mainFields(): array
    {
        return [
            'last_name', 'first_name', 'middle_name', 'extension_name',
            'birth_date', 'birth_place', 'birth_sex', 'civil_status', 'height', 'weight', 'blood_type',
            'umid_id_number', 'pagibig_number', 'philhealth_number', 'sss_number', 'philsys_number', 'tin_number', 'agency_employee_number',
            'citizenship_type', 'citizenship_mode', 'dual_citizenship_country', 'dual_citizenship_details',
            'res_house_lot', 'res_street', 'res_subdivision', 'res_barangay', 'res_city', 'res_province', 'res_zipcode',
            'perm_house_lot', 'perm_street', 'perm_subdivision', 'perm_barangay', 'perm_city', 'perm_province', 'perm_zipcode',
            'residential_address', 'telephone', 'mobile', 'email_address',
            'spouse_last_name', 'spouse_first_name', 'spouse_middle_name', 'spouse_extension_name', 'spouse_occupation', 'spouse_employer',
            'employer_address', 'spouse_telephone',
            'father_last_name', 'father_first_name', 'father_middle_name', 'father_extension_name',
            'mother_last_name', 'mother_first_name', 'mother_middle_name',
            'related_third_degree', 'related_fourth_degree', 'related_fourth_degree_details',
            'admin_offense', 'admin_offense_details',
            'criminal_case', 'criminal_case_date', 'criminal_case_status',
            'convicted', 'convicted_details',
            'separated_service', 'separated_service_details',
            'election_candidate', 'election_candidate_details',
            'election_resigned', 'election_resigned_details',
            'immigrant', 'immigrant_details',
            'indigenous', 'indigenous_details',
            'pwd', 'pwd_details',
            'solo_parent', 'solo_parent_details',
            'issued_id', 'id_number', 'issue_date', 'issue_place',
        ];
    }

    private function normalizeRows(array $rows, array $fields): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $filtered = Arr::only($row, $fields);
            $hasValue = false;

            foreach ($filtered as $value) {
                if ($value !== null && $value !== '' && $value !== []) {
                    $hasValue = true;
                    break;
                }
            }

            if ($hasValue) {
                $normalized[] = $filtered;
            }
        }

        return $normalized;
    }

    private function createRows(string $modelClass, int $personnelId, int $submissionId, array $rows, array $fields, array $extra = []): void
    {
        $rows = $this->normalizeRows($rows, $fields);

        foreach ($rows as $row) {
            $modelClass::create(array_merge($row, $extra, [
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
            ]));
        }
    }

    private function syncLiveRows(string $modelClass, int $personnelId, array $rows, array $fields, array $extra = []): void
    {
        $rows = $this->normalizeRows($rows, $fields);

        $modelClass::query()
            ->where('personnel_id', $personnelId)
            ->whereNull('submission_id')
            ->delete();

        foreach ($rows as $row) {
            $modelClass::create(array_merge($row, $extra, [
                'personnel_id' => $personnelId,
                'submission_id' => null,
            ]));
        }
    }

    private function encodeJsonArray(array $values): string
    {
        return json_encode(array_values($values), JSON_UNESCAPED_UNICODE);
    }

    private function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function booleanFields(): array
    {
        return [
            'related_third_degree',
            'related_fourth_degree',
            'admin_offense',
            'criminal_case',
            'convicted',
            'separated_service',
            'election_candidate',
            'election_resigned',
            'immigrant',
            'indigenous',
            'pwd',
            'solo_parent',
            'is_government',
        ];
    }

    private function numericComparableFields(): array
    {
        return [
            'height',
            'weight',
            'from_year',
            'to_year',
            'year_graduated',
            'number_of_hours',
        ];
    }

    private function normalizeNumericComparableValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }

        if (!is_numeric($value)) {
            return is_string($value) ? trim($value) : (string) $value;
        }

        $number = (float) $value;

        if (fmod($number, 1.0) == 0.0) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(number_format($number, 10, '.', ''), '0'), '.');
    }

    private function normalizeBooleanComparableValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_int($value)) {
            return $value === 1 ? 1 : 0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes'], true)) {
                return 1;
            }

            if (in_array($normalized, ['0', 'false', 'no'], true)) {
                return 0;
            }
        }

        return (int) ((bool) $value);
    }

    private function normalizeComparableFieldValue(string $field, mixed $value): mixed
    {
        if (in_array($field, $this->booleanFields(), true)) {
            return $this->normalizeBooleanComparableValue($value);
        }

        if (in_array($field, $this->numericComparableFields(), true)) {
            return $this->normalizeNumericComparableValue($value);
        }

        return $this->normalizeComparableValue($value);
    }

    private function normalizeComparableValue(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1) {
                return substr($value, 0, 10);
            }
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }

    private function normalizeRowsForCompare(array $rows, array $fields): array
    {
        $rows = $this->normalizeRows($rows, $fields);
        $normalized = [];

        foreach ($rows as $row) {
            $item = [];
            foreach ($fields as $field) {
                $item[$field] = $this->normalizeComparableFieldValue($field, $row[$field] ?? null);
            }
            $normalized[] = $item;
        }

        usort($normalized, function (array $a, array $b) {
            return strcmp(json_encode($a), json_encode($b));
        });

        return $normalized;
    }

    private function rowsAreEqual(array $leftRows, array $rightRows, array $fields): bool
    {
        return $this->normalizeRowsForCompare($leftRows, $fields) === $this->normalizeRowsForCompare($rightRows, $fields);
    }

    private function buildLiveRows(Personnel $personnel, string $relation, array $fields): array
    {
        return $personnel->{$relation}
            ->map(function ($row) use ($fields) {
                return Arr::only($row->toArray(), $fields);
            })
            ->values()
            ->all();
    }

    private function computeChangedMainFields(array $mainData, ?PdsMain $liveMain): array
    {
        $changed = [];

        foreach ($this->mainFields() as $field) {
            $newValue = $this->normalizeComparableFieldValue($field, $mainData[$field] ?? null);
            $oldValue = $this->normalizeComparableFieldValue($field, optional($liveMain)->{$field} ?? null);

            if ($newValue !== $oldValue) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    private function schemaModelMap(): array
    {
        return [
            'children' => PdsChild::class,
            'education' => PdsEducation::class,
            'eligibility' => PdsEligibility::class,
            'work_experience' => PdsWorkExperience::class,
            'voluntary_work' => PdsVoluntaryWork::class,
            'skills' => PdsSkill::class,
            'distinctions' => PdsDistinction::class,
            'memberships' => PdsMembership::class,
            'references' => PdsReference::class,
        ];
    }

    private function applyDirectChanges(Personnel $personnel, array $validated): void
    {
        $personnelId = (int) $personnel->id;
        $mainData = Arr::only($validated, $this->mainFields());

        PdsMain::updateOrCreate(
            ['personnel_id' => $personnelId, 'submission_id' => null],
            array_merge($mainData, ['personnel_id' => $personnelId, 'submission_id' => null])
        );

        $schemas = $this->listSchemas();
        $modelMap = $this->schemaModelMap();

        foreach ($schemas as $key => $schema) {
            $fields = collect($schema['fields'])->pluck('name')->all();
            $rows = $validated[$key] ?? [];
            $this->syncLiveRows($modelMap[$key], $personnelId, $rows, $fields);
        }
    }

    private function listSchemas(): array
    {
        return [
            'children' => [
                'title' => 'Children',
                'relation' => 'pdsChildren',
                'fields' => [
                    ['name' => 'child_name', 'label' => 'Name', 'type' => 'text'],
                    ['name' => 'birth_date', 'label' => 'Birth Date', 'type' => 'date'],
                ],
            ],
            'education' => [
                'title' => 'Educational Background',
                'relation' => 'pdsEducation',
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
            ],
            'eligibility' => [
                'title' => 'Civil Service Eligibility',
                'relation' => 'pdsEligibility',
                'fields' => [
                    ['name' => 'eligibility', 'label' => 'Eligibility', 'type' => 'text'],
                    ['name' => 'rating', 'label' => 'Rating', 'type' => 'text'],
                    ['name' => 'exam_date', 'label' => 'Exam Date', 'type' => 'date'],
                    ['name' => 'exam_place', 'label' => 'Exam Place', 'type' => 'text'],
                    ['name' => 'license_number', 'label' => 'License Number', 'type' => 'text'],
                    ['name' => 'license_valid_until', 'label' => 'License Valid Until', 'type' => 'date'],
                ],
            ],
            'work_experience' => [
                'title' => 'Work Experience',
                'relation' => 'pdsWorkExperience',
                'fields' => [
                    ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                    ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                    ['name' => 'position', 'label' => 'Position', 'type' => 'text'],
                    ['name' => 'company', 'label' => 'Company', 'type' => 'text'],
                    ['name' => 'appointment_status', 'label' => 'Appointment Status', 'type' => 'text'],
                    ['name' => 'is_government', 'label' => 'Government Service', 'type' => 'boolean'],
                ],
            ],
            'voluntary_work' => [
                'title' => 'Voluntary Work',
                'relation' => 'pdsVoluntaryWork',
                'fields' => [
                    ['name' => 'organization_name', 'label' => 'Organization Name', 'type' => 'text'],
                    ['name' => 'organization_address', 'label' => 'Organization Address', 'type' => 'text'],
                    ['name' => 'from_date', 'label' => 'From Date', 'type' => 'date'],
                    ['name' => 'to_date', 'label' => 'To Date', 'type' => 'date'],
                    ['name' => 'number_of_hours', 'label' => 'Number of Hours', 'type' => 'number'],
                    ['name' => 'position', 'label' => 'Position / Nature of Work', 'type' => 'text'],
                ],
            ],
            'skills' => [
                'title' => 'Skills',
                'relation' => 'pdsSkills',
                'fields' => [
                    ['name' => 'skill', 'label' => 'Skill', 'type' => 'text'],
                ],
            ],
            'distinctions' => [
                'title' => 'Distinctions',
                'relation' => 'pdsDistinctions',
                'fields' => [
                    ['name' => 'distinction', 'label' => 'Distinction', 'type' => 'text'],
                ],
            ],
            'memberships' => [
                'title' => 'Memberships',
                'relation' => 'pdsMemberships',
                'fields' => [
                    ['name' => 'membership', 'label' => 'Membership', 'type' => 'text'],
                ],
            ],
            'references' => [
                'title' => 'References',
                'relation' => 'pdsReferences',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                    ['name' => 'contact', 'label' => 'Contact', 'type' => 'text'],
                ],
            ],
        ];
    }

    public function edit(Personnel $personnel)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasAnyRole(['admin', 'school', 'personnel']), 403);

        $this->assertPersonnelScope($personnel);
        $this->loadLivePds($personnel);

        $schemas = $this->listSchemas();
        $currentRows = [];

        foreach ($schemas as $key => $schema) {
            $fieldNames = collect($schema['fields'])->pluck('name')->all();
            $currentRows[$key] = $personnel->{$schema['relation']}
                ->map(fn ($row) => Arr::only($row->toArray(), $fieldNames))
                ->values()
                ->all();
        }

        $pendingSubmission = null;
        if ($user->hasRole('personnel')) {
            $pendingSubmission = PdsSubmission::query()
                ->where('personnel_id', $personnel->id)
                ->where('submitted_by', $user->id)
                ->where('status', 'SUBMITTED')
                ->latest('id')
                ->first();
        }

        return view('personnel.pds_edit', [
            'personnel' => $personnel,
            'pds' => $personnel->pdsMain,
            'currentRows' => $currentRows,
            'schemas' => $schemas,
            'pendingSubmission' => $pendingSubmission,
        ]);
    }

    public function store(Request $request, Personnel $personnel)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasAnyRole(['admin', 'school', 'personnel']), 403);

        $this->assertPersonnelScope($personnel);
        $this->loadLivePds($personnel);

        $validated = $request->validate($this->validationRules());
        $schemas = $this->listSchemas();
        $schemaModels = $this->schemaModelMap();

        if ($user->hasAnyRole(['admin', 'school'])) {
            DB::transaction(function () use ($validated, $personnel, $user) {
                $this->applyDirectChanges($personnel, $validated);
                ActivityLog::log('UPDATE', 'PDS', "Directly updated PDS for personnel {$personnel->id} by {$user->username}");
            });

            return redirect()->route('personnel.show', $personnel)->with('success', 'PDS was updated directly. No approval needed for admin/school edits.');
        }

        $mainData = Arr::only($validated, $this->mainFields());
        $changedMainFields = $this->computeChangedMainFields($mainData, $personnel->pdsMain);
        $changedSections = [];
        $changedRows = [];

        foreach ($schemas as $key => $schema) {
            $fields = collect($schema['fields'])->pluck('name')->all();
            $incomingRows = $validated[$key] ?? [];
            $liveRows = $this->buildLiveRows($personnel, $schema['relation'], $fields);

            if (!$this->rowsAreEqual($incomingRows, $liveRows, $fields)) {
                $changedSections[] = $key;
                $changedRows[$key] = $incomingRows;
            }
        }

        if (empty($changedMainFields) && empty($changedSections)) {
            throw ValidationException::withMessages([
                'pds' => 'No changes detected. Please update at least one field before submitting.',
            ]);
        }

        DB::transaction(function () use ($personnel, $user, $mainData, $changedMainFields, $changedSections, $changedRows, $schemas, $schemaModels) {
            $submission = PdsSubmission::create([
                'personnel_id' => $personnel->id,
                'version_number' => 1,
                'submitted_at' => now(),
                'submitted_by' => $user->id,
                'status' => 'SUBMITTED',
                'changed_main_fields' => $this->encodeJsonArray($changedMainFields),
                'changed_sections' => $this->encodeJsonArray($changedSections),
            ]);

            if (!empty($changedMainFields)) {
                $snapshotMainData = [
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                ];

                foreach ($changedMainFields as $field) {
                    $snapshotMainData[$field] = $mainData[$field] ?? null;
                }

                PdsMain::create($snapshotMainData);
            }

            foreach ($schemas as $key => $schema) {
                if (!in_array($key, $changedSections, true)) {
                    continue;
                }

                $fields = collect($schema['fields'])->pluck('name')->all();
                $this->createRows($schemaModels[$key], $personnel->id, $submission->id, $changedRows[$key] ?? [], $fields);
            }

            ActivityLog::log('CREATE', 'PDS Edit Request', "Created PDS edit request #{$submission->id} for personnel {$personnel->id}");
        });

        return redirect()->route('pds.requests.index')->with('success', 'PDS edit request submitted for approval.');
    }

    public function requests(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasAnyRole(['admin', 'school', 'personnel']), 403);

        $search = trim((string) $request->input('search'));
        $query = PdsSubmission::query()
            ->with(['personnel.pdsMain:id,personnel_id,last_name,first_name', 'submitter:id,username', 'reviewer:id,username']);

        if ($user->hasRole('personnel')) {
            $query->where('personnel_id', (int) $user->personnel_id);
        } elseif ($user->hasRole('school')) {
            $schoolId = $this->schoolScopeId();
            $query->whereHas('personnel', function (Builder $q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            });
        }

        $query->when($search !== '', function (Builder $q) use ($search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('status', 'like', "%{$search}%")
                    ->orWhereHas('personnel', function (Builder $pq) use ($search) {
                        $pq->where('emp_id', 'like', "%{$search}%")
                            ->orWhereHas('pdsMain', function (Builder $mq) use ($search) {
                                $mq->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                    });
            });
        });

        $requests = $query
            ->orderByRaw("CASE WHEN status = 'SUBMITTED' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('personnel.pds_requests', compact('requests'));
    }

    public function showRequest(PdsSubmission $submission)
    {
        $this->assertSubmissionScope($submission);

        $submission->load([
            'personnel.pdsMain',
            'submitter:id,username',
            'reviewer:id,username',
            'pdsMainSnapshot',
            'childrenSnapshots',
            'educationSnapshots',
            'eligibilitySnapshots',
            'workExperienceSnapshots',
            'voluntaryWorkSnapshots',
            'skillsSnapshots',
            'distinctionsSnapshots',
            'membershipsSnapshots',
            'referencesSnapshots',
        ]);

        $changedMainFields = $this->decodeJsonArray($submission->changed_main_fields);
        $changedSections = $this->decodeJsonArray($submission->changed_sections);

        return view('personnel.pds_request_show', [
            'submission' => $submission,
            'schemas' => $this->listSchemas(),
            'changedMainFields' => $changedMainFields,
            'changedSections' => $changedSections,
        ]);
    }

    private function applySubmission(PdsSubmission $submission, int $reviewerId): void
    {
        $submission->load([
            'pdsMainSnapshot',
            'childrenSnapshots',
            'educationSnapshots',
            'eligibilitySnapshots',
            'workExperienceSnapshots',
            'voluntaryWorkSnapshots',
            'skillsSnapshots',
            'distinctionsSnapshots',
            'membershipsSnapshots',
            'referencesSnapshots',
        ]);

        $personnelId = (int) $submission->personnel_id;
        $changedMainFields = $this->decodeJsonArray($submission->changed_main_fields);
        $changedSections = $this->decodeJsonArray($submission->changed_sections);

        if ($submission->pdsMainSnapshot && !empty($changedMainFields)) {
            $liveMain = PdsMain::query()->firstOrNew([
                'personnel_id' => $personnelId,
                'submission_id' => null,
            ]);

            foreach ($changedMainFields as $field) {
                if (!array_key_exists($field, $submission->pdsMainSnapshot->getAttributes())) {
                    continue;
                }
                $liveMain->{$field} = $submission->pdsMainSnapshot->{$field};
            }

            $liveMain->personnel_id = $personnelId;
            $liveMain->submission_id = null;
            $liveMain->save();
        }

        $sectionMap = [
            'children' => ['model' => PdsChild::class, 'rows' => $submission->childrenSnapshots->toArray(), 'fields' => ['child_name', 'birth_date']],
            'education' => ['model' => PdsEducation::class, 'rows' => $submission->educationSnapshots->toArray(), 'fields' => ['level', 'school_name', 'degree', 'from_year', 'to_year', 'highest_level_units', 'year_graduated', 'honors']],
            'eligibility' => ['model' => PdsEligibility::class, 'rows' => $submission->eligibilitySnapshots->toArray(), 'fields' => ['eligibility', 'rating', 'exam_date', 'exam_place', 'license_number', 'license_valid_until']],
            'work_experience' => ['model' => PdsWorkExperience::class, 'rows' => $submission->workExperienceSnapshots->toArray(), 'fields' => ['start_date', 'end_date', 'position', 'company', 'appointment_status', 'is_government']],
            'voluntary_work' => ['model' => PdsVoluntaryWork::class, 'rows' => $submission->voluntaryWorkSnapshots->toArray(), 'fields' => ['organization_name', 'organization_address', 'from_date', 'to_date', 'number_of_hours', 'position']],
            'skills' => ['model' => PdsSkill::class, 'rows' => $submission->skillsSnapshots->toArray(), 'fields' => ['skill']],
            'distinctions' => ['model' => PdsDistinction::class, 'rows' => $submission->distinctionsSnapshots->toArray(), 'fields' => ['distinction']],
            'memberships' => ['model' => PdsMembership::class, 'rows' => $submission->membershipsSnapshots->toArray(), 'fields' => ['membership']],
            'references' => ['model' => PdsReference::class, 'rows' => $submission->referencesSnapshots->toArray(), 'fields' => ['name', 'address', 'contact']],
        ];

        foreach ($changedSections as $sectionKey) {
            $config = $sectionMap[$sectionKey] ?? null;
            if (!$config) {
                continue;
            }

            $this->syncLiveRows($config['model'], $personnelId, $config['rows'], $config['fields']);
        }
    }

    public function approve(PdsSubmission $submission, Request $request)
    {
        $this->assertCanReviewSubmission($submission);

        $validated = $request->validate([
            'review_remarks' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($submission, $validated) {
            $this->applySubmission($submission, (int) Auth::id());

            $submission->update([
                'status' => 'APPROVED',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_remarks' => $validated['review_remarks'] ?? null,
            ]);

            ActivityLog::log('UPDATE', 'PDS Edit Request', "Approved PDS edit request #{$submission->id}");
        });

        return redirect()->route('pds.requests.show', $submission)->with('success', 'PDS edit request approved and applied.');
    }

    public function reject(PdsSubmission $submission, Request $request)
    {
        $this->assertCanReviewSubmission($submission);

        $validated = $request->validate([
            'review_remarks' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'status' => 'REJECTED',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_remarks' => $validated['review_remarks'] ?? null,
        ]);

        ActivityLog::log('UPDATE', 'PDS Edit Request', "Rejected PDS edit request #{$submission->id}");

        return redirect()->route('pds.requests.show', $submission)->with('success', 'PDS edit request rejected.');
    }

    public function destroy(PdsSubmission $submission)
    {
        $this->assertCanDeleteSubmission($submission);

        $submissionId = (int) $submission->id;
        $personnelId = (int) $submission->personnel_id;

        DB::transaction(function () use ($submission, $submissionId, $personnelId) {
            $submission->delete();

            ActivityLog::log('DELETE', 'PDS Edit Request', "Deleted PDS edit request #{$submissionId} for personnel {$personnelId}");
        });

        return redirect()->route('pds.requests.index')->with('success', 'PDS edit request deleted successfully.');
    }
}
