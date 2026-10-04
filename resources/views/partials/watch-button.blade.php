@php
    $isWatched = \App\Models\Watchlist::query()->where('roblox_user_id', $userId)->exists();
@endphp

@if($isWatched)
    <form method="POST" action="{{ route('watchlist.destroy', $userId) }}" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="min-h-11 rounded-md border border-acid px-4 text-sm font-medium text-acid hover:bg-acid hover:text-white">
            Watching
        </button>
    </form>
@else
    <form method="POST" action="{{ route('watchlist.store') }}" class="inline">
        @csrf
        <input type="hidden" name="user_id" value="{{ $userId }}">
        <button type="submit" class="min-h-11 rounded-md border border-black/15 bg-white px-4 text-sm font-medium text-paper hover:border-acid">
            Watch
        </button>
    </form>
@endif