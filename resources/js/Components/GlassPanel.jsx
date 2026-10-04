import { motion, useReducedMotion } from 'framer-motion';

export function GlassPanel({ as: Tag = 'section', className = '', children, style, ...props }) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.div
            className={`glass-panel ${className}`}
            style={{ backdropFilter: 'blur(26px) saturate(160%)', WebkitBackdropFilter: 'blur(26px) saturate(160%)', ...style }}
            initial={reduceMotion ? false : { opacity: 0, y: 24 }}
            whileInView={{ opacity: 1, y: 0 }}
            whileHover={reduceMotion ? undefined : { y: -2, rotateX: 0.3, rotateY: -0.3 }}
            viewport={{ once: true, amount: 0.12 }}
            transition={{ duration: 0.65, ease: [0.22, 1, 0.36, 1] }}
            {...props}
        >
            <Tag>{children}</Tag>
        </motion.div>
    );
}

export function PageTitle({ index, title, meta, actions }) {
    return (
        <header className="page-heading">
            <div className="page-heading-copy">
                <p className="eyebrow"><span>{index}</span><i />{meta}</p>
                <h1>{title}<svg className="chrome-sparkle" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0.8 14.9 9.1 23.2 12l-8.3 2.9L12 23.2l-2.9-8.3L.8 12l8.3-2.9L12 .8Z" /></svg></h1>
            </div>
            {actions && <div className="page-actions">{actions}</div>}
        </header>
    );
}

export function Notice({ children, kind = 'warning' }) {
    if (!children) return null;

    return <div className={`notice notice-${kind}`} role="status">{children}</div>;
}

export function EmptyState({ label, children }) {
    return (
        <div className="empty-state">
            <span className="eyebrow">{label}</span>
            <p>{children}</p>
        </div>
    );
}

export function ActionLink({ href, children, className = '' }) {
    return <a className={`action-link ${className}`} href={href}>{children}<span aria-hidden="true">↗</span></a>;
}
