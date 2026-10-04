<?php

namespace App\Http\Controllers;

use App\Services\RobloxApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    public function form(): View
    {
        return view('compare.form');
    }

    public function compare(Request $request, RobloxApiService $roblox): View|RedirectResponse
    {
        $validated = $request->validate([
            'a' => ['required', 'string', 'max:50'],
            'b' => ['required', 'string', 'max:50'],
        ]);

        $userA = $this->resolve($roblox, trim($validated['a']));
        $userB = $this->resolve($roblox, trim($validated['b']));

        if (! $userA || ! $userB) {
            return back()->withInput()->with('error', 'One or both Roblox accounts were not found.');
        }

        $userA = $roblox->getUserProfile((int) $userA['id']) ?? $userA;
        $userB = $roblox->getUserProfile((int) $userB['id']) ?? $userB;
        $dataA = $this->gather($roblox, (int) $userA['id']);
        $dataB = $this->gather($roblox, (int) $userB['id']);

        return view('compare.result', compact('userA', 'userB', 'dataA', 'dataB'));
    }

    private function resolve(RobloxApiService $roblox, string $identifier): ?array
    {
        if (ctype_digit($identifier)) {
            $userId = filter_var($identifier, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($userId !== false) {
                return $roblox->getUserById($userId);
            }
        }

        return $roblox->getUserByUsername($identifier);
    }

    private function gather(RobloxApiService $roblox, int $userId): array
    {
        $limited = $roblox->getAllLimitedItems($userId);
        $wearing = $roblox->getCurrentlyWearing($userId);
        $bundles = $roblox->getBundles($userId);
        $items = $limited['items'] ?? [];

        return [
            'limited' => $limited['total'] ?? count($items),
            'rap' => $roblox->calculateTotalRap($items),
            'headshot' => $roblox->getAvatarHeadshot($userId),
            'wearing' => count($wearing['assetIds'] ?? []),
            'bundles' => $bundles['total'] ?? count($bundles['bundles'] ?? []),
            'created' => $roblox->getUserProfile($userId)['created'] ?? null,
        ];
    }
}