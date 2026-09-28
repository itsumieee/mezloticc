<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\CachedUser;
use App\Models\CachedInventory;
use App\Models\CachedItemDetail;

class RobloxApiService
{
    private const INVENTORY_LIMITS = [10, 25, 50, 100];
    private const TIMEOUT     = 10;
    private const RETRY_TIMES = 2;
    private const RETRY_DELAY = 500;

    /* ---- Base URLs (verified from Roblox Creator Hub) ---- */
    private const USERS_BASE     = 'https://users.roblox.com';
    private const INVENTORY_BASE = 'https://inventory.roblox.com';
    private const AVATAR_BASE    = 'https://avatar.roblox.com';
    private const CATALOG_BASE   = 'https://catalog.roblox.com';
    private const THUMB_BASE     = 'https://thumbnails.roblox.com';
    private const ECONOMY_BASE   = 'https://economy.roblox.com';

    /* ---- Asset Type IDs (from AvatarAssetType enum) ---- */
    public const ASSET_TYPES = [
        2  => 'T-Shirt',
        8  => 'Hat',
        11 => 'Shirt',
        12 => 'Pants',
        17 => 'Head',
        18 => 'Face',
        19 => 'Gear',
        24 => 'Animation',
        41 => 'Hair',
        42 => 'Face Accessory',
        43 => 'Neck Accessory',
        44 => 'Shoulder Accessory',
        45 => 'Front Accessory',
        46 => 'Back Accessory',
        47 => 'Waist Accessory',
        61 => 'Emote Animation',
    ];

    /* ------------------------------------------------------------------ */
    /* HTTP client with timeout, retry, and 429 handling                   */
    /* ------------------------------------------------------------------ */
    private function client()
    {
        return Http::timeout(self::TIMEOUT)
            ->retry(self::RETRY_TIMES, self::RETRY_DELAY, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception->response && $exception->response->status() === 429);
            })
            ->withHeaders([
                'User-Agent' => 'RobloxAccountChecker/1.0 (Laravel)',
                'Accept'     => 'application/json',
            ]);
    }

    private function normalizeInventoryLimit(int $limit): int
    {
        $closest = self::INVENTORY_LIMITS[0];
        $smallestDifference = abs($limit - $closest);

        foreach (self::INVENTORY_LIMITS as $allowedLimit) {
            $difference = abs($limit - $allowedLimit);
            if ($difference < $smallestDifference) {
                $closest = $allowedLimit;
                $smallestDifference = $difference;
            }
        }

        return $closest;
    }

    /* ------------------------------------------------------------------ */
    /* 1. Resolve username → User ID                                       */
    /* POST /v1/usernames/users                                             */
    /* ------------------------------------------------------------------ */
    public function getUserByUsername(string $username): ?array
    {
        $cacheKey = 'roblox.user.by_username.' . md5($username);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($username) {
            try {
                $response = $this->client()->post(self::USERS_BASE . '/v1/usernames/users', [
                    'usernames'          => [$username],
                    'excludeBannedUsers' => false,
                ]);

                if ($response->failed()) {
                    Log::warning('Roblox username lookup failed', [
                        'username' => $username,
                        'status'   => $response->status(),
                    ]);
                    return null;
                }

                $data = $response->json('data');
                return !empty($data) ? $data[0] : null;
            } catch (\Throwable $e) {
                Log::error('Roblox username lookup exception', [
                    'username' => $username,
                    'error'    => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 2. Get full user profile                                            */
    /* GET /v1/users/{userId}                                              */
    /* ------------------------------------------------------------------ */
    public function getUserProfile(int $userId): ?array
    {
        $cacheKey = "roblox.user.profile.{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId) {
            try {
                $response = $this->client()->get(self::USERS_BASE . "/v1/users/{$userId}");

                if ($response->failed()) {
                    return null;
                }

                $profile = $response->json();

                CachedUser::updateOrCreate(
                    ['roblox_user_id' => $userId],
                    [
                        'username'           => $profile['name'] ?? '',
                        'display_name'       => $profile['displayName'] ?? '',
                        'description'        => $profile['description'] ?? '',
                        'account_created_at' => $profile['created'] ?? null,
                        'raw_profile'        => $profile,
                        'cached_at'          => now(),
                    ]
                );

                return $profile;
            } catch (\Throwable $e) {
                Log::error("Roblox profile fetch failed for {$userId}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 3. Get inventory by asset type                                      */
    /* GET /v2/users/{userId}/inventory/{assetTypeId}                      */
    /* ------------------------------------------------------------------ */
    public function getInventoryCategory(int $userId, int $assetTypeId, int $limit = 100): array
    {
        $limit = $this->normalizeInventoryLimit($limit);
        $cacheKey = "roblox.inventory.{$userId}.{$assetTypeId}.{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId, $assetTypeId, $limit) {
            try {
                $response = $this->client()->get(
                    self::INVENTORY_BASE . "/v2/users/{$userId}/inventory/{$assetTypeId}",
                    ['limit' => $limit]
                );

                if ($response->failed()) {
                    return ['items' => [], 'total' => 0, 'error' => 'Inventory is private or unavailable.'];
                }

                $items = $response->json('data') ?? [];
                $total = $response->json('total') ?? count($items);
                $nextCursor = $response->json('nextPageCursor');

                CachedInventory::updateOrCreate(
                    ['roblox_user_id' => $userId, 'category' => (string) $assetTypeId],
                    [
                        'items'       => $items,
                        'total_count' => $total,
                        'cached_at'   => now(),
                    ]
                );

                return [
                    'items' => $items,
                    'total' => $total,
                    'next_cursor' => $nextCursor,
                    'has_more' => !empty($nextCursor),
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                Log::error("Inventory fetch failed for {$userId} type {$assetTypeId}", ['error' => $e->getMessage()]);
                return ['items' => [], 'total' => 0, 'error' => 'Roblox data is temporarily unavailable.'];
            }
        });
    }

    public function getInventoryCategoryTotal(int $userId, int $assetTypeId): array
    {
        $version = Cache::get("roblox.cache_version.{$userId}", 1);
        $cacheKey = "roblox.inventory_total.{$userId}.{$assetTypeId}.{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($userId, $assetTypeId) {
            $cursor = '';
            $seenCursors = [];
            $total = 0;

            for ($page = 0; $page < 1000; $page++) {
                $result = $this->getInventoryCategoryPaged($userId, $assetTypeId, 100, $cursor);
                if (!empty($result['error'])) {
                    return ['total' => null, 'error' => $result['error']];
                }

                $total += count($result['items'] ?? []);
                $nextCursor = $result['next_cursor'] ?? null;
                if (!$nextCursor) {
                    return ['total' => $total, 'error' => null];
                }

                if (isset($seenCursors[$nextCursor])) {
                    return ['total' => null, 'error' => 'Roblox returned a repeated inventory page cursor.'];
                }

                $seenCursors[$nextCursor] = true;
                $cursor = $nextCursor;
            }

            return ['total' => null, 'error' => 'Inventory count exceeded the supported page scan.'];
        });
    }

    /* ------------------------------------------------------------------ */
    /* 4. Get collectible (Limited) items                                  */
    /* GET /v1/users/{userId}/assets/collectibles                          */
    /* Includes recentAveragePrice field directly!                         */
    /* ------------------------------------------------------------------ */
    public function getLimitedItems(int $userId, int $limit = 100): array
    {
        $limit = $this->normalizeInventoryLimit($limit);
        $cacheKey = "roblox.limited.{$userId}.{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($userId, $limit) {
            try {
                $response = $this->client()->get(
                    self::INVENTORY_BASE . "/v1/users/{$userId}/assets/collectibles",
                    ['limit' => $limit]
                );

                if ($response->failed()) {
                    return ['items' => [], 'total' => 0, 'error' => 'Limited items unavailable (inventory may be private).'];
                }

                $items = $response->json('data') ?? [];
                $total = $response->json('total') ?? count($items);

                $nextCursor = $response->json('nextPageCursor');

                return [
                    'items' => $items,
                    'total' => $total,
                    'next_cursor' => $nextCursor,
                    'has_more' => !empty($nextCursor),
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                Log::error("Limited items fetch failed for {$userId}", ['error' => $e->getMessage()]);
                return ['items' => [], 'total' => 0, 'error' => 'Roblox data is temporarily unavailable.'];
            }
        });
    }

    public function getLimitedItemsTotal(int $userId): array
    {
        $version = Cache::get("roblox.cache_version.{$userId}", 1);
        $cacheKey = "roblox.limited_total.{$userId}.{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($userId) {
            $cursor = '';
            $seenCursors = [];
            $total = 0;

            for ($page = 0; $page < 1000; $page++) {
                $result = $this->getLimitedItemsPaged($userId, 100, $cursor);
                if (!empty($result['error'])) {
                    return ['total' => null, 'error' => $result['error']];
                }

                $total += count($result['items'] ?? []);
                $nextCursor = $result['next_cursor'] ?? null;
                if (!$nextCursor) {
                    return ['total' => $total, 'error' => null];
                }

                if (isset($seenCursors[$nextCursor])) {
                    return ['total' => null, 'error' => 'Roblox returned a repeated collectibles page cursor.'];
                }

                $seenCursors[$nextCursor] = true;
                $cursor = $nextCursor;
            }

            return ['total' => null, 'error' => 'Collectibles count exceeded the supported page scan.'];
        });
    }

    public function getAllLimitedItems(int $userId): array
    {
        $version = Cache::get("roblox.cache_version.{$userId}", 1);
        $cacheKey = "roblox.limited_all.{$userId}.{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($userId) {
            $cursor = '';
            $seenCursors = [];
            $items = [];

            for ($page = 0; $page < 1000; $page++) {
                $result = $this->getLimitedItemsPaged($userId, 100, $cursor);
                if (!empty($result['error'])) {
                    return ['items' => [], 'total' => null, 'error' => $result['error']];
                }

                $items = array_merge($items, $result['items'] ?? []);
                $nextCursor = $result['next_cursor'] ?? null;
                if (!$nextCursor) {
                    return ['items' => $items, 'total' => count($items), 'error' => null];
                }

                if (isset($seenCursors[$nextCursor])) {
                    return ['items' => [], 'total' => null, 'error' => 'Roblox returned a repeated collectibles page cursor.'];
                }

                $seenCursors[$nextCursor] = true;
                $cursor = $nextCursor;
            }

            return ['items' => [], 'total' => null, 'error' => 'Collectibles scan exceeded the supported page limit.'];
        });
    }

    /* ------------------------------------------------------------------ */
    /* 5. Get currently wearing avatar                                     */
    /* GET /v1/users/{userId}/currently-wearing                            */
    /* ------------------------------------------------------------------ */
    public function getCurrentlyWearing(int $userId): ?array
    {
        $cacheKey = "roblox.wearing.{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            try {
                $response = $this->client()->get(
                    self::AVATAR_BASE . "/v1/users/{$userId}/currently-wearing"
                );

                return $response->successful() ? $response->json() : null;
            } catch (\Throwable $e) {
                Log::error("Currently wearing fetch failed for {$userId}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 6. Get avatar full body                                             */
    /* GET /v1/users/{userId}/avatar                                       */
    /* ------------------------------------------------------------------ */
    public function getAvatar(int $userId): ?array
    {
        $cacheKey = "roblox.avatar.{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            try {
                $response = $this->client()->get(
                    self::AVATAR_BASE . "/v1/users/{$userId}/avatar"
                );

                return $response->successful() ? $response->json() : null;
            } catch (\Throwable $e) {
                Log::error("Avatar fetch failed for {$userId}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 7. Get avatar thumbnail (full body)                                  */
    /* GET /v1/users/avatar                                                */
    /* ------------------------------------------------------------------ */
    public function getAvatarThumbnail(int $userId, string $size = '420x420'): ?string
    {
        $cacheKey = "roblox.thumb.{$userId}.{$size}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($userId, $size) {
            try {
                $response = $this->client()->get(self::THUMB_BASE . '/v1/users/avatar', [
                    'userIds'    => $userId,
                    'size'       => $size,
                    'format'     => 'Png',
                    'isCircular' => 'false',
                ]);

                if ($response->failed()) return null;

                $data = $response->json('data');
                return !empty($data) ? ($data[0]['imageUrl'] ?? null) : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 8. Get avatar headshot                                              */
    /* GET /v1/users/avatar-headshot                                       */
    /* ------------------------------------------------------------------ */
    public function getAvatarHeadshot(int $userId, string $size = '150x150'): ?string
    {
        $cacheKey = "roblox.headshot.{$userId}.{$size}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($userId, $size) {
            try {
                $response = $this->client()->get(self::THUMB_BASE . '/v1/users/avatar-headshot', [
                    'userIds'    => $userId,
                    'size'       => $size,
                    'format'     => 'Png',
                    'isCircular' => 'false',
                ]);

                if ($response->failed()) return null;

                $data = $response->json('data');
                return !empty($data) ? ($data[0]['imageUrl'] ?? null) : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 9. Get asset thumbnail                                              */
    /* GET /v1/assets                                                      */
    /* ------------------------------------------------------------------ */
    public function getAssetThumbnails(array $assetIds, string $size = '150x150'): array
    {
        if (empty($assetIds)) return [];

        $ids = implode(',', array_slice($assetIds, 0, 100));
        $cacheKey = 'roblox.asset_thumbs.' . md5($ids . $size);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($ids, $size) {
            try {
                $response = $this->client()->get(self::THUMB_BASE . '/v1/assets', [
                    'assetIds' => $ids,
                    'size'     => $size,
                    'format'   => 'Png',
                ]);

                if ($response->failed()) return [];

                $result = [];
                foreach ($response->json('data') ?? [] as $item) {
                    if (!empty($item['targetId']) && !empty($item['imageUrl'])) {
                        $result[$item['targetId']] = $item['imageUrl'];
                    }
                }
                return $result;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 10. Get bundles owned by user                                       */
    /* GET /v1/users/{userId}/bundles/{bundleType}                         */
    /* bundleType: 1 = Outfit bundles                                      */
    /* ------------------------------------------------------------------ */
    public function getBundles(int $userId, int $bundleType = 1): array
    {
        $version = Cache::get("roblox.cache_version.{$userId}", 1);
        $cacheKey = "roblox.bundles.{$userId}.{$bundleType}.{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($userId, $bundleType) {
            $cursor = '';
            $seenCursors = [];
            $bundles = [];

            for ($page = 0; $page < 1000; $page++) {
                try {
                    $params = [];
                    if ($cursor !== '') {
                        $params['cursor'] = $cursor;
                    }

                    $response = $this->client()->get(
                        self::CATALOG_BASE . "/v1/users/{$userId}/bundles/{$bundleType}",
                        $params
                    );

                    if ($response->failed()) {
                        return ['bundles' => [], 'total' => null, 'error' => 'Bundles unavailable.'];
                    }

                    $bundles = array_merge($bundles, $response->json('data') ?? []);
                    $nextCursor = $response->json('nextPageCursor');
                    if (!$nextCursor) {
                        return ['bundles' => $bundles, 'total' => count($bundles), 'error' => null];
                    }

                    if (isset($seenCursors[$nextCursor])) {
                        return ['bundles' => [], 'total' => null, 'error' => 'Roblox returned a repeated bundles page cursor.'];
                    }

                    $seenCursors[$nextCursor] = true;
                    $cursor = $nextCursor;
                } catch (\Throwable $e) {
                    Log::warning("Bundle fetch failed for {$userId}", ['error' => $e->getMessage()]);
                    return ['bundles' => [], 'total' => null, 'error' => 'Roblox data is temporarily unavailable.'];
                }
            }

            return ['bundles' => [], 'total' => null, 'error' => 'Bundles scan exceeded the supported page limit.'];
        });
    }

    /* ------------------------------------------------------------------ */
    /* 11. Get bundle details                                              */
    /* GET /v1/bundles/{bundleId}/details                                  */
    /* ------------------------------------------------------------------ */
    public function getBundleDetails(int $bundleId): ?array
    {
        $cacheKey = "roblox.bundle.details.{$bundleId}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($bundleId) {
            try {
                $response = $this->client()->get(
                    self::CATALOG_BASE . "/v1/bundles/{$bundleId}/details"
                );

                return $response->successful() ? $response->json() : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 12. Get item details                                                */
    /* GET /v2/assets/{assetId}/details                                    */
    /* ------------------------------------------------------------------ */
    public function getItemDetails(int $assetId): ?array
    {
        $cached = CachedItemDetail::where('asset_id', $assetId)->first();

        if ($cached && $cached->cached_at->diffInHours(now()) < 12) {
            return $cached->raw_details;
        }

        try {
            $response = $this->client()->get(
                self::ECONOMY_BASE . "/v2/assets/{$assetId}/details"
            );

            if ($response->failed()) return null;

            $details = $response->json();

            CachedItemDetail::updateOrCreate(
                ['asset_id' => $assetId],
                [
                    'name'         => $details['Name'] ?? null,
                    'creator_name' => $details['Creator']['Name'] ?? null,
                    'category'     => $details['AssetTypeId'] ?? null,
                    'is_limited'   => $details['IsLimited'] ?? false,
                    'rap'          => $details['RecentAveragePrice'] ?? null,
                    'raw_details'  => $details,
                    'cached_at'    => now(),
                ]
            );

            return $details;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ------------------------------------------------------------------ */
    /* 13. Get resale data (RAP) for a limited item                        */
    /* GET /v1/assets/{assetId}/resale-data                                */
    /* NOTE: May return null for CollectibleItem system limiteds           */
    /* ------------------------------------------------------------------ */
    public function getResaleData(int $assetId): ?array
    {
        $cacheKey = "roblox.resale.{$assetId}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($assetId) {
            try {
                $response = $this->client()->get(
                    self::ECONOMY_BASE . "/v1/assets/{$assetId}/resale-data"
                );

                return $response->successful() ? $response->json() : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 14. Check if inventory is public                                     */
    /* GET /v1/users/{userId}/can-view-inventory                           */
    /* ------------------------------------------------------------------ */
    public function canViewInventory(int $userId): bool
    {
        $cacheKey = "roblox.inv_visible.{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            try {
                $response = $this->client()->get(
                    self::INVENTORY_BASE . "/v1/users/{$userId}/can-view-inventory"
                );

                return $response->successful() && ($response->json('canView') ?? false);
            } catch (\Throwable $e) {
                return false;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 15. Get inventory categories                                        */
    /* GET /v1/users/{userId}/categories                                   */
    /* ------------------------------------------------------------------ */
    public function getInventoryCategories(int $userId): ?array
    {
        $cacheKey = "roblox.inv_categories.{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($userId) {
            try {
                $response = $this->client()->get(
                    self::INVENTORY_BASE . "/v1/users/{$userId}/categories"
                );

                return $response->successful() ? $response->json() : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* 16. Calculate total RAP from collectibles                           */
    /* ------------------------------------------------------------------ */
    public function calculateTotalRap(array $collectibleItems): array
    {
        $totalRap = 0;
        $itemCount = 0;
        $highest = null;
        $lowest = null;

        foreach ($collectibleItems as $item) {
            $rap = $item['recentAveragePrice'] ?? null;
            if ($rap === null) continue;

            $totalRap += $rap;
            $itemCount++;

            if ($highest === null || $rap > $highest['rap']) {
                $highest = ['name' => $item['name'] ?? 'Unknown', 'rap' => $rap, 'assetId' => $item['assetId'] ?? null];
            }
            if ($lowest === null || $rap < $lowest['rap']) {
                $lowest = ['name' => $item['name'] ?? 'Unknown', 'rap' => $rap, 'assetId' => $item['assetId'] ?? null];
            }
        }

        return [
            'total_rap'   => $totalRap,
            'average_rap' => $itemCount > 0 ? round($totalRap / $itemCount) : 0,
            'item_count'  => $itemCount,
            'highest'     => $highest,
            'lowest'      => $lowest,
        ];
    }

    public function getInventoryCategoryPaged(
        int $userId,
        int $assetTypeId,
        int $limit = 30,
        string $cursor = ''
    ): array {
        $limit = $this->normalizeInventoryLimit($limit);
        $version = Cache::get("roblox.cache_version.{$userId}", 1);
        $cacheKey = "roblox.inventory_page.{$userId}.{$assetTypeId}.{$limit}.{$version}.".md5($cursor);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId, $assetTypeId, $limit, $cursor) {
            try {
                $params = ['limit' => $limit];
                if ($cursor !== '') {
                    $params['cursor'] = $cursor;
                }

                $response = $this->client()->get(
                    self::INVENTORY_BASE . "/v2/users/{$userId}/inventory/{$assetTypeId}",
                    $params
                );

                if ($response->failed()) {
                    return [
                        'items' => [],
                        'total' => 0,
                        'next_cursor' => null,
                        'error' => 'Inventory unavailable or private.',
                    ];
                }

                return [
                    'items' => $response->json('data') ?? [],
                    'total' => $response->json('total') ?? count($response->json('data') ?? []),
                    'next_cursor' => $response->json('nextPageCursor'),
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                Log::warning("Paged inventory fetch failed for {$userId}", ['error' => $e->getMessage()]);
                return [
                    'items' => [],
                    'total' => 0,
                    'next_cursor' => null,
                    'error' => 'Roblox data is temporarily unavailable.',
                ];
            }
        });
    }

    public function getLimitedItemsPaged(int $userId, int $limit = 30, string $cursor = ''): array
    {
        $limit = $this->normalizeInventoryLimit($limit);
        $version = Cache::get("roblox.cache_version.{$userId}", 1);
        $cacheKey = "roblox.limited_page.{$userId}.{$limit}.{$version}.".md5($cursor);

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($userId, $limit, $cursor) {
            try {
                $params = ['limit' => $limit];
                if ($cursor !== '') {
                    $params['cursor'] = $cursor;
                }

                $response = $this->client()->get(
                    self::INVENTORY_BASE . "/v1/users/{$userId}/assets/collectibles",
                    $params
                );

                if ($response->failed()) {
                    return [
                        'items' => [],
                        'total' => 0,
                        'next_cursor' => null,
                        'error' => 'Limited items unavailable.',
                    ];
                }

                return [
                    'items' => $response->json('data') ?? [],
                    'total' => $response->json('total') ?? count($response->json('data') ?? []),
                    'next_cursor' => $response->json('nextPageCursor'),
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                Log::warning("Paged limited-item fetch failed for {$userId}", ['error' => $e->getMessage()]);
                return [
                    'items' => [],
                    'total' => 0,
                    'next_cursor' => null,
                    'error' => 'Roblox data is temporarily unavailable.',
                ];
            }
        });
    }
}