<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\ActivityLog;
use App\Models\Personnel;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const SUPPORTED_ROLES = ['admin', 'school', 'encoding_officer', 'personnel'];

    public function activate(User $user)
    {
        // Admin can activate anyone; school can activate users under their school
        if (!Auth::user()->hasRole('admin')) {
            $this->assertSchoolUserCanManageTarget($user);
        }
        $user->status = 'active';
        $user->save();
        // If user is linked to personnel, also activate personnel
        if ($user->personnel_id) {
            $personnel = \App\Models\Personnel::find($user->personnel_id);
            if ($personnel) {
                $personnel->is_active = 1;
                $personnel->save();
            }
        }
        ActivityLog::log('UPDATE', 'User Management', "Activated user account: {$user->username}");
        return back()->with('success', 'User account activated.');
    }

    public function deactivate(User $user)
    {
        if (!Auth::user()->hasRole('admin')) {
            $this->assertSchoolUserCanManageTarget($user);
        }
        $user->status = 'inactive';
        $user->save();
        // If user is linked to personnel, also deactivate personnel
        if ($user->personnel_id) {
            $personnel = \App\Models\Personnel::find($user->personnel_id);
            if ($personnel) {
                $personnel->is_active = 0;
                $personnel->save();
            }
        }
        ActivityLog::log('UPDATE', 'User Management', "Deactivated user account: {$user->username}");
        return back()->with('success', 'User account deactivated.');
    }

    private function ensureHqSchool(): School
    {
        $hq = School::where('name', 'HQ')
            ->orWhere('school_id', 'HQ-0000')
            ->first();

        if ($hq) {
            return $hq;
        }

        return School::create([
            'school_id' => 'HQ-0000',
            'name' => 'HQ',
            'district_id' => null,
            'is_active' => true,
        ]);
    }

    private function ensurePlaceholderPosition(): Position
    {
        return Position::firstOrCreate(
            ['title' => 'Unassigned Position'],
            [
                'description' => 'Auto-generated placeholder position.',
                'type' => 'Non-teaching',
                'is_active' => true,
            ]
        );
    }

    private function createPlaceholderSchool(): School
    {
        $base = 'AUTO-SCHOOL-';
        $counter = 1;

        do {
            $code = $base . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $counter++;
        } while (School::where('school_id', $code)->exists());

        return School::create([
            'school_id' => $code,
            'name' => 'Blank School ' . substr($code, -4),
            'district_id' => null,
            'is_active' => true,
        ]);
    }

    private function createPlaceholderPersonnel(?int $preferredSchoolId = null): Personnel
    {
        $position = $this->ensurePlaceholderPosition();
        $schoolId = $preferredSchoolId ?: $this->ensureHqSchool()->id;

        return Personnel::create([
            'position_id' => $position->id,
            'assigned_school_id' => $schoolId,
            'deployed_school_id' => $schoolId,
            'is_active' => true,
            'current_step' => 1,
            'last_step_increment_date' => now()->toDateString(),
            'employee_type' => 'Regular',
            'salary_grade' => null,
            'salary_actual' => null,
            'branch' => null,
            'emp_id' => null,
            'item_number' => null,
            'profile_photo' => null,
        ]);
    }

    private function defaultPersonnelSchoolId(): int
    {
        $schoolUserId = $this->schoolUserId();
        if ($schoolUserId) {
            return $schoolUserId;
        }

        return $this->ensureHqSchool()->id;
    }

    private function isSchoolUser(): bool
    {
        return Auth::check() && Auth::user()->hasRole('school');
    }

    private function schoolUserId(): ?int
    {
        return $this->isSchoolUser() && Auth::user()->school_id ? (int) Auth::user()->school_id : null;
    }

    private function canSchoolUserManageTarget(User $user): bool
    {
        $schoolId = $this->schoolUserId();
        if (!$schoolId) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'school', 'encoding_officer'])) {
            return false;
        }

        $assignedSchoolId = (int) optional($user->personnel)->assigned_school_id;
        return $assignedSchoolId === $schoolId;
    }

    private function assertSchoolUserCanManageTarget(User $user): void
    {
        if ($this->isSchoolUser()) {
            abort_if(!$this->canSchoolUserManageTarget($user), 403);
        }
    }

    private function resolveLinkedStatus(?int $schoolId, ?int $personnelId, string $fallback = 'active'): string
    {
        if ($personnelId) {
            $personnel = Personnel::find($personnelId);

            return $personnel && (int) $personnel->is_active === 1 ? 'active' : 'inactive';
        }

        if ($schoolId) {
            $school = School::find($schoolId);

            return $school && (int) $school->is_active === 1 ? 'active' : 'inactive';
        }

        return $fallback;
    }

    private function normalizeLinking(array &$validatedData, ?User $currentUser = null, ?string $forcedRole = null): ?array
    {
        $role = $forcedRole ?? ($validatedData['role'] ?? null);
        if (!$role) {
            return ['role' => 'Role is required.'];
        }

        $currentUserId = $currentUser?->id;

        if (!empty($validatedData['school_id']) && !empty($validatedData['personnel_id'])) {
            return ['school_id' => 'Link a user to only one target: school OR personnel.'];
        }

        if ($role === 'school') {
            if (empty($validatedData['school_id'])) {
                $validatedData['school_id'] = $this->createPlaceholderSchool()->id;
            }

            $schoolAlreadyLinked = User::role('school')
                ->where('school_id', $validatedData['school_id'])
                ->when($currentUserId, fn($q) => $q->where('id', '!=', $currentUserId))
                ->exists();

            if ($schoolAlreadyLinked) {
                return ['school_id' => 'This school already has a School user linked.'];
            }

            $validatedData['personnel_id'] = null;
        }

        if ($role === 'personnel') {
            if (empty($validatedData['personnel_id'])) {
                $validatedData['personnel_id'] = $this->createPlaceholderPersonnel($this->defaultPersonnelSchoolId())->id;
            }

            $personnelAlreadyLinked = User::role('personnel')
                ->where('personnel_id', $validatedData['personnel_id'])
                ->when($currentUserId, fn($q) => $q->where('id', '!=', $currentUserId))
                ->exists();

            if ($personnelAlreadyLinked) {
                return ['personnel_id' => 'This personnel already has a linked user.'];
            }

            $validatedData['school_id'] = null;
        }

        if ($role === 'encoding_officer') {
            if (!empty($validatedData['personnel_id'])) {
                return ['personnel_id' => 'Encoding Officer cannot be linked to personnel.'];
            }
            $validatedData['personnel_id'] = null;

            if (empty($validatedData['school_id'])) {
                $validatedData['school_id'] = $this->ensureHqSchool()->id;
            }

            if (!empty($validatedData['school_id'])) {
                $eoForSchoolExists = User::role('encoding_officer')
                    ->where('school_id', $validatedData['school_id'])
                    ->when($currentUserId, fn($q) => $q->where('id', '!=', $currentUserId))
                    ->exists();

                if ($eoForSchoolExists) {
                    return ['school_id' => 'This school already has an Encoding Officer linked.'];
                }
            }
        }

        if ($role === 'admin') {
            $validatedData['school_id'] = $this->ensureHqSchool()->id;
            $validatedData['personnel_id'] = null;
        }

        return null;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $schoolId = $this->schoolUserId();

        $users = User::with(['roles', 'school', 'personnel.pdsMain'])
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->where(function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)
                        ->orWhereHas('personnel', function ($pq) use ($schoolId) {
                            $pq->where('assigned_school_id', $schoolId);
                        });
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('roles', function ($rq) use ($search) {
                            $rq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('school', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('personnel.pdsMain', function ($pq) use ($search) {
                            $pq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
                        ->orderBy('username', 'asc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $schoolId = $this->schoolUserId();

        $linkedSchoolIds = User::role('school')->whereNotNull('school_id')->pluck('school_id')->toArray();
        $linkedEOIds = User::role('encoding_officer')->whereNotNull('school_id')->pluck('school_id')->toArray();
        $availableSchoolUsers = School::whereNotIn('id', $linkedSchoolIds)->orderBy('name')->get(['id', 'name']);
        $availableEncodingOfficerSchools = School::whereNotIn('id', $linkedEOIds)->orderBy('name')->get(['id', 'name']);

        $linkedPersonnelIds = User::role('personnel')->whereNotNull('personnel_id')->pluck('personnel_id')->toArray();
        $personnelList = Personnel::with('pdsMain:id,personnel_id,first_name,last_name')
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->whereNotIn('id', $linkedPersonnelIds)
            ->orderBy('id', 'desc')->get(['id', 'emp_id']);

        $roles = $this->isSchoolUser() ? ['personnel'] : self::SUPPORTED_ROLES;

        if ($schoolId) {
            $availableSchoolUsers = collect();
            $availableEncodingOfficerSchools = collect();
        }

        return view('users.create', [
            'availableSchoolUsers' => $availableSchoolUsers,
            'availableEncodingOfficerSchools' => $availableEncodingOfficerSchools,
            'personnelList' => $personnelList,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolUserId();

        $validatedData = $request->validate([
            'username'     => 'required|string|max:255|unique:users,username',
            'email'        => 'nullable|email|max:255|unique:users,email',
            'password'     => 'required|string|min:4|confirmed',
            'role'         => ['required', Rule::in(self::SUPPORTED_ROLES)],
            'school_id'    => 'nullable|exists:schools,id',
            'personnel_id' => 'nullable|exists:personnel,id',
        ]);

        $linkErrors = $this->normalizeLinking($validatedData);
        if ($linkErrors) {
            return back()->withErrors($linkErrors)->withInput();
        }

        if ($schoolId) {
            if (($validatedData['role'] ?? null) !== 'personnel' || empty($validatedData['personnel_id'])) {
                return back()->withErrors(['role' => 'School users can only create personnel-linked accounts for their school.'])->withInput();
            }

            $personnel = Personnel::find($validatedData['personnel_id']);
            if (!$personnel || (int) $personnel->assigned_school_id !== $schoolId) {
                return back()->withErrors(['personnel_id' => 'Selected personnel is not assigned to your school.'])->withInput();
            }

            $validatedData['school_id'] = null;
        }

        $status = $this->resolveLinkedStatus(
            $validatedData['school_id'] ?? null,
            $validatedData['personnel_id'] ?? null,
            'active'
        );

        $user = User::create([
            'username'     => $validatedData['username'],
            'email'        => $validatedData['email'],
            'password'     => Hash::make($validatedData['password']),
            'school_id'    => $validatedData['school_id'] ?? null,
            'personnel_id' => $validatedData['personnel_id'] ?? null,
            'status'       => $status,
        ]);

        $user->syncRoles([$validatedData['role']]);

        ActivityLog::log(
            'CREATE',
            'User Management',
            "Created new user account: {$validatedData['username']}"
        );

        return redirect('/users')->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        $this->assertSchoolUserCanManageTarget($user);

        $user->load(['roles', 'school', 'personnel.pdsMain']);
        $currentRole = $user->getRoleNames()->first();
        $schoolId = $this->schoolUserId();

        $linkedSchoolIds = User::role('school')
            ->where('id', '!=', $user->id)
            ->whereNotNull('school_id')
            ->pluck('school_id')
            ->toArray();
        $linkedEOIds = User::role('encoding_officer')
            ->where('id', '!=', $user->id)
            ->whereNotNull('school_id')
            ->pluck('school_id')
            ->toArray();

        $availableSchoolUsers = School::where(function ($q) use ($linkedSchoolIds, $user) {
            $q->whereNotIn('id', $linkedSchoolIds);
            if (!empty($user->school_id)) {
                $q->orWhere('id', $user->school_id);
            }
        })->orderBy('name')->get(['id', 'name']);

        $availableEncodingOfficerSchools = School::where(function ($q) use ($linkedEOIds, $user) {
            $q->whereNotIn('id', $linkedEOIds);
            if (!empty($user->school_id)) {
                $q->orWhere('id', $user->school_id);
            }
        })->orderBy('name')->get(['id', 'name']);

        $linkedPersonnelIds = User::role('personnel')
            ->where('id', '!=', $user->id)
            ->whereNotNull('personnel_id')
            ->pluck('personnel_id')
            ->toArray();
        $personnelList = Personnel::with('pdsMain:id,personnel_id,first_name,last_name')
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('assigned_school_id', $schoolId);
            })
            ->where(function ($q) use ($linkedPersonnelIds, $user) {
                $q->whereNotIn('id', $linkedPersonnelIds);
                if (!empty($user->personnel_id)) {
                    $q->orWhere('id', $user->personnel_id);
                }
            })
            ->orderBy('id', 'desc')->get(['id', 'emp_id']);

        if ($schoolId) {
            $availableSchoolUsers = collect();
            $availableEncodingOfficerSchools = collect();
        }

        // Build $schoolOptions for the view
        $schoolOptions = [];
        foreach ($availableSchoolUsers as $school) {
            $schoolOptions[$school->id] = [
                'id' => $school->id,
                'name' => $school->name,
                'allow_school' => true,
                'allow_eo' => false,
            ];
        }
        foreach ($availableEncodingOfficerSchools as $school) {
            if (!isset($schoolOptions[$school->id])) {
                $schoolOptions[$school->id] = [
                    'id' => $school->id,
                    'name' => $school->name,
                    'allow_school' => false,
                    'allow_eo' => true,
                ];
            } else {
                $schoolOptions[$school->id]['allow_eo'] = true;
            }
        }

        return view('users.edit', [
            'user' => $user,
            'availableSchoolUsers' => $availableSchoolUsers,
            'availableEncodingOfficerSchools' => $availableEncodingOfficerSchools,
            'schoolOptions' => $schoolOptions,
            'personnelList' => $personnelList,
            'currentRole' => $currentRole,
            'isSelfAccount' => false,
            'cancelRoute' => 'users.index',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertSchoolUserCanManageTarget($user);

        $schoolId = $this->schoolUserId();
        $currentRole = $user->getRoleNames()->first();

        $validatedData = $request->validate([
            'username'     => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'school_id'    => 'nullable|exists:schools,id',
            'personnel_id' => 'nullable|exists:personnel,id',
            'password'     => 'nullable|string|min:4|confirmed',
        ]);

        $linkErrors = $this->normalizeLinking($validatedData, $user, $currentRole);
        if ($linkErrors) {
            return back()->withErrors($linkErrors)->withInput();
        }

        if ($schoolId) {
            if ($currentRole !== 'personnel' || empty($validatedData['personnel_id'])) {
                return back()->withErrors(['role' => 'School users can only manage personnel-linked accounts for their school.'])->withInput();
            }

            $personnel = Personnel::find($validatedData['personnel_id']);
            if (!$personnel || (int) $personnel->assigned_school_id !== $schoolId) {
                return back()->withErrors(['personnel_id' => 'Selected personnel is not assigned to your school.'])->withInput();
            }

            $validatedData['school_id'] = null;
        }

        $status = $this->resolveLinkedStatus(
            $validatedData['school_id'] ?? null,
            $validatedData['personnel_id'] ?? null,
            $user->status ?? 'active'
        );

        $updateData = [
            'username'     => $validatedData['username'],
            'email'        => $validatedData['email'],
            'school_id'    => $validatedData['school_id'] ?? null,
            'personnel_id' => $validatedData['personnel_id'] ?? null,
            'status'       => $status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        $original = $user->getOriginal();
        $user->update($updateData);

        $changes = [];
        foreach ($user->getChanges() as $key => $newValue) {
            if ($key !== 'updated_at' && $key !== 'password') { 
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue
                ];
            }
        }

        if ($currentRole) {
            $user->syncRoles([$currentRole]);
        }

        if (!empty($changes)) {
            ActivityLog::log(
                'UPDATE',
                'User Management',
                "Updated account details for {$user->username}",
                $changes
            );
        }

        return redirect('/users')->with('success', 'User account updated successfully.');
    }

    public function myAccount()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $user->load(['roles', 'school', 'personnel.pdsMain']);

        return view('users.my_account', compact('user'));
    }

    public function editOwnAccount()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $user->load(['roles', 'school', 'personnel.pdsMain']);

        return view('users.edit', [
            'user' => $user,
            'availableSchoolUsers' => collect(),
            'availableEncodingOfficerSchools' => collect(),
            'schoolOptions' => [],
            'personnelList' => collect(),
            'currentRole' => $user->getRoleNames()->first(),
            'isSelfAccount' => true,
            'cancelRoute' => 'users.account.show',
        ]);
    }

    public function updateOwnAccount(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:4|confirmed',
        ]);

        $updateData = [
            'username' => $validatedData['username'],
            'email' => $validatedData['email'] ?? null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($updateData);

        ActivityLog::log('UPDATE', 'User Management', "Updated own account: {$user->username}");

        return redirect()->route('users.account.show')->with('success', 'Account updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->assertSchoolUserCanManageTarget($user);

        if (Auth::id() === $user->id) {
            return redirect('/users')->with('error', 'You cannot delete your own account.');
        }

        ActivityLog::log(
            'DELETE', 
            'User Management', 
            "Permanently deleted user account: {$user->username}"
        );

        $user->delete();

        return redirect('/users')->with('success', 'User account permanently deleted.');
    }
}