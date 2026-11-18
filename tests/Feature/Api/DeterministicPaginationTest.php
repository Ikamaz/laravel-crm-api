<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeterministicPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_pages_use_the_id_as_a_tie_breaker_for_equal_sort_values(): void
    {
        $owner = User::factory()->create();
        $customers = Customer::factory()->count(3)->for($owner, 'owner')->create();
        $customerIds = $customers->modelKeys();
        $timestamp = now()->startOfSecond();

        DB::table('customers')
            ->whereIn('id', $customerIds)
            ->update([
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        Sanctum::actingAs($owner);

        $descendingIds = $customerIds;
        rsort($descendingIds);

        $firstDescendingPage = $this->getJson('/api/v1/customers?per_page=2&sort=created_at&direction=desc')
            ->assertOk();
        $secondDescendingPage = $this->getJson('/api/v1/customers?per_page=2&sort=created_at&direction=desc&page=2')
            ->assertOk();

        $this->assertSame(array_slice($descendingIds, 0, 2), array_column($firstDescendingPage->json('data'), 'id'));
        $this->assertSame([$descendingIds[2]], array_column($secondDescendingPage->json('data'), 'id'));

        $ascendingIds = $customerIds;
        sort($ascendingIds);

        $ascendingPage = $this->getJson('/api/v1/customers?per_page=2&sort=created_at&direction=asc')
            ->assertOk();

        $this->assertSame(array_slice($ascendingIds, 0, 2), array_column($ascendingPage->json('data'), 'id'));
    }

    public function test_task_pages_use_the_id_as_a_tie_breaker_for_equal_sort_values(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $tasks = Task::factory()->count(3)->for($customer)->for($owner, 'creator')->create();
        $taskIds = $tasks->modelKeys();
        $timestamp = now()->startOfSecond();

        DB::table('tasks')
            ->whereIn('id', $taskIds)
            ->update([
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        Sanctum::actingAs($owner);

        $expectedIds = $taskIds;
        rsort($expectedIds);

        $firstPage = $this->getJson('/api/v1/tasks?per_page=2&sort=created_at&direction=desc')
            ->assertOk();
        $secondPage = $this->getJson('/api/v1/tasks?per_page=2&sort=created_at&direction=desc&page=2')
            ->assertOk();

        $this->assertSame(array_slice($expectedIds, 0, 2), array_column($firstPage->json('data'), 'id'));
        $this->assertSame([$expectedIds[2]], array_column($secondPage->json('data'), 'id'));
    }

    public function test_customer_note_pages_remain_stable_when_notes_share_a_timestamp(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $noteIds = [];

        for ($index = 0; $index < 16; $index++) {
            $noteIds[] = $customer->notes()->create([
                'user_id' => $owner->id,
                'body' => "Note {$index}",
            ])->id;
        }

        $timestamp = now()->startOfSecond();

        DB::table('customer_notes')
            ->whereIn('id', $noteIds)
            ->update([
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        Sanctum::actingAs($owner);

        $expectedIds = $noteIds;
        rsort($expectedIds);

        $firstPage = $this->getJson("/api/v1/customers/{$customer->id}/notes?page=1")
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.current_page', 1);
        $secondPage = $this->getJson("/api/v1/customers/{$customer->id}/notes?page=2")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);

        $this->assertSame(array_slice($expectedIds, 0, 15), array_column($firstPage->json('data'), 'id'));
        $this->assertSame([$expectedIds[15]], array_column($secondPage->json('data'), 'id'));
    }
}
