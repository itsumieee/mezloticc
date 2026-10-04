import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { EmptyState, Notice, PageTitle } from '../../Components/GlassPanel';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Animations() {
    const { props } = usePage();
    const { userId, items = [], total, error } = props;

    return (
        <AppLayout title="Animations">
            <PageTitle index="05" title="Animations" meta={`Emotes · ${items.length} / ${total ?? '—'}`} actions={<RefreshButton userId={userId} />} />
            <Notice>{error}</Notice>
            {items.length === 0 ? <EmptyState label="Animations">{error ? 'Data unavailable.' : 'No public animations or emotes found.'}</EmptyState> : (
                <div className="item-list glass-panel">
                    {items.map((item, index) => {
                        const id = item.assetId ?? item.id;
                        return <a key={`${id}-${index}`} href={`https://www.roblox.com/catalog/${id}`} target="_blank" rel="noreferrer"><span className="list-index">{String(index + 1).padStart(3, '0')}</span><span><strong>{item.name ?? 'Unknown'}</strong><small>#{id ?? '—'}</small></span><b>↗</b></a>;
                    })}
                </div>
            )}
        </AppLayout>
    );
}
