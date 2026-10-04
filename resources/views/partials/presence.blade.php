@php($presence = $presence ?? [])

<div class="presence-panel presence-{{ $presence['status_key'] ?? 'unavailable' }}" aria-label="Account presence">
    <div class="presence-status">
        <span class="presence-dot" aria-hidden="true"></span>
        <span>{{ $presence['status'] ?? 'Unavailable' }}</span>
    </div>

    @if(!empty($presence['game_name']))
        <span class="presence-separator" aria-hidden="true">·</span>
        <span class="presence-playing">Playing
            @if(!empty($presence['game_url']))
                <a href="{{ $presence['game_url'] }}" target="_blank" rel="noopener noreferrer">{{ $presence['game_name'] }} <span aria-hidden="true">↗</span></a>
            @else
                {{ $presence['game_name'] }}
            @endif
        </span>
    @elseif(($presence['status_key'] ?? null) === 'in-game')
        <span class="presence-separator" aria-hidden="true">·</span>
        <span class="presence-playing">Game details private</span>
    @endif

    @if(!empty($presence['last_online']))
        <span class="presence-separator" aria-hidden="true">·</span>
        <span class="presence-last-online">Last online {{ \Carbon\Carbon::parse($presence['last_online'])->format('d.m.Y H:i') }}</span>
    @endif
</div>
