<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_a_customer_without_choosing_its_owner(): void
    {
        $member = User::factory()->create();
        $anotherUser = User::factory()->create();

        Sanctum::actingAs($member);

        $this->postJson('/api/v1/customers', [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'status' => 'lead',
            'owner_id' => $anotherUser->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.first_name', 'Ada')
            ->assertJsonPath('data.status', 'lead')
            ->assertJsonMissingPath('data.owner_id');

        $this->assertDatabaseHas('customers', [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'owner_id' => $member->id,
        ]);
        $this->assertDatabaseMissing('customers', [
            'first_name' => 'Ada',
            'owner_id' => $anotherUser->id,
        ]);
    }

    public function test_member_customer_list_is_scoped_and_supports_search_and_status_filters(): void
    {
        $member = User::factory()->create();
        $anotherUser = User::factory()->create();

        Customer::factory()->for($member, 'owner')->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'status' => 'lead',
        ]);
        Customer::factory()->for($member, 'owner')->create([
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'status' => 'customer',
        ]);
        Customer::factory()->for($anotherUser, 'owner')->create([
            'first_name' => 'Ada',
            'last_name' => 'Byron',
            'status' => 'lead',
        ]);

        Sanctum::actingAs($member);

        $this->getJson('/api/v1/customers?search=Ada&status=lead&per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Ada')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_admin_can_list_customers_owned_by_different_members(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        Customer::factory()->count(2)->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/customers')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_member_can_update_their_customer_but_cannot_transfer_ownership(): void
    {
        $member = User::factory()->create();
        $anotherUser = User::factory()->create();
        $customer = Customer::factory()->for($member, 'owner')->create([
            'first_name' => 'Ada',
        ]);

        Sanctum::actingAs($member);

        $this->patchJson("/api/v1/customers/{$customer->id}", [
            'first_name' => 'Augusta',
            'owner_id' => $anotherUser->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Augusta');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => 'Augusta',
            'owner_id' => $member->id,
        ]);
    }

    public function test_member_cannot_view_update_or_delete_another_members_customer(): void
    {
        $member = User::factory()->create();
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($member);

        $this->getJson("/api/v1/customers/{$customer->id}")->assertNotFound();
        $this->patchJson("/api/v1/customers/{$customer->id}", [
            'first_name' => 'Unauthorized change',
        ])->assertNotFound();
        $this->deleteJson("/api/v1/customers/{$customer->id}")->assertNotFound();

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_member_can_soft_delete_their_customer(): void
    {
        $member = User::factory()->create();
        $customer = Customer::factory()->for($member, 'owner')->create();

        Sanctum::actingAs($member);

        $this->deleteJson("/api/v1/customers/{$customer->id}")->assertNoContent();

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_customer_input_is_validated_and_empty_updates_are_rejected(): void
    {
        $member = User::factory()->create();
        $customer = Customer::factory()->for($member, 'owner')->create();

        Sanctum::actingAs($member);

        $this->postJson('/api/v1/customers', [
            'first_name' => '',
            'last_name' => 'Lovelace',
            'status' => 'pending',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'status']);

        $this->patchJson("/api/v1/customers/{$customer->id}", [
            'owner_id' => User::factory()->create()->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data');
    }
}
