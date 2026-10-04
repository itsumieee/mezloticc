import { useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { GlassPanel, Notice, PageTitle } from '../../Components/GlassPanel';

export default function CompareForm() {
    const { props } = usePage();
    const form = useForm({ a: '', b: '' });
    const errors = { ...form.errors, ...props.errors };

    function submit(event) {
        event.preventDefault();
        form.post('/compare');
    }

    return (
        <AppLayout title="Compare accounts">
            <PageTitle index="CMP" title="Compare" meta="Side-by-side public account data" />
            <Notice>{props.flash?.error}</Notice>
            <form className="compare-form" onSubmit={submit}>
                <div className="compare-fields">
                    {['a', 'b'].map((key, index) => (
                        <GlassPanel className="compare-field" key={key}>
                            <label htmlFor={`account-${key}`}>Account {index === 0 ? 'A' : 'B'}</label>
                            <input id={`account-${key}`} value={form.data[key]} onChange={(event) => form.setData(key, event.target.value)} placeholder="Username or user ID" required maxLength="50" />
                            {errors[key] && <span className="field-error">{errors[key]}</span>}
                        </GlassPanel>
                    ))}
                </div>
                <button className="button-primary" type="submit" disabled={form.processing}>{form.processing ? 'Comparing…' : 'Compare accounts →'}</button>
            </form>
        </AppLayout>
    );
}
