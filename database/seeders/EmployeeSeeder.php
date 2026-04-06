<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Designation;
use App\Models\Employee;
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

        // 3. Create Standard Designations
        $designations = [];
        $titles = ['Teacher I', 'Teacher II', 'Teacher III', 'Master Teacher I', 'Principal I'];
        foreach ($titles as $title) {
            $designations[] = Designation::create([
                'title' => $title,
                'type' => str_contains($title, 'Principal') ? 'nonteaching' : 'teaching',
                'is_active' => true,
            ]);
        }

        // 4. Generate 50 Fake Employees
        for ($i = 0; $i < 50; $i++) {
            Employee::create([
                'employee_id' => $faker->unique()->numerify('######'),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'gender' => $faker->randomElement(['Male', 'Female']),
                
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'), 
                
                'school_id' => $schools[array_rand($schools)]->id,
                'designation_id' => $designations[array_rand($designations)]->id,
                
                'step' => $faker->numberBetween(1, 8),
                'last_step' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                
                'is_active' => true,
                'employee_type' => 'Regular',
            ]);
        }
    }
}