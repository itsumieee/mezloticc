<?php

namespace App\Http\Middleware;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $userId = $request->route('userId');

        return [
            ...parent::share($request),
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'isWatched' => fn () => $userId
                ? Watchlist::query()->where('roblox_user_id', $userId)->exists()
                : false,
        ];
    }
}
