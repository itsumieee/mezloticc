<?php

namespace App\Console\Commands;

use App\Models\AccountSnapshot;
use App\Models\Watchlist;
use App\Services\RobloxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SnapshotUsers extends Command
{
    protected $signature = 'roblox:snapshot-watchlist';

    protected $description = 'Take daily snapshots of watched public accounts';

    public function handle(RobloxApiService $roblox): int
    {
        $users = Watchlist::query()->lazyById(100);
        $count = Watchlist::query()->count();
        $this->info("Snapshotting {$count} watched accounts...");

        foreach ($users as $watchlistItem) {
            $userId = (int) $watchlistItem->roblox_user_id;

            try {
                $limited = $roblox->getAllLimitedItems($userId);
                if (! empty($limited['error'])) {
                    Log::warning('account_snapshot.skipped', [
                        'user_id' => $userId,
                        'reason' => $limited['error'],
                    ]);
                    continue;
                }

                $items = $limited['items'] ?? [];
                $rap = $roblox->calculateTotalRap($items);
                $wearing = $roblox->getCurrentlyWearing($userId);
                $bundles = $roblox->getBundles($userId);
                if (! empty($bundles['error'])) {
                    Log::warning('account_snapshot.skipped', [
                        'user_id' => $userId,
                        'reason' => $bundles['error'],
                    ]);
                    continue;
                }

                AccountSnapshot::query()->updateOrCreate(
                    [
                        'roblox_user_id' => $userId,
                        'snapshot_date' => today()->toDateString(),
                    ],
                    [
                        'limited_count' => $limited['total'] ?? count($items),
                        'total_rap' => $rap['total_rap'] ?? 0,
                        'average_rap' => $rap['average_rap'] ?? 0,
                        'wearing_count' => count($wearing['assetIds'] ?? []),
                        'bundles_count' => $bundles['total'] ?? count($bundles['bundles'] ?? []),
                    ]
                );

                $watchlistItem->update(['last_checked_at' => now()]);
                usleep(300_000);
            } catch (Throwable $exception) {
                Log::warning('account_snapshot.failed', [
                    'user_id' => $userId,
                    'exception' => $exception::class,
                ]);
                $this->warn("Snapshot failed for user {$userId}.");
            }
        }

        $this->info('Snapshot run complete.');

        return self::SUCCESS;
    }
}