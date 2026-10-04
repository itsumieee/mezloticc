import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { GlassPanel } from '../../Components/GlassPanel';

export default function ErrorPage() {
    const { props } = usePage();
    const status = Number(props.status ?? 500);
    const descriptions = {
        404: 'The requested profile or page could not be found.',
        429: `You're searching too fast. Please wait ${props.seconds ?? 60} seconds.`,
        500: 'The request could not be completed right now.',
    };

    return (
        <AppLayout title={`${status} · Request unavailable`}>
            <div className="error-page"><GlassPanel><span className="eyebrow">REQUEST / {status}</span><h1>{status}</h1><p>{props.message || descriptions[status] || 'An unexpected error occurred.'}</p><Link className="button-primary" href="/">Back to game index</Link></GlassPanel></div>
        </AppLayout>
    );
}
