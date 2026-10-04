import { router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Notice, PageTitle } from '../../Components/GlassPanel';
import { ExportLinks, Pagination, default as InventoryGrid } from '../../Components/InventoryGrid';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Inventory() {
    const { props } = usePage();
    const { userId, items = [], thumbnails = {}, error, total, assetTypes = {}, assetTypeId, previousUrl, nextUrl } = props;

    return (
        <AppLayout title="Inventory">
            <PageTitle index="03" title="Inventory" meta="Public Roblox items" actions={<><ExportLinks userId={userId} category={assetTypeId} /><RefreshButton userId={userId} /></>} />
            <div className="inventory-toolbar">
                <p>Showing <strong>{items.length}</strong> of <strong>{total ?? '—'}</strong> items</p>
                <label>Category <select value={assetTypeId} onChange={(event) => router.get(`/dashboard/${userId}/inventory`, { type: event.target.value })}>
                    {Object.entries(assetTypes).map(([id, name]) => <option value={id} key={id}>{name}</option>)}
                </select></label>
            </div>
            <Notice>{error}</Notice>
            <InventoryGrid items={items} thumbnails={thumbnails} label="Inventory" emptyMessage="No items found or inventory is private." />
            <Pagination previousUrl={previousUrl} nextUrl={nextUrl} />
        </AppLayout>
    );
}
