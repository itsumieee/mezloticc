@extends('layouts.app')
@section('title', 'Public API')

@section('content')
<x-page-header num="API" title="Public API" :meta="'Version 1 · 30 requests per minute per IP'" />

<div class="max-w-4xl space-y-8">
    <section class="rounded-lg border border-black/10 bg-white p-5">
        <h2 class="text-sm font-semibold text-paper">Base URL</h2>
        <code class="mt-3 block overflow-x-auto rounded bg-black/[.035] p-3 text-sm text-paper">{{ url('/api/v1') }}</code>
    </section>

    <section>
        <h2 class="mb-3 text-sm font-semibold text-paper">Endpoints</h2>
        <div class="overflow-hidden rounded-lg border border-black/10 bg-white">
            @foreach([
                ['/user/{username-or-id}', 'Resolve a public Roblox profile'],
                ['/user/{userId}/limited', 'Limited items and RAP summary'],
                ['/user/{userId}/inventory/{assetTypeId}', 'Public inventory by asset type'],
                ['/user/{userId}/avatar', 'Avatar render URL and worn asset IDs'],
                ['/user/{userId}/value', 'Aggregated RAP data'],
            ] as [$path, $description])
                <div class="grid gap-1 border-b border-black/5 p-4 last:border-0 sm:grid-cols-[minmax(15rem,.8fr)_1fr] sm:gap-5">
                    <code class="text-sm font-medium text-paper">GET {{ $path }}</code>
                    <span class="text-sm text-paper/60">{{ $description }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-lg border border-black/10 bg-white p-5">
        <h2 class="text-sm font-semibold text-paper">Example</h2>
        <code class="mt-3 block overflow-x-auto rounded bg-black/[.035] p-3 text-sm text-paper">curl {{ url('/api/v1/user/builderman') }}</code>
        <p class="mt-3 text-xs leading-relaxed text-paper/55">Responses include <code>source</code>, <code>fetched_at</code>, and <code>data</code>. Private or unavailable inventories return HTTP 503; unknown users return HTTP 404.</p>
    </section>
</div>
@endsection