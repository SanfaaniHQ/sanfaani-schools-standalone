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

    const submitter = form.querySelector('[data-submit-clicked="true"]')
        || form.querySelector('button[type="submit"], input[type="submit"]');

    const confirmMessage = submitter?.dataset.confirm || form.dataset.confirm;

    if (confirmMessage && !window.confirm(confirmMessage)) {
        event.preventDefault();
        submitter?.removeAttribute('data-submit-clicked');
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
