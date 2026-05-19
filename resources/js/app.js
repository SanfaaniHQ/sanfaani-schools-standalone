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

const ensureToastRegion = () => {
    let region = document.querySelector('[data-toast-region]');

    if (region) {
        return region;
    }

    region = document.createElement('div');
    region.dataset.toastRegion = 'true';
    region.className = 'fixed right-4 top-20 z-[80] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3';
    region.setAttribute('aria-live', 'polite');
    region.setAttribute('aria-atomic', 'true');
    document.body.appendChild(region);

    return region;
};

const showToast = (message, tone = 'success') => {
    const region = ensureToastRegion();
    const toast = document.createElement('div');
    const toneClass = tone === 'error'
        ? 'border-red-300 bg-red-50 text-red-800 dark:border-red-500/40 dark:bg-red-500/15 dark:text-red-200'
        : 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/15 dark:text-emerald-200';

    toast.className = `rounded-lg border px-4 py-3 text-sm font-semibold shadow-lg transition ${toneClass}`;
    toast.textContent = message;
    region.appendChild(toast);

    window.setTimeout(() => {
        toast.classList.add('opacity-0');
        window.setTimeout(() => toast.remove(), 250);
    }, 4200);
};

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const updateResultState = (result) => {
    if (!result?.id) {
        return;
    }

    document.querySelectorAll(`[data-result-row-id="${result.id}"]`).forEach((row) => {
        row.dataset.resultStatus = result.status || '';

        const statusBadge = row.querySelector('[data-result-status-badge]');
        if (statusBadge) {
            statusBadge.textContent = result.status_label || result.status || 'Updated';
            statusBadge.className = result.is_published
                ? 'inline-flex rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300'
                : 'inline-flex rounded-full border border-border-subtle bg-bg-secondary px-2.5 py-1 text-xs font-medium text-text-secondary';
        }

        const publishedLabel = row.querySelector('[data-result-published-label]');
        if (publishedLabel) {
            publishedLabel.innerHTML = result.is_published
                ? `<span class="block font-semibold text-emerald-700 dark:text-emerald-300">Published</span><span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">${escapeHtml(result.published_at_label)}</span>`
                : 'Not published';
        }

        row.querySelectorAll('[data-result-published-by]').forEach((node) => {
            node.textContent = result.published_by || 'N/A';
        });

        row.querySelectorAll('[data-result-published-at]').forEach((node) => {
            node.textContent = result.is_published ? (result.published_at_label || 'Published') : 'N/A';
        });

        row.querySelectorAll('[data-result-version]').forEach((node) => {
            node.textContent = result.result_version || '';
        });

        row.querySelectorAll('[data-result-publish-form]').forEach((form) => {
            form.classList.toggle('hidden', !result.can_publish);
            form.querySelectorAll('button, input[type="submit"]').forEach((control) => {
                control.disabled = !result.can_publish;
            });
        });

        row.querySelectorAll('[data-result-unpublish-form]').forEach((form) => {
            form.classList.toggle('hidden', !result.can_unpublish);
            form.querySelectorAll('button, input[type="submit"]').forEach((control) => {
                control.disabled = !result.can_unpublish;
            });
        });
    });
};

const setSubmitterLoading = (form, submitter) => {
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
};

const restoreSubmitter = (form, submitter) => {
    delete form.dataset.submitting;
    submitter?.removeAttribute('data-submit-clicked');

    if (!submitter) {
        return;
    }

    submitter.disabled = false;

    if (submitter.tagName === 'INPUT' && submitter.dataset.originalValue) {
        submitter.value = submitter.dataset.originalValue;
        return;
    }

    if (submitter.dataset.originalHtml) {
        submitter.innerHTML = submitter.dataset.originalHtml;
    }
};

const submitResultAction = async (form, submitter) => {
    const payload = new FormData(form);

    if (!payload.has('_return_url')) {
        payload.append('_return_url', window.location.href);
    }

    const csrf = form.querySelector('input[name="_token"]')?.value
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || '';

    try {
        const response = await fetch(form.action, {
            method: (form.method || 'POST').toUpperCase(),
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: payload,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok || data.success === false) {
            throw new Error(data.message || `Request failed with HTTP ${response.status}`);
        }

        showToast(data.message || 'Action completed successfully.');
        updateResultState(data.result);
        window.dispatchEvent(new CustomEvent('sanfaani:result-action-completed', { detail: data }));

        if (data.reload !== false) {
            window.setTimeout(() => {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }

                window.location.reload();
            }, 650);
        } else {
            restoreSubmitter(form, submitter);
        }
    } catch (error) {
        showToast(error.message || 'The action could not be completed.', 'error');
        restoreSubmitter(form, submitter);
    }
};

const initGlobalSearch = () => {
    document.querySelectorAll('[data-global-search-root]').forEach((root) => {
        const input = root.querySelector('[data-global-search-input]');
        const results = root.querySelector('[data-global-search-results]');
        const status = root.querySelector('[data-global-search-status]');
        const defaults = root.querySelector('[data-command-default-results]');
        const searchUrl = root.dataset.searchUrl;

        if (!(input instanceof HTMLInputElement) || !results || !status || !defaults || !searchUrl) {
            return;
        }

        let timer = null;
        let controller = null;

        const setStatus = (message, visible = true) => {
            status.textContent = message;
            status.classList.toggle('hidden', !visible);
        };

        const resetSearch = () => {
            controller?.abort();
            results.innerHTML = '';
            defaults.hidden = false;
            setStatus('', false);
        };

        const renderGroups = (groups) => {
            const visibleGroups = (groups || []).filter((group) => Array.isArray(group.items) && group.items.length > 0);

            if (visibleGroups.length === 0) {
                results.innerHTML = '';
                setStatus('No matching records found.');
                return;
            }

            setStatus('', false);
            results.innerHTML = visibleGroups.map((group) => `
                <section>
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-normal text-text-tertiary">${escapeHtml(group.label)}</p>
                    <div class="space-y-1">
                        ${group.items.map((item) => `
                            <a href="${escapeHtml(item.url)}" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary focus:bg-bg-tertiary focus:outline-none">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-border-subtle bg-bg-primary text-xs font-semibold text-brand-primary">${escapeHtml((item.type || '?').slice(0, 1))}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-text-primary">${escapeHtml(item.title)}</span>
                                    <span class="block truncate text-xs text-text-tertiary">${escapeHtml(item.subtitle)}</span>
                                </span>
                                <span class="hidden text-xs font-semibold text-text-muted sm:inline">${escapeHtml(item.type)}</span>
                            </a>
                        `).join('')}
                    </div>
                </section>
            `).join('');
        };

        const runSearch = async () => {
            const query = input.value.trim();

            if (query.length < 2) {
                resetSearch();
                return;
            }

            defaults.hidden = true;
            results.innerHTML = '';
            setStatus('Searching...');

            controller?.abort();
            controller = new AbortController();

            try {
                const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`Search failed with HTTP ${response.status}`);
                }

                const data = await response.json();
                renderGroups(data.groups);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                results.innerHTML = '';
                setStatus('Search is temporarily unavailable.');
            }
        };

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(runSearch, 250);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            const firstResult = results.querySelector('a[href]');
            if (firstResult) {
                event.preventDefault();
                firstResult.click();
            }
        });
    });
};

const initNotificationPolling = () => {
    document.querySelectorAll('[data-notification-root]').forEach((root) => {
        const feedUrl = root.dataset.feedUrl;
        const readUrlTemplate = root.dataset.readUrlTemplate;
        const indexUrl = root.dataset.indexUrl;
        const csrf = root.dataset.csrf || '';
        const emptyLabel = root.dataset.emptyLabel || 'No notifications yet.';
        const list = root.querySelector('[data-notification-list]');
        const button = root.querySelector('[data-notification-toggle]');

        if (!feedUrl || !readUrlTemplate || !indexUrl || !list || !button) {
            return;
        }

        const syncCount = (count) => {
            const normalized = Number(count) || 0;
            let badge = root.querySelector('[data-notification-count]');

            if (normalized <= 0) {
                badge?.remove();
                return;
            }

            if (!badge) {
                badge = document.createElement('span');
                badge.dataset.notificationCount = 'true';
                badge.className = 'absolute right-1.5 top-1.5 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white';
                button.appendChild(badge);
            }

            badge.textContent = normalized > 9 ? '9+' : String(normalized);
        };

        const syncList = (notifications) => {
            if (!Array.isArray(notifications) || notifications.length === 0) {
                list.innerHTML = `<div class="px-4 py-6 text-sm text-text-secondary">${escapeHtml(emptyLabel)}</div>`;
                return;
            }

            list.innerHTML = notifications.map((notification) => {
                const readUrl = readUrlTemplate.replace('__ID__', encodeURIComponent(notification.id));
                const unreadClass = notification.read
                    ? 'border-transparent'
                    : 'border-brand-primary bg-bg-tertiary/50';

                return `
                    <form method="POST" action="${escapeHtml(readUrl)}">
                        <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                        <input type="hidden" name="redirect" value="${escapeHtml(notification.action_url || indexUrl)}">
                        <button type="submit" class="block w-full border-s-2 px-4 py-3 text-start text-sm transition hover:bg-bg-tertiary ${unreadClass}" data-loading-text="Opening...">
                            <span class="block font-semibold text-text-primary">${escapeHtml(notification.title)}</span>
                            ${notification.body ? `<span class="mt-1 block text-xs text-text-secondary">${escapeHtml(notification.body)}</span>` : ''}
                            <span class="mt-2 block text-xs text-text-tertiary">${escapeHtml(notification.created_at)}</span>
                        </button>
                    </form>
                `;
            }).join('');
        };

        const refresh = async () => {
            try {
                const response = await fetch(feedUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                syncCount(data.unread_count);
                syncList(data.notifications);
            } catch (error) {
                // Polling failures should not interrupt the active workflow.
            }
        };

        window.setInterval(refresh, 45000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refresh();
            }
        });
        window.addEventListener('sanfaani:result-action-completed', refresh);
    });
};

initGlobalSearch();
initNotificationPolling();

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

    setSubmitterLoading(form, submitter);

    if (form.matches('[data-result-action-form]')) {
        event.preventDefault();
        submitResultAction(form, submitter);
        return;
    }
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
