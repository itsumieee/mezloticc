import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { EmptyState, Notice, PageTitle } from '../../Components/GlassPanel';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Bundles() {
    const { props } = usePage();
    const { userId, bundles = [], total, error } = props;

    return (
        <AppLayout title="Bundles">
            <PageTitle index="07" title="Bundles" meta={`Outfits · ${total ?? '—'} items`} actions={<RefreshButton userId={userId} />} />
            <Notice>{error}</Notice>
            {bundles.length === 0 ? <EmptyState label="Bundles">{error || 'Data unavailable.'}</EmptyState> : (
                <div className="bundle-grid">{bundles.map((bundle, index) => <a key={bundle.id ?? index} href={`https://www.roblox.com/bundles/${bundle.id ?? ''}`} target="_blank" rel="noreferrer" className="bundle-card glass-panel"><span className="eyebrow">{String(index + 1).padStart(3, '0')}</span><h2>{bundle.name ?? 'Unknown Bundle'}</h2><small>Bundle · {bundle.id ?? '—'}</small><i /></a>)}</div>
            )}
        </AppLayout>
    );
}
