<?php

namespace Tests\Feature\Console;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendTaskDueRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_task_reminder_is_queued_for_the_assignee(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $customer = Customer::factory()->for($creator, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($creator, 'creator')
            ->create([
                'assigned_to' => $assignee->id,
                'due_at' => now()->addHours(2),
            ]);

        $this->artisan('tasks:send-due-reminders')
            ->expectsOutput('Queued 1 task reminder(s).')
            ->assertSuccessful();

        Notification::assertSentTo(
            $assignee,
            TaskDueReminder::class,
            fn (TaskDueReminder $notification, array $channels): bool => $notification->task->is($task)
                && $channels === ['database'],
        );

        $this->assertNotNull($task->fresh()->reminder_dispatched_at);
    }

    public function test_unassigned_task_reminder_is_queued_for_its_creator(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $customer = Customer::factory()->for($creator, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($creator, 'creator')
            ->create([
                'assigned_to' => null,
                'due_at' => now()->addHours(3),
            ]);

        $this->artisan('tasks:send-due-reminders')->assertSuccessful();

        Notification::assertSentTo($creator, TaskDueReminder::class);
        $this->assertNotNull($task->fresh()->reminder_dispatched_at);
    }

    public function test_completed_overdue_undated_and_distant_tasks_are_skipped(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $customer = Customer::factory()->for($creator, 'owner')->create();

        Task::factory()->for($customer)->for($creator, 'creator')->create([
            'due_at' => now()->addHours(2),
            'completed_at' => now(),
        ]);
        Task::factory()->for($customer)->for($creator, 'creator')->create([
            'due_at' => now()->subMinute(),
        ]);
        Task::factory()->for($customer)->for($creator, 'creator')->create([
            'due_at' => null,
        ]);
        Task::factory()->for($customer)->for($creator, 'creator')->create([
            'due_at' => now()->addDays(2),
        ]);

        $this->artisan('tasks:send-due-reminders')
            ->expectsOutput('Queued 0 task reminder(s).')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_a_task_is_not_reminded_again_after_its_first_dispatch(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $customer = Customer::factory()->for($creator, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($creator, 'creator')
            ->create([
                'assigned_to' => $assignee->id,
                'due_at' => now()->addHours(4),
            ]);

        $this->artisan('tasks:send-due-reminders')->assertSuccessful();
        $this->artisan('tasks:send-due-reminders')->assertSuccessful();

        $this->assertCount(1, Notification::sent($assignee, TaskDueReminder::class));
        $this->assertNotNull($task->fresh()->reminder_dispatched_at);
    }

    public function test_task_due_reminder_is_a_queued_database_notification(): void
    {
        $task = Task::factory()->create(['due_at' => now()->addHour()]);
        $recipient = User::factory()->create();
        $notification = new TaskDueReminder($task);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['database'], $notification->via($recipient));
        $this->assertSame([
            'type' => 'task_due_reminder',
            'task_id' => $task->id,
            'customer_id' => $task->customer_id,
            'title' => $task->title,
            'due_at' => $task->due_at->toISOString(),
        ], $notification->toDatabase($recipient));
    }
}
