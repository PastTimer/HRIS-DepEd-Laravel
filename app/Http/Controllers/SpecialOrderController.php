<?php

namespace App\Http\Controllers;

use App\Models\SpecialOrder;
use App\Models\Personnel;
use App\Models\SoType;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SpecialOrderController extends Controller
{
    private function scopedOrdersQuery(): Builder
    {
        $user = Auth::user();
        $query = SpecialOrder::query();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        if (($user->hasRole('school') || $user->hasRole('encoding_officer')) && $user->school_id) {
            return $query
                ->whereHas('personnel', function (Builder $q) use ($user) {
                    $q->where('assigned_school_id', $user->school_id);
                })
                ->whereDoesntHave('personnel', function (Builder $q) use ($user) {
                    $q->where('assigned_school_id', '!=', $user->school_id);
                });
        }

        if ($user->hasRole('personnel') && $user->personnel_id) {
            return $query->whereHas('personnel', function (Builder $q) use ($user) {
                $q->where('personnel.id', $user->personnel_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    private function scopedPersonnelQuery(): Builder
    {
        $user = Auth::user();

        $query = Personnel::with(['pdsMain:id,personnel_id,last_name,first_name'])
            ->where('is_active', true)
            ->orderBy('id')
            ->select(['id', 'emp_id', 'assigned_school_id', 'position_id', 'employee_type']);

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        if (($user->hasRole('school') || $user->hasRole('encoding_officer')) && $user->school_id) {
            return $query->where('assigned_school_id', $user->school_id);
        }

        if ($user->hasRole('personnel') && $user->personnel_id) {
            return $query->where('id', $user->personnel_id);
        }

        return $query->whereRaw('1 = 0');
    }

    private function ensureOrderAccessible(SpecialOrder $specialorder): void
    {
        $allowed = $this->scopedOrdersQuery()->whereKey($specialorder->id)->exists();
        abort_unless($allowed, 403);
    }

    private function ensureSelectedPersonnelAllowed(array $employeeIds): void
    {
        $user = Auth::user();
        $allowedIds = $this->scopedPersonnelQuery()->pluck('id')->all();
        $notAllowed = array_diff($employeeIds, $allowedIds);

        if (!empty($notAllowed)) {
            throw ValidationException::withMessages([
                'employee_ids' => 'One or more selected personnel are outside your allowed scope.',
            ]);
        }

        if ($user && $user->hasRole('personnel') && $user->personnel_id && !in_array((int) $user->personnel_id, $employeeIds, true)) {
            throw ValidationException::withMessages([
                'employee_ids' => 'You must include yourself in the selected personnel list.',
            ]);
        }
    }

    private function canDelete(SpecialOrder $specialorder): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('personnel') && $specialorder->status === 'Approved') {
            return false;
        }

        return $this->scopedOrdersQuery()->whereKey($specialorder->id)->exists();
    }

    private function resolveUnits(Request $request, SoType $type): float
    {
        if ($request->filled('units')) {
            return (float) $request->input('units');
        }

        return (float) $type->value;
    }

    private function buildPivotData(array $employeeIds, float $units): array
    {
        $syncData = [];
        foreach ($employeeIds as $id) {
            $syncData[(int) $id] = ['units' => $units];
        }

        return $syncData;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));


        $query = $this->scopedOrdersQuery()
            ->where('status', 'Approved')
            ->with(['type'])
            ->withCount('personnel');

        $query->when($search !== '', function (Builder $q) use ($search) {
            $q->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('so_number', 'like', "%{$search}%")
                    ->orWhere('series_year', 'like', "%{$search}%")
                    ->orWhereHas('type', function (Builder $typeQ) use ($search) {
                        $typeQ->where('name', 'like', "%{$search}%");
                    });
            });
        });

        $totalSo = (clone $query)->count();
        $orders = $query->latest()->paginate(15)->appends(['search' => $search]);
        $deletableOrderIds = $orders->getCollection()
            ->filter(fn (SpecialOrder $so) => $this->canDelete($so))
            ->pluck('id')
            ->all();

        return view('specialorder.index', compact('orders', 'totalSo', 'deletableOrderIds'));
    }

    public function submissions(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = $this->scopedOrdersQuery()
            ->with(['type', 'creator'])
            ->withCount('personnel');

        $query->when($search !== '', function (Builder $q) use ($search) {
            $q->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('so_number', 'like', "%{$search}%")
                    ->orWhereHas('type', function (Builder $typeQ) use ($search) {
                        $typeQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function (Builder $creatorQ) use ($search) {
                        $creatorQ->where('username', 'like', "%{$search}%");
                    });
            });
        });

        $submissions = $query->latest()->paginate(15)->appends(['search' => $search]);
        $deletableOrderIds = $submissions->getCollection()
            ->filter(fn (SpecialOrder $so) => $this->canDelete($so))
            ->pluck('id')
            ->all();

        return view('specialorder.submissions', compact('submissions', 'deletableOrderIds'));
    }

    public function create()
    {
        $employees = $this->scopedPersonnelQuery()->get();
        $types = SoType::orderBy('name')->get();

        return view('specialorder.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'so_number'    => 'required|string|max:255',
            'series_year'  => 'required|string|size:4',
            'type_id'      => 'required|exists:so_types,id',
            'units'        => 'nullable|numeric',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'required|integer|exists:personnel,id',
        ]);


        $employeeIds = array_map('intval', $validated['employee_ids']);
        $this->ensureSelectedPersonnelAllowed($employeeIds);

        $type = SoType::findOrFail((int) $validated['type_id']);
        $unitsPerPersonnel = $request->input('units_per_personnel', []);

        $specialorder = SpecialOrder::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'so_number'   => $validated['so_number'],
            'series_year' => $validated['series_year'],
            'type_id'     => (int) $validated['type_id'],
            'status'      => 'Pending',
            'created_by'  => Auth::id(),
        ]);

        $pivotData = [];
        foreach ($employeeIds as $id) {
            $pivotData[$id] = [
                'units' => isset($unitsPerPersonnel[$id]) && is_numeric($unitsPerPersonnel[$id]) ? (float)$unitsPerPersonnel[$id] : (float)$type->value
            ];
        }
        $specialorder->personnel()->attach($pivotData);

        ActivityLog::log('CREATE', 'Special Order', "Created SO: {$specialorder->title} (SO#: {$specialorder->so_number})");

        return redirect()->route('specialorder.index')->with('success', 'Special Order created successfully.');
    }

    public function edit(SpecialOrder $specialorder)
    {
        $this->ensureOrderAccessible($specialorder);

        $specialorder->load(['personnel', 'type']);
        $employees = $this->scopedPersonnelQuery()->get();
        $types = SoType::orderBy('name')->get();

        return view('specialorder.edit', compact('specialorder', 'employees', 'types'));
    }

    public function show(SpecialOrder $specialorder)
    {
        $this->ensureOrderAccessible($specialorder);

        $specialorder->load(['type', 'creator', 'approver', 'personnel.pdsMain']);

        return view('specialorder.show', compact('specialorder'));
    }

    public function update(Request $request, SpecialOrder $specialorder)
    {
        $this->ensureOrderAccessible($specialorder);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'so_number'    => 'required|string|max:255',
            'series_year'  => 'required|string|size:4',
            'type_id'      => 'required|exists:so_types,id',
            'units'        => 'nullable|numeric',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'required|integer|exists:personnel,id',
        ]);


        $employeeIds = array_map('intval', $validated['employee_ids']);
        $this->ensureSelectedPersonnelAllowed($employeeIds);

        $original = $specialorder->getOriginal();

        $type = SoType::findOrFail((int) $validated['type_id']);
        $unitsPerPersonnel = $request->input('units_per_personnel', []);

        $specialorder->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'so_number'   => $validated['so_number'],
            'series_year' => $validated['series_year'],
            'type_id'     => (int) $validated['type_id'],
        ]);

        $pivotData = [];
        foreach ($employeeIds as $id) {
            $pivotData[$id] = [
                'units' => isset($unitsPerPersonnel[$id]) && is_numeric($unitsPerPersonnel[$id]) ? (float)$unitsPerPersonnel[$id] : (float)$type->value
            ];
        }
        $specialorder->personnel()->sync($pivotData);

        $changes = [];
        foreach ($specialorder->getChanges() as $key => $val) {
            if ($key !== 'updated_at') {
                $changes[$key] = ['old' => $original[$key] ?? null, 'new' => $val];
            }
        }

        ActivityLog::log('UPDATE', 'Special Order', "Updated SO: {$specialorder->title}", $changes);

        return redirect()->route('specialorder.index')->with('success', 'Special Order updated successfully.');
    }

    public function updateStatus(Request $request, SpecialOrder $specialorder)
    {
        $this->ensureOrderAccessible($specialorder);

        $user = Auth::user();
        abort_unless($user && !$user->hasRole('personnel'), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Approved', 'Rejected', 'Pending'])],
        ]);

        $specialorder->status = $validated['status'];
        if ($validated['status'] === 'Pending') {
            $specialorder->approved_by = null;
            $specialorder->approved_at = null;
        } else {
            $specialorder->approved_by = $user->id;
            $specialorder->approved_at = now();
        }

        $specialorder->save();

        ActivityLog::log('UPDATE', 'Special Order', "Updated status of SO {$specialorder->so_number} to {$specialorder->status}");

        return redirect()->route('specialorder.submissions')->with('success', 'Special Order status updated.');
    }

    public function destroy(SpecialOrder $specialorder)
    {
        $this->ensureOrderAccessible($specialorder);
        abort_unless($this->canDelete($specialorder), 403);

        ActivityLog::log('DELETE', 'Special Order', "Deleted SO: {$specialorder->title}");

        $specialorder->delete();

        return redirect()->route('specialorder.index')->with('success', 'Special Order deleted.');
    }

    public function typeIndex(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $types = SoType::query()
            ->withCount('specialOrders')
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('specialorder.types.index', compact('types'));
    }

    public function typeCreate()
    {
        return view('specialorder.types.create');
    }

    public function typeStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:so_types,name',
            'value' => 'required|numeric',
        ]);

        $type = SoType::create($validated);

        ActivityLog::log('CREATE', 'Special Order Type', "Created SO Type: {$type->name}");

        return redirect()->route('specialorder.types.index')->with('success', 'Order type created successfully.');
    }

    public function typeEdit(SoType $soType)
    {
        return view('specialorder.types.edit', compact('soType'));
    }

    public function typeUpdate(Request $request, SoType $soType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('so_types', 'name')->ignore($soType->id)],
            'value' => 'required|numeric',
        ]);

        $original = $soType->getOriginal();
        $soType->update($validated);

        $changes = [];
        foreach ($soType->getChanges() as $key => $val) {
            if ($key !== 'updated_at') {
                $changes[$key] = ['old' => $original[$key] ?? null, 'new' => $val];
            }
        }

        ActivityLog::log('UPDATE', 'Special Order Type', "Updated SO Type: {$soType->name}", $changes);

        return redirect()->route('specialorder.types.index')->with('success', 'Order type updated successfully.');
    }

    public function typeDestroy(SoType $soType)
    {
        if ($soType->specialOrders()->exists()) {
            return redirect()->route('specialorder.types.index')->with('error', 'Cannot delete type because it is currently in use.');
        }

        ActivityLog::log('DELETE', 'Special Order Type', "Deleted SO Type: {$soType->name}");
        $soType->delete();

        return redirect()->route('specialorder.types.index')->with('success', 'Order type deleted successfully.');
    }
}