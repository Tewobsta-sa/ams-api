<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\YoungStudent;
use App\Models\AdultStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StudentController extends Controller
{
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
        ]);

        $student->update($request->all());
        return response()->json(['message' => 'Student updated successfully', 'student' => $student], 200);
    }

    // Delete a student
    public function destroy($id)
    {
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
