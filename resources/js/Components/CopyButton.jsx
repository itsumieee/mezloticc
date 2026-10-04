import { useState } from 'react';

export default function CopyButton({ value, label }) {
    const [copied, setCopied] = useState(false);

    async function copy() {
        try {
            await navigator.clipboard.writeText(String(value));
            setCopied(true);
            window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: 'Copied to clipboard', kind: 'success' } }));
            window.setTimeout(() => setCopied(false), 1400);
        } catch {
            window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: 'Copy failed. Check clipboard permissions.', kind: 'error' } }));
        }
    }

    return <button className="copy-button" type="button" onClick={copy}><span>{label && `${label} `}{value}</span><b aria-live="polite">{copied ? '✓' : '⎘'}</b></button>;
}

export function ShareButton() {
    async function share() {
        const title = document.title;
        const text = document.querySelector('.profile-feature h2')?.textContent?.trim() || title;
        const url = window.location.href;

        try {
            if (navigator.share) await navigator.share({ title, text, url });
            else await navigator.clipboard.writeText(url);
            window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: navigator.share ? 'Shared profile' : 'Link copied', kind: 'success' } }));
        } catch (error) {
            if (error?.name !== 'AbortError') {
                window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: 'Could not share this link.', kind: 'error' } }));
            }
        }
    }

    return <button className="button-quiet" type="button" onClick={share}>Share profile</button>;
}
