<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Optional data from the unofficial, third-party Rolimons service. */
class RolimonsService
{
    private const BASE_URL = 'https://api.rolimons.com';

    public function getUserAssets(int $userId): ?array
    {
        return Cache::remember("rolimons.assets.{$userId}", now()->addHours(6), function () use ($userId): ?array {
            try {
                $response = Http::timeout(8)
                    ->retry(1, 500)
                    ->acceptJson()
                    ->get(self::BASE_URL."/players/v1/playerassets/{$userId}");

                return $response->successful() ? $response->json() : null;
            } catch (Throwable $exception) {
                Log::notice('rolimons.fetch_failed', [
                    'user_id' => $userId,
                    'exception' => $exception::class,
                ]);

                return null;
            }
        });
    }

    public function getItemValues(array $assetIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', array_slice($assetIds, 0, 250)),
            static fn (int $assetId): bool => $assetId > 0
        )));
        if ($ids === []) {
            return [];
        }

        $cacheKey = 'rolimons.item_values.'.md5(implode(',', $ids));

        return Cache::remember($cacheKey, now()->addHours(3), function () use ($ids): array {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->get(self::BASE_URL.'/item/details', ['ids' => implode(',', $ids)]);

                return $response->successful() ? ($response->json() ?? []) : [];
            } catch (Throwable) {
                return [];
            }
        });
    }
}