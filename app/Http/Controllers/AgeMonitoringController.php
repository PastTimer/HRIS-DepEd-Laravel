<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AgeMonitoringController extends Controller
{
    private function schoolScopeId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->hasRole('school') && $user->school_id) {
            return (int) $user->school_id;
        }

        if ($user && $user->hasRole('encoding_officer') && !$user->isGlobalEncodingOfficer() && $user->school_id) {
            return (int) $user->school_id;
        }

        return null;
    }

    /**
     * Show age monitoring using actual age calculation.
     */
    public function actual(Request $request)
    {

        $perPage = 10;
        $page = $request->input('page', 1);
        $schoolId = $this->schoolScopeId();
        $today = now();
        $query = Personnel::with(['pdsMain', 'position', 'school'])
            ->where('is_active', true)
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->whereHas('pdsMain', function ($q) {
                $q->whereNotNull('birth_date');
            });

        $rows = $query->get()->map(function (Personnel $person) use ($today) {
            $main = $person->pdsMain;
            $birthDate = $main ? \Carbon\Carbon::parse($main->birth_date) : null;
            $age = $birthDate ? $birthDate->age : null;
            $name = $main ? trim(implode(' ', array_filter([
                $main->last_name ? $main->last_name . ',' : null,
                $main->first_name,
                $main->middle_name,
                $main->extension_name,
            ]))) : 'Unnamed Personnel';
            return collect([
                'emp_id' => $person->emp_id,
                'name' => $name !== '' ? $name : 'Unnamed Personnel',
                'birth_date' => $birthDate ? $birthDate->format('m/d/Y') : null,
                'age' => $age,
                'gender' => $main ? $main->birth_sex : 'Unknown',
                'position' => optional($person->position)->title ?? '-',
                'employee_type' => $person->employee_type ?: 'Unknown',
                'station' => optional($person->school)->name ?? '-',
            ]);
        })->sortBy('name')->values();

        $total = $rows->count();
        $pagedRows = $rows->forPage($page, $perPage)->values();
        $ageChart = $this->buildAgeChart($rows, 'age');

        return view('monitoring.agemonitoringactual', [
            'rows' => $pagedRows,
            'ageChart' => json_encode($ageChart),
            'currentYear' => now()->year,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
        ]);
    }

    /**
     * Show age monitoring using year-difference calculation.
     */
    public function year(Request $request)
    {
        $perPage = 10;
        $page = $request->input('page', 1);
        $today = now();
        $schoolId = $this->schoolScopeId();
        $query = Personnel::with(['pdsMain', 'position', 'school'])
            ->where('is_active', true)
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->whereHas('pdsMain', function ($q) {
                $q->whereNotNull('birth_date');
            });

        $rows = $query->get()->map(function (Personnel $person) use ($today) {
            $main = $person->pdsMain;
            $birthDate = $main ? \Carbon\Carbon::parse($main->birth_date) : null;
            $age = $birthDate ? ((int) $today->year - (int) $birthDate->year) : null;
            $name = $main ? trim(implode(' ', array_filter([
                $main->last_name ? $main->last_name . ',' : null,
                $main->first_name,
                $main->middle_name,
                $main->extension_name,
            ]))) : 'Unnamed Personnel';
            return collect([
                'emp_id' => $person->emp_id,
                'name' => $name !== '' ? $name : 'Unnamed Personnel',
                'birth_date' => $birthDate ? $birthDate->format('m/d/Y') : null,
                'age' => $age,
                'gender' => $main ? $main->birth_sex : 'Unknown',
                'position' => optional($person->position)->title ?? '-',
                'employee_type' => $person->employee_type ?: 'Unknown',
                'station' => optional($person->school)->name ?? '-',
            ]);
        })->sortBy('name')->values();

        $total = $rows->count();
        $pagedRows = $rows->forPage($page, $perPage)->values();
        $ageChart = $this->buildAgeChart($rows, 'age');

        return view('monitoring.agemonitoringyear', [
            'rows' => $pagedRows,
            'ageChart' => json_encode($ageChart),
            'currentYear' => now()->year,
            'total' => $total,
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
        $schoolId = $this->schoolScopeId();

        return Personnel::with(['pdsMain', 'position', 'school'])
            ->where('is_active', true)
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
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
