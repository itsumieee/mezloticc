import { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

export default function DataChart({ type = 'bar', labels = [], values = [], horizontal = false }) {
    const canvas = useRef(null);

    useEffect(() => {
        if (!canvas.current || labels.length === 0) return undefined;
        const styles = getComputedStyle(document.documentElement);
        const accent = styles.getPropertyValue('--accent').trim() || '#006878';
        const paper = styles.getPropertyValue('--paper').trim() || '#172630';
        const muted = styles.getPropertyValue('--muted').trim() || '#4f626e';

        const chart = new Chart(canvas.current, {
            type,
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: type === 'doughnut'
                        ? [accent, '#168da0', '#45a6b5', '#7dbbc5', '#416875', '#9cb9c1', '#c2d9de', '#e2eef1']
                        : accent,
                    borderColor: '#edf3f6',
                    borderWidth: type === 'doughnut' ? 3 : 0,
                    barThickness: horizontal ? 14 : undefined,
                }],
            },
            options: {
                responsive: true,
                indexAxis: horizontal ? 'y' : 'x',
                cutout: type === 'doughnut' ? '65%' : undefined,
                plugins: { legend: { display: type === 'doughnut', labels: { color: paper, usePointStyle: true } } },
                scales: type === 'doughnut' ? undefined : {
                    x: { grid: { color: 'rgba(26,55,69,.09)' }, ticks: { color: muted } },
                    y: { grid: { color: 'rgba(26,55,69,.09)' }, ticks: { color: muted }, beginAtZero: true },
                },
            },
        });

        return () => chart.destroy();
    }, [type, labels, values, horizontal]);

    return <canvas ref={canvas} />;
}
