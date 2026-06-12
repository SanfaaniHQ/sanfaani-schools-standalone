@php
    $settings = $reportCard['settings'];
    $school = $reportCard['school'];
    $reportBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
    $reportBrandName = data_get($reportBranding, 'brand_name', $school->name);
    $reportLogo = data_get($reportBranding, 'logo_url') ?: $school->logoUrl();
    $reportInitials = data_get($reportBranding, 'initials', $school->initials());
    $student = $reportCard['student'];
    $results = $reportCard['results'];
    $resultClass = $reportCard['resultClass'] ?? $student->schoolClass;
    $primary = $settings->primary_color ?: '#047857';
    $accent = $settings->accent_color ?: '#0f172a';
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <header class="flex flex-col gap-4 border-b border-gray-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            @if ($settings->show_logo && $reportLogo)
                <img src="{{ $reportLogo }}" alt="{{ $reportBrandName }}" class="h-16 w-16 rounded-xl border border-gray-200 object-contain">
            @elseif ($settings->show_logo)
                <div class="flex h-16 w-16 items-center justify-center rounded-xl text-lg font-semibold text-white" style="background-color: {{ $primary }}">
                    {{ $reportInitials }}
                </div>
            @endif

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Report Card Preview</p>
                <h3 class="text-2xl font-semibold" style="color: {{ $primary }}">{{ $reportBrandName }}</h3>
                @if ($settings->show_school_address && $school->address)
                    <p class="mt-1 text-sm text-gray-600">{{ $school->address }}</p>
                @endif
                <p class="mt-1 text-xs text-gray-500">
                    @if ($settings->show_school_phone && $school->phone) {{ $school->phone }} @endif
                    @if ($settings->show_school_email && $school->email) {{ $school->email }} @endif
                </p>
            </div>
        </div>

        <div class="rounded-xl px-4 py-3 text-sm font-semibold text-white" style="background-color: {{ $accent }}">
            {{ $reportCard['academicSession']->name }}<br>
            {{ $reportCard['term']->name }}
        </div>
    </header>

    <section class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-gray-50 p-3">
            <p class="text-xs font-medium uppercase text-gray-500">Student</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $student->fullName() }}</p>
        </div>
        <div class="rounded-xl bg-gray-50 p-3">
            <p class="text-xs font-medium uppercase text-gray-500">Admission No.</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $student->admission_number }}</p>
        </div>
        <div class="rounded-xl bg-gray-50 p-3">
            <p class="text-xs font-medium uppercase text-gray-500">Class</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $resultClass?->name ?? 'N/A' }} {{ $resultClass?->section ?? '' }}</p>
        </div>
        <div class="rounded-xl bg-gray-50 p-3">
            <p class="text-xs font-medium uppercase text-gray-500">Average</p>
            <p class="mt-1 font-semibold text-gray-900">{{ number_format($reportCard['averageScore'], 2) }}</p>
        </div>
    </section>

    <section class="mt-5 overflow-hidden rounded-xl border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">CA</th>
                    <th class="px-4 py-3 text-left">Exam</th>
                    <th class="px-4 py-3 text-left">Total</th>
                    <th class="px-4 py-3 text-left">Grade</th>
                    @if ($settings->show_teacher_remark)
                        <th class="px-4 py-3 text-left">Teacher Remark</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($results as $result)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $result->subject->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ number_format((float) $result->ca_score, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ number_format((float) $result->exam_score, 2) }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ number_format((float) $result->total_score, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $result->grade }}</td>
                        @if ($settings->show_teacher_remark)
                            <td class="px-4 py-3 text-gray-700">{{ $result->teacher_remark ?: 'N/A' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="mt-5 grid gap-4 sm:grid-cols-2">
        @if ($settings->show_class_teacher)
            <div class="rounded-xl border border-gray-200 p-4">
                @if ($reportCard['classTeacherSignatureUrl'])
                    <img src="{{ $reportCard['classTeacherSignatureUrl'] }}" alt="Class teacher signature" class="mb-3 h-12 object-contain">
                @endif
                <p class="text-sm font-semibold text-gray-900">{{ $settings->class_teacher_title ?: 'Class Teacher' }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ $settings->class_teacher_name ?: 'Name not set' }}</p>
                @if ($reportCard['classTeacherComment'])
                    <p class="mt-3 text-sm text-gray-600">{{ $reportCard['classTeacherComment'] }}</p>
                @endif
            </div>
        @endif

        @if ($settings->show_head_teacher)
            <div class="rounded-xl border border-gray-200 p-4">
                @if ($reportCard['headTeacherSignatureUrl'])
                    <img src="{{ $reportCard['headTeacherSignatureUrl'] }}" alt="Head teacher signature" class="mb-3 h-12 object-contain">
                @endif
                <p class="text-sm font-semibold text-gray-900">{{ $settings->head_teacher_title ?: 'Head Teacher' }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ $settings->head_teacher_name ?: 'Name not set' }}</p>
                @if ($reportCard['headTeacherComment'])
                    <p class="mt-3 text-sm text-gray-600">{{ $reportCard['headTeacherComment'] }}</p>
                @endif
            </div>
        @endif
    </section>
</div>
