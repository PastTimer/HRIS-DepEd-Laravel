<?php

namespace Database\Seeders;

use App\Models\PdsChild;
use App\Models\PdsDistinction;
use App\Models\PdsEducation;
use App\Models\PdsEligibility;
use App\Models\PdsMain;
use App\Models\PdsMembership;
use App\Models\PdsReference;
use App\Models\PdsSkill;
use App\Models\PdsSubmission;
use App\Models\PdsTraining;
use App\Models\PdsVoluntaryWork;
use App\Models\PdsWorkExperience;
use App\Models\Personnel;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PdsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('pds_children')->truncate();
        DB::table('pds_education')->truncate();
        DB::table('pds_eligibility')->truncate();
        DB::table('pds_work_experience')->truncate();
        DB::table('pds_voluntary_work')->truncate();
        DB::table('pds_training')->truncate();
        DB::table('pds_skills')->truncate();
        DB::table('pds_distinctions')->truncate();
        DB::table('pds_memberships')->truncate();
        DB::table('pds_references')->truncate();
        DB::table('pds_main')->truncate();
        DB::table('pds_submissions')->truncate();
        DB::statement('PRAGMA foreign_keys = ON;');

        $personnelList = Personnel::all();

        foreach ($personnelList as $personnel) {
            // Ensure each personnel has a user (if not, create one)
            $user = User::where('personnel_id', $personnel->id)->first();
            if (!$user) {
                $user = User::create([
                    'username' => 'personnel_' . $personnel->id,
                    'password' => bcrypt('1234'),
                    'office' => 'PERSONNEL',
                    'personnel_id' => $personnel->id,
                    'status' => 'active',
                ]);
            }
            $liveMain = $this->buildMainData($faker);

            PdsMain::create(array_merge($liveMain, [
                'personnel_id' => $personnel->id,
                'submission_id' => null,
            ]));

            $this->seedChildren($faker, $personnel->id, null);
            $this->seedEducation($faker, $personnel->id, null);
            $this->seedEligibility($faker, $personnel->id, null);
            $this->seedWorkExperience($faker, $personnel->id, null);
            $this->seedVoluntaryWork($faker, $personnel->id, null);
            $this->seedOtherInfo($faker, $personnel->id, null);
            $this->seedReferences($faker, $personnel->id, null);
            $this->seedTraining($faker, $personnel->id, null);

            if (!$faker->boolean(70)) {
                continue;
            }

            $status = $faker->randomElement(['SUBMITTED', 'APPROVED']);
            $submittedAt = now();
            $reviewedAt = $status === 'APPROVED' ? (clone $submittedAt)->addDays($faker->numberBetween(1, 5)) : null;

            $allSectionKeys = ['children', 'education', 'eligibility', 'work_experience', 'voluntary_work', 'skills', 'distinctions', 'memberships', 'references'];
            $changedSections = $faker->randomElements($allSectionKeys, $faker->numberBetween(1, 3));

            $mainCandidates = ['mobile', 'email_address', 'telephone', 'residential_address', 'civil_status'];
            $changedMainFields = $faker->boolean(40)
                ? $faker->randomElements($mainCandidates, $faker->numberBetween(1, 3))
                : [];

            // Try to use the personnel's user_id as submitted_by if available, else null
            $submittedBy = $user->id;

            $submission = PdsSubmission::create([
                'personnel_id' => $personnel->id,
                'version_number' => 1,
                'submitted_at' => $submittedAt,
                'submitted_by' => $submittedBy,
                'status' => $status,
                'reviewed_at' => $reviewedAt,
                'reviewed_by' => null,
                'review_remarks' => $status === 'APPROVED' ? 'Seeded approved request sample.' : null,
                'changed_main_fields' => json_encode(array_values($changedMainFields), JSON_UNESCAPED_UNICODE),
                'changed_sections' => json_encode(array_values($changedSections), JSON_UNESCAPED_UNICODE),
            ]);

            if (!empty($changedMainFields)) {
                $snapshot = [
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                ];

                foreach ($changedMainFields as $field) {
                    $snapshot[$field] = $this->mutateMainFieldValue($faker, $field, $liveMain[$field] ?? null);
                }

                PdsMain::create($snapshot);
            }

            foreach ($changedSections as $sectionKey) {
                switch ($sectionKey) {
                    case 'children':
                        $this->seedChildren($faker, $personnel->id, $submission->id);
                        break;
                    case 'education':
                        $this->seedEducation($faker, $personnel->id, $submission->id);
                        break;
                    case 'eligibility':
                        $this->seedEligibility($faker, $personnel->id, $submission->id);
                        break;
                    case 'work_experience':
                        $this->seedWorkExperience($faker, $personnel->id, $submission->id);
                        break;
                    case 'voluntary_work':
                        $this->seedVoluntaryWork($faker, $personnel->id, $submission->id);
                        break;
                    case 'skills':
                    case 'distinctions':
                    case 'memberships':
                        $this->seedOtherInfo($faker, $personnel->id, $submission->id, $sectionKey);
                        break;
                    case 'references':
                        $this->seedReferences($faker, $personnel->id, $submission->id);
                        break;
                }
            }
        }
    }

    private function buildMainData($faker): array
    {
        return [
            'last_name' => $faker->lastName(),
            'first_name' => $faker->firstName(),
            'middle_name' => $faker->optional()->firstName(),
            'extension_name' => $faker->optional()->randomElement(['Jr.', 'III', null]),
            'birth_date' => $faker->date('Y-m-d', '-20 years'),
            'birth_place' => $faker->city(),
            'birth_sex' => $faker->randomElement(['MALE', 'FEMALE']),
            'civil_status' => $faker->randomElement(['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED']),
            'height' => $faker->randomFloat(2, 1.4, 2.0),
            'weight' => $faker->randomFloat(2, 40, 100),
            'blood_type' => $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'umid_id_number' => $faker->numerify('##########'),
            'pagibig_number' => $faker->numerify('############'),
            'philhealth_number' => $faker->numerify('############'),
            'sss_number' => $faker->numerify('##-#######-#'),
            'philsys_number' => $faker->unique()->numerify('#############'),
            'tin_number' => $faker->unique()->numerify('###-###-###-###'),
            'agency_employee_number' => $faker->unique()->numerify('######'),
            'citizenship_type' => 'FILIPINO',
            'citizenship_mode' => null,
            'dual_citizenship_country' => null,
            'dual_citizenship_details' => null,
            'res_house_lot' => $faker->buildingNumber(),
            'res_street' => $faker->streetName(),
            'res_subdivision' => $faker->optional()->word(),
            'res_barangay' => 'Brgy. ' . $faker->word(),
            'res_city' => $faker->city(),
            'res_province' => $faker->state(),
            'res_zipcode' => $faker->postcode(),
            'perm_house_lot' => $faker->buildingNumber(),
            'perm_street' => $faker->streetName(),
            'perm_subdivision' => $faker->optional()->word(),
            'perm_barangay' => 'Brgy. ' . $faker->word(),
            'perm_city' => $faker->city(),
            'perm_province' => $faker->state(),
            'perm_zipcode' => $faker->postcode(),
            'residential_address' => $faker->address(),
            'telephone' => $faker->optional()->phoneNumber(),
            'mobile' => $faker->numerify('09#########'),
            'email_address' => $faker->unique()->safeEmail(),
            'spouse_last_name' => $faker->optional()->lastName(),
            'spouse_first_name' => $faker->optional()->firstName(),
            'spouse_middle_name' => $faker->optional()->firstName(),
            'spouse_extension_name' => $faker->optional()->randomElement(['Jr.', 'III', null]),
            'spouse_occupation' => $faker->optional()->jobTitle(),
            'spouse_employer' => $faker->optional()->company(),
            'employer_address' => $faker->optional()->address(),
            'spouse_telephone' => $faker->optional()->phoneNumber(),
            'father_last_name' => $faker->lastName(),
            'father_first_name' => $faker->firstName(),
            'father_middle_name' => $faker->optional()->firstName(),
            'father_extension_name' => $faker->optional()->randomElement(['Jr.', 'III', null]),
            'mother_last_name' => $faker->lastName(),
            'mother_first_name' => $faker->firstName(),
            'mother_middle_name' => $faker->optional()->firstName(),
            'related_third_degree' => $faker->boolean(),
            'related_fourth_degree' => $faker->boolean(),
            'related_fourth_degree_details' => $faker->optional()->sentence(),
            'admin_offense' => $faker->boolean(),
            'admin_offense_details' => $faker->optional()->sentence(),
            'criminal_case' => $faker->boolean(),
            'criminal_case_date' => $faker->optional()->date('Y-m-d', '-10 years'),
            'criminal_case_status' => $faker->optional()->randomElement(['Pending', 'Dismissed', 'Resolved', 'Acquitted', 'Convicted', null]),
            'convicted' => $faker->boolean(),
            'convicted_details' => $faker->optional()->sentence(),
            'separated_service' => $faker->boolean(),
            'separated_service_details' => $faker->optional()->sentence(),
            'election_candidate' => $faker->boolean(),
            'election_candidate_details' => $faker->optional()->sentence(),
            'election_resigned' => $faker->boolean(),
            'election_resigned_details' => $faker->optional()->sentence(),
            'immigrant' => $faker->boolean(),
            'immigrant_details' => $faker->optional()->sentence(),
            'indigenous' => $faker->boolean(),
            'indigenous_details' => $faker->optional()->sentence(),
            'pwd' => $faker->boolean(),
            'pwd_details' => $faker->optional()->sentence(),
            'solo_parent' => $faker->boolean(),
            'solo_parent_details' => $faker->optional()->sentence(),
            'issued_id' => $faker->optional()->word(),
            'id_number' => $faker->optional()->numerify('ID#######'),
            'issue_date' => $faker->optional()->date('Y-m-d'),
            'issue_place' => $faker->optional()->city(),
        ];
    }

    private function mutateMainFieldValue($faker, string $field, mixed $oldValue): mixed
    {
        return match ($field) {
            'mobile' => $faker->numerify('09#########'),
            'email_address' => $faker->unique()->safeEmail(),
            'telephone' => $faker->optional()->phoneNumber(),
            'residential_address' => $faker->address(),
            'civil_status' => $faker->randomElement(['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED']),
            default => $oldValue,
        };
    }

    private function seedChildren($faker, int $personnelId, ?int $submissionId): void
    {
        $count = $faker->numberBetween(1, 3);
        for ($i = 0; $i < $count; $i++) {
            PdsChild::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'child_name' => $faker->firstName() . ' ' . $faker->lastName(),
                'birth_date' => $faker->date('Y-m-d', '-18 years'),
            ]);
        }
    }

    private function seedEducation($faker, int $personnelId, ?int $submissionId): void
    {
        $levels = ['ELEMENTARY', 'SECONDARY', 'COLLEGE'];
        foreach ($levels as $level) {
            $fromYear = $faker->numberBetween(1990, 2012);
            $toYear = $faker->numberBetween($fromYear, 2025);
            $graduated = $faker->boolean(80);

            PdsEducation::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'level' => $level,
                'school_name' => $faker->company() . ' School',
                'degree' => $level === 'COLLEGE' ? $faker->randomElement(['BSEd', 'BSIT', 'BSA', 'BEEd', 'BSCS']) : ($level === 'SECONDARY' ? 'High School Diploma' : null),
                'from_year' => $fromYear,
                'to_year' => $toYear,
                'highest_level_units' => $graduated ? null : $faker->numberBetween(10, 120) . ' units',
                'year_graduated' => $graduated ? $toYear : null,
                'honors' => $faker->optional()->randomElement(['With Honors', 'Cum Laude', 'Salutatorian', 'Valedictorian']),
            ]);
        }
    }

    private function seedEligibility($faker, int $personnelId, ?int $submissionId): void
    {
        $count = $faker->numberBetween(1, 2);
        for ($i = 0; $i < $count; $i++) {
            $validUntil = $faker->optional()->dateTimeBetween('now', '+5 years');
            PdsEligibility::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'eligibility' => $faker->randomElement(['LET', 'Civil Service Professional', 'PRC', 'BAR']),
                'rating' => (string) $faker->numberBetween(75, 99),
                'exam_date' => $faker->date('Y-m-d', '-20 years'),
                'exam_place' => $faker->city(),
                'license_number' => $faker->optional()->numerify('LIC-######'),
                'license_valid_until' => $validUntil ? $validUntil->format('Y-m-d H:i:s') : null,
            ]);
        }
    }

    private function seedWorkExperience($faker, int $personnelId, ?int $submissionId): void
    {
        $count = $faker->numberBetween(1, 4);
        for ($i = 0; $i < $count; $i++) {
            PdsWorkExperience::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'start_date' => $faker->date('Y-m-d', '-20 years'),
                'end_date' => $faker->date('Y-m-d', '-1 year'),
                'position' => $faker->jobTitle(),
                'company' => $faker->company(),
                'appointment_status' => $faker->randomElement(['Permanent', 'Contractual', 'Temporary', 'Substitute']),
                'is_government' => $faker->boolean(70),
            ]);
        }
    }

    private function seedVoluntaryWork($faker, int $personnelId, ?int $submissionId): void
    {
        $count = $faker->numberBetween(1, 2);
        for ($i = 0; $i < $count; $i++) {
            PdsVoluntaryWork::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'organization_name' => $faker->company(),
                'organization_address' => $faker->address(),
                'from_date' => $faker->date('Y-m-d', '-10 years'),
                'to_date' => $faker->date('Y-m-d', '-5 years'),
                'number_of_hours' => $faker->numberBetween(10, 200),
                'position' => $faker->jobTitle(),
            ]);
        }
    }

    private function seedOtherInfo($faker, int $personnelId, ?int $submissionId, ?string $onlySection = null): void
    {
        if ($onlySection === null || $onlySection === 'skills') {
            $skillsCount = $faker->numberBetween(1, 3);
            for ($i = 0; $i < $skillsCount; $i++) {
                PdsSkill::create([
                    'personnel_id' => $personnelId,
                    'submission_id' => $submissionId,
                    'skill' => $faker->randomElement(['Singing', 'Dancing', 'Painting', 'Cooking', 'Photography', 'Writing', 'Sports', 'Gardening', 'Programming', 'Public Speaking']),
                ]);
            }
        }

        if ($onlySection === null || $onlySection === 'distinctions') {
            $distinctionsCount = $faker->numberBetween(1, 2);
            for ($i = 0; $i < $distinctionsCount; $i++) {
                PdsDistinction::create([
                    'personnel_id' => $personnelId,
                    'submission_id' => $submissionId,
                    'distinction' => $faker->randomElement(['Best Employee', 'Leadership Award', 'Community Service', 'Outstanding Performance', 'Perfect Attendance']),
                ]);
            }
        }

        if ($onlySection === null || $onlySection === 'memberships') {
            $membershipsCount = $faker->numberBetween(1, 3);
            for ($i = 0; $i < $membershipsCount; $i++) {
                PdsMembership::create([
                    'personnel_id' => $personnelId,
                    'submission_id' => $submissionId,
                    'membership' => $faker->randomElement(['Red Cross', 'Rotary Club', 'Chess Club', 'Photography Society', 'Professional Association']),
                ]);
            }
        }
    }

    private function seedReferences($faker, int $personnelId, ?int $submissionId): void
    {
        $count = $faker->numberBetween(2, 4);
        for ($i = 0; $i < $count; $i++) {
            PdsReference::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'name' => $faker->name(),
                'address' => $faker->address(),
                'contact' => $faker->phoneNumber(),
            ]);
        }
    }

    private function seedTraining($faker, int $personnelId, ?int $submissionId): void
    {
        $count = $faker->numberBetween(1, 4);
        for ($i = 0; $i < $count; $i++) {
            $verificationStatus = $faker->randomElement(['pending', 'verified', 'rejected']);
            PdsTraining::create([
                'personnel_id' => $personnelId,
                'submission_id' => $submissionId,
                'title' => $faker->catchPhrase(),
                'start_date' => $faker->date('Y-m-d', '-5 years'),
                'end_date' => $faker->date('Y-m-d', 'now'),
                'hours' => $faker->numberBetween(4, 80),
                'type' => $faker->randomElement(['MANAGERIAL', 'SUPERVISORY', 'TECHNICAL']),
                'sponsor' => $faker->company(),
                'created_by' => null,
                'verification_status' => $verificationStatus,
                'verified_by' => $verificationStatus === 'verified' ? null : null,
                'verified_at' => $verificationStatus === 'verified' ? now()->subDays($faker->numberBetween(1, 60)) : null,
                'rejection_reason' => $verificationStatus === 'rejected' ? 'Seeded sample rejection reason.' : null,
            ]);
        }
    }
}
