<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create the Master Admin
        User::create([
            'username' => 'admin',
            'password' => Hash::make('1234'), 
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'role' => 'admin',
            'email' => 'admin@deped.gov.ph',
            'office' => 'SDO',
            'status' => 'active'
        ]);

        // Let's create a test School User while we're at it
        User::create([
            'username' => 'school_user',
            'password' => Hash::make('1234'),
            'first_name' => 'School',
            'last_name' => 'Principal',
            'role' => 'school',
            'office' => 'SCHOOL',
            'status' => 'active'
        ]);
    }
}