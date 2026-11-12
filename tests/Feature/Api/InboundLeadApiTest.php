<?php

namespace Tests\Feature\Api;

use App\Models\InboundLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InboundLeadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_member_cannot_view_inbound_leads(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/inbound-leads')->assertForbidden();
    }

    public function test_admin_can_list_and_update_inbound_lead_status(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $lead = InboundLead::create([
            'name' => 'Nino Example',
            'email' => 'nino@example.com',
            'service' => 'website',
            'locale' => 'ka',
            'status' => 'new',
            'consented_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/inbound-leads')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'new');

        $this->patchJson("/api/v1/inbound-leads/{$lead->id}", [
            'status' => 'contacted',
        ])->assertOk()->assertJsonPath('data.status', 'contacted');
    }
}
