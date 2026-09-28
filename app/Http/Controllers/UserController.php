<?php

namespace App\Http\Controllers;

use App\Models\CachedUser;
use App\Models\CachedInventory;
use App\Models\SearchHistory;
use App\Services\RobloxApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class UserController extends Controller
{
    public function search(Request $request, RobloxApiService $roblox): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:20'],
        ]);
        $username = trim($validated['username']);
        $user = $roblox->getUserByUsername($username);

        SearchHistory::create([
            'query' => $username,
            'resolved_user_id' => $user['id'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        if (empty($user['id']) || !is_numeric($user['id'])) {
            return back()->withInput()->with('error', 'Roblox username was not found or is unavailable.');
        }

        return redirect()->route('dashboard.overview', ['userId' => (int) $user['id']]);
    }

    public function overview(int $userId, RobloxApiService $roblox): View
    {
        $profile = $this->loadProfile($userId, $roblox);
        $limited = $roblox->getLimitedItemsTotal($userId);
        $wearing = $roblox->getCurrentlyWearing($userId) ?? [];
        $animations = $roblox->getInventoryCategoryTotal($userId, 24);
        $emotes = $roblox->getInventoryCategoryTotal($userId, 61);

        return view('dashboard.overview', [
            'userId' => $userId,
            'profile' => $profile,
            'headshot' => $roblox->getAvatarHeadshot($userId),
            'stats' => [
                'limited' => $limited['total'] ?? 0,
                'wearing' => count($wearing['assetIds'] ?? []),
                'animations' => ($animations['total'] ?? 0) + ($emotes['total'] ?? 0),
            ],
            'statsErrors' => array_filter([
                'Limited' => $limited['error'] ?? null,
                'Animations' => $animations['error'] ?? null,
                'Emotes' => $emotes['error'] ?? null,
            ]),
            'visible' => $roblox->canViewInventory($userId),
        ]);
    }

    public function profile(int $userId, RobloxApiService $roblox): View
    {
        return view('dashboard.profile', [
            'userId' => $userId,
            'profile' => $this->loadProfile($userId, $roblox),
            'avatarUrl' => $roblox->getAvatarHeadshot($userId, '420x420'),
        ]);
    }

    public function inventory(Request $request, int $userId, RobloxApiService $roblox): View
    {
        $items = [];
        $error = null;
        $assetTypeId = (int) $request->query('type', 8);
        abort_unless(array_key_exists($assetTypeId, RobloxApiService::ASSET_TYPES), 404);

        $cursor = $request->query('cursor', '');
        abort_unless(is_string($cursor) && strlen($cursor) <= 2048, 400);
        $cursorHistory = $request->query('cursor_history', []);
        $cursorHistory = is_array($cursorHistory)
            ? array_values(array_filter($cursorHistory, static fn ($value) => is_string($value) && strlen($value) <= 2048))
            : [];
        $cursorHistory = array_slice($cursorHistory, -20);
        $historyForNext = $cursorHistory;
        $result = ['total' => 0, 'next_cursor' => null, 'error' => null];

        if (!$roblox->canViewInventory($userId)) {
            $error = 'Inventory is private or unavailable.';
        } else {
            $result = $roblox->getInventoryCategoryPaged($userId, $assetTypeId, 25, $cursor);
            $items = $result['items'] ?? [];
            $error = $result['error'] ?? null;
        }

        $totalResult = $error === null
            ? $roblox->getInventoryCategoryTotal($userId, $assetTypeId)
            : ['total' => null, 'error' => $error];
        if (!empty($totalResult['error'])) {
            $error = $totalResult['error'];
        }

        $assetIds = array_values(array_unique(array_filter(array_map(
            static fn (array $item) => $item['assetId'] ?? $item['id'] ?? null,
            $items
        ))));

        $previousUrl = null;
        if ($cursorHistory !== []) {
            $previousCursor = array_pop($cursorHistory);
            $previousUrl = route('dashboard.inventory', ['userId' => $userId]).'?'.http_build_query([
                'type' => $assetTypeId,
                'cursor' => $previousCursor,
                'cursor_history' => $cursorHistory,
            ]);
        }

        $nextUrl = null;
        if (!empty($result['next_cursor'])) {
            $nextUrl = route('dashboard.inventory', ['userId' => $userId]).'?'.http_build_query([
                'type' => $assetTypeId,
                'cursor' => $result['next_cursor'],
                'cursor_history' => [...$historyForNext, $cursor],
            ]);
        }

        return view('dashboard.inventory', [
            'userId' => $userId,
            'items' => $items,
            'thumbnails' => $roblox->getAssetThumbnails($assetIds),
            'error' => $error,
            'total' => $totalResult['total'],
            'assetTypes' => RobloxApiService::ASSET_TYPES,
            'assetTypeId' => $assetTypeId,
            'previousUrl' => $previousUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    public function limited(Request $request, int $userId, RobloxApiService $roblox): View
    {
        $cursor = $request->query('cursor', '');
        abort_unless(is_string($cursor) && strlen($cursor) <= 2048, 400);

        $cursorHistory = $request->query('cursor_history', []);
        $cursorHistory = is_array($cursorHistory)
            ? array_values(array_filter($cursorHistory, static fn ($value) => is_string($value) && strlen($value) <= 2048))
            : [];
        $cursorHistory = array_slice($cursorHistory, -20);
        $historyForNext = $cursorHistory;

        $result = $roblox->getLimitedItemsPaged($userId, 25, $cursor);
        $totalResult = empty($result['error'])
            ? $roblox->getLimitedItemsTotal($userId)
            : ['total' => null, 'error' => $result['error']];
        if (!empty($totalResult['error'])) {
            $result['error'] = $totalResult['error'];
        }
        $items = $result['items'] ?? [];
        $assetIds = array_values(array_filter(array_map(
            static fn (array $item) => $item['assetId'] ?? null,
            $items
        )));

        $previousUrl = null;
        if ($cursorHistory !== []) {
            $previousCursor = array_pop($cursorHistory);
            $previousUrl = route('dashboard.limited', ['userId' => $userId]).'?'.http_build_query([
                'cursor' => $previousCursor,
                'cursor_history' => $cursorHistory,
            ]);
        }

        $nextUrl = null;
        if (!empty($result['next_cursor'])) {
            $nextUrl = route('dashboard.limited', ['userId' => $userId]).'?'.http_build_query([
                'cursor' => $result['next_cursor'],
                'cursor_history' => [...$historyForNext, $cursor],
            ]);
        }

        return view('dashboard.limited', [
            'userId' => $userId,
            'items' => $items,
            'total' => $totalResult['total'],
            'error' => $result['error'] ?? null,
            'thumbnails' => $roblox->getAssetThumbnails($assetIds),
            'previousUrl' => $previousUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    public function animations(int $userId, RobloxApiService $roblox): View
    {
        $animations = $roblox->getInventoryCategory($userId, 24, 100);
        $emotes = $roblox->getInventoryCategory($userId, 61, 100);
        $items = array_merge($animations['items'] ?? [], $emotes['items'] ?? []);
        $animationTotal = $roblox->getInventoryCategoryTotal($userId, 24);
        $emoteTotal = $roblox->getInventoryCategoryTotal($userId, 61);
        $errors = array_filter([
            $animations['error'] ?? null,
            $emotes['error'] ?? null,
            $animationTotal['error'] ?? null,
            $emoteTotal['error'] ?? null,
        ]);

        return view('dashboard.animations', [
            'userId' => $userId,
            'items' => $items,
            'total' => !empty($errors)
                ? null
                : ($animationTotal['total'] ?? 0) + ($emoteTotal['total'] ?? 0),
            'error' => implode(' ', array_unique($errors)),
        ]);
    }

    public function avatar(int $userId, RobloxApiService $roblox): View
    {
        return view('dashboard.avatar', [
            'userId' => $userId,
            'avatarUrl' => $roblox->getAvatarThumbnail($userId),
            'wearing' => $roblox->getCurrentlyWearing($userId),
        ]);
    }

    public function bundles(int $userId, RobloxApiService $roblox): View
    {
        $result = $roblox->getBundles($userId);

        return view('dashboard.bundles', [
            'userId' => $userId,
            'bundles' => $result['bundles'] ?? [],
            'total' => $result['total'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    public function statistics(int $userId, RobloxApiService $roblox): View
    {
        $limited = $roblox->getLimitedItemsTotal($userId);
        $wearing = $roblox->getCurrentlyWearing($userId) ?? [];
        $animations = $roblox->getInventoryCategoryTotal($userId, 24);
        $emoteTotal = $roblox->getInventoryCategoryTotal($userId, 61);
        $bundles = $roblox->getBundles($userId);
        $categories = [
            'accessories' => $roblox->getInventoryCategoryTotal($userId, 8),
            'faces' => $roblox->getInventoryCategoryTotal($userId, 18),
            'shirts' => $roblox->getInventoryCategoryTotal($userId, 11),
            'pants' => $roblox->getInventoryCategoryTotal($userId, 12),
            'hair' => $roblox->getInventoryCategoryTotal($userId, 41),
        ];

        $count = static fn (array $data): int => (int) ($data['total'] ?? 0);
        $stats = [
            'limited' => $count($limited),
            'wearing' => count($wearing['assetIds'] ?? []),
            'animations' => $count($animations) + $count($emoteTotal),
            'bundles' => (int) ($bundles['total'] ?? count($bundles['bundles'] ?? [])),
            'accessories' => $count($categories['accessories']),
            'faces' => $count($categories['faces']),
            'shirts' => $count($categories['shirts']),
            'pants' => $count($categories['pants']),
            'hair' => $count($categories['hair']),
        ];
        $statsErrors = array_filter([
            'Limited' => $limited['error'] ?? null,
            'Animations' => $animations['error'] ?? null,
            'Emotes' => $emoteTotal['error'] ?? null,
            'Accessories' => $categories['accessories']['error'] ?? null,
            'Faces' => $categories['faces']['error'] ?? null,
            'Shirts' => $categories['shirts']['error'] ?? null,
            'Pants' => $categories['pants']['error'] ?? null,
            'Hair' => $categories['hair']['error'] ?? null,
            'Bundles' => $bundles['error'] ?? null,
        ]);

        $hasMore = [
            'limited' => false,
            'animations' => false,
            'accessories' => false,
            'faces' => false,
            'shirts' => false,
            'pants' => false,
            'hair' => false,
            'bundles' => false,
            'wearing' => false,
        ];

        $chartLabels = [
            'Limited'.($hasMore['limited'] ? '+' : ''),
            'Animations'.($hasMore['animations'] ? '+' : ''),
            'Bundles',
            'Accessories (Hat)'.($hasMore['accessories'] ? '+' : ''),
            'Faces'.($hasMore['faces'] ? '+' : ''),
            'Shirts'.($hasMore['shirts'] ? '+' : ''),
            'Pants'.($hasMore['pants'] ? '+' : ''),
            'Hair'.($hasMore['hair'] ? '+' : ''),
        ];
        $chartValues = [
            $stats['limited'], $stats['animations'], $stats['bundles'], $stats['accessories'],
            $stats['faces'], $stats['shirts'], $stats['pants'], $stats['hair'],
        ];

        return view('dashboard.statistics', [
            'userId' => $userId,
            'stats' => $stats,
            'hasMore' => $hasMore,
            'statsErrors' => $statsErrors,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }

    public function value(int $userId, RobloxApiService $roblox): View
    {
        $limited = $roblox->getAllLimitedItems($userId);

        return view('dashboard.value', [
            'userId' => $userId,
            'rapData' => $roblox->calculateTotalRap($limited['items'] ?? []),
            'error' => $limited['error'] ?? null,
        ]);
    }

    public function refresh(int $userId): RedirectResponse
    {
        $cachedUser = CachedUser::query()->where('roblox_user_id', $userId)->first();
        if ($cachedUser?->username) {
            Cache::forget('roblox.user.by_username.'.md5($cachedUser->username));
        }

        $keys = [
            "roblox.user.profile.{$userId}",
            "roblox.avatar.{$userId}",
            "roblox.wearing.{$userId}",
            "roblox.inv_visible.{$userId}",
            "roblox.inv_categories.{$userId}",
            "roblox.limited.{$userId}.1",
            "roblox.limited.{$userId}.30",
            "roblox.limited.{$userId}.100",
            "roblox.thumb.{$userId}.420x420",
            "roblox.thumb.{$userId}.720x720",
            "roblox.headshot.{$userId}.150x150",
            "roblox.headshot.{$userId}.420x420",
            "roblox.bundles.{$userId}.1",
            "rolimons.rap.{$userId}",
        ];

        foreach (array_keys(RobloxApiService::ASSET_TYPES) as $assetTypeId) {
            foreach ([1, 10, 25, 30, 50, 100] as $limit) {
                $keys[] = "roblox.inventory.{$userId}.{$assetTypeId}.{$limit}";
            }
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Change the generation so any cached cursor pages become unreachable.
        Cache::forever("roblox.cache_version.{$userId}", (string) now()->timestamp.'-'.bin2hex(random_bytes(4)));

        $cachedUser?->delete();
        CachedInventory::query()->where('roblox_user_id', $userId)->delete();

        return back()->with('status', 'Data refreshed. Fresh data will be fetched on the next page load.');
    }

    public function history(): View
    {
        $history = SearchHistory::query()->orderByDesc('created_at')->limit(50)->get();

        return view('history', compact('history'));
    }

    private function loadProfile(int $userId, RobloxApiService $roblox): array
    {
        $profile = $roblox->getUserProfile($userId);

        if ($profile !== null) {
            return $profile;
        }

        $cachedUser = CachedUser::query()
            ->where('roblox_user_id', $userId)
            ->first();
        $cachedProfile = $cachedUser?->raw_profile;

        if (is_array($cachedProfile)) {
            return $cachedProfile;
        }

        abort(404, 'Roblox user not found or unavailable.');
    }
}