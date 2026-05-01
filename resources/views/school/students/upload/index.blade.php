<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Student Bulk Upload
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Upload students into a selected class for {{ $school->name }}.
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
                        Step 1 — Download Student Template
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Select the class first. The downloaded CSV template will include the selected class as reference.
                    </p>

                    <form method="GET"
                          action="{{ route('school.students.upload.template') }}"
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

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Download Student CSV Template
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">
                        Step 2 — Upload Filled Student CSV
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Upload the filled CSV using the same class. The selected class will be used as the real class assignment.
                    </p>

                    <form method="POST"
                          action="{{ route('school.students.upload.store') }}"
                          enctype="multipart/form-data"
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
                            <label class="block text-sm font-medium text-gray-700">Filled CSV File</label>
                            <input type="file"
                                   name="student_file"
                                   accept=".csv,.txt"
                                   class="mt-1 block w-full rounded-xl border border-gray-300 p-3 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('student_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Upload Students
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
                        <p class="font-medium text-gray-900">System reference column</p>
                        <p class="mt-2">class_name</p>
                        <p class="mt-2 text-xs text-gray-500">
                            This is for reference only. The selected class on the upload form is the real class assignment.
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Required columns</p>
                        <p class="mt-2">admission_number, first_name, last_name, gender, status</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Optional columns</p>
                        <p class="mt-2">middle_name, date_of_birth, guardian_name, guardian_phone, guardian_email, address</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Accepted values</p>
                        <p class="mt-2">gender: male or female. status: active or inactive. date_of_birth: YYYY-MM-DD.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>