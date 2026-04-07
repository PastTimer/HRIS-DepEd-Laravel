<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Personnel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        // 1. Base query with relationships
        $query = Training::with(['employees.school']);

        // 2. Security: Filter by school access
        if ($user && $user->hasRole('school') && $user->school_id) {
            $query->whereHas('employees', function($q) use ($user) {
                $q->where('assigned_school_id', $user->school_id);
            });
        }

        // 3. Apply Search Logic
        $query->when($search, function ($q) use ($search) {
            $q->where(function($subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('trefid', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
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

        // 4. Calculate Stats based on the (potentially searched) query
        $stats = [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count()
        ];

        // 5. Paginate and append search for the URL
        $trainings = $query->orderBy('created_at', 'desc')
                        ->paginate(15)
                        ->appends(['search' => $search]);

        return view('training.index', compact('trainings', 'stats'));
    }

    public function create()
    {
        $employees = Personnel::with(['pdsMain:id,personnel_id,last_name,first_name'])
            ->where('is_active', true)
            ->orderBy('id')
            ->select(['id', 'emp_id', 'assigned_school_id', 'position_id', 'employee_type'])
            ->limit(100)
            ->get();
        return view('training.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'hours' => 'required|integer',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'employee_ids' => 'required|array|min:1',
            'file' => 'nullable|file|max:10240',
        ]);

        $trefid = date("ymdHis" . mt_rand(10, 99)); // Legacy style ID
        $path = $request->hasFile('file') ? $request->file('file')->store('trainings', 'public') : null;

        $training = Training::create([
            'trefid' => $trefid,
            'title' => $validated['title'],
            'hours' => $validated['hours'],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'file_path' => $path,
            'status' => 'approved', // Admin default
            'created_by' => Auth::id(),
        ]);

        $training->employees()->attach($request->employee_ids);

        ActivityLog::log('CREATE', 'Training', "Created Training: {$training->title}");

        return redirect('/training')->with('success', 'Training record saved.');
    }

    public function edit(Training $training)
    {
        $training->load('employees');
        // For large datasets, consider AJAX search instead of loading all
        $employees = Personnel::with(['pdsMain:id,personnel_id,last_name,first_name'])
            ->where('is_active', true)
            ->orderBy('id')
            ->select(['id', 'emp_id', 'assigned_school_id', 'position_id', 'employee_type'])
            ->limit(100)
            ->get();
        return view('training.edit', compact('training', 'employees'));
    }

    public function update(Request $request, Training $training)
    {
        $request->validate(['title' => 'required', 'employee_ids' => 'required|array']);

        if ($request->hasFile('file')) {
            if ($training->file_path) Storage::disk('public')->delete($training->file_path);
            $training->file_path = $request->file('file')->store('trainings', 'public');
        }

        $training->update($request->only(['title', 'hours', 'date_from', 'date_to', 'status']));
        $training->employees()->sync($request->employee_ids);

        ActivityLog::log('UPDATE', 'Training', "Updated Training: {$training->title}");

        return redirect('/training')->with('success', 'Training updated.');
    }

    public function destroy(Training $training)
    {
        if ($training->file_path) Storage::disk('public')->delete($training->file_path);
        ActivityLog::log('DELETE', 'Training', "Deleted Training: {$training->title}");
        $training->delete();

        return redirect('/training')->with('success', 'Training deleted.');
    }
}