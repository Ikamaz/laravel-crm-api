<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TaskDueReminder extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Task $task)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_due_reminder',
            'task_id' => $this->task->getKey(),
            'customer_id' => $this->task->customer_id,
            'title' => $this->task->title,
            'due_at' => $this->task->due_at?->toISOString(),
        ];
    }
}
