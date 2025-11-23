<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShowroomTest extends TestCase
{
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

    public function test_about_page_is_accessible()
    {
        $response = $this->get('/sobre-ivonne');
        $response->assertStatus(200);
    }
}
