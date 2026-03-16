<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderBy('role', 'asc')
            ->orderBy('last_name', 'asc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $schools = School::orderBy('name')->get(['id', 'name']);
        $employees = Employee::orderBy('last_name')->get(['id', 'first_name', 'last_name']);

        return view('users.create', compact('schools', 'employees'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username',
            'email'        => 'nullable|email|max:255|unique:users,email',
            'password'     => 'required|string|min:4|confirmed', 
            'role'         => 'required|in:admin,school,personnel', 
            'access_level' => 'required|string|max:255',         
            'office'       => 'required|string|max:255',
            'status'       => 'required|in:active,inactive',
        ]);

        User::create([
            'first_name'   => $validatedData['first_name'],
            'last_name'    => $validatedData['last_name'],
            'username'     => $validatedData['username'],
            'email'        => $validatedData['email'],
            'password'     => Hash::make($validatedData['password']), 
            'role'         => $validatedData['role'],
            'access_level' => $validatedData['access_level'],
            'office'       => $validatedData['office'],
            'status'       => $validatedData['status'],
        ]);

        ActivityLog::log(
            'CREATE', 
            'User Management', 
            "Created new user account: {$validatedData['username']}"
        );

        return redirect('/users')->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        $schools = School::orderBy('name')->get(['id', 'name']);
        $employees = Employee::orderBy('last_name')->get(['id', 'first_name', 'last_name']);

        return view('users.edit', compact('user', 'schools', 'employees'));
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'office'       => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'role'         => 'required|in:admin,school,personnel',
            'access_level' => 'required|string|max:255',
            'status'       => 'required|in:active,inactive',
            'password'     => 'nullable|string|min:4|confirmed', 
        ]);

        $updateData = [
            'first_name'   => $validatedData['first_name'],
            'last_name'    => $validatedData['last_name'],
            'office'       => $validatedData['office'],
            'username'     => $validatedData['username'],
            'email'        => $validatedData['email'],
            'role'         => $validatedData['role'],
            'access_level' => $validatedData['access_level'],
            'status'       => $validatedData['status'],
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