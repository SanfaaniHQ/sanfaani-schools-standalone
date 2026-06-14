@extends('admissions.layout')

@section('title', 'Apply')

@section('content')
<section class="card">
    <div class="eyebrow">Admission application</div>
    <h1 style="font-size: clamp(28px, 4vw, 40px)">Applicant details</h1>
    <p>Complete the applicant and parent or guardian details below. Fields marked with an asterisk are required. Uploaded documents are visible only to authorized school staff.</p>

    @if(!$cycle)
        <div class="notice"><strong>Applications are currently closed.</strong></div>
    @else
        @if($errors->any())
            <div class="error">
                <strong>Please correct the highlighted information.</strong>
                <ul>
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admissions.store') }}" enctype="multipart/form-data" data-loading-text="Submitting application..." class="grid" style="margin-top: 24px">
            @csrf
            <input type="hidden" name="source_channel" value="{{ $sourceChannel }}">
            <input type="hidden" name="{{ config('admissions.form_timestamp_field', 'admission_started_at') }}" value="{{ now()->timestamp }}">
            <input type="text" name="{{ config('admissions.honeypot_field', 'admission_website') }}" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">

            <div class="field"><label for="first_name">First name *</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required></div>
            <div class="field"><label for="last_name">Last name *</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required></div>
            <div class="field full"><label for="other_names">Other names</label><input id="other_names" name="other_names" value="{{ old('other_names') }}"></div>
            <div class="field"><label for="gender">Gender</label><select id="gender" name="gender"><option value="">Select</option><option value="male" @selected(old('gender') === 'male')>Male</option><option value="female" @selected(old('gender') === 'female')>Female</option><option value="other" @selected(old('gender') === 'other')>Other</option></select></div>
            <div class="field"><label for="date_of_birth">Date of birth</label><input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"></div>
            <div class="field"><label for="requested_class_id">Requested class</label><select id="requested_class_id" name="requested_class_id"><option value="">Select class</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string) old('requested_class_id') === (string) $class->id)>{{ $class->name }}{{ $class->section ? ' - '.$class->section : '' }}</option>@endforeach</select></div>
            <div class="field"><label for="previous_school">Previous school</label><input id="previous_school" name="previous_school" value="{{ old('previous_school') }}"></div>

            <div class="field full"><h2 style="margin: 16px 0 0">Parent or guardian</h2></div>
            <div class="field"><label for="guardian_name">Parent or guardian name *</label><input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required></div>
            <div class="field"><label for="guardian_relationship">Relationship *</label><input id="guardian_relationship" name="guardian_relationship" value="{{ old('guardian_relationship') }}" required></div>
            <div class="field"><label for="guardian_phone">Phone *</label><input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}" required></div>
            <div class="field"><label for="guardian_email">Email</label><input id="guardian_email" type="email" name="guardian_email" value="{{ old('guardian_email') }}"></div>
            <div class="field full"><label for="guardian_address">Address</label><textarea id="guardian_address" name="guardian_address" rows="3">{{ old('guardian_address') }}</textarea></div>

            @if(config('admissions.allow_document_uploads'))
                <div class="field full">
                    <label for="documents">Supporting documents</label>
                    <input id="documents" type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                    <small>PDF, JPG, JPEG, or PNG. Maximum {{ config('admissions.max_upload_mb', 5) }} MB per file.</small>
                </div>
            @endif

            <div class="field full">
                <label style="display:flex;align-items:flex-start;gap:10px;font-weight:500">
                    <input type="checkbox" name="consent" value="1" style="width:auto;margin-top:4px" required>
                    I consent to the school using this information solely to process this admission application.
                </label>
            </div>
            <div class="field full"><button class="button" type="submit" data-loading-text="Submitting application...">Submit application</button></div>
        </form>
    @endif
</section>
@endsection
