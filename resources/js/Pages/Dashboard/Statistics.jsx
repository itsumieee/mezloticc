import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { GlassPanel, Notice, PageTitle } from '../../Components/GlassPanel';
import DataChart from '../../Components/DataCharts';
import { RefreshButton } from '../../Components/ProfileCard';

export default function Statistics() {
    const { props } = usePage();
    const { userId, stats = {}, statsErrors = {}, chartLabels = [], chartValues = [] } = props;
    const metrics = [
        ['Limited', stats.limited], ['Wearing', stats.wearing], ['Animations', stats.animations], ['Bundles', stats.bundles],
        ['Accessories', stats.accessories], ['Faces', stats.faces], ['Shirts', stats.shirts], ['Pants', stats.pants],
    ];

    return (
        <AppLayout title="Statistics">
            <PageTitle index="08" title="Statistics" meta="Inventory distribution" actions={<RefreshButton userId={userId} />} />
            {Object.keys(statsErrors).length > 0 && <Notice kind="warning">Some Roblox categories could not be read: {Object.keys(statsErrors).join(', ')}. A zero may mean unavailable data rather than an empty category.</Notice>}
            <div className="metrics-grid statistics-grid">{metrics.map(([label, value], index) => <GlassPanel className="metric-card" key={label}><span className="eyebrow">{String(index + 1).padStart(2, '0')} / {label}</span><strong>{Number(value ?? 0).toLocaleString()}</strong></GlassPanel>)}</div>
            <div className="chart-grid">
                <GlassPanel><div className="section-heading"><span>01</span><h2>Composition</h2><i /><span>DOUGHNUT</span></div><DataChart type="doughnut" labels={chartLabels} values={chartValues} /></GlassPanel>
                <GlassPanel><div className="section-heading"><span>02</span><h2>Volume</h2><i /><span>BAR</span></div><DataChart labels={chartLabels} values={chartValues} horizontal /></GlassPanel>
            </div>
        </AppLayout>
    );
}
