<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = Queue::with(['department', 'doctor', 'patient.user']);

        if ($request->date) {
            $query->where('queue_date', $request->date);
        } else {
            $query->where('queue_date', now()->toDateString());
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $queues = $query->orderBy('queue_number', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $queues,
        ]);
    }

    public function call(Queue $queue)
    {
        if ($queue->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Queue is not in waiting status',
            ], 400);
        }

        $queue->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        $queue->load(['department', 'doctor', 'patient.user']);

        NotificationService::sendQueueCalledNotification($queue);

        return response()->json([
            'success' => true,
            'message' => 'Queue called successfully',
            'data' => $queue,
        ]);
    }

    public function skip(Queue $queue)
    {
        if (!in_array($queue->status, ['waiting', 'called'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot skip this queue',
            ], 400);
        }

        $queue->update([
            'status' => 'skipped',
        ]);

        $queue->load(['department', 'doctor', 'patient.user']);

        return response()->json([
            'success' => true,
            'message' => 'Queue skipped successfully',
            'data' => $queue,
        ]);
    }

    public function done(Queue $queue)
    {
        if ($queue->status !== 'called') {
            return response()->json([
                'success' => false,
                'message' => 'Queue must be called first',
            ], 400);
        }

        $queue->update([
            'status' => 'done',
            'done_at' => now(),
        ]);

        $queue->load(['department', 'doctor', 'patient.user']);

        return response()->json([
            'success' => true,
            'message' => 'Queue completed successfully',
            'data' => $queue,
        ]);
    }

    public function cancelByAdmin(Request $request, Queue $queue)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        if (in_array($queue->status, ['done', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this queue',
            ], 400);
        }

        $queue->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->reason,
            'cancelled_at' => now(),
        ]);

        $queue->load(['department', 'doctor', 'patient.user']);

        return response()->json([
            'success' => true,
            'message' => 'Queue cancelled successfully',
            'data' => $queue,
        ]);
    }

    public function addNote(Request $request, Queue $queue)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        // Gunakan doctor_id dari queue, atau null jika tidak ada
        $doctorId = $queue->doctor_id;

        $note = \App\Models\QueueNote::updateOrCreate(
            ['queue_id' => $queue->id],
            [
                'doctor_id' => $doctorId,
                'notes' => $request->note,
            ]
        );

        if ($note->doctor) {
            $note->load('doctor');
        }

        return response()->json([
            'success' => true,
            'message' => 'Note saved successfully',
            'data' => $note,
        ]);
    }

    public function dailyReport(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->date ?? now()->toDateString();

        $queues = Queue::with(['department'])
            ->where('queue_date', $date)
            ->get();

        $summary = [
            'total' => $queues->count(),
            'waiting' => $queues->where('status', 'waiting')->count(),
            'serving' => $queues->where('status', 'called')->count(),
            'completed' => $queues->where('status', 'done')->count(),
            'skipped' => $queues->where('status', 'skipped')->count(),
            'cancelled' => $queues->where('status', 'cancelled')->count(),
        ];

        $byDepartment = $queues->groupBy('department_id')->map(function ($items) {
            $dept = $items->first()->department;
            return [
                'department_id' => $dept->id,
                'department_name' => $dept->name,
                'total' => $items->count(),
                'completed' => $items->where('status', 'done')->count(),
                'skipped' => $items->where('status', 'skipped')->count(),
                'cancelled' => $items->where('status', 'cancelled')->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'summary' => $summary,
                'by_department' => $byDepartment,
            ],
        ]);
    }

    public function bulkCancel(Request $request)
    {
        $request->validate([
            'queue_ids' => 'required|array',
            'queue_ids.*' => 'exists:queues,id',
            'reason' => 'required|string',
        ]);

        $queues = Queue::whereIn('id', $request->queue_ids)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get();

        $cancelled = [];
        foreach ($queues as $queue) {
            $queue->update([
                'status' => 'cancelled',
                'cancel_reason' => $request->reason,
                'cancelled_at' => now(),
            ]);
            $cancelled[] = $queue->id;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_cancel_queues',
            'new_values' => ['queue_ids' => $cancelled, 'reason' => $request->reason],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($cancelled) . ' queues cancelled',
            'cancelled_ids' => $cancelled,
        ]);
    }

    public function resetAllQueues(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = Queue::where('queue_date', $request->date)
            ->whereIn('status', ['waiting', 'called', 'skipped']);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $count = $query->count();
        $query->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'reset_all_queues',
            'new_values' => [
                'date' => $request->date,
                'department_id' => $request->department_id,
                'count' => $count,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $count . ' queues reset successfully',
        ]);
    }
}
