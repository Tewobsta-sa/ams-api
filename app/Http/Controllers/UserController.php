<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\YoungStudent;
use App\Models\AdultStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:Admin,Student Data Manager,Super User, Attendance Recorder',
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    // Get all users
    public function index()
    {
        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }

        return response()->json(User::all(), 200);
    }

    // Get a single user by ID
    public function show($id)
    {
        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $id,
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        $user->update($request->all());
        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    //search by name
    public function searchByName(Request $request)
    {
        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }

        $request->validate([
            'name' => 'required|string',
        ]);

        $users = User::where('name', 'like', '%' . $request->name . '%')->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No user found'], 404);
        }

        return response()->json($users, 200);
    }

// Filter users by role
    public function filterByRole(Request $request)
    {
        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }

        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $users = User::whereHas('role', function ($query) use ($request) {
            $query->where('name', $request->role);
        })->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No user found'], 404);
        }

        return response()->json($users, 200);
    }


    // Delete a user
    public function destroy($id)
    {
        if (Auth::user()->role->name !== 'Admin') {
            return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
        }
        
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}