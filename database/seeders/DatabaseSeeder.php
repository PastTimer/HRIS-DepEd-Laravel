<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\InternetAndIspSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmployeeSeeder::class,
            UserSeeder::class,
            InternetAndIspSeeder::class,
            EquipmentSeeder::class,
        ]);
    }
}