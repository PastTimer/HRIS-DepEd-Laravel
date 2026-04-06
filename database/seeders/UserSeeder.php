<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use App\Models\Personnel;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'school', 'encoding_officer', 'personnel'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('1234'),
            'email' => 'admin@deped.gov.ph',
            'office' => 'SDO',
            'status' => 'active',
        ]);
        $admin->syncRoles(['admin']);

        // Only 1 school user and 1 encoding officer per school
        $schools = School::all();
        foreach ($schools as $school) {
            $schoolUser = User::create([
                'username' => 'school_user_' . $school->id,
                'password' => Hash::make('1234'),
                'office' => 'SCHOOL',
                'school_id' => $school->id,
                'status' => 'active',
            ]);
            $schoolUser->syncRoles(['school']);

            $encodingOfficer = User::create([
                'username' => 'encoding_officer_' . $school->id,
                'password' => Hash::make('1234'),
                'office' => 'SGOD',
                'school_id' => $school->id,
                'status' => 'active',
            ]);
            $encodingOfficer->syncRoles(['encoding_officer']);
        }

        // Only 1 encoding officer with no school
        $encodingOfficerNoSchool = User::create([
            'username' => 'encoding_officer_noschool',
            'password' => Hash::make('1234'),
            'office' => 'SGOD',
            'school_id' => null,
            'status' => 'active',
        ]);
        $encodingOfficerNoSchool->syncRoles(['encoding_officer']);

        // Optionally, create 1 personnel user (not required by your rule, but kept for completeness)
        $personnelId = Personnel::query()->value('id');
        if ($personnelId) {
            $personnelUser = User::create([
                'username' => 'personnel_user',
                'password' => Hash::make('1234'),
                'office' => 'PERSONNEL',
                'personnel_id' => $personnelId,
                'status' => 'active',
            ]);
            $personnelUser->syncRoles(['personnel']);
        }
    }
}