import { Link, router, usePage } from '@inertiajs/react';
import { GlassPanel, ActionLink } from './GlassPanel';
import { formatDateTime } from '../formatters';

export function Presence({ presence = {} }) {
    const label = presence.status ?? 'Unavailable';

    return (
        <div className={`presence-line presence-${presence.status_key ?? 'unavailable'}`} aria-label="Account presence">
            <span className="status-dot" />{label}
            {presence.game_name && <span>· Playing {presence.game_url
                ? <a href={presence.game_url} target="_blank" rel="noreferrer">{presence.game_name} ↗</a>
                : presence.game_name}</span>}
            {presence.status_key === 'in-game' && !presence.game_name && <span>· Game details private</span>}
            {presence.last_online && <span>· Last online {formatDateTime(presence.last_online)}</span>}
        </div>
    );
}

export function CreatorNetwork({ experiences = [], communities = [] }) {
    if (!experiences.length && !communities.length) return null;

    const columns = [
        ['Experiences created', experiences, (item) => `https://www.roblox.com/games/${item.id}`, (item) => `${Number(item.visits || 0).toLocaleString()} visits`],
        ['Communities', communities, (item) => `https://www.roblox.com/communities/${item.id}`, (item) => `${item.role} · ${Number(item.member_count || 0).toLocaleString()} members`],
    ];

    return (
        <section className="creator-network">
            <div className="section-heading"><span>03</span><h2>Creator & communities</h2><i />PUBLIC ROBLOX DATA</div>
            <div className="creator-grid">
                {columns.map(([label, items, url, detail]) => (
                    <div key={label} className="creator-column">
                        <h3>{label}</h3>
                        {items.slice(0, 4).map((item) => (
                            <a key={item.id} href={url(item)} target="_blank" rel="noreferrer">
                                <span><strong>{item.name}</strong><small>{detail(item)}</small></span><b>↗</b>
                            </a>
                        ))}
                        {items.length === 0 && <p className="muted">No public {label.toLowerCase()} found.</p>}
                    </div>
                ))}
            </div>
        </section>
    );
}

export function ProfileCard({ profile = {}, userId, image, presence, experiences, communities, visible }) {
    return (
        <GlassPanel className="profile-feature">
            <div className="profile-portrait">
                <span className="eyebrow">PUBLIC PROFILE / {userId}</span>
                {image && <img src={image} alt={`${profile.displayName ?? profile.name ?? 'Roblox'} avatar`} />}
                <span className="portrait-id">PROFILE INDEX<br />{userId}</span>
            </div>
            <div className="profile-content">
                <div>
                    <p className="account-state"><span className="status-dot" />{profile.isBanned ? 'Banned account' : 'Public account'}</p>
                    <h2>{profile.displayName ?? 'Unknown'} {profile.hasVerifiedBadge && <span className="verified" aria-label="Verified Roblox account">✓</span>}</h2>
                    <p className="username">@{profile.name ?? 'unknown'}</p>
                    <Presence presence={presence} />
                    <CreatorNetwork experiences={experiences} communities={communities} />
                </div>
                <dl className="profile-metadata">
                    <div><dt>User ID</dt><dd>{userId}</dd></div>
                    <div><dt>Joined Roblox</dt><dd>{formatDateTime(profile.created, '')}</dd></div>
                    <div><dt>Inventory</dt><dd className={visible ? 'accent' : 'muted'}>{visible ? 'Public' : 'Private'}</dd></div>
                </dl>
                {profile.description && <div className="profile-description"><span className="eyebrow">Description</span><p>{profile.description}</p></div>}
            </div>
        </GlassPanel>
    );
}

export function WatchButton({ userId }) {
    const { props } = usePage();
    const watched = Boolean(props.isWatched);

    function submit(event) {
        event.preventDefault();
        if (watched) {
            router.delete(`/watchlist/${userId}`);
            return;
        }

        router.post('/watchlist', { user_id: userId });
    }

    return <button className={`button-quiet ${watched ? 'is-watching' : ''}`} onClick={submit}>{watched ? 'Watching' : 'Watch account'}</button>;
}

export function RefreshButton({ userId }) {
    return <button className="button-quiet" onClick={() => router.post(`/dashboard/${userId}/refresh`)}>Refresh data</button>;
}

export function QuickLinks({ userId }) {
    return <div className="quick-links"><Link href={`/dashboard/${userId}/profile`}>Full profile ↗</Link><ActionLink href={`https://www.roblox.com/users/${userId}/profile`}>Open Roblox</ActionLink></div>;
}
