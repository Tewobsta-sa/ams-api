<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use App\Models\YoungStudent;
use App\Models\AdultStudent;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function markAttendance(Request $request)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'status' => 'required|in:present,absent,permission',
    ]);

    // Get the student
    $student = Student::find($request->student_id);
    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    // Determine class based on student type
    $classId = null;
    if ($student->student_type === 'young') {
        $classId = YoungStudent::where('student_id', $student->id)->value('class_id');
    } elseif ($student->student_type === 'adult') {
        $classId = AdultStudent::where('student_id', $student->id)->value('class_id');
    }

    if (!$classId) {
        return response()->json(['message' => 'Student does not have a registered class'], 422);
    }

    // Set the current date (without allowing user input)
    $currentDate = now()->toDateString();

    // Check if attendance already marked for this student on the same date
    $existingAttendance = Attendance::where('student_id', $request->student_id)
        ->where('class_id', $classId)
        ->where('date', $currentDate)
        ->first();

    if ($existingAttendance) {
        return response()->json(['message' => 'Attendance already marked for this student today'], 422);
    }

    // Save attendance with the student's class ID and current date
    Attendance::create([
        'student_id' => $request->student_id,
        'class_id' => $classId, // Use the fetched class ID
        'date' => $currentDate, // Use server time
        'status' => $request->status,
    ]);

    return response()->json(['message' => 'Attendance marked successfully'], 201);
}

    public function getAttendanceDates()
    {
        $dates = Attendance::select('date')->distinct()->orderBy('date', 'desc')->pluck('date');

        return response()->json(['dates' => $dates]);
    }

    /**
     * Get attendance by class.
     */
    public function getAttendanceByClass($class_id)
    {
        $attendanceRecords = Attendance::where('class_id', $class_id)->with('student')->get();

        return response()->json(['attendance' => $attendanceRecords]);
    }

    /**
     * Get attendance by date.
     */
    public function getAttendanceByDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $attendanceRecords = Attendance::where('date', $request->date)->with('student')->get();

        return response()->json(['attendance' => $attendanceRecords]);
    }

    /**
     * Get attendance by status.
     */
    public function getAttendanceByStatus(Request $request)
    {
        $request->validate(['status' => 'required|in:present,absent,permission']);

        $attendanceRecords = Attendance::where('status', $request->status)->with('student')->get();

        return response()->json(['attendance' => $attendanceRecords]);
    }

    /**
     * Get attendance filtered by class, date, and status.
     */
    public function getFilteredAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'date' => 'nullable|date',
            'status' => 'nullable|in:present,absent,permission',
        ]);

        $query = Attendance::with('student');

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendanceRecords = $query->get();

        return response()->json(['attendance' => $attendanceRecords]);
    }
}
