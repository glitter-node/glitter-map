<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Personal Spatial Memory Log');
    }

    public function test_place_index_requires_authentication(): void
    {
        $response = $this->get(route('places.index'));

        $response->assertRedirect(route('login'));
    }
}
