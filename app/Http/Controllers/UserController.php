<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\YoungStudent;
use App\Models\AdultStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Get all users
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    // Get a single user by ID
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    // Update a user
    public function update(Request $request, $id)
    {
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

    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}