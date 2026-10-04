import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initMotion() {
    if (typeof window === 'undefined' || window.__profileMotionInitialized) return;

    gsap.registerPlugin(ScrollTrigger);

    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let lenis = null;
    const updateScrollTrigger = () => ScrollTrigger.update();

    const startLenis = () => {
        if (lenis || motionPreference.matches) return;

        lenis = new Lenis({
            duration: 1.1,
            smoothWheel: true,
            syncTouch: true,
            wheelMultiplier: 0.8,
            autoRaf: true,
        });
        lenis.on('scroll', updateScrollTrigger);
        window.__profileLens = lenis;
    };
    const stopLenis = () => {
        if (!lenis) return;

        lenis.destroy();
        lenis = null;
        window.__profileLens = null;
    };
    const syncMotionPreference = () => {
        if (motionPreference.matches) stopLenis();
        else startLenis();
    };
    const resizeLenis = () => lenis?.resize();
    let resizeFrame = 0;
    const scheduleResize = () => {
        if (resizeFrame) cancelAnimationFrame(resizeFrame);
        resizeFrame = requestAnimationFrame(() => {
            resizeFrame = 0;
            resizeLenis();
        });
    };

    motionPreference.addEventListener('change', syncMotionPreference);
    window.addEventListener('scroll', updateScrollTrigger, { passive: true });
    window.addEventListener('resize', resizeLenis);
    window.addEventListener('load', scheduleResize, { once: true });
    if (document.readyState === 'complete') scheduleResize();
    document.fonts?.ready.then(scheduleResize);
    window.setTimeout(scheduleResize, 600);
    syncMotionPreference();

    window.__profileMotionInitialized = true;
}
