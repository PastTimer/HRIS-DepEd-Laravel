<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personnel;
use App\Models\PdsMain;
use App\Models\PdsSubmission;
use App\Models\PdsChild;
use App\Models\PdsEducation;
use App\Models\PdsEligibility;
use App\Models\PdsWorkExperience;
use App\Models\PdsVoluntaryWork;
use App\Models\PdsTraining;
use App\Models\PdsReference;
use App\Models\PdsSkill;
use App\Models\PdsDistinction;
use App\Models\PdsMembership;
use Faker\Factory as Faker;

class PdsSeeder extends Seeder

{
    public function run(): void
    {
        $faker = Faker::create();
        $personnelList = Personnel::all();

        foreach ($personnelList as $personnel) {
            // Submission
            $submission = PdsSubmission::create([
                'personnel_id' => $personnel->id,
                'version_number' => 1,
                'submitted_at' => now(),
                'submitted_by' => null,
                'status' => 'SUBMITTED',
                'reviewed_at' => null,
                'reviewed_by' => null,
                'review_remarks' => null,
            ]);

            // Main
            $main = PdsMain::create([
                'personnel_id' => $personnel->id,
                'submission_id' => $submission->id,
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
            ]);

            // Children
            $childrenCount = $faker->numberBetween(1, 3);
            for ($c = 0; $c < $childrenCount; $c++) {
                PdsChild::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'child_name' => $faker->firstName() . ' ' . $faker->lastName(),
                    'birth_date' => $faker->date('Y-m-d', '-18 years'),
                ]);
            }

            // Education
            $educationLevels = ['ELEMENTARY', 'SECONDARY', 'COLLEGE'];
            foreach ($educationLevels as $level) {
                $fromYear = $faker->numberBetween(1990, 2010);
                $toYear = $faker->numberBetween($fromYear, 2024);
                $graduated = $faker->boolean(80); // 80% chance graduated
                PdsEducation::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'level' => $level,
                    'school_name' => $faker->company() . ' School',
                    'degree' => $level === 'COLLEGE' ? $faker->randomElement(['BSEd', 'BSIT', 'BSA', 'BEEd', 'BSCS']) : ($level === 'SECONDARY' ? 'High School Diploma' : null),
                    'from_year' => $fromYear,
                    'to_year' => $toYear,
                    'highest_level_units' => $graduated ? null : $faker->numberBetween(10, 100) . ' units',
                    'year_graduated' => $graduated ? $toYear : null,
                    'honors' => $faker->optional()->randomElement(['With Honors', 'Cum Laude', 'Salutatorian', 'Valedictorian']),
                ]);
            }

            // Eligibility
            $eligibilityCount = $faker->numberBetween(1, 2);
            for ($e = 0; $e < $eligibilityCount; $e++) {
                $licenseValidUntil = $faker->optional()->dateTimeBetween('now', '+5 years');
                PdsEligibility::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'eligibility' => $faker->randomElement(['LET', 'Civil Service Professional', 'N/A', 'PRC', 'BAR']),
                    'rating' => (string) $faker->numberBetween(75, 99),
                    'exam_date' => $faker->date('Y-m-d', '-20 years'),
                    'exam_place' => $faker->city(),
                    'license_number' => $faker->optional()->numerify('LIC-######'),
                    'license_valid_until' => $licenseValidUntil ? $licenseValidUntil->format('Y-m-d H:i:s') : null,
                ]);
            }

            // Work Experience
            $workCount = $faker->numberBetween(1, 4);
            for ($w = 0; $w < $workCount; $w++) {
                PdsWorkExperience::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'start_date' => $faker->date('Y-m-d', '-20 years'),
                    'end_date' => $faker->date('Y-m-d', '-1 year'),
                    'position' => $faker->jobTitle(),
                    'company' => $faker->company(),
                    'appointment_status' => $faker->randomElement(['Permanent', 'Contractual', 'Temporary', 'Substitute']),
                    'is_government' => $faker->boolean(70), // 70% chance government
                ]);
            }

            // Voluntary Work
            $volCount = $faker->numberBetween(1, 2);
            for ($v = 0; $v < $volCount; $v++) {
                PdsVoluntaryWork::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'organization_name' => $faker->company(),
                    'organization_address' => $faker->address(),
                    'from_date' => $faker->date('Y-m-d', '-10 years'),
                    'to_date' => $faker->date('Y-m-d', '-5 years'),
                    'number_of_hours' => $faker->numberBetween(10, 200),
                    'position' => $faker->jobTitle(),
                ]);
            }

            // Training
            $trainingCount = $faker->numberBetween(1, 4);
            for ($t = 0; $t < $trainingCount; $t++) {
                PdsTraining::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'title' => $faker->catchPhrase(),
                    'start_date' => $faker->date('Y-m-d', '-5 years'),
                    'end_date' => $faker->date('Y-m-d', 'now'),
                    'hours' => $faker->numberBetween(4, 80),
                    'type' => $faker->randomElement(['MANAGERIAL', 'SUPERVISORY', 'TECHNICAL']),
                    'sponsor' => $faker->company(),
                    'verification_status' => 'verified',
                ]);
            }

            // VIII. Other Information
            $skillsCount = $faker->numberBetween(1, 3);
            for ($s = 0; $s < $skillsCount; $s++) {
                PdsSkill::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'skill' => $faker->randomElement([
                        'Singing', 'Dancing', 'Painting', 'Cooking', 'Photography', 'Writing', 'Sports', 'Gardening', 'Programming', 'Public Speaking', 'Driving', 'Musical Instrument', 'Crafting', 'Baking', 'Drawing', 'Swimming', 'Chess', 'Blogging', 'Fishing', 'Hiking',
                    ]),
                ]);
            }

            $distinctionsCount = $faker->numberBetween(1, 2);
            for ($d = 0; $d < $distinctionsCount; $d++) {
                PdsDistinction::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'distinction' => $faker->randomElement([
                        'Best Employee', 'Leadership Award', 'Community Service', 'Volunteer Recognition', 'Outstanding Performance', 'Perfect Attendance', 'Innovation Award', 'Top Sales', 'Dean’s Lister', 'Essay Contest Winner', 'Science Fair Winner', 'Art Exhibit Participant', 'Sportsmanship Award', 'Scholarship Recipient', 'Civic Award',
                    ]),
                ]);
            }

            $membershipsCount = $faker->numberBetween(1, 3);
            for ($m = 0; $m < $membershipsCount; $m++) {
                PdsMembership::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'membership' => $faker->randomElement([
                        'Red Cross', 'Rotary Club', 'Chess Club', 'Photography Society', 'Writers Guild', 'Sports Club', 'Music Society', 'Parent-Teacher Association', 'Environmental Group', 'Alumni Association', 'Youth Organization', 'Civic Group', 'Professional Association', 'Volunteer Group', 'Religious Organization',
                    ]),
                ]);
            }

            // References
            $referenceCount = $faker->numberBetween(2, 4);
            for ($r = 0; $r < $referenceCount; $r++) {
                PdsReference::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'name' => $faker->name(),
                    'address' => $faker->address(),
                    'contact' => $faker->phoneNumber(),
                ]);
            }
        }
    }
}
