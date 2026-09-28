<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/?q=Builderman')
            ->assertOk()
            ->assertSee('editorial-ticker', false)
            ->assertSee('cmdPalette', false)
            ->assertSee('aria-keyshortcuts="Control+K Meta+K"', false)
            ->assertSee('value="Builderman"', false);
    }
}
