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

    private function createBlankPersonnelRecord(int $schoolId): Personnel
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

        $blankSchoolUser = User::where('username', 'blank_school_user')->first();
        if (!$blankSchoolUser) {
            $blankSchool = $this->createBlankSchoolRecord();
            $blankSchoolUser = User::create([
                'username' => 'blank_school_user',
                'password' => Hash::make('1234'),
                'school_id' => $blankSchool->id,
                'status' => 'active',
            ]);
        }
        $blankSchoolUser->syncRoles(['school']);

        $blankEoUser = User::where('username', 'blank_eo_user')->first();
        if (!$blankEoUser) {
            $blankEoUser = User::create([
                'username' => 'blank_eo_user',
                'password' => Hash::make('1234'),
                'school_id' => $hq->id,
                'status' => 'active',
            ]);
        }
        $blankEoUser->syncRoles(['encoding_officer']);

        $blankPersonnelUser = User::where('username', 'blank_personnel_user')->first();
        if (!$blankPersonnelUser) {
            $blankPersonnel = $this->createBlankPersonnelRecord($hq->id);
            $blankPersonnelUser = User::create([
                'username' => 'blank_personnel_user',
                'password' => Hash::make('1234'),
                'personnel_id' => $blankPersonnel->id,
                'status' => 'active',
            ]);
        }
        $blankPersonnelUser->syncRoles(['personnel']);
    }
}
