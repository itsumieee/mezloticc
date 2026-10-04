<?php

namespace Tests\Feature;

use App\Models\AccountSnapshot;
use App\Models\Watchlist;
use App\Services\RobloxApiService;
use App\Services\RolimonsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_export_contains_user_and_inventory_data(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getLimitedItemsPaged')->once()->with(156, 100, '')
            ->andReturn([
                'items' => [['assetId' => 1, 'name' => 'Test Limited', 'recentAveragePrice' => 25]],
                'total' => 1,
                'next_cursor' => null,
                'error' => null,
            ]);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)
            ->andReturn(['name' => 'builderman', 'displayName' => 'Builderman']);

        $this->get(route('dashboard.export', [
            'userId' => 156,
            'format' => 'json',
            'category' => 'collectibles',
        ]))
            ->assertOk()
            ->assertHeader('Content-Disposition')
            ->assertJsonPath('user.id', 156)
            ->assertJsonPath('items.0.assetId', 1);
    }

    public function test_csv_export_streams_a_safe_attachment(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('canViewInventory')->once()->with(156)->andReturn(true);
        $roblox->shouldReceive('getInventoryCategoryPaged')->once()->with(156, 8, 100, '')
            ->andReturn([
                'items' => [['assetId' => 1, 'name' => '=1+1', 'recentAveragePrice' => 25]],
                'total' => 1,
                'next_cursor' => null,
                'error' => null,
            ]);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)
            ->andReturn(['name' => 'builderman']);

        $response = $this->get(route('dashboard.export', [
            'userId' => 156,
            'format' => 'csv',
            'category' => 8,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('builderman_8_', $response->headers->get('Content-Disposition'));

        ob_start();
        $response->baseResponse->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString("'=1+1", $csv);
    }

    public function test_public_api_resolves_a_user_id(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserById')->once()->with(156)->andReturn([
            'id' => 156,
            'name' => 'builderman',
            'displayName' => 'Builderman',
        ]);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)->andReturn([
            'name' => 'builderman',
            'displayName' => 'Builderman',
            'description' => 'Roblox user',
        ]);
        $roblox->shouldReceive('getAvatarHeadshot')->once()->with(156)->andReturn('https://tr.rbxcdn.com/headshot');
        $roblox->shouldReceive('getAvatarThumbnail')->once()->with(156)->andReturn('https://tr.rbxcdn.com/avatar');

        $this->getJson('/api/v1/user/156')
            ->assertOk()
            ->assertJsonPath('data.id', 156)
            ->assertJsonPath('data.username', 'builderman');
    }

    public function test_public_inventory_api_rejects_private_inventory(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('canViewInventory')->once()->with(156)->andReturn(false);
        $roblox->shouldNotReceive('getInventoryCategory');

        $this->getJson('/api/v1/user/156/inventory/8')
            ->assertStatus(503)
            ->assertJsonPath('error', 'unavailable');
    }

    public function test_watchlist_adds_and_lists_a_public_account(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserById')->once()->with(156)->andReturn([
            'id' => 156,
            'name' => 'builderman',
            'displayName' => 'Builderman',
        ]);

        $this->post(route('watchlist.store'), ['user_id' => 156, 'note' => 'Roblox account'])
            ->assertRedirect();
        $this->assertDatabaseHas('watchlists', ['roblox_user_id' => 156, 'username' => 'builderman']);

        $this->get(route('watchlist.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Watchlist')
                ->where('items.data.0.display_name', 'Builderman')
                ->where('items.data.0.note', 'Roblox account'));
    }

    public function test_compare_resolves_and_renders_two_accounts(): void
    {
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserByUsername')->twice()->andReturn(
            ['id' => 156, 'name' => 'builderman', 'displayName' => 'Builderman'],
            ['id' => 157, 'name' => 'testuser', 'displayName' => 'Test User'],
        );
        $roblox->shouldReceive('getUserProfile')->andReturnUsing(static fn (int $id): array => [
            'id' => $id,
            'name' => $id === 156 ? 'builderman' : 'testuser',
            'displayName' => $id === 156 ? 'Builderman' : 'Test User',
            'created' => '2007-01-01T00:00:00Z',
        ]);
        $roblox->shouldReceive('getAllLimitedItems')->andReturn(['items' => [], 'total' => 0, 'error' => null]);
        $roblox->shouldReceive('calculateTotalRap')->andReturn([
            'total_rap' => 0,
            'average_rap' => 0,
            'item_count' => 0,
            'highest' => null,
            'lowest' => null,
        ]);
        $roblox->shouldReceive('getCurrentlyWearing')->andReturn(['assetIds' => []]);
        $roblox->shouldReceive('getBundles')->andReturn(['bundles' => [], 'total' => 0, 'error' => null]);
        $roblox->shouldReceive('getAvatarHeadshot')->andReturn(null);

        $this->post(route('compare'), ['a' => 'builderman', 'b' => 'testuser'])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Compare/Result')
                ->where('userA.displayName', 'Builderman')
                ->where('userB.displayName', 'Test User')
                ->where('dataA.rap.total_rap', 0));
    }

    public function test_snapshot_command_records_one_daily_snapshot_per_watched_account(): void
    {
        Watchlist::create([
            'roblox_user_id' => 156,
            'username' => 'builderman',
            'display_name' => 'Builderman',
        ]);

        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getAllLimitedItems')->once()->with(156)->andReturn([
            'items' => [['assetId' => 1, 'name' => 'Limited', 'recentAveragePrice' => 200]],
            'total' => 1,
            'error' => null,
        ]);
        $roblox->shouldReceive('calculateTotalRap')->once()->andReturn([
            'total_rap' => 200,
            'average_rap' => 200,
            'item_count' => 1,
            'highest' => null,
            'lowest' => null,
        ]);
        $roblox->shouldReceive('getCurrentlyWearing')->once()->with(156)->andReturn(['assetIds' => [1, 2]]);
        $roblox->shouldReceive('getBundles')->once()->with(156)->andReturn(['bundles' => [], 'total' => 0]);

        $this->artisan('roblox:snapshot-watchlist')->assertSuccessful();

        $snapshot = AccountSnapshot::query()->where('roblox_user_id', 156)->firstOrFail();
        $this->assertSame(today()->toDateString(), $snapshot->snapshot_date->toDateString());
        $this->assertSame(1, $snapshot->limited_count);
        $this->assertSame(200, $snapshot->total_rap);
        $this->assertSame(2, $snapshot->wearing_count);
        $this->assertSame(1, AccountSnapshot::query()->where('roblox_user_id', 156)->count());
    }

    public function test_og_svg_escapes_profile_text_and_caches_only_safe_avatar_urls(): void
    {
        Storage::fake('public');
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserProfile')->once()->with(156)->andReturn([
            'id' => 156,
            'name' => 'builderman',
            'displayName' => 'Builder </text><script>alert(1)</script>',
        ]);
        $roblox->shouldReceive('getAvatarThumbnail')->once()->with(156, '420x420')
            ->andReturn('https://tr.rbxcdn.com/avatar.png');
        $roblox->shouldReceive('getLimitedItems')->once()->with(156, 100)
            ->andReturn(['total' => 4, 'error' => null]);

        $response = $this->get(route('og.image', 156));
        $svg = $response->getContent();

        $response->assertOk()->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8');
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $svg);
        $this->assertStringNotContainsString('<script>alert(1)', $svg);
        $this->assertStringContainsString('https://tr.rbxcdn.com/avatar.png', $svg);
        Storage::disk('public')->assertExists('og/156.svg');
    }

    public function test_og_svg_does_not_embed_an_untrusted_avatar_host(): void
    {
        Storage::fake('public');
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserProfile')->once()->with(157)->andReturn([
            'id' => 157,
            'name' => 'testuser',
            'displayName' => 'Test User',
        ]);
        $roblox->shouldReceive('getAvatarThumbnail')->once()->with(157, '420x420')
            ->andReturn('https://attacker.example/image.svg');
        $roblox->shouldReceive('getLimitedItems')->once()->with(157, 100)
            ->andReturn(['total' => 0, 'error' => null]);

        $svg = $this->get(route('og.image', 157))->assertOk()->getContent();

        $this->assertStringNotContainsString('attacker.example', $svg);
        $this->assertStringNotContainsString('<image', $svg);
    }

    public function test_rolimons_data_is_cached_and_remains_optional(): void
    {
        Cache::flush();
        Http::fake([
            'https://api.rolimons.com/players/v1/playerassets/156' => Http::response(['items' => [1, 2]], 200),
        ]);

        $service = app(RolimonsService::class);
        $first = $service->getUserAssets(156);
        $second = $service->getUserAssets(156);

        $this->assertSame(['items' => [1, 2]], $first);
        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_api_docs_page_is_available(): void
    {
        $this->get(route('api.docs'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ApiDocs'));
    }
}