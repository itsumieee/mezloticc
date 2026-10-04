<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RobloxApiService;
use Illuminate\Http\JsonResponse;

class PublicApiController extends Controller
{
    public function __construct(private readonly RobloxApiService $roblox) {}

    public function user(string $identifier): JsonResponse
    {
        $user = ctype_digit($identifier)
            ? $this->roblox->getUserById((int) $identifier)
            : $this->roblox->getUserByUsername($identifier);

        if (! $user || empty($user['id'])) {
            return response()->json([
                'error' => 'user_not_found',
                'message' => 'Roblox user not found.',
            ], 404);
        }

        $userId = (int) $user['id'];
        $profile = $this->roblox->getUserProfile($userId) ?? $user;

        return $this->success([
            'id' => $userId,
            'username' => $user['name'] ?? null,
            'display_name' => $user['displayName'] ?? null,
            'description' => $profile['description'] ?? null,
            'created_at' => $profile['created'] ?? null,
            'is_banned' => $profile['isBanned'] ?? null,
            'headshot_url' => $this->roblox->getAvatarHeadshot($userId),
            'avatar_url' => $this->roblox->getAvatarThumbnail($userId),
        ]);
    }

    public function limited(int $userId): JsonResponse
    {
        $result = $this->roblox->getAllLimitedItems($userId);
        if (! empty($result['error'])) {
            return $this->unavailable($result['error']);
        }

        $items = $result['items'] ?? [];

        return $this->success([
            'total' => $result['total'] ?? count($items),
            'rap_summary' => $this->roblox->calculateTotalRap($items),
            'items' => $items,
        ]);
    }

    public function inventory(int $userId, int $assetTypeId): JsonResponse
    {
        if (! isset(RobloxApiService::ASSET_TYPES[$assetTypeId])) {
            return response()->json(['error' => 'invalid_asset_type'], 404);
        }

        if (! $this->roblox->canViewInventory($userId)) {
            return $this->unavailable('Inventory is private or unavailable.');
        }

        $result = $this->roblox->getInventoryCategory($userId, $assetTypeId, 100);
        if (! empty($result['error'])) {
            return $this->unavailable($result['error']);
        }

        return $this->success([
            'asset_type_id' => $assetTypeId,
            'total' => $result['total'] ?? count($result['items'] ?? []),
            'items' => $result['items'] ?? [],
        ]);
    }

    public function avatar(int $userId): JsonResponse
    {
        $wearing = $this->roblox->getCurrentlyWearing($userId);
        $image = $this->roblox->getAvatarThumbnail($userId, '720x720');

        if (! $wearing && ! $image) {
            return $this->unavailable('Avatar data is unavailable.');
        }

        return $this->success([
            'render_url' => $image,
            'asset_ids' => $wearing['assetIds'] ?? [],
        ]);
    }

    public function value(int $userId): JsonResponse
    {
        $result = $this->roblox->getAllLimitedItems($userId);
        if (! empty($result['error'])) {
            return $this->unavailable($result['error']);
        }

        return $this->success($this->roblox->calculateTotalRap($result['items'] ?? []));
    }

    private function success(array $data): JsonResponse
    {
        return response()->json([
            'source' => 'Roblox Public Web API',
            'fetched_at' => now()->toISOString(),
            'data' => $data,
        ]);
    }

    private function unavailable(string $message): JsonResponse
    {
        return response()->json([
            'error' => 'unavailable',
            'message' => $message,
        ], 503);
    }
}