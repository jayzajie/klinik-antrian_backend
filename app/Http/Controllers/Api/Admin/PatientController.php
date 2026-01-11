<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::with('user');

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('phone', 'like', "%{$request->search}%");
        }

        $patients = $query->latest()->paginate(20);

        return response()->json($patients);
    }

    public function show($id)
    {
        $patient = Patient::with(['user', 'queues.department', 'queues.doctor'])->findOrFail($id);
        return response()->json($patient);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
        ]);

        $oldValues = $patient->toArray();
        $patient->update($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_patient',
            'model_type' => Patient::class,
            'model_id' => $patient->id,
            'old_values' => $oldValues,
            'new_values' => $patient->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($patient->load('user'));
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_patient',
            'model_type' => Patient::class,
            'model_id' => $patient->id,
            'old_values' => $patient->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $patient->user()->delete();
        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully']);
    }
}
