@extends('admissions.layout')

@section('title', 'Application status')

@section('content')
<section class="card">
    <div class="eyebrow">Verified application</div>
    <h1 style="font-size: clamp(28px, 4vw, 40px)">Application status</h1>
    <div class="details">
        <div class="detail"><strong>Application number</strong><span>{{ $application->application_number }}</span></div>
        <div class="detail"><strong>Applicant</strong><span>{{ $application->first_name }} {{ mb_substr($application->last_name, 0, 1) }}.</span></div>
        <div class="detail"><strong>Cycle</strong><span>{{ $application->cycle?->name }}</span></div>
        <div class="detail"><strong>Requested class</strong><span>{{ $application->requestedClass?->name ?? 'Not selected' }}</span></div>
        <div class="detail"><strong>Current status</strong><span>{{ str($application->status)->replace('_', ' ')->title() }}</span></div>
        <div class="detail"><strong>Payment status</strong><span>{{ str($application->payment_status)->replace('_', ' ')->title() }}</span></div>
    </div>
    @if($application->notes->isNotEmpty())
        <h2>School updates</h2>
        @foreach($application->notes as $note)<p class="notice">{{ $note->note }}</p>@endforeach
    @endif
    <p>For privacy, this page shows only the status details needed by the applicant or guardian.</p>
</section>
@endsection
