<?php

namespace App\Console\Commands;

use App\Models\Queue;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendQueueReminders extends Command
{
    protected $signature = 'queue:send-reminders';
    protected $description = 'Send queue reminders for tomorrow';

    public function handle()
    {
        $tomorrow = now()->addDay()->toDateString();

        $queues = Queue::with(['patient.user', 'department'])
            ->where('queue_date', $tomorrow)
            ->where('status', 'waiting')
            ->get();

        $count = 0;
        foreach ($queues as $queue) {
            NotificationService::sendQueueReminderNotification($queue);
            $count++;
        }

        $this->info("Sent {$count} queue reminders for {$tomorrow}");
        
        return 0;
    }
}
