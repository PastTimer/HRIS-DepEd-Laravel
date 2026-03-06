<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Training::with('employees');

        // Security: Filter by school access
        if ($user && $user->role === 'school') {
            $query->whereHas('employees', function($q) use ($user) {
                $q->whereHas('school', function($sq) use ($user) {
                    $sq->where('name', $user->access_level);
                });
            });
        }

        $stats = [
            'total' => $query->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count()
        ];

        $trainings = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('training.index', compact('trainings', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('last_name')->get();
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
        $employees = Employee::where('is_active', true)->orderBy('last_name')->get();
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