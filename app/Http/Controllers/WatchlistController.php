<?php

namespace App\Http\Controllers;

use App\Models\PresenceSnapshot;
use App\Models\Watchlist;
use App\Services\RobloxApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(): View
    {
        $items = Watchlist::query()->orderByDesc('last_checked_at')->paginate(25);
        $userIds = $items->getCollection()->pluck('roblox_user_id');
        $presence = PresenceSnapshot::query()
            ->whereIn('roblox_user_id', $userIds)
            ->latest('observed_at')
            ->get()
            ->unique('roblox_user_id')
            ->keyBy('roblox_user_id');
        $recentPresenceRows = PresenceSnapshot::query()
            ->whereIn('roblox_user_id', $userIds)
            ->where('observed_at', '>=', now()->subMinutes(20))
            ->latest('observed_at')
            ->get();
        $alerts = [];

        foreach ($recentPresenceRows->groupBy('roblox_user_id') as $userId => $snapshots) {
            $latest = $snapshots->first();
            $previous = $snapshots->get(1);

            if ($previous && ($latest->status_key !== $previous->status_key || $latest->game_name !== $previous->game_name)) {
                $alerts[$userId] = $latest;
            }
        }

        return view('watchlist.index', [
            'items' => $items,
            'presence' => $presence,
            'alerts' => $alerts,
        ]);
    }

    public function store(Request $request, RobloxApiService $roblox): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $userId = (int) $validated['user_id'];
        $profile = $roblox->getUserById($userId);
        if (! $profile) {
            return back()->with('error', 'Cannot add this account: Roblox user not found.');
        }

        Watchlist::query()->updateOrCreate(
            ['roblox_user_id' => $userId],
            [
                'username' => $profile['name'] ?? (string) $userId,
                'display_name' => $profile['displayName'] ?? null,
                'note' => $validated['note'] ?? null,
                'last_checked_at' => now(),
            ]
        );

        return back()->with('status', 'Account added to watchlist.');
    }

    public function destroy(int $userId): RedirectResponse
    {
        Watchlist::query()->where('roblox_user_id', $userId)->delete();

        return back()->with('status', 'Account removed from watchlist.');
    }
}