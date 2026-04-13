<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StepMonitoringController extends Controller
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
     * Backward-compatible endpoint.
     */
    public function index(Request $request)
    {
        return $this->year($request);
    }

    /**
     * Show STEP increment monitoring (year view).
     */
    public function year(Request $request)
    {
        $perPage = 10;
        $personnel = $this->basePersonnelQuery()->paginate($perPage);

        $currentYear = now()->year;
        $years = collect(range($currentYear, $currentYear + 7));

        $tableData = $personnel->getCollection()->map(function (Personnel $person) use ($years) {
            $lastStep = $person->last_step_increment_date ? Carbon::parse($person->last_step_increment_date) : null;
            $scheduledYears = $this->projectedStepYears($lastStep, (int) $years->last());

            $row = [
                'emp_id' => $person->emp_id,
                'name' => $this->resolvePersonnelName($person),
                'position' => optional($person->position)->title ?? '-',
                'last_step' => $lastStep ? $lastStep->format('m/d/Y') : '-',
                'years' => []
            ];

            foreach ($years as $year) {
                $row['years'][$year] = in_array((int) $year, $scheduledYears, true)
                    ? $lastStep->format('m/d') . '/' . $year
                    : '';
            }

            return $row;
        });

        return view('monitoring.stepmonitoring', [
            'tableData' => $tableData,
            'years' => $years,
            'currentYear' => $currentYear,
            'personnel' => $personnel,
        ]);
    }

    /**
     * Show STEP increment monitoring (month view for current year).
     */
    public function month(Request $request)
    {
        $perPage = 10;
        $personnel = $this->basePersonnelQuery()->paginate($perPage);
        $currentYear = now()->year;

        $tableData = $personnel->getCollection()->map(function (Personnel $person) use ($currentYear) {
            $lastStep = $person->last_step_increment_date ? Carbon::parse($person->last_step_increment_date) : null;
            $dueDate = $this->projectedStepDateForYear($lastStep, $currentYear);

            $months = [];
            for ($month = 1; $month <= 12; $month++) {
                $months[$month] = ($dueDate && (int) $dueDate->month === $month)
                    ? $dueDate->format('m/d/Y')
                    : '';
            }

            return [
                'emp_id' => $person->emp_id,
                'name' => $this->resolvePersonnelName($person),
                'position' => optional($person->position)->title ?? '-',
                'last_step' => $lastStep ? $lastStep->format('m/d/Y') : '-',
                'months' => $months,
            ];
        });

        return view('monitoring.stepmonitoringmonth', [
            'tableData' => $tableData,
            'currentYear' => $currentYear,
            'personnel' => $personnel,
        ]);
    }

    private function basePersonnelQuery()
    {
        $schoolId = $this->schoolScopeId();

        return Personnel::with(['pdsMain', 'position', 'school'])
            ->where('is_active', true)
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->where('assigned_school_id', $schoolId);
            })
            ->orderBy('emp_id');
    }

    private function resolvePersonnelName(Personnel $person): string
    {
        $main = $person->pdsMain;
        if (!$main) {
            return 'No PDS Record';
        }

        $name = trim(implode(' ', array_filter([
            $main->last_name ? $main->last_name . ',' : null,
            $main->first_name,
            $main->middle_name,
            $main->extension_name,
        ])));

        return $name !== '' ? $name : 'Unnamed Personnel';
    }

    private function projectedStepYears(?Carbon $lastStep, int $maxYear): array
    {
        if (!$lastStep) {
            return [];
        }

        $years = [];
        $year = (int) $lastStep->year;
        while ($year <= $maxYear) {
            $years[] = $year;
            $year += 3;
        }

        return $years;
    }

    private function projectedStepDateForYear(?Carbon $lastStep, int $targetYear): ?Carbon
    {
        if (!$lastStep) {
            return null;
        }

        $year = (int) $lastStep->year;
        while ($year < $targetYear) {
            $year += 3;
        }

        if ($year !== $targetYear) {
            return null;
        }

        $month = (int) $lastStep->month;
        $maxDay = Carbon::create($targetYear, $month, 1)->daysInMonth;
        $day = min((int) $lastStep->day, $maxDay);

        return Carbon::create($targetYear, $month, $day);
    }
}
