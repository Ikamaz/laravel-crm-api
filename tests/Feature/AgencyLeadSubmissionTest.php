<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyLeadSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_enquiry_form_stores_a_lead_with_consent(): void
    {
        $response = $this->from('/')
            ->post(route('agency.leads.store'), [
                'name' => 'Nino Example',
                'email' => 'nino@example.com',
                'company' => 'Example Ltd',
                'service' => 'website',
                'message' => 'We need a new business website.',
                'locale' => 'ka',
                'consent' => '1',
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('lead_success', true);

        $this->assertDatabaseHas('inbound_leads', [
            'name' => 'Nino Example',
            'email' => 'nino@example.com',
            'company' => 'Example Ltd',
            'service' => 'website',
            'locale' => 'ka',
            'status' => 'new',
        ]);
    }

    public function test_enquiry_requires_at_least_one_contact_method(): void
    {
        $response = $this->from('/')
            ->post(route('agency.leads.store'), [
                'name' => 'Nino Example',
                'service' => 'website',
                'locale' => 'ka',
                'consent' => '1',
            ]);

        $response->assertSessionHasErrors(['email', 'phone']);
        $this->assertDatabaseCount('inbound_leads', 0);
    }
}
