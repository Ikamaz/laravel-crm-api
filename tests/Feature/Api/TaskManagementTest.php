<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_owner_can_create_a_task_and_the_server_assigns_its_creator(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/customers/{$customer->id}/tasks", [
            'title' => 'Call about the proposal',
            'description' => 'Confirm the revised delivery date.',
            'due_at' => now()->addDay()->toISOString(),
            'assignee_id' => $assignee->id,
            'created_by' => $assignee->id,
            'completed_at' => now()->toISOString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Call about the proposal')
            ->assertJsonPath('data.customer.id', $customer->id)
            ->assertJsonPath('data.creator.id', $owner->id)
            ->assertJsonPath('data.assignee.id', $assignee->id)
            ->assertJsonPath('data.completed_at', null);

        $this->assertDatabaseHas('tasks', [
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'assigned_to' => $assignee->id,
            'title' => 'Call about the proposal',
            'completed_at' => null,
        ]);
    }

    public function test_member_cannot_create_a_task_for_another_members_customer(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($member);

        $this->postJson("/api/v1/customers/{$customer->id}/tasks", [
            'title' => 'Unauthorized follow-up',
        ])->assertNotFound();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_member_task_list_includes_owned_customer_tasks_and_tasks_assigned_to_them_only(): void
    {
        $member = User::factory()->create();
        $otherOwner = User::factory()->create();
        $creator = User::factory()->create();

        $ownedCustomer = Customer::factory()->for($member, 'owner')->create();
        $otherCustomer = Customer::factory()->for($otherOwner, 'owner')->create();

        Task::factory()
            ->for($ownedCustomer)
            ->for($creator, 'creator')
            ->create(['title' => 'Owned customer task']);

        Task::factory()
            ->for($otherCustomer)
            ->for($creator, 'creator')
            ->create([
                'title' => 'Assigned task',
                'assigned_to' => $member->id,
            ]);

        Task::factory()
            ->for($otherCustomer)
            ->for($creator, 'creator')
            ->create(['title' => 'Unrelated task']);

        Sanctum::actingAs($member);

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_can_list_tasks_across_all_customers(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        Task::factory()->count(3)->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_assignee_can_view_and_complete_a_task_but_cannot_reassign_it(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $anotherUser = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create(['assigned_to' => $assignee->id]);

        Sanctum::actingAs($assignee);

        $this->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.assignee.id', $assignee->id);

        $this->patchJson("/api/v1/tasks/{$task->id}", [
            'completed_at' => now()->toISOString(),
        ])->assertOk();

        $this->assertNotNull($task->fresh()->completed_at);

        $this->patchJson("/api/v1/tasks/{$task->id}", [
            'assignee_id' => $anotherUser->id,
        ])->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $assignee->id,
        ]);
    }

    public function test_customer_owner_can_update_task_details_and_change_the_assignee(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create(['assigned_to' => $assignee->id]);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated follow-up',
            'assignee_id' => $newAssignee->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated follow-up')
            ->assertJsonPath('data.assignee.id', $newAssignee->id);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated follow-up',
            'assigned_to' => $newAssignee->id,
        ]);
    }

    public function test_unrelated_member_cannot_view_update_or_delete_a_task(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create();

        Sanctum::actingAs($outsider);

        $this->getJson("/api/v1/tasks/{$task->id}")->assertNotFound();
        $this->patchJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Unauthorized edit',
        ])->assertNotFound();
        $this->deleteJson("/api/v1/tasks/{$task->id}")->assertNotFound();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_task_creator_can_delete_a_task_they_created(): void
    {
        $owner = User::factory()->create();
        $creator = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($creator, 'creator')
            ->create();

        Sanctum::actingAs($creator);

        $this->deleteJson("/api/v1/tasks/{$task->id}")->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_customer_owner_can_still_access_tasks_after_soft_deleting_the_customer(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $task = Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create();

        $customer->delete();

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.customer.id', $customer->id);

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_task_list_can_filter_open_completed_and_overdue_tasks(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create([
                'title' => 'Overdue task',
                'due_at' => now()->subDay(),
            ]);

        Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create([
                'title' => 'Completed task',
                'due_at' => now()->subDay(),
                'completed_at' => now()->subHour(),
            ]);

        Task::factory()
            ->for($customer)
            ->for($owner, 'creator')
            ->create([
                'title' => 'Upcoming task',
                'due_at' => now()->addDay(),
            ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/tasks?status=overdue')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Overdue task');

        $this->getJson('/api/v1/tasks?status=completed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Completed task');

        $this->getJson('/api/v1/tasks?status=open')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_task_creation_validates_title_and_assignee(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/customers/{$customer->id}/tasks", [
            'title' => str_repeat('x', 161),
            'assignee_id' => 999999,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'assignee_id']);

        $this->assertDatabaseCount('tasks', 0);
    }
}
