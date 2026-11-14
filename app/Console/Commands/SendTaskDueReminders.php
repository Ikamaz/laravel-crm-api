<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueReminder;
use Illuminate\Console\Command;

class SendTaskDueReminders extends Command
{
    protected $signature = 'tasks:send-due-reminders';

    protected $description = 'Queue reminders for incomplete tasks due within the next 24 hours';

    public function handle(): int
    {
        $now = now();
        $windowEndsAt = $now->copy()->addDay();
        $queued = 0;

        Task::query()
            ->whereNull('completed_at')
            ->whereNull('reminder_dispatched_at')
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$now, $windowEndsAt])
            ->with(['assignee', 'creator'])
            ->chunkById(100, function ($tasks) use (&$queued): void {
                foreach ($tasks as $task) {
                    $recipient = $task->assignee ?? $task->creator;

                    $recipient->notify(new TaskDueReminder($task));

                    $task->forceFill([
                        'reminder_dispatched_at' => now(),
                    ])->save();

                    $queued++;
                }
            });

        $this->info(sprintf('Queued %d task reminder(s).', $queued));

        return self::SUCCESS;
    }
}
