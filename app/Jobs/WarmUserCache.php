<?php

namespace App\Jobs;

use App\Services\RobloxApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class WarmUserCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const INVENTORY_ASSET_TYPES = [8, 11, 12, 17, 18, 24, 41, 42, 43, 44, 45, 46, 47, 61];

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(public int $userId) {}

    public function handle(RobloxApiService $roblox): void
    {
        try {
            $roblox->getUserProfile($this->userId);
            $roblox->getAvatarThumbnail($this->userId);
            $roblox->getAvatarHeadshot($this->userId);
            $roblox->getCurrentlyWearing($this->userId);
            $roblox->getLimitedItems($this->userId, 100);
            $roblox->getBundles($this->userId);

            foreach (self::INVENTORY_ASSET_TYPES as $assetTypeId) {
                $roblox->getInventoryCategory($this->userId, $assetTypeId, 100);
                usleep(200_000);
            }

            Log::info('warm_user_cache.completed', ['user_id' => $this->userId]);
        } catch (Throwable $exception) {
            Log::error('warm_user_cache.failed', [
                'user_id' => $this->userId,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }
}