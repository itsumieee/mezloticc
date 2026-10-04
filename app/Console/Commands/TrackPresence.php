<?php

namespace App\Console\Commands;

use App\Models\PresenceSnapshot;
use App\Models\SearchHistory;
use App\Models\Watchlist;
use App\Services\RobloxApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrackPresence extends Command
{
    protected $signature = 'roblox:track-presence';

    protected $description = 'Record online presence and currently played games for watched users';

    public function handle(RobloxApiService $roblox): int
    {
        $userIds = Watchlist::query()
            ->pluck('roblox_user_id')
            ->merge(
                SearchHistory::query()
                    ->whereNotNull('resolved_user_id')
                    ->distinct()
                    ->pluck('resolved_user_id')
            )
            ->map(static fn ($userId): int => (int) $userId)
            ->filter(static fn (int $userId): bool => $userId > 0)
            ->unique()
            ->values();

        $this->info("Tracking presence for {$userIds->count()} accounts...");

        foreach ($userIds as $userId) {
            $userId = (int) $userId;

            try {
                $presence = $roblox->getUserPresence($userId);
                PresenceSnapshot::create([
                    'roblox_user_id' => $userId,
                    'status_key' => $presence['status_key'] ?? 'unavailable',
                    'game_name' => $presence['game_name'] ?? null,
                    'place_id' => $presence['place_id'] ?? null,
                    'universe_id' => $presence['universe_id'] ?? null,
                    'observed_at' => now(),
                ]);

                Watchlist::query()
                    ->where('roblox_user_id', $userId)
                    ->update(['last_checked_at' => now()]);
            } catch (Throwable $exception) {
                Log::warning('presence_snapshot.failed', [
                    'user_id' => $userId,
                    'exception' => $exception::class,
                ]);
                $this->warn("Presence tracking failed for user {$userId}.");
            }
        }

        PresenceSnapshot::query()
            ->where('observed_at', '<', Carbon::now()->subDays(30))
            ->delete();

        $this->info('Presence tracking complete.');

        return self::SUCCESS;
    }
}
