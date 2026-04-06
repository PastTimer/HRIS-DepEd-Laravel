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

class PersonnelController extends Controller
{
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
            'employee_type' => $validatedData['employee_type'],
        ];
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
        $search = $request->input('search');

        $personnelList = Personnel::with(['school', 'position', 'pdsMain'])
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
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return view('personnel.create', compact('schools', 'positions'));
    }

    public function store(Request $request)
    {
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
            'employee_type' => 'required|string|max:100',
            'assigned_school_id' => 'required|exists:schools,id',
            'deployed_school_id' => 'nullable|exists:schools,id',

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
        $personnel->load('pdsMain');
        $schools = School::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return view('personnel.edit', compact('personnel', 'schools', 'positions'));
    }

    public function update(Request $request, Personnel $personnel)
    {
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
            'employee_type' => 'required|string|max:100',
            'assigned_school_id' => 'required|exists:schools,id',
            'deployed_school_id' => 'nullable|exists:schools,id',

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
        ])->findOrFail($id);

        return view('personnel.show', compact('personnel'));
    }
}
