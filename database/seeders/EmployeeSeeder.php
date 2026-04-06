<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Position;
use App\Models\Personnel;
use App\Models\PdsMain;
use App\Models\PdsSubmission;
use App\Models\PdsChild;
use App\Models\PdsEducation;
use App\Models\PdsEligibility;
use App\Models\PdsWorkExperience;
use App\Models\PdsTraining;
use App\Models\PdsReference;
use App\Models\District; 
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Create Districts FIRST 
        $districts = [];
        for ($i = 1; $i <= 5; $i++) {
            $districts[] = District::create([
                'name' => 'District ' . $i
            ]);
        }

        // 2. Create 5 Dummy Schools
        $schools = [];
        for ($i = 1; $i <= 5; $i++) {
            $schools[] = School::create([
                'school_id' => '1011' . $faker->unique()->numerify('##'),
                'name' => $faker->city() . ' National High School',
                'district_id' => $districts[array_rand($districts)]->id,
                'governance_level' => $faker->randomElement(['Elementary', 'Secondary', 'Integrated']),
                'ro' => 'RO ' . $faker->randomElement(['I', 'II', 'III', 'IV-A', 'NCR']),
                'sdo' => 'SDO ' . $faker->citySuffix(),
                'address_street' => $faker->streetAddress(),
                'address_barangay' => 'Brgy. ' . $faker->word(),
                'address_city' => $faker->city(),
                'address_province' => $faker->state(),
                'psgc' => $faker->numerify('#######'),
                'coordinates_lat' => $faker->latitude(),
                'coordinates_long' => $faker->longitude(),
                'travel_time_min' => $faker->numberBetween(5, 120),
                'access_paths' => 'Paved, Dirt',
                'contact_mobile1' => $faker->phoneNumber(),
                'contact_mobile2' => $faker->phoneNumber(),
                'contact_landline' => $faker->phoneNumber(),
                'head_name' => $faker->name(),
                'head_position' => $faker->randomElement(['Principal', 'OIC', 'Head Teacher']),
                'head_email' => $faker->safeEmail(),
                'admin_name' => $faker->name(),
                'admin_mobile' => $faker->phoneNumber(),
                'nearby_institutions' => 'Barangay Hall, Police Station',
                'notes' => $faker->sentence(),
                'is_active' => true,
            ]);
        }

        // 3. Create All Positions (from hr10.sql)
        $positionData = [
            ['Accountant I', '', 'Non-teaching'],
            ['Accountant III', '', 'Non-teaching'],
            ['Administrative Aide I', '', 'Non-teaching'],
            ['Administrative Aide III', '', 'Non-teaching'],
            ['Administrative Aide IV', '', 'Non-teaching'],
            ['Administrative Aide VI', '', 'Non-teaching'],
            ['Administrative Assistant I', '', 'Non-teaching'],
            ['Administrative Assistant II', '', 'Non-teaching'],
            ['Administrative Assistant III', '', 'Non-teaching'],
            ['Administrative Officer I', '', 'Non-teaching'],
            ['Administrative Officer II', '', 'Non-teaching'],
            ['Administrative Officer IV', '', 'Non-teaching'],
            ['Administrative Officer V', '', 'Non-teaching'],
            ['Assistant Principal II', '', 'Non-teaching'],
            ['Assistant Schools Division Superintendent', '', 'Non-teaching'],
            ['Attorney III', '', 'Non-teaching'],
            ['Chief Education Supervisor', '', 'Non-teaching'],
            ['Dentist II', '', 'Non-teaching'],
            ['Education Program Specialist II', '', 'Non-teaching'],
            ['Education Program Supervisor', '', 'Non-teaching'],
            ['Engineer III', '', 'Non-teaching'],
            ['Guidance Coordinator III', '', 'Non-teaching'],
            ['Guidance Counselor I', '', 'Non-teaching'],
            ['Guidance Counselor II', '', 'Non-teaching'],
            ['Guidance Counselor III', '', 'Non-teaching'],
            ['Head Teacher II', '', 'Non-teaching'],
            ['Head Teacher III', '', 'Non-teaching'],
            ['Head Teacher IV', '', 'Non-teaching'],
            ['Head Teacher VI', '', 'Non-teaching'],
            ['Information Technology Officer I', '', 'Non-teaching'],
            ['Librarian II', '', 'Non-teaching'],
            ['Master Teacher I', '', 'Teaching'],
            ['Master Teacher II', '', 'Teaching'],
            ['Medical Officer III', '', 'Non-teaching'],
            ['Nurse II', '', 'Non-teaching'],
            ['Planning Officer III', '', 'Non-teaching'],
            ['Principal I', '', 'Non-teaching'],
            ['Principal II', '', 'Non-teaching'],
            ['Principal III', '', 'Non-teaching'],
            ['Principal IV', '', 'Non-teaching'],
            ['Project Development Officer II', '', 'Non-teaching'],
            ['Project Development OfficerI', '', 'Non-teaching'],
            ['Public Schools District Supervisor', '', 'Non-teaching'],
            ['Registrar 1', '', 'Non-teaching'],
            ['School Librarian', '', 'Non-teaching'],
            ['Schools Division Superintendent', '', 'Non-teaching'],
            ['Security Guard I', '', 'Non-teaching'],
            ['Security Guard II', '', 'Non-teaching'],
            ['Senior Education Program Specialist ', '', 'Non-teaching'],
            ['Special Science Teacher I', '', 'Teaching'],
            ['SPET I', '', 'Teaching'],
            ['SPET II', '', 'Teaching'],
            ['SPET III', '', 'Teaching'],
            ['Teacher I', '', 'Teaching'],
            ['Teacher II', '', 'Teaching'],
            ['Teacher III', '', 'Teaching'],
        ];
        $positions = [];
        foreach ($positionData as $row) {
            $positions[] = Position::create([
                'title' => $row[0],
                'description' => $row[1],
                'type' => $row[2],
                'is_active' => true,
            ]);
        }

        // 4. Generate 50 Fake Personnel + linked PDS records
        for ($i = 0; $i < 50; $i++) {
            $employeeId = $faker->unique()->numerify('######');
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $middleName = $faker->optional(0.7)->firstName();
            $nameExt = $faker->randomElement([null, 'Jr.', 'III']);
            $gender = $faker->randomElement(['Male', 'Female']);
            $birthDate = $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d');
            $placeOfBirth = $faker->city();
            $civilStatus = $faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']);
            $bloodType = $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);

            $assignedSchoolId = $schools[array_rand($schools)]->id;
            $deployedSchoolId = $faker->boolean(25) ? $schools[array_rand($schools)]->id : $assignedSchoolId;

            $step = $faker->numberBetween(1, 8);
            $lastStep = $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d');
            $employeeType = $faker->randomElement(['Regular', 'Contractual', 'Substitute']);

            $gsisNo = $faker->numerify('##########');
            $pagibigNo = $faker->numerify('############');
            $philhealthNo = $faker->numerify('############');
            $sssNo = $faker->numerify('##-#######-#');
            $tinNo = $faker->unique()->numerify('###-###-###-###');

            $contactNo = $faker->numerify('09#########');
            $emailAddress = $faker->unique()->safeEmail();
            $residentialAddress = $faker->address();

            // Guarantee unique item_number for all personnel
            $itemNumber = $faker->unique()->bothify('ITEM-####');

            $personnel = Personnel::create([
                'emp_id' => $employeeId,
                'assigned_school_id' => $assignedSchoolId,
                'deployed_school_id' => $deployedSchoolId,
                'position_id' => $positions[array_rand($positions)]->id,
                'item_number' => $itemNumber,
                'current_step' => $step,
                'last_step_increment_date' => $lastStep,
                'employee_type' => $employeeType,
                'profile_photo' => null,
                'is_active' => $faker->boolean(90),
            ]);

            $submission = PdsSubmission::create([
                'personnel_id' => $personnel->id,
                'version_number' => 1,
                'submitted_at' => now(),
                'status' => 'SUBMITTED',
            ]);

            PdsMain::create([
                'personnel_id' => $personnel->id,
                'submission_id' => $submission->id,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'extension_name' => $nameExt,
                'birth_date' => $birthDate,
                'birth_place' => $placeOfBirth,
                'birth_sex' => strtoupper($gender),
                'civil_status' => strtoupper($civilStatus),
                'blood_type' => $bloodType,
                'umid_id_number' => $gsisNo,
                'pagibig_number' => $pagibigNo,
                'philhealth_number' => $philhealthNo,
                'sss_number' => $sssNo,
                'tin_number' => $tinNo,
                'agency_employee_number' => $employeeId,
                'mobile' => $contactNo,
                'email_address' => $emailAddress,
                'residential_address' => $residentialAddress,
            ]);

            $childrenCount = $faker->numberBetween(0, 3);
            for ($c = 0; $c < $childrenCount; $c++) {
                PdsChild::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'child_name' => $faker->name(),
                    'birth_date' => $faker->dateTimeBetween('-18 years', '-1 year')->format('Y-m-d'),
                ]);
            }

            $educationLevels = ['ELEMENTARY', 'SECONDARY', 'COLLEGE'];
            foreach ($educationLevels as $level) {
                PdsEducation::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'level' => $level,
                    'school_name' => $faker->company() . ' School',
                    'degree' => $level === 'COLLEGE' ? $faker->randomElement(['BSEd', 'BSIT', 'BSA']) : null,
                    'from_year' => $faker->numberBetween(1990, 2010),
                    'to_year' => $faker->numberBetween(2011, 2024),
                    'honors' => $faker->optional(0.2)->randomElement(['With Honors', 'Cum Laude']),
                ]);
            }

            if ($faker->boolean(70)) {
                PdsEligibility::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'eligibility' => $faker->randomElement(['LET', 'Civil Service Professional', 'N/A']),
                    'rating' => (string) $faker->numberBetween(75, 95),
                    'exam_date' => $faker->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
                    'exam_place' => $faker->city(),
                    'license_number' => $faker->numerify('LIC-######'),
                    'license_valid_until' => $faker->dateTimeBetween('now', '+5 years')->format('Y-m-d H:i:s'),
                ]);
            }

            $workCount = $faker->numberBetween(1, 3);
            for ($w = 0; $w < $workCount; $w++) {
                PdsWorkExperience::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'start_date' => $faker->dateTimeBetween('-20 years', '-5 years')->format('Y-m-d'),
                    'end_date' => $faker->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
                    'position' => $faker->jobTitle(),
                    'company' => $faker->company(),
                    'appointment_status' => $faker->randomElement(['Permanent', 'Contractual', 'Temporary']),
                ]);
            }

            $pdsTrainingCount = $faker->numberBetween(1, 3);
            for ($t = 0; $t < $pdsTrainingCount; $t++) {
                PdsTraining::create([
                    'personnel_id' => $personnel->id,
                    'submission_id' => $submission->id,
                    'title' => $faker->catchPhrase(),
                    'start_date' => $faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
                    'end_date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'hours' => $faker->numberBetween(4, 40),
                    'type' => $faker->randomElement(['MANAGERIAL', 'SUPERVISORY', 'TECHNICAL']),
                    'sponsor' => $faker->company(),
                ]);
            }

            $referenceCount = $faker->numberBetween(1, 3);
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