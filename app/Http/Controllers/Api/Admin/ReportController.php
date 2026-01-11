<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function queueReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = Queue::with(['department', 'doctor', 'patient.user'])
            ->whereBetween('queue_date', [$request->date_from, $request->date_to]);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $queues = $query->get();

        $stats = [
            'total' => $queues->count(),
            'waiting' => $queues->where('status', 'waiting')->count(),
            'called' => $queues->where('status', 'called')->count(),
            'done' => $queues->where('status', 'done')->count(),
            'cancelled' => $queues->where('status', 'cancelled')->count(),
        ];

        $data = [
            'queues' => $queues,
            'stats' => $stats,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.queue', $data);
        
        return $pdf->download('queue-report-' . date('Y-m-d') . '.pdf');
    }

    public function queueData(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = Queue::with(['department', 'doctor', 'patient.user'])
            ->whereBetween('queue_date', [$request->date_from, $request->date_to]);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $queues = $query->get();

        $stats = [
            'total' => $queues->count(),
            'waiting' => $queues->where('status', 'waiting')->count(),
            'called' => $queues->where('status', 'called')->count(),
            'done' => $queues->where('status', 'done')->count(),
            'cancelled' => $queues->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'queues' => $queues,
            'stats' => $stats,
        ]);
    }
}
