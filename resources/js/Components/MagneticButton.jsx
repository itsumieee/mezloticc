import { useRef } from 'react';
import { motion, useReducedMotion } from 'framer-motion';

export default function MagneticButton({ children, className = '', ...props }) {
    const button = useRef(null);
    const reduceMotion = useReducedMotion();

    function move(event) {
        if (reduceMotion || !button.current) return;
        const bounds = button.current.getBoundingClientRect();
        const x = (event.clientX - bounds.left - bounds.width / 2) * 0.1;
        const y = (event.clientY - bounds.top - bounds.height / 2) * 0.13;
        button.current.style.transform = `translate3d(${x}px, ${y}px, 0)`;
    }

    function reset() {
        if (button.current) button.current.style.transform = '';
    }

    return <motion.button ref={button} className={className} onPointerMove={move} onPointerLeave={reset} whileTap={reduceMotion ? undefined : { scale: 0.98 }} {...props}>{children}</motion.button>;
}
