<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personnel;
use App\Models\Position;
use App\Models\School;
use App\Models\ServiceRecord;
use Illuminate\Support\Arr;

class ServiceRecordSeeder extends Seeder
{
    public function run(): void
    {
        // Get all personnel
        $personnelList = Personnel::all();
        $positions = Position::pluck('id')->all();
        $schools = School::pluck('id')->all();
        $statuses = ['Regular', 'Contractual', 'Substitute'];

        foreach ($personnelList as $personnel) {
            // Each personnel gets 2-4 service records, sequential, no overlap
            $numRecords = rand(2, 4);
            $startDate = now()->subYears($numRecords)->startOfYear();
            $salary = rand(20000, 40000);
            for ($i = 0; $i < $numRecords; $i++) {
                $positionId = Arr::random($positions);
                $schoolId = Arr::random($schools);
                $status = Arr::random($statuses);
                $branch = $i % 2 === 0 ? 'Main' : 'Annex';
                $lvAbsWoPay = $i === 1 ? '' : null;
                $dateFrom = $startDate->copy()->addYears($i);
                $dateTo = $i === $numRecords - 1 ? null : $dateFrom->copy()->addYear()->subDay();
                ServiceRecord::create([
                    'personnel_id' => $personnel->id,
                    'position_id' => $positionId,
                    'school_id' => $schoolId,
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo ? $dateTo->toDateString() : null,
                    'status' => $status,
                    'salary' => $salary + ($i * 1000),
                    'branch' => $branch,
                    'lv_abs_wo_pay' => $lvAbsWoPay,
                ]);
            }
        }
    }
}
