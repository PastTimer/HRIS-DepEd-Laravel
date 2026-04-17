<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\Position;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BlankRoleUsersSeeder extends Seeder
{
    private function ensureHqSchool(): School
    {
        $hq = School::where('name', 'HQ')
            ->orWhere('school_id', 'HQ')
            ->first();
        if ($hq) {
            return $hq;
        }

        // Create Division, Cluster, District for HQ if not exist
        $division = \App\Models\Division::firstOrCreate(['name' => 'HQ Division']);
        $cluster = \App\Models\Cluster::firstOrCreate(['name' => 'HQ Cluster']);
        $district = \App\Models\District::firstOrCreate(
            ['name' => 'HQ District'],
            ['division_id' => $division->id, 'cluster_id' => $cluster->id]
        );
        // Ensure district has correct division/cluster
        $district->division_id = $division->id;
        $district->cluster_id = $cluster->id;
        $district->save();

        return School::create([
            'school_id' => 'HQ',
            'name' => 'HQ',
            'district_id' => $district->id,
            'address_street' => 'HQ Street',
            'address_city' => 'HQ City',
            'address_province' => 'HQ Province',
            'is_active' => true,
        ]);
    }

    private function ensurePlaceholderPosition(): Position
    {
        return Position::firstOrCreate(
            ['title' => 'Unassigned Position'],
            [
                'description' => '-',
                'type' => 'Non-teaching',
                'is_active' => true,
            ]
        );
    }

    private function createBlankSchoolRecord(): School
    {
        $counter = 1;
        do {
            $id = str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $code = 'SCHOOL-' . $id;
            $counter++;
        } while (School::where('school_id', $code)->exists());

        return School::create([
            'school_id' => $code,
            'name' => 'School ' . $id,
            'is_active' => true,
        ]);
    }

    private function createBlankPersonnelRecord(?int $schoolId = null): Personnel
    {
        $position = $this->ensurePlaceholderPosition();

        return Personnel::create([
            'position_id' => $position->id,
            'assigned_school_id' => $schoolId,
            'deployed_school_id' => $schoolId,
            'is_active' => true,
            'current_step' => 1,
            'last_step_increment_date' => now()->toDateString(),
            'employee_type' => 'Regular',
            'salary_grade' => null,
            'salary_actual' => null,
            'branch' => null,
            'emp_id' => null,
            'item_number' => null,
            'profile_photo' => null,
        ]);
    }

    public function run(): void
    {
        foreach (['school', 'encoding_officer', 'personnel'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $hq = $this->ensureHqSchool();

        // Create 5 blank schools and users, and 5 blank encoding officers (one per blank school)
        $blankSchoolIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $blankSchool = null;
            $blankSchool = $this->createBlankSchoolRecord();
            $blankSchoolIds[] = $blankSchool->id;

            // Use real-like username patterns
            $schoolUsername = 'school_' . strtolower($blankSchool->school_id) . '_user';
            $eoUsername = 'eo_' . strtolower($blankSchool->school_id) . '_user';

            $blankSchoolUser = User::where('username', $schoolUsername)->first();
            $blankEoUser = User::where('username', $eoUsername)->first();

            if (!$blankSchoolUser) {
                $blankSchoolUser = User::create([
                    'username' => $schoolUsername,
                    'password' => Hash::make('1234'),
                    'school_id' => $blankSchool->id,
                    'status' => 'active',
                ]);
            }
            $blankSchoolUser->syncRoles(['school']);

            if (!$blankEoUser) {
                $blankEoUser = User::create([
                    'username' => $eoUsername,
                    'password' => Hash::make('1234'),
                    'school_id' => $blankSchool->id,
                    'status' => 'active',
                ]);
            }
            $blankEoUser->syncRoles(['encoding_officer']);
        }

        // Create 10 blank personnel and users (no station/school)
        for ($i = 1; $i <= 10; $i++) {
            $blankPersonnel = $this->createBlankPersonnelRecord(null);
            $username = 'personnel_' . $blankPersonnel->id . '_user';
            $blankPersonnelUser = User::where('username', $username)->first();
            if (!$blankPersonnelUser) {
                $blankPersonnelUser = User::create([
                    'username' => $username,
                    'password' => Hash::make('1234'),
                    'personnel_id' => $blankPersonnel->id,
                    'status' => 'active',
                ]);
            }
            $blankPersonnelUser->syncRoles(['personnel']);
        }
    }
}
