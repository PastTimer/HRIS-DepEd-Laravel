<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentMovement;
use App\Models\School;
use App\Models\Personnel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    private const ITEM_OPTIONS = [
        'Desktop', 'Laptop', 'Printer', 'Projector', 'Tablet', 'Server', 'Networking Equipment', 'Furniture', 'Other',
    ];

    private const BRAND_OPTIONS = [
        'Acer', 'Asus', 'Dell', 'HP', 'Lenovo', 'Epson', 'Brother', 'Canon', 'Samsung', 'Apple', 'Other',
    ];

    private const PACKAGE_OPTIONS = [
        'Batch 35', 'Batch 36', 'Batch 40', 'Batch 42', 'Batch 44', 'Other',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Equipment::with(['school', 'accountableOfficer.pdsMain'])
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('item', 'like', "%{$search}%")
                    ->orWhere('item_description', 'like', "%{$search}%")
                    ->orWhere('property_no', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('brand_manufacturer', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('equipment_condition', 'like', "%{$search}%")
                    ->orWhere('disposition_status', 'like', "%{$search}%")
                    ->orWhereHas('school', function($schoolQuery) use ($search) {
                        $schoolQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('school_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('accountableOfficer', function($officerQuery) use ($search) {
                        $officerQuery->whereHas('pdsMain', function ($pdsQuery) use ($search) {
                            $pdsQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    });
                });
            })
            ->orderByDesc('created_at');

        // School users should only see inventory linked to their school.
        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $query->where('school_id', Auth::user()->school_id);
        }

        $equipments = $query->paginate(15)->appends(['search' => $search]);

        return view('equipments.index', compact('equipments'));
    }

    public function create()
    {
        $schools = $this->schoolsForCurrentUser();
        $employees = Personnel::with('pdsMain')->where('is_active', true)->orderBy('id')->get();
        $items = self::ITEM_OPTIONS;
        $brands = self::BRAND_OPTIONS;
        $packages = self::PACKAGE_OPTIONS;

        return view('equipments.create', compact('schools', 'employees', 'items', 'brands', 'packages'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateEquipment($request);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $validatedData['school_id'] = Auth::user()->school_id;
        }

        $equipmentData = $this->buildPayload($request, $validatedData, true);
        $equipment = Equipment::create($equipmentData);
        $this->logMovementIfNeeded($equipment, null, $equipmentData, $request);

        ActivityLog::log(
            'CREATE', 
            'Equipment Inventory', 
            "Added new equipment: {$equipment->model} {$equipment->unit} {$equipment->brand_manufacturer} (Property No: {$equipment->property_no})"
        );

        return redirect()->route('equipment.index')->with('success', "Equipment '{$equipment->item}' added successfully!");
    }

    public function edit(Equipment $equipment)
    {
        $schools = $this->schoolsForCurrentUser();
        $employees = Personnel::with('pdsMain')->where('is_active', true)->orderBy('id')->get();
        $items = self::ITEM_OPTIONS;
        $brands = self::BRAND_OPTIONS;
        $packages = self::PACKAGE_OPTIONS;

        return view('equipments.edit', compact('equipment', 'schools', 'employees', 'items', 'brands', 'packages'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validatedData = $this->validateEquipment($request, $equipment->id);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $validatedData['school_id'] = Auth::user()->school_id;
        }

        $updateData = $this->buildPayload($request, $validatedData, false);

        $original = $equipment->getOriginal();
        $oldAccountableOfficerId = $equipment->accountable_officer_id;

        $equipment->update($updateData);
        $this->logMovementIfNeeded($equipment, $oldAccountableOfficerId, $updateData, $request);

        $changes = [];
        foreach ($equipment->getChanges() as $key => $newValue) {
            if ($key !== 'updated_at') { 
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue
                ];
            }
        }

        if (!empty($changes)) {
            ActivityLog::log(
                'UPDATE', 
                'Equipment Inventory', 
                "Updated equipment details for: {$equipment->model} {$equipment->unit} {$equipment->brand_manufacturer} (Property No: {$equipment->property_no})",
                $changes
            );
        }

        return redirect()->route('equipment.index')->with('success', "Equipment '{$equipment->item}' updated successfully!");
    }

    public function show(Equipment $equipment)
    {
        $equipment->load([
            'school',
            'creator',
            'accountableOfficer.pdsMain',
            'custodian.pdsMain',
            'movements.fromPersonnel.pdsMain',
            'movements.toPersonnel.pdsMain',
            'movements.creator',
        ]);

        return view('equipments.show', [
            'equipment' => $equipment,
            'movements' => $equipment->movements,
        ]);
    }

    public function destroy(Equipment $equipment)
    {

        ActivityLog::log(
            'DELETE', 
            'Equipment', 
            "Permanently deleted equipment: {$equipment->model} {$equipment->unit} {$equipment->brand_manufacturer} (Property No: {$equipment->property_no})"
        );

        $equipment->delete();

        return redirect()->route('equipment.index')->with('success', 'Equipment record removed successfully.');
    }

    private function validateEquipment(Request $request, ?int $equipmentId = null): array
    {
        $propertyUniqueRule = 'nullable|string|max:255|unique:equipment,property_no';
        if ($equipmentId !== null) {
            $propertyUniqueRule .= ',' . $equipmentId;
        }

        return $request->validate([
            'property_no'             => $propertyUniqueRule,
            'old_property_no'         => 'nullable|string|max:255',
            'serial_number'           => 'nullable|string|max:255',

            'item'                    => 'required|string|max:255',
            'unit'                    => 'nullable|string|max:50',
            'brand_manufacturer'      => 'nullable|string|max:255',
            'model'                   => 'nullable|string|max:255',
            'item_description'        => 'nullable|string',
            'specifications'          => 'nullable|string',

            'is_dcp'                  => 'nullable|boolean',
            'dcp_package'             => 'nullable|string|max:255',
            'dcp_year'                => 'nullable|string|max:4',

            'acquisition_cost'        => 'nullable|numeric|min:0',
            'classification'          => 'nullable|string|max:255',
            'estimated_useful_life'   => 'nullable|integer|min:0',
            'gl_sl_code'              => 'nullable|string|max:255',
            'uacs_code'               => 'nullable|string|max:255',

            'mode_acquisition'        => 'nullable|string|max:255',
            'source_acquisition'      => 'nullable|string|max:255',
            'donor'                   => 'nullable|string|max:255',
            'source_funds'            => 'nullable|string|max:255',
            'allotment_class'         => 'nullable|string|max:255',
            'received_date'           => 'nullable|date',
            'pmp_reference'           => 'nullable|string|max:255',

            'transaction_type'        => 'nullable|string|max:255',
            'supporting_doc_type'     => 'nullable|string|max:255',
            'supporting_doc_no'       => 'nullable|string|max:255',
            'accountable_officer_id'  => 'nullable|exists:personnel,id',
            'accountable_date'        => 'nullable|date',
            'custodian_id'            => 'nullable|exists:personnel,id',
            'custodian_date'          => 'nullable|date',

            'new_accountable_id'      => 'nullable|exists:personnel,id',
            'new_accountable_date'    => 'nullable|date',
            'new_supporting_doc_type' => 'nullable|string|max:255',
            'new_supporting_doc_no'   => 'nullable|string|max:255',

            'supplier'                => 'nullable|string|max:255',
            'supplier_contact'        => 'nullable|string|max:255',
            'under_warranty'          => 'nullable|boolean',
            'warranty_end_date'       => 'nullable|date',

            'equipment_location'      => 'nullable|string|max:255',
            'is_functional'           => 'nullable|boolean',
            'equipment_condition'     => 'nullable|string|max:255',
            'disposition_status'      => 'nullable|string|max:255',
            'remarks'                 => 'nullable|string',

            'school_id'               => 'required|exists:schools,id',
        ]);
    }

    private function buildPayload(Request $request, array $validatedData, bool $isCreate): array
    {
        $payload = $validatedData;

        $incomingNewAccountableId = $request->input('new_accountable_id');
        $incomingNewAccountableDate = $request->input('new_accountable_date');

        if (!empty($incomingNewAccountableId)) {
            $payload['accountable_officer_id'] = $incomingNewAccountableId;
            if (!empty($incomingNewAccountableDate)) {
                $payload['accountable_date'] = $incomingNewAccountableDate;
            }
        }

        $payload['qr_code'] = !empty($payload['property_no']) ? 'QR-' . $payload['property_no'] : null;
        $payload['category'] = (isset($payload['acquisition_cost']) && (float) $payload['acquisition_cost'] >= 50000)
            ? 'High-value'
            : 'Low-value';

        $payload['is_dcp'] = $request->boolean('is_dcp');
        $payload['under_warranty'] = $request->boolean('under_warranty');
        $payload['is_functional'] = $request->boolean('is_functional');

        if ($isCreate) {
            $payload['created_by'] = Auth::id();
        }

        // Movement fields are tracked in equipment_movements and are not persisted in equipment.
        unset(
            $payload['new_accountable_id'],
            $payload['new_accountable_date'],
            $payload['new_supporting_doc_type'],
            $payload['new_supporting_doc_no']
        );

        return $payload;
    }

    private function logMovementIfNeeded(Equipment $equipment, ?int $oldAccountableOfficerId, array $payload, Request $request): void
    {
        $newAccountableOfficerId = $request->filled('new_accountable_id')
            ? (int) $request->input('new_accountable_id')
            : ($payload['accountable_officer_id'] ?? null);

        $movementRequested =
            $oldAccountableOfficerId !== $newAccountableOfficerId
            || $request->filled('new_accountable_id')
            || $request->filled('new_supporting_doc_type')
            || $request->filled('new_supporting_doc_no');

        if (!$movementRequested) {
            return;
        }

        $movementDate = $request->input('new_accountable_date')
            ?? $payload['accountable_date']
            ?? $payload['received_date']
            ?? now()->toDateString();

        $documentType = $request->input('new_supporting_doc_type')
            ?? $payload['supporting_doc_type']
            ?? $payload['transaction_type']
            ?? null;

        $documentNumber = $request->input('new_supporting_doc_no')
            ?? $payload['supporting_doc_no']
            ?? null;

        EquipmentMovement::create([
            'equipment_id' => $equipment->id,
            'from_personnel_id' => $oldAccountableOfficerId,
            'to_personnel_id' => $newAccountableOfficerId,
            'movement_date' => $movementDate,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'remarks' => $payload['remarks'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    private function schoolsForCurrentUser()
    {
        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            return School::where('id', Auth::user()->school_id)->orderBy('name')->get();
        }

        return School::orderBy('name')->get();
    }
}