<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\School;
use App\Models\Employee;
use App\Models\ActivityLog;
use App\Models\Training;
use App\Models\SpecialOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipments = Equipment::with(['school', 'accountableOfficer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('equipments.index', compact('equipments'));
    }

    public function create()
    {
        // Fetch data for our dropdowns
        $schools = School::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('last_name')->get();

        // Standard Dropdown Options (to keep the blade file clean)
        $items = ['Desktop', 'Laptop', 'Printer', 'Projector', 'Tablet', 'Server', 'Networking Equipment', 'Furniture', 'Other'];
        $brands = ['Acer', 'Asus', 'Dell', 'HP', 'Lenovo', 'Epson', 'Brother', 'Canon', 'Samsung', 'Apple', 'Other'];
        $packages = ['Batch 35', 'Batch 36', 'Batch 40', 'Batch 42', 'Batch 44', 'Other'];

        return view('equipments.create', compact('schools', 'employees', 'items', 'brands', 'packages'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Core
            'property_no'        => 'nullable|string|max:255|unique:equipment,property_no',
            'old_property_no'    => 'nullable|string|max:255',
            'serial_number'      => 'nullable|string|max:255',
            // Details
            'item'               => 'required|string|max:255',
            'unit'               => 'nullable|string|max:50',
            'brand_manufacturer' => 'nullable|string|max:255',
            'model'              => 'nullable|string|max:255',
            'item_description'   => 'nullable|string',
            'specifications'     => 'nullable|string',
            // DCP
            'is_dcp'             => 'boolean',
            'dcp_package'        => 'nullable|string|max:255',
            'dcp_year'           => 'nullable|string|max:4',
            // Financial
            'acquisition_cost'   => 'nullable|numeric|min:0',
            'classification'     => 'nullable|string|max:255',
            'estimated_useful_life'=> 'nullable|integer|min:0',
            'gl_sl_code'         => 'nullable|string|max:255',
            'uacs_code'          => 'nullable|string|max:255',
            // Acquisition
            'mode_acquisition'   => 'nullable|string|max:255',
            'source_acquisition' => 'nullable|string|max:255',
            'donor'              => 'nullable|string|max:255',
            'source_funds'       => 'nullable|string|max:255',
            'allotment_class'    => 'nullable|string|max:255',
            'received_date'      => 'nullable|date',
            'pmp_reference'      => 'nullable|string|max:255',
            // Accountability
            'transaction_type'       => 'nullable|string|max:255',
            'supporting_doc_type'    => 'nullable|string|max:255',
            'supporting_doc_no'      => 'nullable|string|max:255',
            'accountable_officer_id' => 'nullable|exists:employees,id',
            'accountable_date'       => 'nullable|date',
            'custodian_id'           => 'nullable|exists:employees,id',
            'custodian_date'         => 'nullable|date',
            // Supplier
            'supplier'           => 'nullable|string|max:255',
            'supplier_contact'   => 'nullable|string|max:255',
            'under_warranty'     => 'boolean',
            'warranty_end_date'  => 'nullable|date',
            // Status
            'equipment_location' => 'nullable|string|max:255',
            'is_functional'      => 'boolean',
            'equipment_condition'=> 'nullable|string|max:255',
            'disposition_status' => 'nullable|string|max:255',
            'remarks'            => 'nullable|string',
            // Associations
            'school_id'          => 'required|exists:schools,id',
        ]);

        // 1. Process custom data
        $qrCode = $validatedData['property_no'] ? 'QR-' . $validatedData['property_no'] : null;
        $category = (isset($validatedData['acquisition_cost']) && $validatedData['acquisition_cost'] >= 50000) ? 'High-value' : 'Low-value';

        // 2. Add processed data and creator ID to the array
        $equipmentData = array_merge($validatedData, [
            'qr_code'    => $qrCode,
            'category'   => $category,
            'created_by' => Auth::id(),
            'is_dcp'         => $request->has('is_dcp'),
            'under_warranty' => $request->has('under_warranty'),
            'is_functional'  => $request->has('is_functional'),
        ]);

        $equipment = Equipment::create($equipmentData);

        ActivityLog::log(
            'CREATE', 
            'Equipment Inventory', 
            "Added new equipment: {$equipment->model} {$equipment->unit} {$equipment->brand_manufacturer} (Property No: {$equipment->property_no})"
        );

        return redirect('/equipment')->with('success', "Equipment '{$equipment->item}' added successfully!");
    }

    public function edit(Equipment $equipment)
    {
        // Fetch data for our dropdowns
        $schools = School::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('last_name')->get();

        // Standard Dropdown Options
        $items = ['Desktop', 'Laptop', 'Printer', 'Projector', 'Tablet', 'Server', 'Networking Equipment', 'Furniture', 'Other'];
        $brands = ['Acer', 'Asus', 'Dell', 'HP', 'Lenovo', 'Epson', 'Brother', 'Canon', 'Samsung', 'Apple', 'Other'];
        $packages = ['Batch 35', 'Batch 36', 'Batch 40', 'Batch 42', 'Batch 44', 'Other'];

        return view('equipments.edit', compact('equipment', 'schools', 'employees', 'items', 'brands', 'packages'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validatedData = $request->validate([
            'property_no'        => 'nullable|string|max:255|unique:equipment,property_no,' . $equipment->id,
            'old_property_no'    => 'nullable|string|max:255',
            'serial_number'      => 'nullable|string|max:255',
            // Details
            'item'               => 'required|string|max:255',
            'unit'               => 'nullable|string|max:50',
            'brand_manufacturer' => 'nullable|string|max:255',
            'model'              => 'nullable|string|max:255',
            'item_description'   => 'nullable|string',
            'specifications'     => 'nullable|string',
            // DCP
            'is_dcp'             => 'boolean',
            'dcp_package'        => 'nullable|string|max:255',
            'dcp_year'           => 'nullable|string|max:4',
            // Financial
            'acquisition_cost'   => 'nullable|numeric|min:0',
            'classification'     => 'nullable|string|max:255',
            'estimated_useful_life'=> 'nullable|integer|min:0',
            'gl_sl_code'         => 'nullable|string|max:255',
            'uacs_code'          => 'nullable|string|max:255',
            // Acquisition
            'mode_acquisition'   => 'nullable|string|max:255',
            'source_acquisition' => 'nullable|string|max:255',
            'donor'              => 'nullable|string|max:255',
            'source_funds'       => 'nullable|string|max:255',
            'allotment_class'    => 'nullable|string|max:255',
            'received_date'      => 'nullable|date',
            'pmp_reference'      => 'nullable|string|max:255',
            // Accountability
            'transaction_type'       => 'nullable|string|max:255',
            'supporting_doc_type'    => 'nullable|string|max:255',
            'supporting_doc_no'      => 'nullable|string|max:255',
            'accountable_officer_id' => 'nullable|exists:employees,id',
            'accountable_date'       => 'nullable|date',
            'custodian_id'           => 'nullable|exists:employees,id',
            'custodian_date'         => 'nullable|date',
            // Movement Tracking
            'new_accountable_id'      => 'nullable|exists:employees,id',
            'new_accountable_date'    => 'nullable|date',
            'new_supporting_doc_type' => 'nullable|string|max:255',
            'new_supporting_doc_no'   => 'nullable|string|max:255',
            // Supplier
            'supplier'           => 'nullable|string|max:255',
            'supplier_contact'   => 'nullable|string|max:255',
            'under_warranty'     => 'boolean',
            'warranty_end_date'  => 'nullable|date',
            // Status
            'equipment_location' => 'nullable|string|max:255',
            'is_functional'      => 'boolean',
            'equipment_condition'=> 'nullable|string|max:255',
            'disposition_status' => 'nullable|string|max:255',
            'remarks'            => 'nullable|string',
            // Associations
            'school_id'          => 'required|exists:schools,id',
        ]);

        // Process dynamic fields
        $qrCode = $validatedData['property_no'] ? 'QR-' . $validatedData['property_no'] : null;
        $category = (isset($validatedData['acquisition_cost']) && $validatedData['acquisition_cost'] >= 50000) ? 'High-value' : 'Low-value';

        $updateData = array_merge($validatedData, [
            'qr_code'        => $qrCode,
            'category'       => $category,
        ]);

        $original = $equipment->getOriginal();

        $equipment->update($updateData);

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

        return redirect('/equipment')->with('success', "Equipment '{$equipment->item}' updated successfully!");
    }

    public function destroy(Equipment $equipment)
    {

        ActivityLog::log(
            'DELETE', 
            'Equipment', 
            "Permanently deleted equipment: {$equipment->model} {$equipment->unit} {$equipment->brand_manufacturer} (Property No: {$equipment->property_no})"
        );

        $equipment->delete();

        return redirect('/equipment')->with('success', 'Equipment record removed successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'school', 
            'deployedStation', 
            'equipment',      
            'trainings' => function($q) {
                $q->where('status', 'approved'); 
            },
            'specialOrders',  
        ]);

        $designations = \DB::table('steplist')
            ->where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->get();

        $files = \Storage::disk('public')->files("uploads/{$employee->id}");

        return view('employee.show', compact('employee', 'designations', 'files'));
    }
}