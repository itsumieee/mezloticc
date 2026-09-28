import Chart from 'chart.js/auto';

window.renderCategoryChart = function (canvasId, labels, values) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !labels.length) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#e2231a',
                    '#20242a',
                    '#59616b',
                    '#87909a',
                    '#b7bec6',
                    '#d0d5db',
                    '#6c7783',
                    '#a2aab3',
                ],
                borderColor: '#ffffff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#4f5863',
                        font: { family: 'DM Sans', size: 12 },
                        boxWidth: 9,
                        padding: 14,
                    }
                }
            }
        }
    });
};