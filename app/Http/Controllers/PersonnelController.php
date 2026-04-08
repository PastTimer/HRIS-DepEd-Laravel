<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PdsMain;
use App\Models\Personnel;
use App\Models\Position;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PersonnelController extends Controller
{
    private function schoolScopeId(): ?int
    {
        $user = Auth::user();

        if ($user && ($user->hasRole('school') || $user->hasRole('encoding_officer'))) {
            return $user->school_id ? (int) $user->school_id : null;
        }

        return null;
    }

    private function assertCanWritePersonnel(): void
    {
        $user = Auth::user();

        if ($user && ($user->hasRole('encoding_officer') || $user->hasRole('personnel'))) {
            abort(403);
        }
    }

    private function assertPersonnelRecordAccess(Personnel $personnel): void
    {
        $user = Auth::user();

        if ($user && $user->hasRole('personnel')) {
            abort_if((int) $user->personnel_id !== (int) $personnel->id, 403);
            return;
        }

        $schoolId = $this->schoolScopeId();
        if ($schoolId) {
            abort_if((int) $personnel->assigned_school_id !== $schoolId, 403);
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

    private function pdsMainPayload(array $validatedData): array
    {
        return [
            'last_name' => $validatedData['last_name'] ?? null,
            'first_name' => $validatedData['first_name'] ?? null,
            'middle_name' => $validatedData['middle_name'] ?? null,
            'extension_name' => $validatedData['name_ext'] ?? null,
            'birth_date' => $validatedData['date_of_birth'] ?? null,
            'birth_place' => $validatedData['place_of_birth'] ?? null,
            'birth_sex' => isset($validatedData['gender']) ? strtoupper($validatedData['gender']) : null,
            'civil_status' => isset($validatedData['civil_status']) ? strtoupper($validatedData['civil_status']) : null,
            'blood_type' => $validatedData['blood_type'] ?? null,
            'umid_id_number' => $validatedData['gsis_no'] ?? null,
            'pagibig_number' => $validatedData['pagibig_no'] ?? null,
            'philhealth_number' => $validatedData['philhealth_no'] ?? null,
            'sss_number' => $validatedData['sss_no'] ?? null,
            'tin_number' => $validatedData['tin_no'] ?? null,
            'agency_employee_number' => $validatedData['employee_id'] ?? null,
            'mobile' => $validatedData['contact_no'] ?? null,
            'email_address' => $validatedData['email_address'] ?? null,
            'residential_address' => $validatedData['address'] ?? null,
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->hasRole('personnel') && $user->personnel_id) {
            return redirect()->route('personnel.show', $user->personnel_id);
        }

        $search = $request->input('search');
        $schoolId = $this->schoolScopeId();

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
            ->orderByDesc('id')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('personnel.index', ['personnelList' => $personnelList]);
    }

    public function create()
    {
        $this->assertCanWritePersonnel();

        $schoolId = $this->schoolScopeId();
        $schools = School::where('is_active', true)->orderBy('name')->get();
        if ($schoolId) {
            $schools = $schools->where('id', $schoolId)->values();
        }

        $positions = Position::orderBy('title')->get();

        return view('personnel.create', compact('schools', 'positions'));
    }

    public function store(Request $request)
    {
        $this->assertCanWritePersonnel();

        $schoolId = $this->schoolScopeId();
        $validatedData = $request->validate([
            // Personal (PDS)
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'name_ext' => 'nullable|string|max:10',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'civil_status' => 'nullable|string|max:50',
            'blood_type' => 'nullable|string|max:10',

            // Operational Personnel
            'employee_id' => 'nullable|string|max:255|unique:personnel,emp_id',
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
            'service_start_date' => 'nullable|date',

            // IDs (PDS)
            'gsis_no' => 'nullable|string|max:100',
            'pagibig_no' => 'nullable|string|max:100',
            'philhealth_no' => 'nullable|string|max:100',
            'sss_no' => 'nullable|string|max:100',
            'tin_no' => 'nullable|string|max:100',

            // Contact (PDS)
            'contact_no' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'address' => 'nullable|string',

            'is_active' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($schoolId) {
            abort_if((int) $validatedData['assigned_school_id'] !== $schoolId, 403);
            if (!empty($validatedData['deployed_school_id'])) {
                abort_if((int) $validatedData['deployed_school_id'] !== $schoolId, 403);
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }

        DB::transaction(function () use ($validatedData, $photoPath) {
            $personnel = Personnel::create($this->personnelPayload($validatedData, $photoPath));

            PdsMain::updateOrCreate(
                ['personnel_id' => $personnel->id],
                $this->pdsMainPayload($validatedData)
            );

            $this->createServiceRecordFromPersonnel(
                $personnel,
                $validatedData['service_start_date'] ?? ($validatedData['last_step'] ?? now()->toDateString())
            );
        });

        ActivityLog::log(
            'CREATE',
            'Personnel',
            "Created new personnel record: {$validatedData['first_name']} {$validatedData['last_name']}"
        );

        return redirect()->route('personnel.index')->with('success', 'Personnel record added successfully.');
    }

    public function edit(Personnel $personnel)
    {
        $this->assertCanWritePersonnel();
        $this->assertPersonnelRecordAccess($personnel);

        $personnel->load('pdsMain');
        $schoolId = $this->schoolScopeId();
        $schools = School::orderBy('name')->get();
        if ($schoolId) {
            $schools = $schools->where('id', $schoolId)->values();
        }

        $positions = Position::orderBy('title')->get();

        return view('personnel.edit', compact('personnel', 'schools', 'positions'));
    }

    public function update(Request $request, Personnel $personnel)
    {
        $this->assertCanWritePersonnel();
        $this->assertPersonnelRecordAccess($personnel);

        $schoolId = $this->schoolScopeId();
        $validatedData = $request->validate([
            // Personal (PDS)
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'name_ext' => 'nullable|string|max:10',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'civil_status' => 'nullable|string|max:50',
            'blood_type' => 'nullable|string|max:10',

            // Operational Personnel
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

            // IDs (PDS)
            'gsis_no' => 'nullable|string|max:100',
            'pagibig_no' => 'nullable|string|max:100',
            'philhealth_no' => 'nullable|string|max:100',
            'sss_no' => 'nullable|string|max:100',
            'tin_no' => 'nullable|string|max:100',

            // Contact (PDS)
            'contact_no' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'address' => 'nullable|string',

            'is_active' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($schoolId) {
            abort_if((int) $validatedData['assigned_school_id'] !== $schoolId, 403);
            if (!empty($validatedData['deployed_school_id'])) {
                abort_if((int) $validatedData['deployed_school_id'] !== $schoolId, 403);
            }
        }

        $photoPath = $personnel->profile_photo;

        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }

        $original = $personnel->getOriginal();

        DB::transaction(function () use ($personnel, $validatedData, $photoPath) {
            $personnel->update($this->personnelPayload($validatedData, $photoPath));

            PdsMain::updateOrCreate(
                ['personnel_id' => $personnel->id],
                $this->pdsMainPayload($validatedData)
            );

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

        return redirect()->route('personnel.index')->with('success', 'Personnel record updated successfully.');
    }

    public function destroy(Personnel $personnel)
    {
        $this->assertCanWritePersonnel();
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
            'pdsMain',
            'position',
            'school',
            'equipment',
            'trainings',
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
