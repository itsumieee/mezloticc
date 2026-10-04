import Chart from 'chart.js/auto';

const ACCENT = '#e22630';
const PAPER = '#f3f0f0';
const INK = '#090909';
const INK_2 = '#151313';
const PALETTE = [
    '#e22630', '#ff5b64', '#a81922', '#6f151b',
    '#f09095', '#393133', '#777073', '#c9c2c3',
];
const MONO = { family: 'JetBrains Mono', size: 10 };

const tooltipStyle = {
    backgroundColor: '#171313',
    borderColor: '#7f2027',
    borderWidth: 1,
    titleColor: '#ffffff',
    bodyColor: '#e2dada',
    titleFont: MONO,
    bodyFont: MONO,
    padding: 12,
    displayColors: false,
};

window.renderCategoryChart = function (canvasId, labels, values) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !labels.length) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: PALETTE,
                borderColor: INK,
                borderWidth: 3,
                hoverBorderColor: ACCENT,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#e7e0e0',
                        font: MONO,
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        usePointStyle: true,
                        pointStyle: 'rect',
                    }
                },
                tooltip: tooltipStyle,
            }
        }
    });
};

window.renderDonutChart = function (canvasId, labels, values) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !labels.length) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: PALETTE,
                borderColor: INK,
                borderWidth: 3,
                hoverBorderColor: ACCENT,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: PAPER,
                        font: MONO,
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        usePointStyle: true,
                        pointStyle: 'rect',
                    }
                },
                tooltip: tooltipStyle,
            }
        }
    });
};

window.renderBarChart = function (canvasId, labels, values) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !labels.length) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: ACCENT,
                hoverBackgroundColor: PAPER,
                borderWidth: 0,
                barThickness: 14,
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: tooltipStyle,
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.09)', drawBorder: false },
                    ticks: { color: '#a89c9d', font: MONO },
                },
                y: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#e7e0e0', font: MONO },
                }
            }
        }
    });
};

window.renderHistogram = function (canvasId, values) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !values.length) return;

    const buckets = [
        { label: '0–99', min: 0, max: 100 },
        { label: '100–499', min: 100, max: 500 },
        { label: '500–1K', min: 500, max: 1000 },
        { label: '1K–5K', min: 1000, max: 5000 },
        { label: '5K–10K', min: 5000, max: 10000 },
        { label: '10K–50K', min: 10000, max: 50000 },
        { label: '50K–100K', min: 50000, max: 100000 },
        { label: '100K+', min: 100000, max: Infinity },
    ];

    const counts = buckets.map((bucket) => values.filter((value) => value >= bucket.min && value < bucket.max).length);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: buckets.map((bucket) => bucket.label),
            datasets: [{
                data: counts,
                backgroundColor: ACCENT,
                hoverBackgroundColor: PAPER,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: tooltipStyle,
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#a89c9d', font: MONO },
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.09)', drawBorder: false },
                    ticks: { color: '#a89c9d', font: MONO, precision: 0 },
                    beginAtZero: true,
                }
            }
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.28s ease-out';
    requestAnimationFrame(() => document.body.style.opacity = '1');
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('http') || link.target === '_blank') return;
    if (event.metaKey || event.ctrlKey || event.shiftKey) return;

    event.preventDefault();
    document.body.style.opacity = '0';
    setTimeout(() => window.location = href, 180);
});