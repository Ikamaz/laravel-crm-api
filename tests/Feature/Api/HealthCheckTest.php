<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_api_health_endpoint_returns_a_json_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'status' => 'ok',
                    'service' => 'Laravel CRM API',
                ],
            ]);
    }

    public function test_framework_health_endpoint_returns_successful_response(): void
    {
        $this->get('/up')->assertOk();
    }
}
