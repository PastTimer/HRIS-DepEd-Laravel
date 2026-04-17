<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Position;
use App\Models\Personnel;
use App\Models\District; 
use Faker\Factory as Faker;

class PersonnelSeeder extends Seeder
{
    // Creates dummy schools, positions, and personnel.

    public function run(): void
    {
        $faker = Faker::create();

        // 1. Create Divisions and Clusters FIRST
        $divisions = [];
        for ($i = 1; $i <= 2; $i++) {
            $divisions[] = \App\Models\Division::create([
                'name' => 'Division ' . $i
            ]);
        }
        $clusters = [];
        for ($i = 1; $i <= 2; $i++) {
            $clusters[] = \App\Models\Cluster::create([
                'name' => 'Cluster ' . $i
            ]);
        }

        // 2. Create Districts and assign to Division and Cluster
        $districts = [];
        for ($i = 1; $i <= 5; $i++) {
            $division = $divisions[array_rand($divisions)];
            $cluster = $clusters[array_rand($clusters)];
            $districts[] = District::create([
                'name' => 'District ' . $i,
                'division_id' => $division->id,
                'cluster_id' => $cluster->id
            ]);
        }

        // 3. Create 10 Dummy Schools
        $schools = [];
        for ($i = 1; $i <= 10; $i++) {
            $district = $districts[array_rand($districts)];
            $schools[] = School::create([
                'school_id' => '1011' . $faker->unique()->numerify('##'),
                'name' => $faker->city() . ' National High School',
                'district_id' => $district->id,
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

        // 4. Generate 20 Fake Personnel
        for ($i = 0; $i < 20; $i++) {
            $employeeId = $faker->unique()->numerify('######');
            $assignedSchoolId = $schools[array_rand($schools)]->id;
            $deployedSchoolId = $faker->boolean(25) ? $schools[array_rand($schools)]->id : $assignedSchoolId;
            $step = $faker->numberBetween(1, 8);
            $lastStep = $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d');
            $employeeType = $faker->randomElement(['Regular', 'Contractual', 'Substitute']);
            $itemNumber = $faker->unique()->bothify('ITEM-####');
            Personnel::create([
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
        }
    }
}