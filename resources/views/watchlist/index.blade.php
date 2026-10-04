@extends('layouts.app')
@section('title', 'Watchlist')

@section('content')
<x-page-header num="WL" title="Watchlist" :meta="$items->total() . ' tracked accounts'" />

@if(session('status'))<p role="status" class="mb-5 text-sm text-acid">{{ session('status') }}</p>@endif
@if(session('error'))<div class="mb-5"><x-error-state label="Watchlist" :message="session('error')" /></div>@endif

<form method="POST" action="{{ route('watchlist.store') }}" class="mb-8 grid gap-3 rounded-lg border border-white/10 bg-white p-4 md:grid-cols-[1fr_1fr_auto]">
    @csrf
    <input type="number" min="1" name="user_id" value="{{ old('user_id') }}" required placeholder="Roblox user ID"
           class="min-h-11 rounded-md border border-black/10 bg-white px-3 text-sm text-paper outline-none focus:border-acid">
    <input type="text" maxlength="200" name="note" value="{{ old('note') }}" placeholder="Note (optional)"
           class="min-h-11 rounded-md border border-black/10 bg-white px-3 text-sm text-paper outline-none focus:border-acid">
    <button type="submit" class="min-h-11 rounded-md bg-acid px-5 text-sm font-semibold text-white">Add account</button>
</form>

@if($items->isEmpty())
    <x-empty-state label="WL / Empty" message="No accounts tracked yet." />
@else
    <div class="divide-y divide-black/10 rounded-lg border border-black/10 bg-white">
        @foreach($items as $item)
            <div class="flex flex-wrap items-center justify-between gap-4 p-4 md:p-5">
                <div class="min-w-0">
                    <div class="truncate font-semibold text-paper">{{ $item->display_name ?: $item->username }}</div>
                    <div class="mt-1 text-xs text-paper/50">@{{ $item->username }} · ID {{ $item->roblox_user_id }}
                        @if($item->note) · {{ $item->note }} @endif
                    </div>
                    @php($latestPresence = $presence[$item->roblox_user_id] ?? null)
                    @if($latestPresence)
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs" aria-label="Latest tracked presence">
                            <span class="inline-flex items-center gap-1.5 {{ $latestPresence->status_key === 'online' ? 'text-green-600' : ($latestPresence->status_key === 'in-game' ? 'text-blue-600' : 'text-paper/50') }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $latestPresence->status_key === 'in-game' ? 'In game' : ucfirst($latestPresence->status_key) }}
                            </span>
                            @if($latestPresence->game_name)
                                <span class="text-paper/50">· {{ $latestPresence->game_name }}</span>
                            @endif
                            <span class="text-paper/40">· {{ $latestPresence->observed_at->diffForHumans() }}</span>
                        </div>
                        @if(isset($alerts[$item->roblox_user_id]))
                            <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-blue-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                Status changed recently
                            </div>
                        @endif
                    @else
                        <div class="mt-2 text-xs text-paper/40">Waiting for first presence check</div>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.overview', $item->roblox_user_id) }}" class="text-sm font-medium text-paper/70 hover:text-acid">View account</a>
                    <form method="POST" action="{{ route('watchlist.destroy', $item->roblox_user_id) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="min-h-10 px-3 text-sm text-paper/50 hover:text-signal">Remove</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $items->links() }}</div>
@endif
@endsection