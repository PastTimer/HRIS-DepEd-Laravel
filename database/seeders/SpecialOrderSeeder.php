<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpecialOrder;
use App\Models\SoType;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;

class SpecialOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get some types and personnel
        $types = SoType::all();
        $personnel = Personnel::take(10)->pluck('id')->all();

        if ($types->isEmpty() || empty($personnel)) {
            $this->command->warn('No SoTypes or Personnel found. Skipping SpecialOrder seeding.');
            return;
        }

        foreach (range(1, 5) as $i) {
            $type = $types->random();
            $so = SpecialOrder::create([
                'title' => "Sample Special Order $i",
                'description' => "This is a sample description for SO $i.",
                'so_number' => sprintf('SO%03d', $i),
                'series_year' => date('Y'),
                'type_id' => $type->id,
                'status' => 'Approved',
                'created_by' => 1,
            ]);
            // Attach random personnel (2-5 per SO)
            $selected = collect($personnel)->shuffle()->take(rand(2, 5))->all();
            $pivotData = [];
            foreach ($selected as $pid) {
                $pivotData[$pid] = ['units' => $type->value];
            }
            $so->personnel()->attach($pivotData);
        }
    }
}
