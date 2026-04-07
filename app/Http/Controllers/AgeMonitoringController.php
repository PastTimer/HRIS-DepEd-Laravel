<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use Carbon\Carbon;

class AgeMonitoringController extends Controller
{
    /**
     * Show age monitoring using actual age calculation.
     */
    public function actual(Request $request)
    {
        $rows = $this->buildAgeRows('actual');
        $ageChart = $this->buildAgeChart($rows, 'age');
        $page = $request->input('page', 1);
        $perPage = 10;
        $pagedRows = $rows->forPage($page, $perPage);
        return view('monitoring.agemonitoringactual', [
            'rows' => $pagedRows,
            'ageChart' => json_encode($ageChart),
            'currentYear' => now()->year,
            'total' => $rows->count(),
            'perPage' => $perPage,
            'currentPage' => $page,
        ]);
    }

    /**
     * Show age monitoring using year-difference calculation.
     */
    public function year(Request $request)
    {
        $rows = $this->buildAgeRows('year');
        $ageChart = $this->buildAgeChart($rows, 'age');
        $page = $request->input('page', 1);
        $perPage = 10;
        $pagedRows = $rows->forPage($page, $perPage);
        return view('monitoring.agemonitoringyear', [
            'rows' => $pagedRows,
            'ageChart' => json_encode($ageChart),
            'currentYear' => now()->year,
            'total' => $rows->count(),
            'perPage' => $perPage,
            'currentPage' => $page,
        ]);
    }

    /**
     * Show personnel by age group with employment type and gender stats.
     */
    public function ageGroup(Request $request)
    {
        $mode = $request->query('mode', 'actual');
        $mode = in_array($mode, ['actual', 'year'], true) ? $mode : 'actual';

        $age = $request->query('age');
        $age = is_numeric($age) ? (int) $age : null;

        if ($age !== null) {
            $rows = $this->buildAgeRows($mode)->where('age', $age)->values();
        } else {
            $rows = $this->buildAgeRows($mode);
        }

        $etypeChart = [['Etype', 'Count']];
        $etypeStats = $rows->groupBy('employee_type')->map->count();
        foreach ($etypeStats as $etype => $count) {
            $etypeChart[] = [$etype ?: 'Unknown', $count];
        }

        $genderChart = [['Gender', 'Count']];
        $genderStats = $rows->groupBy('gender')->map->count();
        foreach ($genderStats as $gender => $count) {
            $genderChart[] = [$gender ?: 'Unknown', $count];
        }

        return view('monitoring.agegroup', [
            'age' => $age,
            'mode' => $mode,
            'rows' => $rows,
            'etypeChart' => json_encode($etypeChart),
            'genderChart' => json_encode($genderChart),
            'currentYear' => now()->year,
        ]);
    }

    private function buildAgeRows(string $mode)
    {
        $today = now();

        return Personnel::with(['pdsMain', 'position', 'school'])
            ->where('is_active', true)
            ->get()
            ->filter(fn (Personnel $person) => $person->pdsMain && $person->pdsMain->birth_date)
            ->map(function (Personnel $person) use ($mode, $today) {
                $main = $person->pdsMain;
                $birthDate = Carbon::parse($main->birth_date);
                $age = $mode === 'year'
                    ? ((int) $today->year - (int) $birthDate->year)
                    : $birthDate->age;

                $name = trim(implode(' ', array_filter([
                    $main->last_name ? $main->last_name . ',' : null,
                    $main->first_name,
                    $main->middle_name,
                    $main->extension_name,
                ])));

                return collect([
                    'emp_id' => $person->emp_id,
                    'name' => $name !== '' ? $name : 'Unnamed Personnel',
                    'birth_date' => $birthDate->format('m/d/Y'),
                    'age' => $age,
                    'gender' => $main->birth_sex ?: 'Unknown',
                    'position' => optional($person->position)->title ?? '-',
                    'employee_type' => $person->employee_type ?: 'Unknown',
                    'station' => optional($person->school)->name ?? '-',
                ]);
            })
            ->sortBy('name')
            ->values();
    }

    private function buildAgeChart($rows, string $column): array
    {
        $chart = [['Age Group', 'Count']];
        $ageStats = $rows->groupBy($column)->map->count()->sortKeys();
        foreach ($ageStats as $age => $count) {
            $chart[] = [$age, $count];
        }

        return $chart;
    }
}
