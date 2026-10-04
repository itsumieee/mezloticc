import AppLayout from '../Layouts/AppLayout';
import { GlassPanel, PageTitle } from '../Components/GlassPanel';

const endpoints = [
    ['/user/{username-or-id}', 'Resolve a public Roblox profile'],
    ['/user/{userId}/limited', 'Limited items and RAP summary'],
    ['/user/{userId}/inventory/{assetTypeId}', 'Public inventory by asset type'],
    ['/user/{userId}/avatar', 'Avatar render URL and worn asset IDs'],
    ['/user/{userId}/value', 'Aggregated RAP data'],
];

export default function ApiDocs() {
    const baseUrl = `${window.location.origin}/api/v1`;

    return (
        <AppLayout title="Public API">
            <PageTitle index="API" title="Public API" meta="Version 1 · 30 requests per minute per IP" />
            <div className="api-docs">
                <GlassPanel><h2>Base URL</h2><code>{baseUrl}</code></GlassPanel>
                <section><div className="section-heading"><span>01</span><h2>Endpoints</h2><i /></div>
                    <GlassPanel className="endpoint-list">{endpoints.map(([path, description]) => <div key={path}><code>GET {path}</code><span>{description}</span></div>)}</GlassPanel>
                </section>
                <GlassPanel><h2>Example</h2><code>curl {baseUrl}/user/builderman</code><p>Responses include <code>source</code>, <code>fetched_at</code>, and <code>data</code>. Private or unavailable inventories return HTTP 503; unknown users return HTTP 404.</p></GlassPanel>
            </div>
        </AppLayout>
    );
}
