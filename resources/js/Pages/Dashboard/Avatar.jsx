import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { EmptyState, GlassPanel, PageTitle } from '../../Components/GlassPanel';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Avatar() {
    const { props } = usePage();
    const { userId, avatarUrl, wearing } = props;
    const assetIds = wearing?.assetIds ?? [];

    return (
        <AppLayout title="Avatar">
            <PageTitle index="06" title="Avatar" meta={`Currently wearing · ${assetIds.length} items`} actions={<RefreshButton userId={userId} />} />
            <GlassPanel className="avatar-stage">{avatarUrl ? <img src={avatarUrl} alt={`Avatar for Roblox user ${userId}`} /> : <EmptyState label="Avatar">Avatar rendering unavailable.</EmptyState>}</GlassPanel>
            {assetIds.length > 0 && (
                <section className="equipment-section">
                    <div className="section-heading"><span>EQUIPMENT</span><i /><span>{assetIds.length} ITEMS</span></div>
                    <div className="equipment-grid">{assetIds.map((id, index) => <a key={id} href={`https://www.roblox.com/catalog/${id}`} target="_blank" rel="noreferrer"><small>{String(index + 1).padStart(2, '0')}</small><span>#{id}</span></a>)}</div>
                </section>
            )}
        </AppLayout>
    );
}
