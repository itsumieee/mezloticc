import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { GlassPanel, PageTitle } from '../../Components/GlassPanel';
import { CreatorNetwork, Presence, RefreshButton } from '../../Components/ProfileCard';
import { formatDateTime } from '../../formatters';

export default function Profile() {
    const { props } = usePage();
    const { userId, profile = {}, avatarUrl, presence, experiences, communities } = props;

    return (
        <AppLayout title="Profile">
            <PageTitle index="02" title="Profile" meta={`@${profile.name ?? '—'} · ${userId}`} actions={<RefreshButton userId={userId} />} />
            <GlassPanel className="profile-details-card">
                <div className="detail-avatar">{avatarUrl ? <img src={avatarUrl} alt={`${profile.displayName ?? profile.name} avatar`} /> : <p>Avatar render unavailable.</p>}</div>
                <div className="detail-content">
                    <span className="eyebrow">Display name</span>
                    <h2>{profile.displayName ?? '—'} {profile.hasVerifiedBadge && <span className="verified" aria-label="Verified Roblox account">✓</span>}</h2>
                    <Presence presence={presence} />
                    <dl className="detail-metadata">
                        <div><dt>Username</dt><dd>@{profile.name ?? '—'}</dd></div>
                        <div><dt>User ID</dt><dd>{userId}</dd></div>
                        <div><dt>Created</dt><dd>{formatDateTime(profile.created, '')}</dd></div>
                        <div><dt>Status</dt><dd className={profile.isBanned ? 'negative' : 'positive'}>{profile.isBanned ? 'Banned' : 'Active'}</dd></div>
                    </dl>
                    {profile.description && <div className="profile-description"><span className="eyebrow">Description</span><p>{profile.description}</p></div>}
                    <CreatorNetwork experiences={experiences} communities={communities} />
                </div>
            </GlassPanel>
        </AppLayout>
    );
}
