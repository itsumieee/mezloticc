<?php

namespace Tests\Feature;

use App\Services\RobloxApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RobloxApiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_username_lookup_resolves_and_caches_the_user(): void
    {
        Http::fake([
            'users.roblox.com/v1/usernames/users' => Http::response([
                'data' => [[
                    'id' => 1,
                    'name' => 'builderman',
                    'displayName' => 'Builderman',
                ]],
            ]),
        ]);

        $service = app(RobloxApiService::class);
        $first = $service->getUserByUsername('builderman');
        $second = $service->getUserByUsername('builderman');

        $this->assertSame(1, $first['id']);
        $this->assertSame('builderman', $second['name']);
        Http::assertSentCount(1);
    }

    public function test_unknown_username_and_api_failures_return_null(): void
    {
        Http::fake([
            'users.roblox.com/v1/usernames/users' => Http::sequence()
                ->push(['data' => []])
                ->push(['message' => 'Unavailable'], 500),
        ]);

        $service = app(RobloxApiService::class);

        $this->assertNull($service->getUserByUsername('missing-user'));
        Cache::forget('roblox.user.by_username.'.md5('failing-user'));
        $this->assertNull($service->getUserByUsername('failing-user'));
    }

    public function test_user_id_lookup_uses_the_profile_cache(): void
    {
        Http::fake([
            'https://users.roblox.com/v1/users/156' => Http::response([
                'id' => 156,
                'name' => 'builderman',
                'displayName' => 'Builderman',
            ]),
        ]);

        $service = app(RobloxApiService::class);
        $this->assertSame('builderman', $service->getUserById(156)['name']);
        $this->assertSame(156, $service->getUserById(156)['id']);
        Http::assertSentCount(1);
    }

    public function test_private_inventory_returns_a_structured_error(): void
    {
        Http::fake([
            'https://inventory.roblox.com/v2/users/1/inventory/8*' => Http::response(null, 403),
        ]);

        $result = app(RobloxApiService::class)->getInventoryCategory(1, 8);

        $this->assertSame([], $result['items']);
        $this->assertStringContainsString('private', strtolower($result['error']));
    }

    public function test_total_rap_ignores_items_without_a_resale_price(): void
    {
        $result = app(RobloxApiService::class)->calculateTotalRap([
            ['name' => 'A', 'assetId' => 1, 'recentAveragePrice' => 100],
            ['name' => 'B', 'assetId' => 2, 'recentAveragePrice' => 500],
            ['name' => 'C', 'assetId' => 3, 'recentAveragePrice' => null],
            ['name' => 'D', 'assetId' => 4, 'recentAveragePrice' => 200],
        ]);

        $this->assertSame(800, $result['total_rap']);
        $this->assertSame(267, $result['average_rap']);
        $this->assertSame(3, $result['item_count']);
        $this->assertSame(500, $result['highest']['rap']);
        $this->assertSame(100, $result['lowest']['rap']);
    }
}