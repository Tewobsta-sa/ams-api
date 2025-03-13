<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Student;
use App\Models\YoungStudent;
use App\Models\AdultStudent;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
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
            'role' => 'required|string|in:Admin,Student Data Manager,Student,Super User',
            'student_type' => 'nullable|string|in:young,adult',
        ]);
    
        if ($request->role === 'Student') {
            $request->validate([
                'birth_date' => 'required|date',
                'christian_name' => 'required|string',
                'gender' => 'required|string',
                'educational_level' => 'required|string',
                'discrit' => 'nullable|string',
                'special_place' => 'nullable|string',
                'house_no' => 'nullable|string',
                'class_name' => 'required|string|exists:classes,name', 
                'parent_name' => 'required_if:student_type,young|string',
                'parent_phone_number' => 'required_if:student_type,young|string',
                'school_name' => 'required_if:student_type,young|string',
                'emergency_responder' => 'required_if:student_type,adult|string',
                'phone_number' => 'required_if:student_type,adult|string',
                'emergency_responder_phone_number' => 'required_if:student_type,adult|string',
            ]);
    
            // Get the class by ID and check if it belongs to the correct student type
            $class = Classes::where('name', $request->class_name)->first();

            if (!$class) {
                return response()->json(['message' => 'Class not found.'], 422);
            }

            if ($class->student_type !== $request->student_type) {
                return response()->json(['message' => 'Invalid class selection for student type.'], 422);
            }

    
            // Calculate age from birth date
            $age = Carbon::parse($request->birth_date)->age;
    
            // Register the student
            $student = Student::create([
                'name' => $request->name,
                'gender' => $request->gender,
                'christian_name' => $request->christian_name,
                'birth_date' => $request->birth_date,
                'age' => $age,
                'educational_level' => $request->educational_level,
                'discrit' => $request->discrit,
                'special_place' => $request->special_place,
                'house_no' => $request->house_no,
                'student_type' => $request->student_type,
            ]);
    
            // Assign to the correct student type table
            if ($request->student_type === 'young') {
                YoungStudent::create([
                    'student_id' => $student->id,
                    'parent_name' => $request->parent_name,
                    'parent_phone_number' => $request->parent_phone_number,
                    'school_name' => $request->school_name,
                    'class_id' => $class->id, // Store class ID
                ]);
            } elseif ($request->student_type === 'adult') {
                AdultStudent::create([
                    'student_id' => $student->id,
                    'emergency_responder' => $request->emergency_responder,
                    'phone_number' => $request->phone_number,
                    'emergency_responder_phone_number' => $request->emergency_responder_phone_number,
                    'class_id' => $class->id, // Store class ID
                ]);
            }
    
            return response()->json(['message' => 'Student registered successfully'], 201);
        } else {
            $role = Role::where('name', $request->role)->firstOrFail();
    
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role_id' => $role->id,
            ]);
    
            return response()->json(['message' => 'User registered successfully'], 201);
        }
    }
    

    public function login(Request $request)
    {
        // Validate the request input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate an authentication token (for API-based login)
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }
}
