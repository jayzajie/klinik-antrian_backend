<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueDisplayController extends Controller
{
    public function display(Request $request)
    {
        $departmentId = $request->department_id;
        $date = $request->date ?? now()->toDateString();

        $query = Queue::with(['patient.user', 'doctor', 'department'])
            ->where('queue_date', $date)
            ->whereIn('status', ['waiting', 'called']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $queues = $query->orderBy('department_id')
            ->orderBy('queue_number')
            ->get();

        $currentQueues = $queues->where('status', 'called')->groupBy('department_id');
        $waitingQueues = $queues->where('status', 'waiting')->groupBy('department_id');

        $departments = Department::where('is_active', true)->get();

        $display = [];
        foreach ($departments as $dept) {
            $current = $currentQueues->get($dept->id)?->first();
            $waiting = $waitingQueues->get($dept->id)?->values();

            $display[] = [
                'department' => [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ],
                'current_queue' => $current ? [
                    'queue_number' => $current->queue_number,
                    'patient_name' => $current->patient->user->name,
                ] : null,
                'waiting_count' => $waiting ? $waiting->count() : 0,
                'next_queues' => $waiting ? $waiting->take(3)->map(fn($q) => $q->queue_number)->values() : [],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'departments' => $display,
                'last_updated' => now()->toISOString(),
            ],
        ]);
    }
}
