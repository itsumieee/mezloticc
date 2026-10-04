import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Notice, PageTitle } from '../../Components/GlassPanel';
import { ExportLinks, Pagination, default as InventoryGrid } from '../../Components/InventoryGrid';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Limited() {
    const { props } = usePage();
    const { userId, items = [], thumbnails = {}, total, error, previousUrl, nextUrl } = props;

    return (
        <AppLayout title="Limited">
            <PageTitle index="04" title="Limited" meta={`Collectibles · ${items.length} / ${total ?? '—'}`} actions={<><ExportLinks userId={userId} /><RefreshButton userId={userId} /></>} />
            <Notice>{error}</Notice>
            <InventoryGrid items={items} thumbnails={thumbnails} label={error ? 'Data unavailable' : 'No collectibles detected'} emptyMessage={error ? 'Roblox could not return this inventory.' : 'No public collectible Limited items were returned by Roblox.'} filterable />
            {items.length === 0 && !error && <p className="muted limited-note">Regular hats, clothing and accessories are not counted as Limited collectibles. You can confirm the public account inventory on Roblox.</p>}
            <Pagination previousUrl={previousUrl} nextUrl={nextUrl} />
            {items.length === 0 && !error && <a className="action-link" href={`https://www.roblox.com/users/${userId}/profile`} target="_blank" rel="noreferrer">Open Roblox profile ↗</a>}
        </AppLayout>
    );
}
