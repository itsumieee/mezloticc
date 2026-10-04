<?php

namespace App\Console\Commands;

use App\Models\CachedInventory;
use App\Models\CachedItemDetail;
use App\Models\CachedUser;
use App\Models\SearchHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneStaleCache extends Command
{
    protected $signature = 'roblox:prune-cache {--days=7}';

    protected $description = 'Delete cached Roblox data and search history older than N days';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = DB::transaction(fn (): array => [
            'users' => CachedUser::query()->where('cached_at', '<', $cutoff)->delete(),
            'inventories' => CachedInventory::query()->where('cached_at', '<', $cutoff)->delete(),
            'items' => CachedItemDetail::query()->where('cached_at', '<', $cutoff)->delete(),
            'history' => SearchHistory::query()->where('created_at', '<', $cutoff)->delete(),
        ]);

        $this->info(sprintf(
            'Pruned: users=%d inventories=%d items=%d history=%d',
            $deleted['users'],
            $deleted['inventories'],
            $deleted['items'],
            $deleted['history'],
        ));

        return self::SUCCESS;
    }
}