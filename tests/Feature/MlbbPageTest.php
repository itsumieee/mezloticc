<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MlbbPageTest extends TestCase
{
    public function test_mlbb_preview_page_is_available_without_fabricated_account_data(): void
    {
        $this->get('/mlbb')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Mlbb')
                ->missing('player')
                ->missing('skins')
                ->missing('rating'));
    }
}
