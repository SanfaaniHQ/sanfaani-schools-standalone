<x-ui.panel>
    <form method="GET" action="{{ route('school.reports.index') }}" class="grid gap-4 lg:grid-cols-6">
        <div>
            <label for="reports-date-from" class="text-xs font-semibold uppercase tracking-normal text-text-secondary">From</label>
            <input
                id="reports-date-from"
                type="date"
                name="date_from"
                value="{{ $filters['date_from'] ?? '' }}"
                class="mt-1 w-full rounded-md border-border-subtle bg-bg-primary text-sm text-text-primary"
            >
        </div>

        <div>
            <label for="reports-date-to" class="text-xs font-semibold uppercase tracking-normal text-text-secondary">To</label>
            <input
                id="reports-date-to"
                type="date"
                name="date_to"
                value="{{ $filters['date_to'] ?? '' }}"
                class="mt-1 w-full rounded-md border-border-subtle bg-bg-primary text-sm text-text-primary"
            >
        </div>

        <div>
            <label for="reports-class" class="text-xs font-semibold uppercase tracking-normal text-text-secondary">Class</label>
            <select id="reports-class" name="school_class_id" class="mt-1 w-full rounded-md border-border-subtle bg-bg-primary text-sm text-text-primary">
                <option value="">All classes</option>
                @foreach ($filterOptions['classes'] as $class)
                    <option value="{{ $class->id }}" @selected((string) ($filters['school_class_id'] ?? '') === (string) $class->id)>
                        {{ trim($class->name.' '.$class->section) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="reports-session" class="text-xs font-semibold uppercase tracking-normal text-text-secondary">Session</label>
            <select id="reports-session" name="academic_session_id" class="mt-1 w-full rounded-md border-border-subtle bg-bg-primary text-sm text-text-primary">
                <option value="">All sessions</option>
                @foreach ($filterOptions['academicSessions'] as $session)
                    <option value="{{ $session->id }}" @selected((string) ($filters['academic_session_id'] ?? '') === (string) $session->id)>
                        {{ $session->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="reports-term" class="text-xs font-semibold uppercase tracking-normal text-text-secondary">Term</label>
            <select id="reports-term" name="term_id" class="mt-1 w-full rounded-md border-border-subtle bg-bg-primary text-sm text-text-primary">
                <option value="">All terms</option>
                @foreach ($filterOptions['terms'] as $term)
                    <option value="{{ $term->id }}" @selected((string) ($filters['term_id'] ?? '') === (string) $term->id)>
                        {{ $term->name }}{{ $term->academicSession ? ' - '.$term->academicSession->name : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="reports-status" class="text-xs font-semibold uppercase tracking-normal text-text-secondary">Status</label>
            <select id="reports-status" name="status" class="mt-1 w-full rounded-md border-border-subtle bg-bg-primary text-sm text-text-primary">
                <option value="">All statuses</option>
                @foreach ($filterOptions['statuses'] as $status)
                    <option value="{{ $status }}" @selected((string) ($filters['status'] ?? '') === (string) $status)>
                        {{ str($status)->replace('_', ' ')->title() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-end gap-2 lg:col-span-6">
            <button type="submit" class="ui-button-primary">Apply Filters</button>
            <a href="{{ route('school.reports.index') }}" class="ui-button-secondary">Reset</a>
        </div>
    </form>
</x-ui.panel>
