import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// ─── Alpine.js global setup ───────────────────────────────────────
window.Alpine = Alpine;
Alpine.start();

// ─── Chart.js global defaults ────────────────────────────────────
Chart.defaults.font.family = "'IBM Plex Sans', sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#6B7C8D';
Chart.defaults.plugins.legend.labels.boxWidth = 12;
Chart.defaults.plugins.tooltip.backgroundColor = '#264653';
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.scale.grid.color = 'rgba(214,225,234,0.6)';
Chart.defaults.scale.grid.drawBorder = false;

// ─── Theme colour tokens ──────────────────────────────────────────
window.chartColors = {
    teal: '#2A9D8F',
    coral: '#E76F51',
    slate: '#264653',
    mistyBlue: '#8AB4C2',
    tealAlpha: 'rgba(42,157,143,0.15)',
    coralAlpha: 'rgba(231,111,81,0.15)',
    slateAlpha: 'rgba(38,70,83,0.15)',
};

// ─── Timeline chart initialiser ───────────────────────────────────
/**
 * Call window.initTimelineChart(canvasId, timelineData) from a Blade view
 * after the <canvas> element is rendered.
 *
 * timelineData shape: { labels, spend, ctr, cpc, conversions }
 */
window.initTimelineChart = function (canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Spend ($)',
                    data: data.spend || [],
                    borderColor: window.chartColors.teal,
                    backgroundColor: window.chartColors.tealAlpha,
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'y',
                    pointRadius: 3,
                    borderWidth: 2,
                },
                {
                    label: 'CTR (%)',
                    data: data.ctr || [],
                    borderColor: window.chartColors.coral,
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'y1',
                    pointRadius: 3,
                    borderWidth: 2,
                    borderDash: [5, 3],
                },
                {
                    label: 'CPC ($)',
                    data: data.cpc || [],
                    borderColor: window.chartColors.slate,
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'y1',
                    pointRadius: 3,
                    borderWidth: 2,
                    borderDash: [2, 4],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const label = ctx.dataset.label || '';
                            const value = ctx.parsed.y;
                            if (label.includes('$')) return ` ${label}: $${value.toFixed(2)}`;
                            if (label.includes('CTR')) return ` ${label}: ${value.toFixed(3)}%`;
                            return ` ${label}: ${value}`;
                        },
                    },
                },
            },
            scales: {
                x: { grid: { color: 'rgba(214,225,234,0.4)' } },
                y: {
                    type: 'linear',
                    position: 'left',
                    ticks: { callback: (v) => '$' + v.toLocaleString() },
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                },
            },
        },
    });
};

// ─── Campaign bar chart ───────────────────────────────────────────
window.initCampaignChart = function (canvasId, campaigns) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !campaigns || !campaigns.length) return null;

    const labels = campaigns.map((c) => c.name.length > 24 ? c.name.slice(0, 24) + '…' : c.name);
    const spend = campaigns.map((c) => c.spend);
    const roas = campaigns.map((c) => c.roas);

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Spend ($)',
                    data: spend,
                    backgroundColor: window.chartColors.tealAlpha,
                    borderColor: window.chartColors.teal,
                    borderWidth: 1.5,
                    borderRadius: 4,
                    yAxisID: 'y',
                },
                {
                    label: 'ROAS (×)',
                    data: roas,
                    type: 'line',
                    borderColor: window.chartColors.coral,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 4,
                    yAxisID: 'y1',
                    tension: 0.3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'end' } },
            scales: {
                x: { grid: { display: false } },
                y: {
                    type: 'linear',
                    position: 'left',
                    ticks: { callback: (v) => '$' + v.toLocaleString() },
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { callback: (v) => v + 'x' },
                },
            },
        },
    });
};