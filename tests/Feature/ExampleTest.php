<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_restaurant_index(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('restaurants.index'));
    }

    public function test_restaurant_index_page_renders_successfully(): void
    {
        $response = $this->get(route('restaurants.index'));

        $response->assertStatus(200);
        $response->assertSee('Neighborhood dining log');
    }
}
