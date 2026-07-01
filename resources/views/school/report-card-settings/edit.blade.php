<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Report Card Settings</h2>
                <p class="mt-1 text-sm text-gray-500">Configure report card display without changing academic result data.</p>
            </div>

            <a href="{{ route('school.report-card-settings.preview') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Open Preview</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-2">
                @if (session('success'))
                    <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('school.report-card-settings.update') }}" enctype="multipart/form-data" data-loading-text="Saving..." class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <section class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Branding</h3>
                        <div class="responsive-form-grid mt-5 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Template</label>
                                <select name="report_card_template_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" @selected((int) old('report_card_template_id', $settings->report_card_template_id) === (int) $template->id)>{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">School Name Font</label>
                                <select name="school_name_font" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach (['default' => 'Default', 'serif' => 'Serif', 'sans' => 'Sans', 'formal' => 'Formal'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('school_name_font', $settings->school_name_font) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Primary Color</label>
                                <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?: '#047857') }}" class="mt-1 h-12 w-full rounded-xl border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Accent Color</label>
                                <input type="color" name="accent_color" value="{{ old('accent_color', $settings->accent_color ?: '#0f172a') }}" class="mt-1 h-12 w-full rounded-xl border border-gray-300">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Header Layout</h3>
                        <div class="mt-5 grid gap-6 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Header Type</label>
                                <select name="header_type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach (['classic' => 'Classic', 'centered' => 'Centered', 'compact' => 'Compact'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('header_type', $settings->header_type) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Student Info Layout</label>
                                <select name="student_info_layout" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach (['two_column' => 'Two Column', 'single_column' => 'Single Column', 'compact' => 'Compact'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('student_info_layout', $settings->student_info_layout) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Result Table Style</label>
                                <select name="result_table_style" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach (['standard' => 'Standard', 'compact' => 'Compact', 'bordered' => 'Bordered'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('result_table_style', $settings->result_table_style) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Student Information and Result Table</h3>
                        <div class="mt-5 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ([
                                'show_logo' => 'Show logo',
                                'show_school_address' => 'Show address',
                                'show_school_phone' => 'Show phone',
                                'show_school_email' => 'Show email',
                                'show_student_photo' => 'Show student photo',
                                'show_teacher_remark' => 'Show teacher remark',
                            ] as $field => $label)
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings->{$field})) class="rounded border-gray-300 text-gray-900">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Signatures</h3>
                        <div class="responsive-form-grid mt-5 gap-6">
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="show_class_teacher" value="1" @checked(old('show_class_teacher', $settings->show_class_teacher)) class="rounded border-gray-300 text-gray-900">
                                    Show class teacher
                                </label>
                                <input type="text" name="class_teacher_title" value="{{ old('class_teacher_title', $settings->class_teacher_title) }}" placeholder="Class Teacher title" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <input type="text" name="class_teacher_name" value="{{ old('class_teacher_name', $settings->class_teacher_name) }}" placeholder="Class Teacher name" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <input type="file" name="class_teacher_signature_upload" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="show_head_teacher" value="1" @checked(old('show_head_teacher', $settings->show_head_teacher)) class="rounded border-gray-300 text-gray-900">
                                    Show head teacher
                                </label>
                                <input type="text" name="head_teacher_title" value="{{ old('head_teacher_title', $settings->head_teacher_title) }}" placeholder="Head Teacher title" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <input type="text" name="head_teacher_name" value="{{ old('head_teacher_name', $settings->head_teacher_name) }}" placeholder="Head Teacher name" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <input type="file" name="head_teacher_signature_upload" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Automated Comments</h3>
                        <div class="responsive-form-grid mt-5 gap-3">
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 text-sm font-medium text-gray-700">
                                <input type="checkbox" name="enable_auto_class_teacher_comment" value="1" @checked(old('enable_auto_class_teacher_comment', $settings->enable_auto_class_teacher_comment)) class="rounded border-gray-300 text-gray-900">
                                Auto class teacher comment
                            </label>
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 text-sm font-medium text-gray-700">
                                <input type="checkbox" name="enable_auto_head_teacher_comment" value="1" @checked(old('enable_auto_head_teacher_comment', $settings->enable_auto_head_teacher_comment)) class="rounded border-gray-300 text-gray-900">
                                Auto head teacher comment
                            </label>
                        </div>
                        <p class="mt-3 text-sm text-gray-500">Comment rules can be expanded later. PDF, QR, and full designer tools are available on selected plans.</p>
                    </section>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('school.dashboard') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                        <button type="submit" data-loading-text="Saving..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save Settings</button>
                    </div>
                </form>
            </div>

            <aside class="lg:col-span-1">
                @include('school.report-card-settings.partials.preview-card', ['reportCard' => $reportCard])
            </aside>
        </div>
    </div>
</x-app-layout>
