import { useForm } from '@inertiajs/react';
import MagneticButton from './MagneticButton';

export default function SearchBar({ initialValue = '' }) {
    const form = useForm({ username: initialValue });

    function submit(event) {
        event.preventDefault();
        form.post('/search');
    }

    return (
        <form className="lookup-panel glass-panel" onSubmit={submit}>
            <label htmlFor="username">Roblox username or ID</label>
            <div className="lookup-input-row">
                <span className="lookup-prefix" aria-hidden="true">@</span>
                <input
                    id="username"
                    name="username"
                    value={form.data.username}
                    onChange={(event) => form.setData('username', event.target.value)}
                    placeholder="builderman or 156"
                    required
                    maxLength="50"
                    autoComplete="off"
                    aria-describedby="lookup-hint"
                />
                <MagneticButton type="submit" disabled={form.processing}>
                    {form.processing ? 'Checking…' : 'Inspect profile'} <span aria-hidden="true">↗</span>
                </MagneticButton>
            </div>
            {form.errors.username && <p className="field-error">{form.errors.username}</p>}
            <div className="lookup-hint" id="lookup-hint">
                <span>Username or numeric user ID</span><span>Enter · Ctrl K</span>
            </div>
        </form>
    );
}
