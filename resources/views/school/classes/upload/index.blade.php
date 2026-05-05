<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Upload Classes</h2>
                <p class="mt-1 text-sm text-gray-500">Create class records for {{ $school->name }} from a CSV file.</p>
            </div>
            <a href="{{ route('school.classes.index') }}"
               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Classes
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <form method="POST"
                  action="{{ route('school.classes.upload.store') }}"
                  enctype="multipart/form-data"
                  data-loading-text="Uploading..."
                  class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2">
                @csrf
                <h3 class="text-base font-semibold text-gray-900">CSV Upload</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Required column: <span class="font-medium">name</span>. Optional columns: <span class="font-medium">code</span>, <span class="font-medium">status</span>.
                </p>

                @if (session('import_errors'))
                    <div class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Upload was not saved. Fix these rows and try again.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <label class="mt-5 block text-sm font-medium text-gray-700">Class CSV</label>
                <input type="file" name="class_file" accept=".csv,text/csv"
                       class="mt-2 block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                @error('class_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <a href="{{ route('school.classes.upload.template') }}"
                       class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Download Template
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Upload Classes
                    </button>
                </div>
            </form>

            <div class="rounded-2xl bg-white p-6 text-sm text-gray-600 shadow-sm">
                <h3 class="font-semibold text-gray-900">Validation Rules</h3>
                <p class="mt-2">Class names must be unique within the school. If validation finds row-level errors, no records are created.</p>
                <p class="mt-3">Accepted status values are active and inactive. Blank status becomes active.</p>
            </div>
        </div>
    </div>
</x-app-layout>
