<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_200_when_all_systems_healthy(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'response_time_ms',
            'checks' => [
                'database' => ['status'],
                'cache' => ['status'],
                'queue' => ['status'],
                'storage' => ['status'],
                'application' => ['status'],
            ],
        ]);

        $response->assertJson([
            'status' => 'healthy',
        ]);
    }

    public function test_health_check_validates_database_connectivity(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
        $this->assertEquals('ok', $response->json('checks.database.status'));
    }

    public function test_health_check_includes_response_time(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
        $this->assertIsNumeric($response->json('response_time_ms'));
        $this->assertLessThan(1000, $response->json('response_time_ms'));
    }
}
