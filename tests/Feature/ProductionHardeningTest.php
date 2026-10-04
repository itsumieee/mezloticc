<?php

namespace Tests\Feature;

use App\Jobs\WarmUserCache;
use App\Models\CachedInventory;
use App\Models\CachedItemDetail;
use App\Models\CachedUser;
use App\Models\SearchHistory;
use App\Services\RobloxApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_and_trace_id_are_added_to_responses(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            (string) $response->headers->get('Content-Security-Policy')
        );
        $this->assertTrue(Str::isUuid((string) $response->headers->get('X-Trace-Id')));
    }

    public function test_health_endpoint_checks_database_and_cache(): void
    {
        $this->getJson(route('health'))
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'checks' => [
                    'database' => 'ok',
                    'cache' => 'ok',
                ],
            ]);
    }

    public function test_security_headers_and_trace_id_are_also_added_to_exception_responses(): void
    {
        $response = $this->get('/missing-production-hardening-route');

        $response->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');

        $this->assertTrue(Str::isUuid((string) $response->headers->get('X-Trace-Id')));
    }

    public function test_successful_search_queues_cache_warming(): void
    {
        Queue::fake();
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserByUsername')->once()->with('builderman')->andReturn([
            'id' => 156,
            'name' => 'builderman',
        ]);

        $this->post(route('search'), ['username' => 'builderman'])
            ->assertRedirect(route('dashboard.overview', 156));

        Queue::assertPushedOn('default', WarmUserCache::class, fn (WarmUserCache $job): bool => $job->userId === 156);
    }

    public function test_numeric_search_resolves_a_roblox_user_id(): void
    {
        Queue::fake();
        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldReceive('getUserById')->once()->with(156)->andReturn([
            'id' => 156,
            'name' => 'builderman',
        ]);
        $roblox->shouldNotReceive('getUserByUsername');

        $this->post(route('search'), ['username' => '156'])
            ->assertRedirect(route('dashboard.overview', 156));

        Queue::assertPushedOn('default', WarmUserCache::class, fn (WarmUserCache $job): bool => $job->userId === 156);
    }

    public function test_global_search_limit_returns_service_unavailable(): void
    {
        for ($attempt = 0; $attempt < 500; $attempt++) {
            RateLimiter::hit('roblox-search:global', 60);
        }

        $roblox = $this->mock(RobloxApiService::class);
        $roblox->shouldNotReceive('getUserByUsername');

        $this->post(route('search'), ['username' => 'builderman'])
            ->assertStatus(503)
            ->assertHeader('Retry-After');
    }

    public function test_prune_cache_only_removes_records_older_than_the_cutoff(): void
    {
        $old = now()->subDays(8);

        CachedUser::create([
            'roblox_user_id' => 101,
            'username' => 'old-user',
            'cached_at' => $old,
        ]);
        CachedInventory::create([
            'roblox_user_id' => 101,
            'category' => '8',
            'items' => [],
            'cached_at' => $old,
        ]);
        CachedItemDetail::create([
            'asset_id' => 101,
            'name' => 'Old item',
            'cached_at' => $old,
        ]);
        $oldSearch = SearchHistory::create(['query' => 'old search']);
        $oldSearch->forceFill(['created_at' => $old])->save();

        CachedUser::create([
            'roblox_user_id' => 102,
            'username' => 'recent-user',
            'cached_at' => now(),
        ]);

        $this->assertSame(0, Artisan::call('roblox:prune-cache', ['--days' => 7]));
        $this->assertDatabaseMissing('cached_users', ['roblox_user_id' => 101]);
        $this->assertDatabaseHas('cached_users', ['roblox_user_id' => 102]);
        $this->assertDatabaseMissing('cached_inventories', ['roblox_user_id' => 101]);
        $this->assertDatabaseMissing('cached_item_details', ['asset_id' => 101]);
        $this->assertDatabaseMissing('search_histories', ['id' => $oldSearch->id]);
    }
}