import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { GlassPanel, Notice, PageTitle } from '../../Components/GlassPanel';
import { ProfileCard, RefreshButton, WatchButton } from '../../Components/ProfileCard';
import { ShareButton } from '../../Components/CopyButton';
import { formatDateTime } from '../../formatters';

export default function Overview() {
    const { props } = usePage();
    const {
        userId, profile = {}, headshot, stats = {}, statsErrors = {},
        experiences = [], communities = [], presence = {}, topGames = [],
        recentPresence = [], visible, snapshots = [],
    } = props;

    return (
        <AppLayout title="Overview">
            <PageTitle index="01" title="Overview" meta={`User · ${profile.name ?? 'unknown'} · ${userId}`} actions={<><ShareButton /><WatchButton userId={userId} /><RefreshButton userId={userId} /></>} />
            <ProfileCard profile={profile} userId={userId} image={headshot} presence={presence} experiences={experiences} communities={communities} visible={visible} />

            <div className="section-heading"><span>02</span><h2>Account at a glance</h2><i /><span>PUBLIC DATA</span></div>
            {snapshots.length > 1 && <Trend snapshots={snapshots} />}
            <div className="metrics-grid">
                {[
                    ['01', 'Limited', stats.limited ?? 0],
                    ['02', 'Wearing', stats.wearing ?? 0],
                    ['03', 'Animations', stats.animations ?? '—'],
                    ['04', 'Robux', 'N/A'],
                ].map(([number, label, value]) => (
                    <GlassPanel className="metric-card" key={label}>
                        <span className="eyebrow">{number} / {label}</span><strong>{Number.isFinite(value) ? Number(value).toLocaleString() : value}</strong>
                    </GlassPanel>
                ))}
            </div>

            <Notice>{Object.keys(statsErrors).length > 0 && `Could not count every page for: ${Object.keys(statsErrors).join(', ')}.`}</Notice>
            {!visible && <Notice>Inventory is private. Some data cannot be displayed.</Notice>}

            <section className="activity-section">
                <div className="section-heading"><span>03</span><h2>Presence activity</h2><i /><span>LAST 30 DAYS</span></div>
                <div className="activity-grid">
                    <GlassPanel><h3 className="panel-title">Frequently detected experiences</h3>
                        {topGames.length ? topGames.map((game) => <div className="activity-row" key={`${game.place_id}-${game.game_name}`}><span>{game.game_name}</span><small>{game.detections} detections</small></div>) : <p className="muted">No recent public activity recorded.</p>}
                    </GlassPanel>
                    <GlassPanel><h3 className="panel-title">Recent presence checks</h3>
                        {recentPresence.length ? recentPresence.slice(0, 8).map((entry) => <div className="activity-row" key={entry.id}><span>{entry.place_id ? <a href={`https://www.roblox.com/games/${entry.place_id}`} target="_blank" rel="noreferrer">{entry.game_name || entry.status_key} ↗</a> : entry.game_name || entry.status_key}</span><small>{formatDateTime(entry.observed_at)}</small></div>) : <p className="muted">No recent presence recorded.</p>}
                    </GlassPanel>
                </div>
            </section>
            <div className="inline-links"><Link href={`/dashboard/${userId}/profile`}>Profile details ↗</Link><Link href={`/dashboard/${userId}/inventory`}>Browse inventory ↗</Link></div>
        </AppLayout>
    );
}

function Trend({ snapshots }) {
    const latest = snapshots[snapshots.length - 1];
    const previous = snapshots[snapshots.length - 2];
    const delta = Number(latest.total_rap) - Number(previous.total_rap);

    return (
        <GlassPanel className="trend-card">
            <div className="trend-heading"><h3>Recent change</h3><span>{snapshots.length} daily snapshots</span></div>
            <div className="trend-values">
                <div><small>Total RAP</small><strong>{Number(latest.total_rap).toLocaleString()}</strong><em className={delta >= 0 ? 'positive' : 'negative'}>{delta > 0 ? '+' : ''}{delta.toLocaleString()} vs previous snapshot</em></div>
                <div><small>Limited items</small><strong>{Number(latest.limited_count).toLocaleString()}</strong></div>
                <div><small>Wearing</small><strong>{Number(latest.wearing_count).toLocaleString()}</strong></div>
                <div><small>Bundles</small><strong>{Number(latest.bundles_count).toLocaleString()}</strong></div>
            </div>
        </GlassPanel>
    );
}
