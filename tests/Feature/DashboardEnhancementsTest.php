<?php

namespace Tests\Feature;

use App\Models\CachedInventory;
use App\Models\CachedUser;
use App\Models\SearchHistory;
use App\Services\RolimonsService;
use App\Services\RobloxApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_history_page_lists_recent_queries(): void
    {
        SearchHistory::create([
            'query' => 'Builderman',
            'resolved_user_id' => 156,
        ]);

        $this->get(route('history'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('History')
                ->where('history.0.query', 'Builderman')
                ->where('history.0.resolved_user_id', 156));
    }

    public function test_refresh_clears_user_cache_and_database_cache_rows(): void
    {
        CachedUser::create([
            'roblox_user_id' => 156,
            'username' => 'Builderman',
            'raw_profile' => ['name' => 'Builderman'],
            'cached_at' => now(),
        ]);
        CachedInventory::create([
            'roblox_user_id' => 156,
            'category' => '8',
            'items' => [],
            'total_count' => 0,
            'cached_at' => now(),
        ]);
        Cache::put('roblox.user.profile.156', ['name' => 'old'], 600);
        Cache::put('roblox.inventory.156.8.100', ['items' => []], 600);

        $this->from(route('dashboard.overview', 156))
            ->post(route('dashboard.refresh', 156))
            ->assertRedirect(route('dashboard.overview', 156))
            ->assertSessionHas('status');

        $this->assertNull(Cache::get('roblox.user.profile.156'));
        $this->assertNull(Cache::get('roblox.inventory.156.8.100'));
        $this->assertDatabaseMissing('cached_users', ['roblox_user_id' => 156]);
        $this->assertDatabaseMissing('cached_inventories', ['roblox_user_id' => 156]);
    }

    public function test_inventory_uses_cursor_pagination_and_renders_item_cards(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('canViewInventory')->once()->with(156)->andReturn(true);
        $roblox->shouldReceive('getInventoryCategoryPaged')
            ->once()
            ->with(156, 8, 25, '')
            ->andReturn([
                'items' => [[
                    'assetId' => 123,
                    'name' => 'Test Hat',
                    'recentAveragePrice' => 450,
                ]],
                'total' => 31,
                'next_cursor' => 'cursor-next',
                'error' => null,
            ]);
        $roblox->shouldReceive('getInventoryCategoryTotal')
            ->once()
            ->with(156, 8)
            ->andReturn(['total' => 31, 'error' => null]);
        $roblox->shouldReceive('getAssetThumbnails')
            ->once()
            ->with([123])
            ->andReturn([123 => 'https://example.test/hat.png']);

        $this->get(route('dashboard.inventory', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Inventory')
                ->where('items.0.name', 'Test Hat')
                ->where('total', 31)
                ->where('thumbnails.123', 'https://example.test/hat.png')
                ->where('nextUrl', fn (string $url): bool => str_contains($url, 'cursor-next')));
    }

    public function test_inventory_api_uses_a_supported_limit_instead_of_returning_zero(): void
    {
        Http::fake([
            'https://inventory.roblox.com/v2/users/156/inventory/8*' => Http::response([
                'data' => [['assetId' => 123, 'name' => 'Test Hat']],
                'nextPageCursor' => null,
                'previousPageCursor' => null,
            ]),
            'https://inventory.roblox.com/v1/users/156/assets/collectibles*' => Http::response([
                'data' => [['assetId' => 456, 'name' => 'Test Limited']],
                'nextPageCursor' => null,
                'previousPageCursor' => null,
            ]),
        ]);

        $service = app(RobloxApiService::class);
        $result = $service->getInventoryCategory(156, 8, 1);
        $limited = $service->getLimitedItems(156, 1);

        $this->assertCount(1, $result['items']);
        $this->assertSame(1, $result['total']);
        $this->assertNull($result['error']);
        $this->assertCount(1, $limited['items']);
        Http::assertSent(fn ($request) =>
            str_contains($request->url(), '/inventory/8') && str_contains($request->url(), 'limit=10')
        );
        Http::assertSent(fn ($request) =>
            str_contains($request->url(), '/assets/collectibles') && str_contains($request->url(), 'limit=10')
        );
    }

    public function test_inventory_total_scans_and_counts_every_cursor_page(): void
    {
        $firstPage = array_map(static fn (int $id): array => ['assetId' => $id], range(1, 100));
        Http::fakeSequence('https://inventory.roblox.com/v2/users/156/inventory/8*')
            ->push(['data' => $firstPage, 'nextPageCursor' => 'page-two'])
            ->push(['data' => [
                ['assetId' => 101],
                ['assetId' => 102],
                ['assetId' => 103],
            ], 'nextPageCursor' => null]);

        $total = app(RobloxApiService::class)->getInventoryCategoryTotal(156, 8);

        $this->assertSame(103, $total['total']);
        $this->assertNull($total['error']);
        Http::assertSentCount(2);
    }

    public function test_limited_total_counts_all_collectible_pages(): void
    {
        Http::fakeSequence('https://inventory.roblox.com/v1/users/156/assets/collectibles*')
            ->push([
                'data' => array_map(static fn (int $id): array => ['assetId' => $id], range(1, 100)),
                'nextPageCursor' => 'collectibles-page-two',
            ])
            ->push([
                'data' => array_map(static fn (int $id): array => ['assetId' => $id], range(101, 225)),
                'nextPageCursor' => null,
            ]);

        $total = app(RobloxApiService::class)->getLimitedItemsTotal(156);

        $this->assertSame(225, $total['total']);
        $this->assertNull($total['error']);
        Http::assertSentCount(2);
    }

    public function test_bundles_load_and_count_every_cursor_page(): void
    {
        Http::fakeSequence('https://catalog.roblox.com/v1/users/156/bundles/1*')
            ->push([
                'data' => array_map(static fn (int $id): array => ['id' => $id], range(1, 10)),
                'nextPageCursor' => 'bundles-page-two',
            ])
            ->push([
                'data' => array_map(static fn (int $id): array => ['id' => $id], range(11, 13)),
                'nextPageCursor' => null,
            ]);

        $result = app(RobloxApiService::class)->getBundles(156);

        $this->assertCount(13, $result['bundles']);
        $this->assertSame(13, $result['total']);
        $this->assertNull($result['error']);
        Http::assertSentCount(2);
    }

    public function test_inventory_value_is_calculated_from_all_limited_pages(): void
    {
        $items = [
            ['assetId' => 1, 'name' => 'Limited A', 'recentAveragePrice' => 20],
            ['assetId' => 2, 'name' => 'Limited B', 'recentAveragePrice' => 35],
        ];
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getAllLimitedItems')->once()->with(156)
            ->andReturn(['items' => $items, 'total' => 2, 'error' => null]);
        $roblox->shouldReceive('calculateTotalRap')->once()->with($items)
            ->andReturn([
                'total_rap' => 55,
                'average_rap' => 28,
                'item_count' => 2,
                'highest' => ['name' => 'Limited B', 'rap' => 35],
                'lowest' => ['name' => 'Limited A', 'rap' => 20],
            ]);
        $rolimons = $this->mock(RolimonsService::class);
        $rolimons->shouldReceive('getUserAssets')->once()->with(156)->andReturn(null);

        $this->get(route('dashboard.value', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Value')
                ->where('rapData.total_rap', 55)
                ->where('rapValues', [20, 35]));
    }

    public function test_limited_page_explains_a_successful_empty_collectibles_response(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getLimitedItemsPaged')->once()->with(156, 25, '')
            ->andReturn([
                'items' => [],
                'total' => 0,
                'next_cursor' => null,
                'error' => null,
            ]);
        $roblox->shouldReceive('getLimitedItemsTotal')->once()->with(156)
            ->andReturn(['total' => 225, 'error' => null]);
        $roblox->shouldReceive('getAssetThumbnails')->once()->with([])->andReturn([]);

        $this->get(route('dashboard.limited', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Limited')
                ->where('items', [])
                ->where('total', 225)
                ->where('error', null));
    }

    public function test_statistics_counts_emotes_when_animation_asset_type_is_empty(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getLimitedItemsTotal')->once()->with(156)
            ->andReturn(['total' => 0, 'error' => null]);
        $roblox->shouldReceive('getCurrentlyWearing')->once()->with(156)
            ->andReturn(['assetIds' => []]);
        $roblox->shouldReceive('getBundles')->once()->with(156)
            ->andReturn(['bundles' => [], 'error' => null]);
        $roblox->shouldReceive('getInventoryCategoryTotal')->andReturnUsing(
            static function (int $userId, int $assetTypeId): array {
                return match ($assetTypeId) {
                    61 => [
                        'total' => 9,
                        'error' => null,
                    ],
                    default => ['total' => 0, 'error' => null],
                };
            }
        );

        $this->get(route('dashboard.statistics', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Statistics')
                ->where('stats.animations', 9)
                ->where('chartValues.1', 9));
    }

    public function test_overview_renders_username_with_at_sign_instead_of_blade_syntax(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)
            ->andReturn([
                'name' => 'ilonabevan56708',
                'displayName' => 'LONNN',
                'hasVerifiedBadge' => true,
            ]);
        $roblox->shouldReceive('getLimitedItemsTotal')->once()->with(156)
            ->andReturn(['total' => 0, 'error' => null]);
        $roblox->shouldReceive('getCurrentlyWearing')->once()->with(156)
            ->andReturn(['assetIds' => []]);
        $roblox->shouldReceive('getInventoryCategoryTotal')->once()->with(156, 24)
            ->andReturn(['total' => 0, 'error' => null]);
        $roblox->shouldReceive('getInventoryCategoryTotal')->once()->with(156, 61)
            ->andReturn(['total' => 1, 'error' => null]);
        $roblox->shouldReceive('getAvatarHeadshot')->once()->with(156)->andReturn(null);
        $roblox->shouldReceive('getUserPresence')->once()->with(156)->andReturn([
            'status' => 'In game',
            'status_key' => 'in-game',
            'game_name' => 'LONNN World',
            'game_url' => 'https://www.roblox.com/games/987',
            'last_online' => '2026-09-28T10:30:00.000Z',
        ]);
        $roblox->shouldReceive('getOwnedExperiences')->once()->with(156)->andReturn([
            ['id' => 987, 'name' => 'LONNN World', 'playing' => 4, 'visits' => 1234],
        ]);
        $roblox->shouldReceive('getCommunities')->once()->with(156)->andReturn([
            ['id' => 654, 'name' => 'LONNN Community', 'role' => 'Owner', 'member_count' => 42],
        ]);
        $roblox->shouldReceive('canViewInventory')->once()->with(156)->andReturn(true);

        $this->get(route('dashboard.overview', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Overview')
                ->where('profile.name', 'ilonabevan56708')
                ->where('profile.hasVerifiedBadge', true)
                ->where('stats.animations', 1)
                ->where('presence.game_name', 'LONNN World')
                ->where('communities.0.name', 'LONNN Community'));
    }

    public function test_profile_shows_verified_badge_only_when_roblox_reports_it(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)->andReturn([
            'name' => 'cadzz20',
            'displayName' => 'cadzz',
            'hasVerifiedBadge' => true,
        ]);
        $roblox->shouldReceive('getAvatarHeadshot')->once()->with(156, '420x420')->andReturn(null);
        $roblox->shouldReceive('getUserPresence')->once()->with(156)->andReturn([
            'status' => 'Offline',
            'status_key' => 'offline',
            'game_name' => null,
            'game_url' => null,
        ]);
        $roblox->shouldReceive('getOwnedExperiences')->once()->with(156)->andReturn([]);
        $roblox->shouldReceive('getCommunities')->once()->with(156)->andReturn([]);

        $this->get(route('dashboard.profile', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Profile')
                ->where('profile.hasVerifiedBadge', true));
    }

    public function test_profile_hides_verified_badge_when_roblox_does_not_report_it(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)->andReturn([
            'name' => 'cadzz20',
            'displayName' => 'cadzz',
            'hasVerifiedBadge' => false,
        ]);
        $roblox->shouldReceive('getAvatarHeadshot')->once()->with(156, '420x420')->andReturn(null);
        $roblox->shouldReceive('getUserPresence')->once()->with(156)->andReturn([
            'status' => 'Unavailable',
            'status_key' => 'unavailable',
            'game_name' => null,
            'game_url' => null,
        ]);
        $roblox->shouldReceive('getOwnedExperiences')->once()->with(156)->andReturn([]);
        $roblox->shouldReceive('getCommunities')->once()->with(156)->andReturn([]);

        $this->get(route('dashboard.profile', 156))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Profile')
                ->where('profile.hasVerifiedBadge', false));
    }

    public function test_search_endpoint_returns_custom_rate_limit_page(): void
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->post(route('search'), [])->assertRedirect();
        }

        $this->post(route('search'), [])
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Errors/ErrorPage')
                ->where('status', 429)
                ->has('seconds'));
    }
}
