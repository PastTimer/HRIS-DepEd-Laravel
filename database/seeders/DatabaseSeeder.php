<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PersonnelSeeder::class,
            PdsSeeder::class,
            UserSeeder::class,
            BlankRoleUsersSeeder::class,
            InternetAndIspSeeder::class,
            EquipmentSeeder::class,
            ServiceRecordSeeder::class,
            SpecialOrderSeeder::class,
        ]);
    }
}