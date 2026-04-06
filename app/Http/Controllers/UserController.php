<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\ActivityLog;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const SUPPORTED_ROLES = ['admin', 'school', 'encoding_officer', 'personnel'];

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

    private function normalizeLinking(array &$validatedData, ?User $currentUser = null): ?array
    {
        $role = $validatedData['role'];
        $currentUserId = $currentUser?->id;

        if (!empty($validatedData['school_id']) && !empty($validatedData['personnel_id'])) {
            return ['school_id' => 'Link a user to only one target: school OR personnel.'];
        }

        if ($role === 'school') {
            if (empty($validatedData['school_id'])) {
                return ['school_id' => 'School role requires a linked school.'];
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
                return ['personnel_id' => 'Personnel role requires a linked personnel record.'];
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
            // Encoding officer may optionally pick a school.
            $validatedData['personnel_id'] = null;

            if (!empty($validatedData['school_id'])) {
                $eoForSchoolExists = User::role('encoding_officer')
                    ->where('school_id', $validatedData['school_id'])
                    ->when($currentUserId, fn($q) => $q->where('id', '!=', $currentUserId))
                    ->exists();

                if ($eoForSchoolExists) {
                    return ['school_id' => 'This school already has an Encoding Officer linked.'];
                }
            }

            // Only allow one encoding officer with no school
            if (empty($validatedData['school_id'])) {
                $eoNoSchoolExists = User::role('encoding_officer')
                    ->whereNull('school_id')
                    ->when($currentUserId, fn($q) => $q->where('id', '!=', $currentUserId));

                if ($eoNoSchoolExists->exists()) {
                    return ['school_id' => 'There is already an Encoding Officer with no school.'];
                }
            }
        }

        if ($role === 'admin') {
            $validatedData['school_id'] = null;
            $validatedData['personnel_id'] = null;
        }

        return null;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with(['roles', 'school', 'personnel.pdsMain'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('office', 'like', "%{$search}%")
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
        $linkedSchoolIds = User::role('school')->whereNotNull('school_id')->pluck('school_id')->toArray();
        $linkedEOIds = User::role('encoding_officer')->whereNotNull('school_id')->pluck('school_id')->toArray();
        $availableSchoolUsers = School::whereNotIn('id', $linkedSchoolIds)->orderBy('name')->get(['id', 'name']);
        $availableEncodingOfficerSchools = School::whereNotIn('id', $linkedEOIds)->orderBy('name')->get(['id', 'name']);

        $linkedPersonnelIds = User::role('personnel')->whereNotNull('personnel_id')->pluck('personnel_id')->toArray();
        $personnelList = Personnel::with('pdsMain:id,personnel_id,first_name,last_name')
            ->whereNotIn('id', $linkedPersonnelIds)
            ->orderBy('id', 'desc')->get(['id', 'emp_id']);

        $roles = self::SUPPORTED_ROLES;

        return view('users.create', [
            'availableSchoolUsers' => $availableSchoolUsers,
            'availableEncodingOfficerSchools' => $availableEncodingOfficerSchools,
            'personnelList' => $personnelList,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username'     => 'required|string|max:255|unique:users,username',
            'email'        => 'nullable|email|max:255|unique:users,email',
            'password'     => 'required|string|min:4|confirmed',
            'role'         => ['required', Rule::in(self::SUPPORTED_ROLES)],
            'office'       => 'required|string|max:255',
            'school_id'    => 'nullable|exists:schools,id',
            'personnel_id' => 'nullable|exists:personnel,id',
        ]);

        $linkErrors = $this->normalizeLinking($validatedData);
        if ($linkErrors) {
            return back()->withErrors($linkErrors)->withInput();
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
            'office'       => $validatedData['office'],
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
        $user->load(['roles', 'school', 'personnel.pdsMain']);
        $currentRole = $user->getRoleNames()->first();

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
            ->where(function ($q) use ($linkedPersonnelIds, $user) {
                $q->whereNotIn('id', $linkedPersonnelIds);
                if (!empty($user->personnel_id)) {
                    $q->orWhere('id', $user->personnel_id);
                }
            })
            ->orderBy('id', 'desc')->get(['id', 'emp_id']);

        $roles = self::SUPPORTED_ROLES;

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
            'roles' => $roles,
            'currentRole' => $currentRole,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'office'       => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'role'         => ['required', Rule::in(self::SUPPORTED_ROLES)],
            'school_id'    => 'nullable|exists:schools,id',
            'personnel_id' => 'nullable|exists:personnel,id',
            'password'     => 'nullable|string|min:4|confirmed',
        ]);

        $linkErrors = $this->normalizeLinking($validatedData, $user);
        if ($linkErrors) {
            return back()->withErrors($linkErrors)->withInput();
        }

        $status = $this->resolveLinkedStatus(
            $validatedData['school_id'] ?? null,
            $validatedData['personnel_id'] ?? null,
            $user->status ?? 'active'
        );

        $updateData = [
            'office'       => $validatedData['office'],
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

        $user->syncRoles([$validatedData['role']]);

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

    public function destroy(User $user)
    {
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