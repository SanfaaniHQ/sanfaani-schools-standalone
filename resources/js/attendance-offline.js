const DATABASE_NAME = 'sanfaani-attendance-offline';
const DATABASE_VERSION = 1;
const STORE_NAME = 'attendance_records';
const SYNCED_RETENTION_MS = 24 * 60 * 60 * 1000;

const requestResult = (request) => new Promise((resolve, reject) => {
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error || new Error('Browser storage request failed.'));
});

const openDatabase = () => new Promise((resolve, reject) => {
    if (!('indexedDB' in window)) {
        reject(new Error('IndexedDB is not available in this browser.'));
        return;
    }

    const request = window.indexedDB.open(DATABASE_NAME, DATABASE_VERSION);

    request.onupgradeneeded = () => {
        const database = request.result;

        if (database.objectStoreNames.contains(STORE_NAME)) {
            return;
        }

        const store = database.createObjectStore(STORE_NAME, { keyPath: 'client_uuid' });
        store.createIndex('school_id', 'school_id', { unique: false });
        store.createIndex('record_key', 'record_key', { unique: false });
        store.createIndex('sync_state', 'sync_state', { unique: false });
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error || new Error('Browser storage could not be opened.'));
});

const storeRecords = async (records) => {
    const database = await openDatabase();
    const transaction = database.transaction(STORE_NAME, 'readwrite');
    const store = transaction.objectStore(STORE_NAME);

    for (const record of records) {
        const matching = await requestResult(store.index('record_key').getAll(record.record_key));
        const existing = matching.find((item) => (
            item.school_id === record.school_id && item.sync_state !== 'synced'
        ));

        store.put({
            ...record,
            client_uuid: existing?.client_uuid || record.client_uuid,
            sync_state: 'pending',
            last_error: null,
        });
    }

    await new Promise((resolve, reject) => {
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error || new Error('Offline attendance could not be stored.'));
        transaction.onabort = () => reject(transaction.error || new Error('Offline attendance storage was interrupted.'));
    });

    database.close();
};

const recordsForSchool = async (schoolId) => {
    const database = await openDatabase();
    const transaction = database.transaction(STORE_NAME, 'readonly');
    const records = await requestResult(
        transaction.objectStore(STORE_NAME).index('school_id').getAll(String(schoolId))
    );

    database.close();

    return records;
};

const updateRecord = async (clientUuid, changes) => {
    const database = await openDatabase();
    const transaction = database.transaction(STORE_NAME, 'readwrite');
    const store = transaction.objectStore(STORE_NAME);
    const record = await requestResult(store.get(clientUuid));

    if (record) {
        store.put({ ...record, ...changes });
    }

    await new Promise((resolve, reject) => {
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error || new Error('Offline attendance state could not be updated.'));
    });

    database.close();
};

const pruneSyncedRecords = async (schoolId) => {
    const records = await recordsForSchool(schoolId);
    const expired = records.filter((record) => (
        record.sync_state === 'synced'
        && record.synced_at
        && Date.now() - new Date(record.synced_at).getTime() > SYNCED_RETENTION_MS
    ));

    if (expired.length === 0) {
        return;
    }

    const database = await openDatabase();
    const transaction = database.transaction(STORE_NAME, 'readwrite');
    const store = transaction.objectStore(STORE_NAME);

    expired.forEach((record) => store.delete(record.client_uuid));

    await new Promise((resolve, reject) => {
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error || new Error('Synced attendance cleanup failed.'));
    });

    database.close();
};

const clientUuid = () => {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
        const random = Math.floor(Math.random() * 16);
        const value = character === 'x' ? random : (random & 0x3) | 0x8;

        return value.toString(16);
    });
};

const formRecords = (form) => {
    const attendanceDate = form.querySelector('input[name="attendance_date"]')?.value || '';
    const academicSessionId = form.querySelector('input[name="academic_session_id"]')?.value || null;
    const termId = form.querySelector('input[name="term_id"]')?.value || null;
    const schoolId = String(form.dataset.schoolId || '');
    const classId = Number(form.dataset.classId || 0);
    const capturedAt = new Date().toISOString();

    return [...form.querySelectorAll('input[name$="[student_id]"]')].map((studentInput) => {
        const match = studentInput.name.match(/^records\[(\d+)]\[student_id]$/);
        const index = match?.[1];
        const studentId = Number(studentInput.value);
        const status = form.querySelector(`[name="records[${index}][status]"]`)?.value || '';
        const note = form.querySelector(`[name="records[${index}][note]"]`)?.value?.trim() || null;

        return {
            client_uuid: clientUuid(),
            record_key: `${schoolId}:${classId}:${studentId}:${attendanceDate}`,
            school_id: schoolId,
            school_class_id: classId,
            student_id: studentId,
            attendance_date: attendanceDate,
            status,
            note,
            captured_at: capturedAt,
            academic_session_id: academicSessionId ? Number(academicSessionId) : null,
            term_id: termId ? Number(termId) : null,
            source: 'browser_offline',
            sync_state: 'pending',
            stored_at: capturedAt,
            last_error: null,
        };
    });
};

const serverPayload = (record) => ({
    client_uuid: record.client_uuid,
    school_class_id: record.school_class_id,
    student_id: record.student_id,
    attendance_date: record.attendance_date,
    status: record.status,
    note: record.note,
    captured_at: record.captured_at,
    academic_session_id: record.academic_session_id,
    term_id: record.term_id,
    source: 'browser_offline',
});

const setFeedback = (root, message, tone = 'info') => {
    const feedback = root.querySelector('[data-offline-feedback]');

    if (!feedback) {
        return;
    }

    feedback.textContent = message;
    feedback.dataset.tone = tone;
    feedback.className = tone === 'error'
        ? 'mt-3 text-sm font-medium text-red-700'
        : tone === 'success'
            ? 'mt-3 text-sm font-medium text-green-700'
            : 'mt-3 text-sm text-gray-600';
};

const refreshState = async (root) => {
    const records = await recordsForSchool(root.dataset.schoolId);
    const currentClassId = Number(root.dataset.classId);
    const currentDate = root.dataset.attendanceDate;
    const current = records.filter((record) => (
        Number(record.school_class_id) === currentClassId && record.attendance_date === currentDate
    ));
    const counts = {
        pending: records.filter((record) => record.sync_state === 'pending').length,
        failed: records.filter((record) => record.sync_state === 'failed').length,
        synced: records.filter((record) => record.sync_state === 'synced').length,
    };

    root.querySelector('[data-offline-pending-count]')?.replaceChildren(String(counts.pending));
    root.querySelector('[data-offline-failed-count]')?.replaceChildren(String(counts.failed));
    root.querySelector('[data-offline-synced-count]')?.replaceChildren(String(counts.synced));

    root.querySelectorAll('[data-offline-state-student-id]').forEach((target) => {
        const studentRecords = current
            .filter((record) => String(record.student_id) === target.dataset.offlineStateStudentId)
            .sort((left, right) => String(right.stored_at).localeCompare(String(left.stored_at)));
        const latest = studentRecords[0];

        target.textContent = latest
            ? latest.sync_state === 'failed'
                ? 'Failed'
                : latest.sync_state === 'synced'
                    ? 'Synced'
                    : 'Pending'
            : 'Not queued';
        target.dataset.state = latest?.sync_state || 'none';
        target.className = latest?.sync_state === 'failed'
            ? 'text-xs font-semibold text-red-700'
            : latest?.sync_state === 'synced'
                ? 'text-xs font-semibold text-green-700'
                : latest?.sync_state === 'pending'
                    ? 'text-xs font-semibold text-amber-700'
                    : 'text-xs text-gray-500';
    });

    const syncButton = root.querySelector('[data-offline-sync-button]');
    if (syncButton) {
        syncButton.disabled = !navigator.onLine || counts.pending + counts.failed === 0;
    }
};

const syncRecords = async (root, includeFailed = false) => {
    if (!navigator.onLine || root.dataset.syncEnabled !== 'true') {
        await refreshState(root);
        return;
    }

    const allRecords = await recordsForSchool(root.dataset.schoolId);
    const queued = allRecords
        .filter((record) => record.sync_state === 'pending' || (includeFailed && record.sync_state === 'failed'))
        .slice(0, 200);

    if (queued.length === 0) {
        await refreshState(root);
        return;
    }

    const syncButton = root.querySelector('[data-offline-sync-button]');
    if (syncButton) {
        syncButton.disabled = true;
        syncButton.textContent = 'Syncing...';
    }
    setFeedback(root, `Syncing ${queued.length} offline attendance record(s)...`);

    try {
        const response = await fetch(root.dataset.syncUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ records: queued.map(serverPayload) }),
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok || data.success !== true || !Array.isArray(data.results)) {
            throw new Error(data.message || `Offline attendance sync failed with HTTP ${response.status}.`);
        }

        for (const result of data.results) {
            await updateRecord(result.client_uuid, result.accepted ? {
                sync_state: 'synced',
                synced_at: new Date().toISOString(),
                last_error: null,
            } : {
                sync_state: 'failed',
                last_error: result.message || 'The server rejected this offline attendance record.',
            });
        }

        const synced = data.results.filter((result) => result.accepted).length;
        const failed = data.results.length - synced;
        setFeedback(
            root,
            `Offline attendance sync finished: ${synced} accepted, ${failed} failed.`,
            failed > 0 ? 'error' : 'success'
        );
    } catch (error) {
        setFeedback(root, error.message || 'Offline attendance sync could not be completed.', 'error');
    } finally {
        if (syncButton) {
            syncButton.textContent = 'Sync Pending Attendance';
        }
        await refreshState(root);
    }
};

const updateNetworkState = (root) => {
    const target = root.querySelector('[data-offline-network-status]');

    if (target) {
        target.textContent = navigator.onLine ? 'Online' : 'Offline';
        target.className = navigator.onLine
            ? 'font-semibold text-green-700'
            : 'font-semibold text-amber-700';
    }
};

const initializeRoot = async (root) => {
    const form = root.querySelector('[data-attendance-offline-form]');
    const syncButton = root.querySelector('[data-offline-sync-button]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        if (navigator.onLine) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        try {
            const records = formRecords(form);
            await storeRecords(records);
            setFeedback(
                root,
                `${records.length} attendance record(s) saved in this browser and waiting for sync.`,
                'success'
            );
            await refreshState(root);
        } catch (error) {
            setFeedback(root, error.message || 'Attendance could not be saved in browser storage.', 'error');
        }
    });

    syncButton?.addEventListener('click', () => syncRecords(root, true));

    window.addEventListener('offline', () => {
        updateNetworkState(root);
        refreshState(root).catch(() => {});
    });
    window.addEventListener('online', () => {
        updateNetworkState(root);
        syncRecords(root, false).catch(() => {});
    });

    updateNetworkState(root);

    try {
        await pruneSyncedRecords(root.dataset.schoolId);
        await refreshState(root);

        if (navigator.onLine) {
            await syncRecords(root, false);
        }
    } catch (error) {
        setFeedback(root, error.message || 'Browser storage is unavailable.', 'error');
    }
};

document.querySelectorAll('[data-attendance-offline-root]').forEach((root) => {
    initializeRoot(root);
});
