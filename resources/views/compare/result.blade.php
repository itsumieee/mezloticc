@extends('layouts.app')
@section('title', 'Comparison Result')

@section('content')
<x-page-header num="CMP" title="Comparison" :meta="'Public data only'">
    <a href="{{ route('compare.form') }}" class="border border-white/15 px-4 py-2 font-mono text-xs uppercase">Compare again</a>
</x-page-header>

@php
    $rows = [
        ['Username', $userA['name'] ?? '—', $userB['name'] ?? '—'],
        ['Display name', $userA['displayName'] ?? '—', $userB['displayName'] ?? '—'],
        ['User ID', $userA['id'], $userB['id']],
        ['Created', $dataA['created'] ? \Carbon\Carbon::parse($dataA['created'])->format('d.m.Y') : '—', $dataB['created'] ? \Carbon\Carbon::parse($dataB['created'])->format('d.m.Y') : '—'],
        ['Limited items', $dataA['limited'], $dataB['limited']],
        ['Total RAP', number_format($dataA['rap']['total_rap']), number_format($dataB['rap']['total_rap'])],
        ['Average RAP', number_format($dataA['rap']['average_rap']), number_format($dataB['rap']['average_rap'])],
        ['Wearing', $dataA['wearing'], $dataB['wearing']],
        ['Bundles', $dataA['bundles'], $dataB['bundles']],
    ];
@endphp

<div class="overflow-x-auto rounded-lg border border-white/10 bg-white">
    <table class="w-full min-w-[600px] border-collapse text-left">
        <thead>
            <tr class="border-b border-black/10 text-xs uppercase tracking-wide text-paper/50">
                <th class="p-4">Metric</th>
                @foreach([[$userA, $dataA], [$userB, $dataB]] as [$user, $data])
                    <th class="p-4">
                        <div class="flex items-center gap-3">
                            @if($data['headshot'])<img src="{{ $data['headshot'] }}" alt="" class="size-10 rounded-full">@endif
                            <span class="text-base font-semibold text-paper">{{ $user['displayName'] ?? $user['name'] ?? 'Roblox user' }}</span>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as [$label, $valueA, $valueB])
                <tr class="border-b border-black/5 last:border-0">
                    <th class="p-4 text-sm font-medium text-paper/60">{{ $label }}</th>
                    <td class="p-4 text-sm text-paper">{{ $valueA }}</td>
                    <td class="p-4 text-sm text-paper">{{ $valueB }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection