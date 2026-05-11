import './bootstrap';

// ── Scroll reveal ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });

    els.forEach(el => io.observe(el));
});

// ── Global chart defaults (Chart.js) ──────────
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = '"IBM Plex Sans", sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#2F5061';

    Chart.defaults.plugins.legend.labels.boxWidth = 10;
    Chart.defaults.plugins.legend.labels.padding = 16;

    Chart.defaults.plugins.tooltip.backgroundColor = '#2F5061';
    Chart.defaults.plugins.tooltip.titleColor = '#F5F5F7';
    Chart.defaults.plugins.tooltip.bodyColor = '#F5F5F7';
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.padding = 12;

    Chart.defaults.scale.grid.color = 'rgba(47,80,97,0.06)';
    Chart.defaults.scale.ticks.color = '#6B8A97';
});