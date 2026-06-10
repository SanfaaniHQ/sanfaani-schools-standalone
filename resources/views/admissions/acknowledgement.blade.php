@extends('admissions.layout')

@section('title', 'Application received')

@section('content')
<section class="card">
    <div class="eyebrow">Application received</div>
    <h1>Thank you, {{ $application->first_name }}.</h1>
    <p>Your application is now in the private school admission portal. Keep both values below; the tracking token is shown only on this acknowledgement.</p>
    <div class="details">
        <div class="detail"><strong>Application number</strong><span>{{ $application->application_number }}</span></div>
        <div><strong>Tracking token</strong><div class="token">{{ $trackingToken }}</div></div>
        <div class="detail"><strong>Status</strong><span>Submitted</span></div>
        <div class="detail"><strong>Online payment required</strong><span>No</span></div>
    </div>
    <div class="actions"><a class="button" href="{{ route('admissions.track') }}">Track application</a></div>
</section>
@endsection
