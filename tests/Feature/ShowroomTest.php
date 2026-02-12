<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShowroomTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_page_is_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_catalog_page_is_accessible()
    {
        $response = $this->get('/catalogo');
        $response->assertStatus(200);
    }

    public function test_contact_page_is_accessible()
    {
        $response = $this->get('/contacto');
        $response->assertStatus(200);
    }

}
