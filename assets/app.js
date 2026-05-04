import './stimulus_bootstrap.js';

function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        bootstrap.Tooltip.getOrCreateInstance(el);
    });
}

document.addEventListener('DOMContentLoaded', initTooltips);
document.addEventListener('turbo:load', initTooltips);
