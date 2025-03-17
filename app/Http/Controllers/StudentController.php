<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\YoungStudent;
use App\Models\AdultStudent;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function registerStudent(Request $request)
    {

        if (Auth::user()->role->name !== 'Student Data Manager') {
            return response()->json(['message' => 'Forbidden: Only Admins can register students.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'christian_name' => 'required|string',
            'gender' => 'required|string',
            'educational_level' => 'required|string',
            'discrit' => 'nullable|string',
            'special_place' => 'nullable|string',
            'house_no' => 'nullable|string',
            'student_type' => 'required|string|in:young,adult',
            'class_name' => 'required|string|exists:classes,name',
        ]);

        $class = Classes::where('name', $request->class_name)->first();

        if (!$class) {
            return response()->json(['message' => 'Class not found.'], 422);
        }

        if ($class->student_type !== $request->student_type) {
            return response()->json(['message' => 'Invalid class selection for student type.'], 422);
        }

        $age = Carbon::parse($request->birth_date)->age;

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

        if ($request->student_type === 'young') {
            $request->validate([
                'parent_name' => 'required|string',
                'parent_phone_number' => 'required|string',
                'school_name' => 'required|string',
            ]);

            YoungStudent::create([
                'student_id' => $student->id,
                'parent_name' => $request->parent_name,
                'parent_phone_number' => $request->parent_phone_number,
                'school_name' => $request->school_name,
                'class_id' => $class->id,
            ]);
        } elseif ($request->student_type === 'adult') {
            $request->validate([
                'emergency_responder' => 'required|string',
                'phone_number' => 'required|string',
                'emergency_responder_phone_number' => 'required|string',
            ]);

            AdultStudent::create([
                'student_id' => $student->id,
                'emergency_responder' => $request->emergency_responder,
                'phone_number' => $request->phone_number,
                'emergency_responder_phone_number' => $request->emergency_responder_phone_number,
                'class_id' => $class->id,
            ]);
        }

        return response()->json(['message' => 'Student registered successfully'], 201);
    }

    // Get all students
    public function index()
    {
        return response()->json(Student::with(['youngStudent', 'adultStudent'])->get(), 200);
    }

    // Get a single student by ID
    public function show($id)
    {
        $student = Student::with(['youngStudent', 'adultStudent'])->find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        return response()->json($student, 200);
    }
    
    public function filterByClass(Request $request)
{
        // Validate the request to ensure 'class_id' is provided
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id', // Ensure class_id is provided and valid
        ]);

        // Get the validated class ID
        $classId = $request->input('class_id');

        // Fetch students from young_students and adult_students tables
        $youngStudents = YoungStudent::where('class_id', $classId)->get();
        $adultStudents = AdultStudent::where('class_id', $classId)->get();

        // Merge both collections into one
        $students = $youngStudents->merge($adultStudents);

        // Return the combined list of students
        return response()->json($students, 200);
}
    

    // Search students by name
    public function searchByName(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:1', // Ensure search term is provided
        ]);

        $searchTerm = $request->search;

        $students = Student::with(['youngStudent', 'adultStudent'])
                            ->where(function ($q) use ($searchTerm) {
                                $q->where('name', 'LIKE', "%{$searchTerm}%")
                                  ->orWhere('christian_name', 'LIKE', "%{$searchTerm}%");
                            })
                            ->get();

        return response()->json($students, 200);
    }

    // Update a student
    public function update(Request $request, $id)
{
    if (Auth::user()->role->name !== 'Student Data Manager') {
        return response()->json(['message' => 'Forbidden: Only Admins can register users.'], 403);
    }

    $student = Student::find($id);
    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    $request->validate([
        'name' => 'sometimes|string|max:255',
        'gender' => 'sometimes|string',
        'birth_date' => 'sometimes|date',
        'christian_name' => 'sometimes|string',
        'educational_level' => 'sometimes|string',
        'discrit' => 'nullable|string',
        'special_place' => 'nullable|string',
        'house_no' => 'nullable|string',
        'student_type' => 'sometimes|string|in:young,adult',
        'class_name' => 'sometimes|string|exists:classes,name',
    ]);

    if ($request->has('birth_date')) {
        $age = Carbon::parse($request->birth_date)->age;
        $request->merge(['age' => $age]);
    }

    $student->update($request->only([
        'name', 'gender', 'birth_date', 'christian_name', 
        'educational_level', 'discrit', 'special_place', 
        'house_no', 'age'
    ]));

    if ($request->has('class_name')) {
        $class = Classes::where('name', $request->class_name)->first();
        if (!$class) {
            return response()->json(['message' => 'Class not found.'], 422);
        }

        if ($student->student_type !== $class->student_type) {
            return response()->json(['message' => 'Invalid class selection for student type.'], 422);
        }
    }

    if ($student->student_type === 'young') {
        $youngStudent = YoungStudent::where('student_id', $student->id)->first();
        if ($youngStudent) {
            $request->validate([
                'parent_name' => 'sometimes|string',
                'parent_phone_number' => 'sometimes|string',
                'school_name' => 'sometimes|string',
            ]);
            $youngStudent->update($request->only(['parent_name', 'parent_phone_number', 'school_name']));
        }
    } elseif ($student->student_type === 'adult') {
        $adultStudent = AdultStudent::where('student_id', $student->id)->first();
        if ($adultStudent) {
            $request->validate([
                'emergency_responder' => 'sometimes|string',
                'phone_number' => 'sometimes|string',
                'emergency_responder_phone_number' => 'sometimes|string',
            ]);
            $adultStudent->update($request->only(['emergency_responder', 'phone_number', 'emergency_responder_phone_number']));
        }
    }

    return response()->json(['message' => 'Student updated successfully', 'student' => $student], 200);
}

    // Delete a student
    public function destroy($id)
    {
        if (Auth::user()->role->name !== 'Student Data Manager') {
            return response()->json(['message' => 'Forbidden: Only Student Data Manager can register users.'], 403);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        if ($student->student_type === 'young') {
            YoungStudent::where('student_id', $student->id)->delete();
        } elseif ($student->student_type === 'adult') {
            AdultStudent::where('student_id', $student->id)->delete();
        }

        $student->delete();
        return response()->json(['message' => 'Student deleted successfully'], 200);
    }
}
