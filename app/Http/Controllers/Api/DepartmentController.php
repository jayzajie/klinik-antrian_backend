<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
        ]);
    }

    public function show(Department $department)
    {
        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    public function schedules(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;
        $dayOfWeek = date('w', strtotime($date));

        $schedules = DoctorSchedule::with(['doctor', 'department'])
            ->where('department_id', $id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }
}
