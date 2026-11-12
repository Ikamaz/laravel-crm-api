<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerNoteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_owner_can_create_a_note_and_the_server_assigns_its_author(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/customers/{$customer->id}/notes", [
            'body' => 'Discussed the initial project requirements.',
            'user_id' => $anotherUser->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.body', 'Discussed the initial project requirements.')
            ->assertJsonPath('data.author.id', $owner->id)
            ->assertJsonPath('data.author.name', $owner->name)
            ->assertJsonMissingPath('data.user_id');

        $this->assertDatabaseHas('customer_notes', [
            'customer_id' => $customer->id,
            'user_id' => $owner->id,
            'body' => 'Discussed the initial project requirements.',
        ]);
    }

    public function test_customer_owner_can_list_notes_in_newest_first_order(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $olderNote = $customer->notes()->create([
            'user_id' => $owner->id,
            'body' => 'Earlier call.',
        ]);
        $olderNote->forceFill(['created_at' => now()->subMinute()])->save();

        $customer->notes()->create([
            'user_id' => $owner->id,
            'body' => 'Latest call.',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/customers/{$customer->id}/notes")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.body', 'Latest call.')
            ->assertJsonPath('data.1.body', 'Earlier call.')
            ->assertJsonPath('data.0.author.id', $owner->id);
    }

    public function test_member_cannot_list_or_create_notes_for_another_members_customer(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($member);

        $this->getJson("/api/v1/customers/{$customer->id}/notes")->assertNotFound();

        $this->postJson("/api/v1/customers/{$customer->id}/notes", [
            'body' => 'This must not be saved.',
        ])->assertNotFound();

        $this->assertDatabaseCount('customer_notes', 0);
    }

    public function test_note_author_can_delete_their_note(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $note = $customer->notes()->create([
            'user_id' => $owner->id,
            'body' => 'Follow-up completed.',
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson("/api/v1/customers/{$customer->id}/notes/{$note->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('customer_notes', ['id' => $note->id]);
    }

    public function test_customer_owner_can_delete_a_note_written_by_an_admin(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $note = $customer->notes()->create([
            'user_id' => $admin->id,
            'body' => 'Admin added a handover note.',
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson("/api/v1/customers/{$customer->id}/notes/{$note->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('customer_notes', ['id' => $note->id]);
    }

    public function test_member_cannot_delete_another_members_note(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $note = $customer->notes()->create([
            'user_id' => $owner->id,
            'body' => 'Private customer note.',
        ]);

        Sanctum::actingAs($member);

        $this->deleteJson("/api/v1/customers/{$customer->id}/notes/{$note->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('customer_notes', ['id' => $note->id]);
    }

    public function test_note_cannot_be_deleted_through_a_different_customer_route(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $otherCustomer = Customer::factory()->for($owner, 'owner')->create();
        $note = $customer->notes()->create([
            'user_id' => $owner->id,
            'body' => 'Belongs to the first customer.',
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson("/api/v1/customers/{$otherCustomer->id}/notes/{$note->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('customer_notes', ['id' => $note->id]);
    }

    public function test_note_body_is_required_and_cannot_exceed_5000_characters(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->for($owner, 'owner')->create();

        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/customers/{$customer->id}/notes", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');

        $this->postJson("/api/v1/customers/{$customer->id}/notes", [
            'body' => str_repeat('a', 5001),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');

        $this->assertDatabaseCount('customer_notes', 0);
    }

    public function test_admin_can_list_notes_for_any_customers(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $customer = Customer::factory()->for($owner, 'owner')->create();
        $customer->notes()->create([
            'user_id' => $owner->id,
            'body' => 'Customer history.',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/customers/{$customer->id}/notes")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'Customer history.');
    }
}
