import './stimulus_bootstrap.js';
import './styles/app.css';

function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
}

document.addEventListener('DOMContentLoaded', initTooltips);
document.addEventListener('turbo:load', initTooltips);
