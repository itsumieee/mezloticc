<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $status = ['status' => 'ok', 'checks' => []];

        try {
            DB::select('SELECT 1');
            $status['checks']['database'] = 'ok';
        } catch (Throwable) {
            $status['checks']['database'] = 'fail';
            $status['status'] = 'degraded';
        }

        $cacheKey = 'health-check:'.Str::uuid();
        try {
            Cache::put($cacheKey, 'ok', 5);
            $status['checks']['cache'] = Cache::get($cacheKey) === 'ok' ? 'ok' : 'fail';
            if ($status['checks']['cache'] === 'fail') {
                $status['status'] = 'degraded';
            }
        } catch (Throwable) {
            $status['checks']['cache'] = 'fail';
            $status['status'] = 'degraded';
        } finally {
            try {
                Cache::forget($cacheKey);
            } catch (Throwable) {
                // The health response should report the cache failure, not fail itself.
            }
        }

        return response()->json(
            $status,
            $status['status'] === 'ok' ? 200 : 503
        );
    }
}