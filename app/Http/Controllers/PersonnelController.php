<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Personnel;
use App\Models\Position;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Barryvdh\DomPDF\Facade\Pdf;

class PersonnelController extends Controller
{
    private function denyWithRedirect(string $routeName, string $message): void
    {
        throw new HttpResponseException(
            redirect()->route($routeName)->with('warning', $message)
        );
    }

    private function schoolScopeId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->hasRole('school') && $user->school_id) {
            return (int) $user->school_id;
        }

        if ($user && $user->hasRole('encoding_officer') && $user->school_id) {
            return (int) $user->school_id;
        }

        return null;
    }

    private function assertCanCreateOrDeletePersonnel(): void
    {
        $user = Auth::user();

        if ($user && ($user->hasRole('encoding_officer') || $user->hasRole('personnel'))) {
            abort(403);
        }
    }

    private function assertCanEditPersonnelDetails(): void
    {
        $user = Auth::user();

        if ($user && $user->hasRole('encoding_officer')) {
            abort(403);
        }
    }

    private function assertPersonnelRecordAccess(Personnel $personnel): void
    {
        $user = Auth::user();

        if ($user && $user->hasRole('personnel')) {
            if ((int) $user->personnel_id !== (int) $personnel->id) {
                $this->denyWithRedirect('personnel.me', 'You do not have access to that personnel record.');
            }
            return;
        }

        $schoolId = $this->schoolScopeId();
        if ($schoolId) {
            if ((int) $personnel->assigned_school_id !== $schoolId) {
                $this->denyWithRedirect('personnel.index', 'You no longer have access to that personnel record.');
            }
        }
    }

    private function personnelPayload(array $validatedData, ?string $photoPath): array
    {
        return [
            'position_id' => $validatedData['position_id'],
            'assigned_school_id' => $validatedData['assigned_school_id'],
            'deployed_school_id' => $validatedData['deployed_school_id'] ?? $validatedData['assigned_school_id'],
            'profile_photo' => $photoPath,
            'is_active' => (bool) $validatedData['is_active'],
            'emp_id' => $validatedData['employee_id'] ?? null,
            'item_number' => $validatedData['item_no'] ?? null,
            'current_step' => $validatedData['step'],
            'last_step_increment_date' => $validatedData['last_step'],
            'salary_grade' => $validatedData['sg'] ?? null,
            'salary_actual' => $validatedData['salary_actual'] ?? null,
            'branch' => $validatedData['branch'] ?? null,
            'employee_type' => $validatedData['employee_type'],
        ];
    }

    private function serviceRecordPayloadFromPersonnel(Personnel $personnel, string $dateFrom): array
    {
        return [
            'position_id' => $personnel->position_id,
            'school_id' => $personnel->deployed_school_id ?? $personnel->assigned_school_id,
            'date_from' => $dateFrom,
            'status' => $personnel->employee_type,
            'salary' => $personnel->salary_actual,
            'branch' => $personnel->branch,
        ];
    }

    private function createServiceRecordFromPersonnel(Personnel $personnel, ?string $dateFrom = null): void
    {
        $dateValue = $dateFrom ?: now()->toDateString();

        // Find the latest service record with a blank end date and set its date_to
        $openRecord = $personnel->serviceRecords()
            ->whereNull('date_to')
            ->orderByDesc('date_from')
            ->first();
        if ($openRecord) {
            // Set date_to to the day before the new record's date_from
            $prevEnd = date('Y-m-d', strtotime($dateValue . ' -1 day'));
            $openRecord->date_to = $prevEnd;
            $openRecord->save();
        }

        $personnel->serviceRecords()->create(
            $this->serviceRecordPayloadFromPersonnel($personnel, $dateValue)
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->hasRole('personnel') && $user->personnel_id) {
            return redirect()->route('personnel.show', $user->personnel_id);
        }

        $search = $request->input('search');
        $schoolId = $this->schoolScopeId();

        // Sort: assigned first, unassigned last
        $personnelList = Personnel::with(['school', 'position', 'pdsMain'])
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->where('assigned_school_id', $schoolId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('emp_id', 'like', "%{$search}%")
                        ->orWhere('item_number', 'like', "%{$search}%")
                        ->orWhereHas('position', function ($positionQuery) use ($search) {
                            $positionQuery->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('school', function ($schoolQuery) use ($search) {
                            $schoolQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('school_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('pdsMain', function ($pdsQuery) use ($search) {
                            $pdsQuery->where('last_name', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('email_address', 'like', "%{$search}%");
                        });
                });
            })
            ->get()
            ->sortByDesc(function ($personnel) {
                // Assigned: has assigned_school_id, position_id, emp_id, and pdsMain with first/last name
                $hasSchool = !is_null($personnel->assigned_school_id);
                $hasPosition = !is_null($personnel->position_id);
                $hasEmpId = !is_null($personnel->emp_id) && $personnel->emp_id !== '';
                $hasName = $personnel->pdsMain && $personnel->pdsMain->first_name && $personnel->pdsMain->last_name;
                return $hasSchool && $hasPosition && $hasEmpId && $hasName ? 1 : 0;
            })
            ->values();

        // Paginate manually since we used get() and sortByDesc
        $perPage = 15;
        $currentPage = request()->input('page', 1);
        $personnelList = new \Illuminate\Pagination\LengthAwarePaginator(
            $personnelList->forPage($currentPage, $perPage),
            $personnelList->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Assigned: has assigned_school_id, position_id, emp_id, first_name, and last_name
        // Unassigned: missing any of those
        $assignedCount = Personnel::when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->whereNotNull('assigned_school_id')
            ->whereNotNull('position_id')
            ->whereNotNull('emp_id')
            ->where('emp_id', '!=', '')
            ->whereHas('pdsMain', function ($q) {
                $q->whereNotNull('first_name')->where('first_name', '!=', '')
                  ->whereNotNull('last_name')->where('last_name', '!=', '');
            })
            ->count();
        $unassignedCount = Personnel::when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->where(function ($q) {
                $q->whereNull('assigned_school_id')
                  ->orWhereNull('position_id')
                  ->orWhereNull('emp_id')
                  ->orWhere('emp_id', '=','')
                  ->orWhereDoesntHave('pdsMain', function ($sub) {
                      $sub->whereNotNull('first_name')->where('first_name', '!=', '')
                          ->whereNotNull('last_name')->where('last_name', '!=', '');
                  });
            })
            ->count();

        return view('personnel.index', [
            'personnelList' => $personnelList,
            'assignedCount' => $assignedCount,
            'unassignedCount' => $unassignedCount,
        ]);
    }

    public function create()
    {
        $this->assertCanCreateOrDeletePersonnel();

        $schoolId = $this->schoolScopeId();
        $schools = School::where('is_active', true)->orderBy('name')->get();
        if ($schoolId) {
            $schools = $schools->where('id', $schoolId)->values();
        }

        $positions = Position::orderBy('title')->get();

        // If admin, allow unassigned (null) as a school option
        $isAdmin = auth()->user() && auth()->user()->hasRole('admin');
        return view('personnel.create', compact('schools', 'positions', 'isAdmin'));
    }

    public function store(Request $request)
    {
        $this->assertCanCreateOrDeletePersonnel();


        $schoolId = $this->schoolScopeId();
        $isAdmin = auth()->user() && auth()->user()->hasRole('admin');
        $input = $request->all();
        // If admin and no school selected, set both assigned and deployed school to null
        if ($isAdmin && (empty($input['assigned_school_id']) || $input['assigned_school_id'] === '')) {
            $input['assigned_school_id'] = null;
            $input['deployed_school_id'] = null;
        }
        $validatedData = $request->validate([
            'employee_id' => 'nullable|string|max:255|unique:personnel,emp_id',
            'position_id' => 'required|exists:positions,id',
            'item_no' => 'nullable|string|max:255',
            'step' => 'required|integer|min:1',
            'last_step' => 'required|date',
            'sg' => 'nullable|string|max:50',
            'salary_actual' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:255',
            'employee_type' => 'required|string|max:100',
            'assigned_school_id' => $isAdmin ? 'nullable|exists:schools,id' : 'required|exists:schools,id',
            'deployed_school_id' => 'nullable|exists:schools,id',
            'service_start_date' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);
        $validatedData['assigned_school_id'] = $input['assigned_school_id'];
        $validatedData['deployed_school_id'] = $input['deployed_school_id'];

        if ($schoolId) {
            abort_if((int) $validatedData['assigned_school_id'] !== $schoolId, 403);
            if (!empty($validatedData['deployed_school_id'])) {
                abort_if((int) $validatedData['deployed_school_id'] !== $schoolId, 403);
            }
        }

        DB::transaction(function () use ($validatedData) {
            $personnel = Personnel::create($this->personnelPayload($validatedData, null));

            $this->createServiceRecordFromPersonnel(
                $personnel,
                $validatedData['service_start_date'] ?? ($validatedData['last_step'] ?? now()->toDateString())
            );
        });

        ActivityLog::log(
            'CREATE',
            'Personnel',
            'Created new personnel details record.'
        );

        return redirect()->route('personnel.index')->with('success', 'Personnel record added successfully.');
    }

    public function exportPds(Personnel $personnel)
    {
        $this->assertPersonnelRecordAccess($personnel);

        $personnel->load([
            'pdsMain',
            'pdsChildren',
            'pdsEducation',
            'pdsEligibility',
            'pdsWorkExperience',
            'pdsTraining',
            'pdsReferences',
        ]);

        $pdf = Pdf::loadView('personnel.pds_export', compact('personnel'))
            ->setPaper('a4', 'portrait');

        $lastName = $personnel->pdsMain?->last_name;
        $firstName = $personnel->pdsMain?->first_name;

        $namePart = trim(implode(' ', array_filter([$lastName, $firstName])));
        $baseName = filled($namePart) ? Str::slug($namePart, '_') : 'personnel_' . $personnel->id;
        $filename = 'PDS_' . $baseName . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function edit(Personnel $personnel)
    {
        $this->assertCanEditPersonnelDetails();
        $this->assertPersonnelRecordAccess($personnel);

        // For edit: always show all schools for school users (for transfer)
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return view('personnel.edit', compact('personnel', 'schools', 'positions'));
    }

    public function update(Request $request, Personnel $personnel)
    {
        $this->assertCanEditPersonnelDetails();
        $this->assertPersonnelRecordAccess($personnel);

        $validatedData = $request->validate([
            'employee_id' => 'nullable|string|max:255|unique:personnel,emp_id,' . $personnel->id,
            'position_id' => 'required|exists:positions,id',
            'item_no' => 'nullable|string|max:255',
            'step' => 'required|integer|min:1',
            'last_step' => 'required|date',
            'sg' => 'nullable|string|max:50',
            'salary_actual' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:255',
            'employee_type' => 'required|string|max:100',
            'assigned_school_id' => 'required|exists:schools,id',
            'deployed_school_id' => 'nullable|exists:schools,id',
            'service_effective_date' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        // School users are allowed to transfer personnel to other schools during edit.

        $original = $personnel->getOriginal();
        $oldAssignedSchoolId = (int) $personnel->assigned_school_id;

        DB::transaction(function () use ($personnel, $validatedData) {
            $personnel->update($this->personnelPayload($validatedData, $personnel->profile_photo));

            if ($personnel->wasChanged(['position_id', 'employee_type', 'assigned_school_id', 'deployed_school_id', 'salary_actual', 'branch'])) {
                $this->createServiceRecordFromPersonnel(
                    $personnel,
                    $validatedData['service_effective_date'] ?? now()->toDateString()
                );
            }
        });

        $changes = [];
        foreach ($personnel->getChanges() as $key => $newValue) {
            if ($key !== 'updated_at') {
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($changes)) {
            ActivityLog::log(
                'UPDATE',
                'Personnel',
                "Updated personnel details for ID: {$personnel->emp_id}",
                $changes
            );
        }

        $stationChanged = $oldAssignedSchoolId !== (int) $personnel->assigned_school_id;
        $isPersonnelUser = Auth::user()?->hasRole('personnel') ?? false;

        $redirectRoute = $isPersonnelUser
            ? route('personnel.show', $personnel)
            : route('personnel.index');

        $response = redirect($redirectRoute)->with('success', 'Personnel details updated successfully.');

        if ($isPersonnelUser && $stationChanged) {
            $response->with('warning', 'You changed your station assignment. Please verify your profile scope and related records.');
        }

        return $response;
    }

    public function updateManualLeaveCredits(Request $request, Personnel $personnel)
    {
        $this->assertPersonnelRecordAccess($personnel);

        $user = Auth::user();
        abort_unless($user && $user->hasAnyRole(['admin', 'school', 'encoding_officer']), 403);

        $validated = $request->validate([
            'manually_added_credits' => 'required|numeric',
        ]);

        $oldValue = (float) ($personnel->manually_added_credits ?? 0);
        $personnel->manually_added_credits = (float) $validated['manually_added_credits'];
        $personnel->save();

        ActivityLog::log(
            'UPDATE',
            'Leave Credits',
            "Updated manually added credits for personnel ID: {$personnel->id}",
            [
                'manually_added_credits' => [
                    'old' => $oldValue,
                    'new' => (float) $personnel->manually_added_credits,
                ],
            ]
        );

        return back()->with('success', 'Manually added leave credits updated successfully.');
    }

    public function destroy(Personnel $personnel)
    {
        $this->assertCanCreateOrDeletePersonnel();
        $this->assertPersonnelRecordAccess($personnel);

        if ($personnel->profile_photo && Storage::disk('public')->exists($personnel->profile_photo)) {
            Storage::disk('public')->delete($personnel->profile_photo);
        }

        ActivityLog::log(
            'DELETE',
            'Personnel',
            "Permanently deleted personnel record: {$personnel->emp_id}"
        );

        $personnel->delete();

        return redirect()->route('personnel.index')->with('success', 'Personnel record removed successfully.');
    }

    public function show($id)
    {
        $personnel = Personnel::with([
            'pdsMain' => function ($q) {
                $q->whereNull('submission_id');
            },
            'pdsChildren' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsEducation' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsEligibility' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsWorkExperience' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsVoluntaryWork' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsTraining' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsSkills' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsDistinctions' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsMemberships' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'pdsReferences' => function ($q) {
                $q->whereNull('submission_id')->orderBy('id');
            },
            'position',
            'school',
            'equipment',
            'specialOrders',
            'serviceRecords.position',
            'serviceRecords.school',
        ])->findOrFail($id);

        $this->assertPersonnelRecordAccess($personnel);

        $positions = Position::orderBy('title')->get();
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $employeeTypes = ['Regular', 'Contractual', 'Substitute'];

        return view('personnel.show', compact('personnel', 'positions', 'schools', 'employeeTypes'));
    }
}
