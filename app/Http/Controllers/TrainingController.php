<?php

namespace App\Http\Controllers;

use App\Models\PdsTraining;
use App\Models\Personnel;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    private function schoolScopeId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->hasRole('school') && $user->school_id) {
            return (int) $user->school_id;
        }

        if ($user && $user->hasRole('encoding_officer') && !$user->isGlobalEncodingOfficer() && $user->school_id) {
            return (int) $user->school_id;
        }

        return null;
    }

    private function assertTrainingAccess(PdsTraining $training): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasRole('personnel')) {
            abort_if((int) $training->personnel_id !== (int) $user->personnel_id, 403);
            return;
        }

        $schoolId = $this->schoolScopeId();
        if (!$schoolId) {
            return;
        }

        $recordSchoolId = (int) optional($training->personnel)->assigned_school_id;
        abort_if($recordSchoolId !== $schoolId, 403);
    }

    private function assertPersonnelCanModifyPending(PdsTraining $training): void
    {
        $user = Auth::user();
        if ($user && $user->hasRole('personnel')) {
            abort_if($training->verification_status !== 'pending', 403);
        }
    }

    private function assertNonPersonnelCanModifyVerified(PdsTraining $training): void
    {
        $user = Auth::user();
        if ($user && !$user->hasRole('personnel')) {
            abort_if($training->verification_status !== 'verified', 403);
        }
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = Auth::user();
        $isPersonnel = $user && $user->hasRole('personnel');
        $schoolId = $this->schoolScopeId();

        $query = PdsTraining::with(['personnel.pdsMain']);

        if ($isPersonnel) {
            $query->where('personnel_id', $user->personnel_id)
                ->where('verification_status', 'verified');
        } elseif ($schoolId) {
            $query->whereHas('personnel', function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            });
            $query->where('verification_status', 'verified');
        } else {
            $query->where('verification_status', 'verified');
        }

        $query->when($search, function ($q) use ($search, $isPersonnel) {
            $q->where(function ($subQuery) use ($search, $isPersonnel) {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('sponsor', 'like', "%{$search}%");

                if (!$isPersonnel) {
                    $subQuery->orWhereHas('personnel.pdsMain', function ($pdsQ) use ($search) {
                        $pdsQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                }
            });
        });

        $stats = [
            'total' => $isPersonnel ? null : (clone $query)->count(),
        ];

        $trainings = $query->orderBy('start_date', 'desc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('training.index', compact('trainings', 'stats'));
    }

    public function create()
    {
        $schoolId = $this->schoolScopeId();

        $employees = Personnel::with(['pdsMain:id,personnel_id,last_name,first_name'])
            ->where('is_active', true)
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->orderBy('id')
            ->select(['id', 'emp_id', 'assigned_school_id', 'position_id', 'employee_type'])
            ->limit(100)
            ->get();

        return view('training.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isPersonnel = $user && $user->hasRole('personnel');
        $schoolId = $this->schoolScopeId();

        if ($isPersonnel) {
            $validated = $request->validate([
                'title' => 'required|string',
                'hours' => 'required|integer',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'type' => 'required|string',
                'sponsor' => 'required|string',
            ]);

            $training = PdsTraining::create([
                'personnel_id' => $user->personnel_id,
                'created_by' => $user->id,
                'title' => $validated['title'],
                'hours' => $validated['hours'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'type' => $validated['type'],
                'sponsor' => $validated['sponsor'],
                'verification_status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
                'rejection_reason' => null,
            ]);

            ActivityLog::log('CREATE', 'Training', "Created Training request: {$training->title} for Personnel ID: {$user->personnel_id}");
        } else {
            $validated = $request->validate([
                'title' => 'required|string',
                'hours' => 'required|integer',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'type' => 'required|string',
                'sponsor' => 'required|string',
                'employee_ids' => 'required|array|min:1',
                'employee_ids.*' => 'required|integer|exists:personnel,id',
            ]);

            if ($schoolId) {
                $validCount = Personnel::whereIn('id', $validated['employee_ids'])
                    ->where('assigned_school_id', $schoolId)
                    ->count();

                abort_if($validCount !== count($validated['employee_ids']), 403);
            }

            foreach ($validated['employee_ids'] as $personnelId) {
                $training = PdsTraining::create([
                    'personnel_id' => $personnelId,
                    'created_by' => $user->id,
                    'title' => $validated['title'],
                    'hours' => $validated['hours'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'type' => $validated['type'],
                    'sponsor' => $validated['sponsor'],
                    'verification_status' => 'verified',
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                    'rejection_reason' => null,
                ]);

                ActivityLog::log('CREATE', 'Training', "Created Training: {$training->title} for Personnel ID: {$personnelId}");
            }
        }

        return redirect('/training')->with('success', 'Training records saved.');
    }

    // Requests list: Personnel sees own requests; Admin/School sees personnel-created requests.
    public function requests(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $user = Auth::user();
        $schoolId = $this->schoolScopeId();
        $isPersonnel = $user && $user->hasRole('personnel');

        $query = PdsTraining::with(['personnel.pdsMain', 'creator']);

        if ($isPersonnel) {
            $query->where('created_by', $user->id);
        } elseif ($schoolId) {
            $query->whereHas('personnel', function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            });

            $query->whereHas('creator.roles', function (Builder $q) {
                $q->where('name', 'personnel');
            });
        } else {
            $query->whereHas('creator.roles', function (Builder $q) {
                $q->where('name', 'personnel');
            });
        }

        $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('sponsor', 'like', "%{$search}%")
                    ->orWhereHas('personnel.pdsMain', function ($pdsQ) use ($search) {
                        $pdsQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        });

        $requests = $query
            ->orderByRaw("CASE WHEN verification_status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('training.requests', compact('requests'));
    }

    // ADMIN/SCHOOL: Approve a training request
    public function approveRequest(PdsTraining $training)
    {
        $this->assertTrainingAccess($training);

        $training->update([
            'verification_status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Training request approved.');
    }

    // ADMIN/SCHOOL: Reject a training request
    public function rejectRequest(Request $request, PdsTraining $training)
    {
        $this->assertTrainingAccess($training);

        $training->update([
            'verification_status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return back()->with('success', 'Training request rejected.');
    }

    public function show(PdsTraining $training)
    {
        $this->assertTrainingAccess($training);

        return view('training.show', compact('training'));
    }

    public function edit(PdsTraining $training)
    {
        $this->assertTrainingAccess($training);
        $this->assertPersonnelCanModifyPending($training);
        $this->assertNonPersonnelCanModifyVerified($training);

        return view('training.edit', compact('training'));
    }

    public function update(Request $request, PdsTraining $training)
    {
        $this->assertTrainingAccess($training);
        $this->assertPersonnelCanModifyPending($training);
        $this->assertNonPersonnelCanModifyVerified($training);

        $validated = $request->validate([
            'title' => 'required|string',
            'hours' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|string',
            'sponsor' => 'required|string',
        ]);

        $user = Auth::user();
        $isPersonnel = $user && $user->hasRole('personnel');

        $data = [
            'title' => $validated['title'],
            'hours' => $validated['hours'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'type' => $validated['type'],
            'sponsor' => $validated['sponsor'],
        ];

        if ($isPersonnel) {
            $data['verification_status'] = 'pending';
            $data['verified_by'] = null;
            $data['verified_at'] = null;
            $data['rejection_reason'] = null;
        } else {
            $data['verification_status'] = 'verified';
            $data['verified_by'] = $user->id;
            $data['verified_at'] = now();
            $data['rejection_reason'] = null;
        }

        $training->update($data);
        ActivityLog::log('UPDATE', 'Training', "Updated Training: {$training->title}");

        return redirect('/training')->with('success', $isPersonnel ? 'Update request sent for approval.' : 'Training updated.');
    }

    public function destroy(PdsTraining $training)
    {
        $this->assertTrainingAccess($training);
        $this->assertPersonnelCanModifyPending($training);
        $this->assertNonPersonnelCanModifyVerified($training);

        $user = Auth::user();
        $isPersonnel = $user && $user->hasRole('personnel');

        if ($isPersonnel) {
            ActivityLog::log('DELETE', 'Training', "Deleted pending Training request: {$training->title}");
            $training->delete();

            return redirect('/training/requests')->with('success', 'Pending training request deleted.');
        }

        ActivityLog::log('DELETE', 'Training', "Deleted Training: {$training->title}");
        $training->delete();

        return redirect('/training')->with('success', 'Training deleted.');
    }
}