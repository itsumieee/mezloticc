@if(!empty($experiences) || !empty($communities))
    <section class="creator-network" data-reveal="up">
        <div class="editorial-section-heading">
            <span class="section-index">03</span>
            <h2>Creator & communities</h2>
            <span class="section-rule"></span>
            <span class="section-note">PUBLIC ROBLOX DATA</span>
        </div>

        <div class="creator-network-grid">
            <div class="creator-network-column">
                <div class="creator-network-label">Experiences created</div>
                @forelse(array_slice($experiences, 0, 4) as $experience)
                    <a class="creator-network-item" href="https://www.roblox.com/games/{{ $experience['id'] }}" target="_blank" rel="noopener noreferrer">
                        <span>
                            <strong>{{ $experience['name'] }}</strong>
                            <small>{{ number_format($experience['visits']) }} visits</small>
                        </span>
                        <span aria-hidden="true">↗</span>
                    </a>
                @empty
                    <p class="creator-network-empty">No public experiences found.</p>
                @endforelse
            </div>

            <div class="creator-network-column">
                <div class="creator-network-label">Communities</div>
                @forelse(array_slice($communities, 0, 4) as $community)
                    <a class="creator-network-item" href="https://www.roblox.com/communities/{{ $community['id'] }}" target="_blank" rel="noopener noreferrer">
                        <span>
                            <strong>{{ $community['name'] }}</strong>
                            <small>{{ $community['role'] }} · {{ number_format($community['member_count']) }} members</small>
                        </span>
                        <span aria-hidden="true">↗</span>
                    </a>
                @empty
                    <p class="creator-network-empty">No public communities found.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
