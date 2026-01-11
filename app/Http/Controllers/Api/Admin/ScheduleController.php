<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorSchedule::with(['doctor', 'department']);

        if ($request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $schedules = $query->get();

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'quota_per_day' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $schedule = DoctorSchedule::create($request->all());
        $schedule->load(['doctor', 'department']);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully',
            'data' => $schedule,
        ], 201);
    }

    public function update(Request $request, DoctorSchedule $schedule)
    {
        $request->validate([
            'day_of_week' => 'integer|between:0,6',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i|after:start_time',
            'quota_per_day' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $schedule->update($request->all());
        $schedule->load(['doctor', 'department']);

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
            'data' => $schedule,
        ]);
    }

    public function destroy(DoctorSchedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully',
        ]);
    }
}
