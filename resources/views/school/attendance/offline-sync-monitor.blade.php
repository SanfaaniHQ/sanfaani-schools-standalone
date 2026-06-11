<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Offline Attendance Sync Monitor"
            description="Read-only health view for server-known browser offline attendance sync receipts and safe sync attempt summaries."
        >
            <x-slot name="actions">
                <x-ui.action-button :href="route('school.attendance.index')" variant="secondary">Attendance</x-ui.action-button>
                <x-ui.action-button :href="route('school.attendance.reports')" variant="secondary">Reports</x-ui.action-button>
                @if ($canViewAuditLogs)
                    <x-ui.action-button :href="route('school.audit-logs.index', ['action' => 'attendance'])" variant="secondary">Audit Logs</x-ui.action-button>
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    @php
        $statusLabel = fn (string $status): string => str($status)->replace('_', ' ')->title()->toString();
        $statusTone = function (string $status): string {
            return match ($status) {
                'synced', 'skipped_duplicate' => 'success',
                'conflict' => 'warning',
                'failed_validation', 'failed_permission' => 'danger',
                'processing' => 'info',
                default => 'neutral',
            };
        };
        $formatAt = function (mixed $value): string {
            if (! $value) {
                return 'None recorded';
            }

            return \Illuminate\Support\Carbon::parse($value)->format('d M Y H:i');
        };
        $safeDetail = function (string $status): string {
            return match ($status) {
                'synced' => 'Accepted by Laravel validation and linked to an attendance record.',
                'conflict' => 'Conflict or existing-row update was handled by attendance duplicate rules.',
                'processing' => 'Receipt was created but has not been finalized.',
                'failed_validation' => 'Validation failure count from safe sync attempt summaries.',
                'failed_permission' => 'Permission failure count from safe sync attempt summaries.',
                'skipped_duplicate' => 'Duplicate attempt count from safe sync attempt summaries.',
                default => 'Server-known offline sync status.',
            };
        };
    @endphp

    <div class="space-y-6">
        <x-ui.alert
            tone="warning"
            title="Attendance-only offline pilot"
            body="Stage 9 monitors server-known offline attendance sync attempts only. Browser-local pending records are invisible to Laravel until the browser attempts sync, and full portal offline mode is not implemented."
        />

        <x-ui.alert
            tone="info"
            title="Privacy boundary"
            body="This monitor shows receipt status, safe counts, class/date/actor context, and sanitized explanations. It does not show raw browser payloads, payload hashes, secrets, stack traces, student biodata, or browser IndexedDB contents."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <x-ui.stat-card
                label="Server receipts"
                :value="$summary['receipt_total']"
                meta="Durable receipt rows known to Laravel"
                tone="brand"
            />
            <x-ui.stat-card
                label="Synced"
                :value="$summary['synced_count']"
                :meta="'Latest: '.$formatAt($summary['latest_successful_sync_at'])"
                tone="success"
            />
            <x-ui.stat-card
                label="Skipped duplicates"
                :value="$summary['skipped_duplicate_count']"
                meta="From safe sync attempt summaries"
                tone="success"
            />
            <x-ui.stat-card
                label="Conflicts"
                :value="$summary['conflict_count']"
                :meta="'Latest: '.$formatAt($summary['latest_failure_or_conflict_at'])"
                :tone="$summary['conflict_count'] > 0 ? 'warning' : 'neutral'"
            />
            <x-ui.stat-card
                label="Validation failures"
                :value="$summary['failed_validation_count']"
                meta="No raw payload shown"
                :tone="$summary['failed_validation_count'] > 0 ? 'danger' : 'neutral'"
            />
            <x-ui.stat-card
                label="Permission failures"
                :value="$summary['failed_permission_count']"
                meta="School and class scope enforced"
                :tone="$summary['failed_permission_count'] > 0 ? 'danger' : 'neutral'"
            />
        </section>

        <x-ui.panel
            title="Filters"
            description="Filter durable server receipt rows by status, server processing date, class, and submitting user where your role can safely see them."
        >
            <form method="GET" action="{{ route('school.attendance.offline-sync-monitor') }}" class="grid gap-3 md:grid-cols-5">
                <select name="status" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $statusLabel($status) }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All visible classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) ($filters['school_class_id'] ?? 0) === (int) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
                <select name="processed_by" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All allowed users</option>
                    @foreach ($recorders as $recorder)
                        <option value="{{ $recorder->id }}" @selected((int) ($filters['processed_by'] ?? 0) === (int) $recorder->id)>{{ $recorder->name }}</option>
                    @endforeach
                </select>
                <div class="flex flex-wrap gap-2 md:col-span-5">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Apply filters</button>
                    <a href="{{ route('school.attendance.offline-sync-monitor') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear filters</a>
                </div>
            </form>
        </x-ui.panel>

        @if ($summary['attempt_scope_note'])
            <x-ui.alert tone="info" :body="$summary['attempt_scope_note']" />
        @endif

        <section class="grid gap-4 lg:grid-cols-3">
            <x-ui.panel title="By User" description="Server-known receipt rows by submitting user.">
                <div class="space-y-3">
                    @forelse ($byUser as $row)
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <span class="font-semibold text-text-primary">{{ $row['label'] }}</span>
                                <span class="font-mono text-brand-primary">{{ $row['total'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-text-secondary">{{ $row['synced'] }} synced / {{ $row['conflict'] }} conflict</p>
                        </div>
                    @empty
                        <x-ui.empty-state title="No user receipt counts" body="No server-known receipts match the current scope." />
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel title="By Class" description="Class-linked receipt rows only. Failed attempts may not have a receipt link.">
                <div class="space-y-3">
                    @forelse ($byClass as $row)
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <span class="font-semibold text-text-primary">{{ $row['label'] }}</span>
                                <span class="font-mono text-brand-primary">{{ $row['total'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-text-secondary">{{ $row['synced'] }} synced / {{ $row['conflict'] }} conflict</p>
                        </div>
                    @empty
                        <x-ui.empty-state title="No class receipt counts" body="No class-linked receipts match the current scope." />
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel title="By Sync Date" description="Receipt rows grouped by server processing date.">
                <div class="space-y-3">
                    @forelse ($byDate as $row)
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <span class="font-semibold text-text-primary">{{ $row['label'] }}</span>
                                <span class="font-mono text-brand-primary">{{ $row['total'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-text-secondary">{{ $row['synced'] }} synced / {{ $row['conflict'] }} conflict</p>
                        </div>
                    @empty
                        <x-ui.empty-state title="No date receipt counts" body="No server-known receipts match the current date scope." />
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <x-ui.table-card
            title="Recent Server-Known Receipts"
            description="Durable attendance offline sync receipts. Browser-local pending records do not appear here until sync reaches Laravel."
        >
            <table class="enterprise-table">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-left">Receipt</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Class / Date</th>
                        <th class="px-5 py-3 text-left">Submitted By</th>
                        <th class="px-5 py-3 text-left">Processed</th>
                        <th class="px-5 py-3 text-left">Safe Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        @php
                            $record = $receipt->attendanceRecord;
                            $classLabel = trim(($record?->schoolClass?->name ?? 'Unlinked').' '.($record?->schoolClass?->section ?? ''));
                        @endphp
                        <tr>
                            <td class="px-5 py-4 text-sm">
                                <p class="font-semibold text-text-primary">#{{ $receipt->id }}</p>
                                <p class="mt-1 font-mono text-xs text-text-secondary">{{ \Illuminate\Support\Str::limit($receipt->client_uuid, 18) }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm">
                                <x-ui.badge :tone="$statusTone($receipt->result_status)">{{ $statusLabel($receipt->result_status) }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-text-secondary">
                                <p class="font-medium text-text-primary">{{ $classLabel ?: 'Unlinked receipt' }}</p>
                                <p class="mt-1">{{ $record?->attendance_date?->format('d M Y') ?? 'No attendance row linked' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-text-secondary">{{ $receipt->processedBy?->name ?? 'System' }}</td>
                            <td class="px-5 py-4 text-sm text-text-secondary">{{ $formatAt($receipt->processed_at ?? $receipt->created_at) }}</td>
                            <td class="px-5 py-4 text-sm text-text-secondary">{{ $safeDetail($receipt->result_status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12">
                                <x-ui.empty-state
                                    title="No server-known sync receipts"
                                    body="No durable offline attendance sync receipt matches this scope. Browser-local pending records remain invisible until a sync attempt reaches Laravel."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <x-slot name="footer">
                {{ $receipts->links() }}
            </x-slot>
        </x-ui.table-card>

        <x-ui.panel tone="info" title="Stage 8 / Stage 9 Boundary">
            <div class="space-y-2 text-sm leading-6 text-text-secondary">
                <p>Stage 8 added browser offline attendance capture for the class register. Stage 9 adds this server-side monitor for submitted sync attempts, durable receipts, conflicts, failures, and audit trail context.</p>
                <p>The hosted Laravel portal/database remains the source of truth. Offline results, admissions, LMS, fees, CBT, live classes, and full portal offline mode are not implemented in this stage.</p>
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
