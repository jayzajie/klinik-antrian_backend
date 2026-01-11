<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $todayQueues = Queue::where('queue_date', $date);

        $stats = [
            'today_total' => $todayQueues->count(),
            'waiting' => (clone $todayQueues)->where('status', 'waiting')->count(),
            'serving' => (clone $todayQueues)->where('status', 'called')->count(),
            'completed' => (clone $todayQueues)->where('status', 'completed')->count(),
            'cancelled' => (clone $todayQueues)->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
