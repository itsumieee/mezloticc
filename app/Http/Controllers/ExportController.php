<?php

namespace App\Http\Controllers;

use App\Services\RobloxApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function inventory(Request $request, int $userId, string $format, RobloxApiService $roblox): JsonResponse|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'json'], true), 404);

        $category = $request->query('category', 'collectibles');
        abort_unless(is_string($category), 422);

        if ($category === 'collectibles') {
            $assetTypeId = null;
        } else {
            $assetTypeId = filter_var($category, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($assetTypeId !== false && isset(RobloxApiService::ASSET_TYPES[$assetTypeId]), 404);

            if (! $roblox->canViewInventory($userId)) {
                return response()->json(['error' => 'Inventory is private or unavailable.'], 403);
            }
        }

        $firstPage = $this->loadPage($roblox, $userId, $assetTypeId, '');
        if (! empty($firstPage['error'])) {
            return response()->json(['error' => $firstPage['error']], 503);
        }

        $profile = $roblox->getUserProfile($userId);
        $username = $profile['name'] ?? "user_{$userId}";
        $filename = sprintf(
            '%s_%s_%s',
            Str::slug($username) ?: "user_{$userId}",
            Str::slug($category) ?: 'inventory',
            now()->format('Ymd-His')
        );

        if ($format === 'json') {
            $export = $this->collectPages($roblox, $userId, $assetTypeId, $firstPage);
            if ($export['error'] !== null) {
                return response()->json(['error' => $export['error']], 503);
            }

            return response()->json([
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'display_name' => $profile['displayName'] ?? null,
                ],
                'category' => $category,
                'total' => $firstPage['total'] ?? count($export['items']),
                'exported_at' => now()->toISOString(),
                'source' => 'Roblox public API',
                'items' => $export['items'],
            ])->header('Content-Disposition', 'attachment; filename="'.$filename.'.json"');
        }

        return response()->streamDownload(function () use ($roblox, $userId, $assetTypeId, $firstPage): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fputcsv($output, ['assetId', 'name', 'rap', 'serialNumber', 'assetType']);
            $this->writeCsvItems($output, $firstPage['items'] ?? []);

            $cursor = $firstPage['next_cursor'] ?? null;
            $seenCursors = [];
            for ($page = 0; $cursor !== null && $cursor !== '' && $page < 1000; $page++) {
                if (isset($seenCursors[$cursor])) {
                    fputcsv($output, ['', 'Export incomplete: Roblox returned a repeated cursor.']);
                    break;
                }

                $seenCursors[$cursor] = true;
                $result = $this->loadPage($roblox, $userId, $assetTypeId, $cursor);
                if (! empty($result['error'])) {
                    fputcsv($output, ['', 'Export incomplete: '.$result['error']]);
                    break;
                }

                $this->writeCsvItems($output, $result['items'] ?? []);
                $cursor = $result['next_cursor'] ?? null;
            }

            if ($cursor !== null && $cursor !== '' && count($seenCursors) >= 1000) {
                fputcsv($output, ['', 'Export incomplete: page limit reached.']);
            }

            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function loadPage(RobloxApiService $roblox, int $userId, ?int $assetTypeId, string $cursor): array
    {
        return $assetTypeId === null
            ? $roblox->getLimitedItemsPaged($userId, 100, $cursor)
            : $roblox->getInventoryCategoryPaged($userId, $assetTypeId, 100, $cursor);
    }

    private function collectPages(RobloxApiService $roblox, int $userId, ?int $assetTypeId, array $firstPage): array
    {
        $items = $firstPage['items'] ?? [];
        $cursor = $firstPage['next_cursor'] ?? null;
        $seenCursors = [];

        for ($page = 0; $cursor !== null && $cursor !== '' && $page < 1000; $page++) {
            if (isset($seenCursors[$cursor])) {
                return ['items' => [], 'error' => 'Roblox returned a repeated page cursor.'];
            }

            $seenCursors[$cursor] = true;
            $result = $this->loadPage($roblox, $userId, $assetTypeId, $cursor);
            if (! empty($result['error'])) {
                return ['items' => [], 'error' => $result['error']];
            }

            $items = [...$items, ...($result['items'] ?? [])];
            $cursor = $result['next_cursor'] ?? null;
        }

        if ($cursor !== null && $cursor !== '') {
            return ['items' => [], 'error' => 'Export exceeded the supported page limit.'];
        }

        return ['items' => $items, 'error' => null];
    }

    private function writeCsvItems($output, array $items): void
    {
        foreach ($items as $item) {
            $assetType = $item['assetType'] ?? '';
            if (is_array($assetType)) {
                $assetType = $assetType['name'] ?? $assetType['id'] ?? '';
            }

            fputcsv($output, [
                $item['assetId'] ?? $item['id'] ?? '',
                $this->safeCsvText((string) ($item['name'] ?? '')),
                $item['recentAveragePrice'] ?? '',
                $item['serialNumber'] ?? '',
                $this->safeCsvText((string) $assetType),
            ]);
        }
    }

    private function safeCsvText(string $value): string
    {
        return preg_match('/^[\t\r ]*[=+@-]/', $value) === 1 ? "'".$value : $value;
    }
}