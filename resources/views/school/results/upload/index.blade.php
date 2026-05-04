<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Class-Based Result Upload
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Select class, session, term, and result type before uploading scores for {{ $school->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('upload_error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                    {{ session('upload_error') }}
                </div>
            @endif

            @if (session('import_errors') && count(session('import_errors')) > 0)
                <div class="mb-6 rounded-xl bg-yellow-50 p-4 text-sm text-yellow-800">
                    <p class="font-semibold">Some rows were skipped:</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach (array_slice(session('import_errors'), 0, 20) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                    @if (count(session('import_errors')) > 20)
                        <p class="mt-2">
                            Showing first 20 errors only.
                        </p>
                    @endif
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">
                        Step 1 - Download Prepared Template
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Choose the class, session, term, and result type. The system will generate a CSV template with students and subjects already filled.
                    </p>

                    <form method="GET"
                          action="{{ route('school.results.upload.template') }}"
                          data-loading-text="Preparing template..."
                          class="mt-6 space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class</label>
                            <select name="school_class_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select class</option>
                                @foreach ($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(request('school_class_id') == $schoolClass->id)>
                                        {{ $schoolClass->name }} {{ $schoolClass->section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Session</label>
                            <select name="academic_session_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select session</option>
                                @foreach ($academicSessions as $academicSession)
                                    <option value="{{ $academicSession->id }}" @selected(request('academic_session_id') == $academicSession->id)>
                                        {{ $academicSession->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(request('term_id') == $term->id)>
                                        {{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Result Type</label>
                            <select name="result_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="term_result">Term Result</option>
                                <option value="assessment_result" disabled>Assessment / Test Result - Available on selected plans</option>
                                <option value="cbt_result" disabled>CBT Result - Available on selected plans</option>
                            </select>
                        </div>

                        <button type="submit"
                                data-loading-text="Preparing template..."
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Download Prepared CSV Template
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">
                        Step 2 - Upload Filled CSV
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Fill only CA score, exam score, status, and optional teacher remark in the downloaded template. Then upload the saved CSV here using the same class, session, term, and result type.
                    </p>

                    <form method="POST"
                          action="{{ route('school.results.upload.store') }}"
                          enctype="multipart/form-data"
                          data-loading-text="Uploading..."
                          class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class</label>
                            <select name="school_class_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select class</option>
                                @foreach ($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                                        {{ $schoolClass->name }} {{ $schoolClass->section }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_class_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Session</label>
                            <select name="academic_session_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select session</option>
                                @foreach ($academicSessions as $academicSession)
                                    <option value="{{ $academicSession->id }}" @selected(old('academic_session_id') == $academicSession->id)>
                                        {{ $academicSession->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Select term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>
                                        {{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Result Type</label>
                            <select name="result_type"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="term_result" @selected(old('result_type') === 'term_result')>Term Result</option>
                                <option value="assessment_result" disabled>Assessment / Test Result - Available on selected plans</option>
                                <option value="cbt_result" disabled>CBT Result - Available on selected plans</option>
                            </select>
                            @error('result_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Filled CSV File</label>
                            <input type="file"
                                   name="result_file"
                                   accept=".csv,.txt"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 p-3 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('result_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                data-loading-text="Uploading..."
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Upload Results
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">
                    Template Rules
                </h3>

                <div class="mt-4 grid gap-4 text-sm text-gray-600 md:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Columns generated by the system</p>
                        <p class="mt-2">
                            class_name, academic_session, term, result_type, admission_number, student_name, subject_code, subject_name
                        </p>
                        <p class="mt-2 text-xs text-gray-500">
                            These columns are for reference. Do not edit them.
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Columns the school should fill</p>
                        <p class="mt-2">ca_score, exam_score, status, teacher_remark</p>
                        <p class="mt-2 text-xs text-gray-500">
                            teacher_remark is optional and can be left blank.
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Score limits</p>
                        <p class="mt-2">CA score: 0-40. Exam score: 0-60.</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Allowed status</p>
                        <p class="mt-2">draft or reviewed.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
