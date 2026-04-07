<?php

namespace App\Http\Controllers;

use App\Models\SpecialOrder;
use App\Models\Personnel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpecialOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        // 1. Base query with relationships
        $query = SpecialOrder::with(['employees.school']);

        // 2. Security: School-level users see only orders containing their personnel
        if ($user && $user->hasRole('school') && $user->school_id) {
            $query->whereHas('employees', function($q) use ($user) {
                $q->where('assigned_school_id', $user->school_id);
            });
        }

        // 3. Apply Search Logic
        $query->when($search, function ($q) use ($search) {
            $q->where(function($subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('so_no', 'like', "%{$search}%")
                        ->orWhere('series_year', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        // Search related Personnel
                        ->orWhereHas('employees', function($empQ) use ($search) {
                            $empQ->whereHas('pdsMain', function ($pdsQ) use ($search) {
                                $pdsQ->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%");
                            })->orWhereHas('school', function($schQ) use ($search) {
                                $schQ->where('name', 'like', "%{$search}%");
                            });
                        });
            });
        });

        // 4. Calculate Stats (Dynamic)
        $totalSo = (clone $query)->count();

        // 5. Fetch Paginated Results
        $specialorder = $query->orderBy('created_at', 'desc')
                            ->paginate(15)
                            ->appends(['search' => $search]);

        return view('specialorder.index', compact('specialorder', 'totalSo'));
    }

    public function create()
    {
        $employees = Personnel::with('pdsMain')->where('is_active', true)->orderBy('id')->get();
        return view('specialorder.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string',
            'so_no'        => 'required|string',
            'series_year'  => 'required|string|max:4',
            'type'         => 'required|string',
            'custom_type'  => 'nullable|string|required_if:type,custom',
            'file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'employee_ids' => 'required|array|min:1',
        ]);

        $finalType = ($request->type === 'custom') ? $request->custom_type : $request->type;
        $path = $request->hasFile('file') ? $request->file('file')->store('special_orders', 'public') : null;

        $specialorder = SpecialOrder::create([
            'title'       => $validated['title'],
            'so_no'       => $validated['so_no'],
            'series_year' => $validated['series_year'],
            'type'        => $finalType,
            'file_path'   => $path,
            'created_by'  => Auth::id(),
        ]);

        $specialorder->employees()->attach($request->employee_ids);

        ActivityLog::log('CREATE', 'Special Order', "Created SO: {$specialorder->title} (SO#: {$specialorder->so_no})");

        return redirect('/specialorder')->with('success', 'Special Order created successfully.');
    }

    public function edit(SpecialOrder $specialorder)
    {
        $specialorder->load('employees');
        $employees = Personnel::with('pdsMain')->where('is_active', true)->orderBy('id')->get();
        return view('specialorder.edit', compact('specialorder', 'employees'));
    }

    public function update(Request $request, SpecialOrder $specialorder)
    {
        $request->validate([
            'title'        => 'required|string',
            'employee_ids' => 'required|array|min:1',
        ]);

        $original = $specialorder->getOriginal();
        $finalType = ($request->type === 'custom') ? $request->custom_type : $request->type;

        // Handle File Update
        if ($request->hasFile('file')) {
            if ($specialorder->file_path) Storage::disk('public')->delete($specialorder->file_path);
            $specialorder->file_path = $request->file('file')->store('special_orders', 'public');
        }

        $specialorder->update([
            'title'       => $request->title,
            'so_no'       => $request->so_no,
            'series_year' => $request->series_year,
            'type'        => $finalType,
        ]);

        // Sync many-to-many relationship
        $specialorder->employees()->sync($request->employee_ids);

        // Audit Trail: Log specific changes
        $changes = [];
        foreach ($specialorder->getChanges() as $key => $val) {
            if ($key !== 'updated_at') {
                $changes[$key] = ['old' => $original[$key] ?? null, 'new' => $val];
            }
        }

        ActivityLog::log('UPDATE', 'Special Order', "Updated SO: {$specialorder->title}", $changes);

        return redirect('/specialorder')->with('success', 'Special Order updated successfully.');
    }

    public function destroy(SpecialOrder $specialorder)
    {
        ActivityLog::log('DELETE', 'Special Order', "Deleted SO: {$specialorder->title}");
        
        // Delete attachment if exists
        if ($specialorder->file_path) Storage::disk('public')->delete($specialorder->file_path);
        
        $specialorder->delete();

        return redirect('/specialorder')->with('success', 'Special Order deleted.');
    }
}