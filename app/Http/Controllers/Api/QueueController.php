<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TakeQueueRequest;
use App\Models\Department;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function take(TakeQueueRequest $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found',
            ], 404);
        }

        $department = Department::where('id', $request->department_id)
            ->where('is_active', true)
            ->first();

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not active',
            ], 400);
        }

        $existingQueue = Queue::where('queue_date', $request->queue_date)
            ->where('department_id', $request->department_id)
            ->where('patient_id', $patient->id)
            ->first();

        if ($existingQueue) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a queue for this department on this date',
            ], 400);
        }

        try {
            $queue = DB::transaction(function () use ($request, $patient) {
                $maxQueueNumber = Queue::where('queue_date', $request->queue_date)
                    ->where('department_id', $request->department_id)
                    ->lockForUpdate()
                    ->max('queue_number');

                $queueNumber = ($maxQueueNumber ?? 0) + 1;

                $waitingCount = Queue::where('queue_date', $request->queue_date)
                    ->where('department_id', $request->department_id)
                    ->whereIn('status', ['waiting', 'called'])
                    ->count();

                $estimatedWaitMinutes = $waitingCount * 15;

                return Queue::create([
                    'queue_date' => $request->queue_date,
                    'department_id' => $request->department_id,
                    'doctor_id' => $request->doctor_id,
                    'patient_id' => $patient->id,
                    'queue_number' => $queueNumber,
                    'status' => 'waiting',
                    'estimated_wait_minutes' => $estimatedWaitMinutes,
                ]);
            });

            $queue->load(['department', 'doctor', 'patient.user']);

            return response()->json([
                'success' => true,
                'message' => 'Queue taken successfully',
                'data' => $queue,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to take queue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function myQueues(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found',
            ], 404);
        }

        $query = Queue::with(['department', 'doctor', 'note.doctor'])
            ->where('patient_id', $patient->id);

        if ($request->date) {
            $query->where('queue_date', $request->date);
        }

        $queues = $query->orderBy('queue_date', 'desc')
            ->orderBy('queue_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $queues,
        ]);
    }

    public function cancel(Request $request, Queue $queue)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient || $queue->patient_id !== $patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($queue->status, ['waiting'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this queue',
            ], 400);
        }

        $queueDate = $queue->queue_date;
        $today = now()->toDateString();
        
        if ($queueDate <= $today) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel queue on the same day or past date',
            ], 400);
        }

        $queue->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->reason ?? 'Cancelled by patient',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Queue cancelled successfully',
            'data' => $queue,
        ]);
    }

    public function status(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date',
        ]);

        $queues = Queue::with(['patient.user', 'doctor'])
            ->where('department_id', $request->department_id)
            ->where('queue_date', $request->date)
            ->orderBy('queue_number', 'asc')
            ->get();

        $currentQueue = $queues->where('status', 'called')->first();
        $waitingCount = $queues->whereIn('status', ['waiting'])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'current_queue' => $currentQueue,
                'waiting_count' => $waitingCount,
                'queues' => $queues,
            ],
        ]);
    }

    public function active(Request $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        $activeQueue = Queue::with(['department', 'doctor'])
            ->where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'called'])
            ->where('queue_date', now()->toDateString())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $activeQueue,
        ]);
    }
}
