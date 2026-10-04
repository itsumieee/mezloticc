@if($topGames->isNotEmpty() || $recentPresence->isNotEmpty())
    <section class="presence-activity" data-reveal="up">
        <div class="editorial-section-heading">
            <span class="section-index">04</span>
            <h2>Activity tracking</h2>
            <span class="section-rule"></span>
            <span class="section-note">LAST 30 DAYS</span>
        </div>

        <div class="presence-activity-grid">
            <div>
                <div class="creator-network-label">Top games detected</div>
                @forelse($topGames as $index => $game)
                    <div class="tracked-game">
                        <span class="tracked-game-rank">0{{ $index + 1 }}</span>
                        <span class="tracked-game-name">
                            @if($game->place_id)
                                <a href="https://www.roblox.com/games/{{ $game->place_id }}" target="_blank" rel="noopener noreferrer">{{ $game->game_name }} <span aria-hidden="true">↗</span></a>
                            @else
                                {{ $game->game_name }}
                            @endif
                            <small>{{ $game->detections }} detections</small>
                        </span>
                    </div>
                @empty
                    <p class="creator-network-empty">No game sessions detected yet.</p>
                @endforelse
            </div>

            <div>
                <div class="creator-network-label">Recent checks</div>
                @forelse($recentPresence->take(5) as $snapshot)
                    <div class="presence-history-row">
                        <span class="presence-history-dot presence-history-{{ $snapshot->status_key }}"></span>
                        <span>{{ $snapshot->status_key === 'in-game' ? ($snapshot->game_name ?? 'In game') : ucfirst($snapshot->status_key) }}</span>
                        <time datetime="{{ $snapshot->observed_at->toIso8601String() }}">{{ $snapshot->observed_at->format('d.m H:i') }}</time>
                    </div>
                @empty
                    <p class="creator-network-empty">No presence checks recorded yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
