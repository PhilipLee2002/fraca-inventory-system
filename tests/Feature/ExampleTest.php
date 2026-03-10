<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     * 
     * Tests that the home page redirects to login for unauthenticated users.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Home page requires authentication, so it should redirect to login
        $response->assertStatus(302);
    }
}
