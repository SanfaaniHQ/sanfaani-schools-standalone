import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('click', (event) => {
    const button = event.target.closest('button[type="submit"], input[type="submit"]');

    if (!button || !button.form) {
        return;
    }

    button.form.querySelectorAll('[data-submit-clicked="true"]').forEach((element) => {
        element.removeAttribute('data-submit-clicked');
    });

    button.setAttribute('data-submit-clicked', 'true');
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.noLoading === 'true') {
        return;
    }

    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }

    const submitter = event.submitter
        || form.querySelector('[data-submit-clicked="true"]')
        || form.querySelector('button[type="submit"], input[type="submit"]');

    const confirmMessage = submitter?.dataset.confirm || form.dataset.confirm;

    if (confirmMessage && !window.confirm(confirmMessage)) {
        event.preventDefault();
        submitter?.removeAttribute('data-submit-clicked');
        delete form.dataset.submitting;
        return;
    }

    form.dataset.submitting = 'true';

    if (!submitter) {
        return;
    }

    const loadingText = submitter.dataset.loadingText || form.dataset.loadingText || 'Processing...';

    submitter.disabled = true;

    if (submitter.tagName === 'INPUT') {
        submitter.dataset.originalValue = submitter.value;
        submitter.value = loadingText;
        return;
    }

    submitter.dataset.originalHtml = submitter.innerHTML;
    submitter.innerHTML = `
        <span class="inline-flex items-center gap-2">
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
            <span>${loadingText}</span>
        </span>
    `;
});

document.addEventListener('click', (event) => {
    const anchor = event.target.closest('a[href^="#"]');

    if (!anchor || anchor.hash.length <= 1) {
        return;
    }

    const target = document.querySelector(anchor.hash);

    if (!target) {
        return;
    }

    event.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

document.querySelectorAll('[data-faq-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const panel = document.querySelector(toggle.dataset.faqToggle);

        if (!panel) {
            return;
        }

        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!isOpen));
        panel.hidden = isOpen;
    });
});

document.querySelectorAll('[data-pricing-toggle]').forEach((toggle) => {
    toggle.addEventListener('change', () => {
        const period = toggle.value;

        document.querySelectorAll('[data-price-period]').forEach((price) => {
            price.hidden = price.dataset.pricePeriod !== period;
        });
    });
});

const syncTermDropdown = (sessionSelect) => {
    const targetSelector = sessionSelect.dataset.termTarget;
    const termSelect = targetSelector ? document.querySelector(targetSelector) : null;

    if (!(termSelect instanceof HTMLSelectElement)) {
        return;
    }

    const sessionId = sessionSelect.value;
    let selectedOptionStillVisible = false;

    termSelect.querySelectorAll('option[data-session-id]').forEach((option) => {
        const matches = !sessionId || option.dataset.sessionId === sessionId;
        option.hidden = !matches;
        option.disabled = !matches;

        if (matches && option.selected) {
            selectedOptionStillVisible = true;
        }
    });

    if (!selectedOptionStillVisible) {
        termSelect.value = '';
    }
};

document.querySelectorAll('[data-session-term-source]').forEach((sessionSelect) => {
    syncTermDropdown(sessionSelect);
    sessionSelect.addEventListener('change', () => syncTermDropdown(sessionSelect));
});

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const input = document.querySelector(toggle.dataset.passwordToggle);

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        toggle.textContent = showPassword ? 'Hide' : 'Show';
        toggle.setAttribute('aria-pressed', String(showPassword));
    });
});

const formatScore = (value) => {
    if (!Number.isFinite(value)) {
        return '0.00';
    }

    return value.toFixed(2);
};

const findGradingScale = (scales, score) => scales.find((scale) => (
    Number(scale.min_score) <= score && Number(scale.max_score) >= score
));

document.querySelectorAll('[data-result-workspace]').forEach((workspace) => {
    const scaleSource = workspace.querySelector('[data-result-grading-scales]');
    const scales = scaleSource ? JSON.parse(scaleSource.textContent || '[]') : [];

    const syncRow = (row) => {
        const caInput = row.querySelector('[data-score-field="ca"]');
        const examInput = row.querySelector('[data-score-field="exam"]');
        const totalTarget = row.querySelector('[data-total-score]');
        const gradeTarget = row.querySelector('[data-grade-label]');
        const remarkTarget = row.querySelector('[data-grade-remark]');
        const ca = Number.parseFloat(caInput?.value || '0');
        const exam = Number.parseFloat(examInput?.value || '0');
        const total = (Number.isFinite(ca) ? ca : 0) + (Number.isFinite(exam) ? exam : 0);
        const grading = findGradingScale(scales, total);

        if (totalTarget) {
            totalTarget.textContent = formatScore(total);
        }

        if (gradeTarget) {
            gradeTarget.textContent = grading?.grade || 'N/A';
        }

        if (remarkTarget) {
            remarkTarget.textContent = grading?.remark || 'No active grading match';
        }
    };

    workspace.querySelectorAll('[data-result-row]').forEach((row) => {
        syncRow(row);

        row.querySelectorAll('[data-score-field]').forEach((input) => {
            input.addEventListener('input', () => syncRow(row));
        });
    });
});
