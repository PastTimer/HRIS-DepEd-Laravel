<?php

namespace App\Http\Controllers;

use App\Models\PdsTraining;
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
        $query = PdsTraining::with(['personnel.pdsMain']);

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
                        ->orWhere('sponsor', 'like', "%{$search}%")
                        ->orWhereHas('personnel.pdsMain', function ($pdsQ) use ($search) {
                            $pdsQ->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%");
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
        $trainings = $query->orderBy('start_date', 'desc')
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
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|string',
            'sponsor' => 'required|string',
            'employee_ids' => 'required|array|min:1',
        ]);

        foreach ($validated['employee_ids'] as $personnelId) {
            $training = PdsTraining::create([
                'personnel_id' => $personnelId,
                'title' => $validated['title'],
                'hours' => $validated['hours'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'type' => $validated['type'],
                'sponsor' => $validated['sponsor'],
            ]);
            ActivityLog::log('CREATE', 'Training', "Created Training: {$training->title} for Personnel ID: {$personnelId}");
        }

        return redirect('/training')->with('success', 'Training records saved.');
    }

    public function edit(PdsTraining $training)
    {
        $employees = Personnel::with(['pdsMain:id,personnel_id,last_name,first_name'])
            ->where('is_active', true)
            ->orderBy('id')
            ->select(['id', 'emp_id', 'assigned_school_id', 'position_id', 'employee_type'])
            ->limit(100)
            ->get();
        return view('training.edit', compact('training', 'employees'));
    }

    public function update(Request $request, PdsTraining $training)
    {
        $request->validate([
            'title' => 'required|string',
            'hours' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|string',
            'sponsor' => 'required|string',
        ]);

        $training->update($request->only(['title', 'hours', 'start_date', 'end_date', 'type', 'sponsor']));

        ActivityLog::log('UPDATE', 'Training', "Updated Training: {$training->title}");

        return redirect('/training')->with('success', 'Training updated.');
    }

    public function destroy(PdsTraining $training)
    {
        ActivityLog::log('DELETE', 'Training', "Deleted Training: {$training->title}");
        $training->delete();

        return redirect('/training')->with('success', 'Training deleted.');
    }
}