<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['department', 'user'])->get();

        return response()->json([
            'success' => true,
            'data' => $doctors,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $doctor = Doctor::create($request->all());
        $doctor->load(['department']);

        return response()->json([
            'success' => true,
            'message' => 'Doctor created successfully',
            'data' => $doctor,
        ], 201);
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['department', 'schedules']);

        return response()->json([
            'success' => true,
            'data' => $doctor,
        ]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'department_id' => 'exists:departments,id',
            'name' => 'string|max:255',
            'specialization' => 'nullable|string',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $doctor->update($request->all());
        $doctor->load(['department']);

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated successfully',
            'data' => $doctor,
        ]);
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted successfully',
        ]);
    }
}
