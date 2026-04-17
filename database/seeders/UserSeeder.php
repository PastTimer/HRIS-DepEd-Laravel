<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    //  Creates default users for each role and assigns them to schools and personnel as needed.
    
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

    public function run(): void
    {
        foreach (['admin', 'school', 'encoding_officer', 'personnel'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $hq = $this->ensureHqSchool();

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('1234'),
                'email' => 'admin@deped.gov.ph',
                'school_id' => $hq->id,
                'status' => 'active',
            ]
        );
        $admin->syncRoles(['admin']);

        $schools = School::orderBy('id')->get();
        foreach ($schools as $school) {
            $schoolUser = User::firstOrCreate(
                ['username' => 'school_user_' . $school->id],
                [
                    'password' => Hash::make('1234'),
                    'school_id' => $school->id,
                    'status' => 'active',
                ]
            );
            $schoolUser->syncRoles(['school']);

            $encodingOfficer = User::firstOrCreate(
                ['username' => 'encoding_officer_' . $school->id],
                [
                    'password' => Hash::make('1234'),
                    'school_id' => $school->id,
                    'status' => 'active',
                ]
            );
            $encodingOfficer->syncRoles(['encoding_officer']);
        }

        $personnelId = Personnel::query()->value('id');
        if ($personnelId) {
            $personnelUser = User::firstOrCreate(
                ['username' => 'personnel_user'],
                [
                    'password' => Hash::make('1234'),
                    'personnel_id' => $personnelId,
                    'status' => 'active',
                ]
            );
            $personnelUser->syncRoles(['personnel']);
        }
    }
}
