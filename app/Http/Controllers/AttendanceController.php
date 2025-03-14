<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function markAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,permission',
        ]);

        // Check if attendance already marked for this student on the same date
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->first();

        if ($existingAttendance) {
            return response()->json(['message' => 'Attendance already marked for this student on this date'], 422);
        }

        // Save attendance
        Attendance::create([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'date' => $request->date,
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
