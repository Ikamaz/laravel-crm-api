<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_inbound_lead_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get('/admin/leads')
            ->assertOk()
            ->assertSee('Inbound enquiries');
    }

    public function test_regular_member_cannot_view_the_inbound_lead_dashboard(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin/leads')
            ->assertForbidden();
    }
}
