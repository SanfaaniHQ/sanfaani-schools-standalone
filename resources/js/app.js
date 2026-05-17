import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const applyTheme = (theme) => {
    const normalizedTheme = theme === 'light' ? 'light' : 'dark';

    document.documentElement.classList.toggle('light', normalizedTheme === 'light');
    document.documentElement.classList.toggle('dark', normalizedTheme !== 'light');
    localStorage.setItem('sanfaani-theme', normalizedTheme);
};

const storedTheme = localStorage.getItem('sanfaani-theme');

if (!storedTheme) {
    applyTheme('light');
}

document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
    });
});

document.addEventListener('keydown', (event) => {
    const opensCommandPalette = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

    if (!opensCommandPalette) {
        return;
    }

    event.preventDefault();
    window.dispatchEvent(new CustomEvent('sanfaani:open-command-palette'));
});

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
    target.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        block: 'start',
    });
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
        const showLabel = toggle.dataset.showLabel || 'Show';
        const hideLabel = toggle.dataset.hideLabel || 'Hide';

        input.type = showPassword ? 'text' : 'password';
        toggle.textContent = showPassword ? hideLabel : showLabel;
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

const inlineResultTimers = new WeakMap();

const collectInlineResultPayload = (row) => {
    const payload = {};

    row.querySelectorAll('[data-inline-result-field]').forEach((field) => {
        payload[field.dataset.inlineResultField] = field.value;
    });

    return payload;
};

const setInlineResultState = (row, state) => {
    row.dataset.inlineResultState = state;

    row.querySelectorAll('[data-inline-result-save]').forEach((button) => {
        button.textContent = state === 'saving' ? 'Saving...' : state === 'saved' ? 'Saved' : 'Save Draft';
        button.disabled = state === 'saving';
    });
};

const saveInlineResultRow = async (row) => {
    const url = row.dataset.inlineResultUrl;

    if (!url) {
        return;
    }

    setInlineResultState(row, 'saving');

    const response = await fetch(url, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': row.dataset.csrf || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(collectInlineResultPayload(row)),
    });

    if (!response.ok) {
        setInlineResultState(row, 'error');
        throw new Error(`Autosave failed with HTTP ${response.status}`);
    }

    const data = await response.json();
    const setText = (selector, value) => {
        const target = row.querySelector(selector);

        if (target) {
            target.textContent = value || 'N/A';
        }
    };

    setText('[data-inline-result-total]', data.total_score);
    setText('[data-inline-result-grade]', data.grade);
    setText('[data-inline-result-pass-fail]', data.pass_fail);
    setText('[data-inline-result-remark]', data.remark);
    setText('[data-inline-result-updated-by]', data.updated_by);
    setText('[data-inline-result-last-edited]', data.last_edited);
    setText('[data-inline-result-version]', data.result_version);
    setInlineResultState(row, 'saved');

    window.setTimeout(() => {
        if (row.dataset.inlineResultState === 'saved') {
            setInlineResultState(row, 'idle');
        }
    }, 1800);
};

document.querySelectorAll('[data-inline-result-row]').forEach((row) => {
    row.querySelectorAll('[data-inline-result-field]').forEach((field) => {
        field.addEventListener('input', () => {
            window.clearTimeout(inlineResultTimers.get(row));
            inlineResultTimers.set(row, window.setTimeout(() => {
                saveInlineResultRow(row).catch(() => {});
            }, 900));
        });
    });

    row.querySelectorAll('[data-inline-result-save]').forEach((button) => {
        button.addEventListener('click', () => {
            window.clearTimeout(inlineResultTimers.get(row));
            saveInlineResultRow(row).catch(() => {});
        });
    });
});
