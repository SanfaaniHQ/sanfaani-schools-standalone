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
