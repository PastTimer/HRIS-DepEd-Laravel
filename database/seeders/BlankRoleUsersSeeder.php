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
            ->orWhere('school_id', 'HQ-0000')
            ->first();
        if ($hq) {
            return $hq;
        }

        return School::create([
            'school_id' => 'HQ-0000',
            'name' => 'HQ',
            'district_id' => null,
            'is_active' => true,
        ]);
    }

    private function ensurePlaceholderPosition(): Position
    {
        return Position::firstOrCreate(
            ['title' => 'Unassigned Position'],
            [
                'description' => 'Auto-generated placeholder position.',
                'type' => 'Non-teaching',
                'is_active' => true,
            ]
        );
    }

    private function createBlankSchoolRecord(): School
    {
        $counter = 1;
        do {
            $code = 'BLANK-SCHOOL-' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $counter++;
        } while (School::where('school_id', $code)->exists());

        return School::create([
            'school_id' => $code,
            'name' => 'Blank School ' . substr($code, -4),
            'district_id' => null,
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
            $schoolUsername = 'blank_school_user_' . $i;
            $eoUsername = 'blank_eo_user_' . $i;

            $blankSchoolUser = User::where('username', $schoolUsername)->first();
            $blankEoUser = User::where('username', $eoUsername)->first();
            $blankSchool = null;

            if (!$blankSchoolUser || !$blankEoUser) {
                $blankSchool = $this->createBlankSchoolRecord();
                $blankSchoolIds[] = $blankSchool->id;
            } else {
                $blankSchool = School::find($blankSchoolUser ? $blankSchoolUser->school_id : $blankEoUser->school_id);
                $blankSchoolIds[] = $blankSchool->id;
            }

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
            $username = 'blank_personnel_user_' . $i;
            $blankPersonnelUser = User::where('username', $username)->first();
            if (!$blankPersonnelUser) {
                $blankPersonnel = $this->createBlankPersonnelRecord(null);
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
