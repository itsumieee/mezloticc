<?php

namespace App\Http\Controllers;

use App\Models\OgImage;
use App\Services\RobloxApiService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OgImageController extends Controller
{
    public function __invoke(int $userId, RobloxApiService $roblox): Response
    {
        $cached = OgImage::query()->where('roblox_user_id', $userId)->first();
        $disk = Storage::disk('public');

        if ($cached?->generated_at?->isAfter(now()->subDay()) && $disk->exists($cached->path)) {
            return response($disk->get($cached->path), 200, $this->headers());
        }

        $profile = $roblox->getUserProfile($userId);
        if (! $profile) {
            abort(404, 'Roblox user not found or unavailable.');
        }

        $avatarUrl = $roblox->getAvatarThumbnail($userId, '420x420');
        $limited = $roblox->getLimitedItems($userId, 100);
        $svg = $this->buildSvg($userId, $profile, $avatarUrl, (int) ($limited['total'] ?? 0));
        $path = "og/{$userId}.svg";

        if ($disk->put($path, $svg)) {
            OgImage::query()->updateOrCreate(
                ['roblox_user_id' => $userId],
                ['path' => $path, 'generated_at' => now()]
            );
        }

        return response($svg, 200, $this->headers());
    }

    private function buildSvg(int $userId, array $profile, ?string $avatarUrl, int $limitedCount): string
    {
        $displayName = $this->escapeXml((string) ($profile['displayName'] ?? 'Unknown'));
        $username = $this->escapeXml((string) ($profile['name'] ?? 'unknown'));
        $safeAvatar = $this->safeAvatarUrl($avatarUrl);
        $avatar = $safeAvatar === null
            ? ''
            : '<image href="'.$this->escapeXml($safeAvatar).'" x="790" y="135" width="340" height="340" preserveAspectRatio="xMidYMid meet" />';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-label="Roblox profile for {$displayName}">
  <rect width="1200" height="630" fill="#ffffff" />
  <rect x="24" y="24" width="1152" height="582" fill="none" stroke="#d8dadd" />
  <text x="64" y="88" fill="#555555" font-family="Arial, sans-serif" font-size="16" letter-spacing="3">ROBLOX ACCOUNT CHECKER</text>
  <text x="64" y="220" fill="#20242a" font-family="Arial, sans-serif" font-size="64" font-weight="700">{$displayName}</text>
  <text x="66" y="274" fill="#68717d" font-family="Arial, sans-serif" font-size="25">@{$username}</text>
  <text x="66" y="322" fill="#68717d" font-family="Arial, sans-serif" font-size="18">USER ID · {$userId}</text>
  <line x1="64" y1="390" x2="690" y2="390" stroke="#e1e4e8" />
  <text x="64" y="446" fill="#68717d" font-family="Arial, sans-serif" font-size="17">PUBLIC LIMITED ITEMS</text>
  <text x="64" y="520" fill="#20242a" font-family="Arial, sans-serif" font-size="54" font-weight="600">{$limitedCount}</text>
  {$avatar}
</svg>
SVG;
    }

    private function safeAvatarUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $isRobloxCdn = $host === 'rbxcdn.com' || str_ends_with($host, '.rbxcdn.com');
        $isRobloxDomain = $host === 'roblox.com' || str_ends_with($host, '.roblox.com');

        return ($parts['scheme'] ?? null) === 'https' && ($isRobloxCdn || $isRobloxDomain)
            ? $url
            : null;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function headers(): array
    {
        return [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}