@extends('admissions.layout')

@section('title', 'Track application')

@section('content')
<section class="card">
    <div class="eyebrow">Private status check</div>
    <h1 style="font-size: clamp(28px, 4vw, 40px)">Track your application</h1>
    <p>Enter the application number and either the tracking token or the guardian phone number used during submission.</p>
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admissions.track.submit') }}" class="grid" style="margin-top:24px">
        @csrf
        <div class="field full"><label for="application_number">Application number *</label><input id="application_number" name="application_number" value="{{ old('application_number') }}" required></div>
        <div class="field"><label for="tracking_token">Tracking token</label><input id="tracking_token" name="tracking_token"></div>
        <div class="field"><label for="guardian_phone">Guardian phone</label><input id="guardian_phone" name="guardian_phone"></div>
        <div class="field full"><button class="button" type="submit">Check status</button></div>
    </form>
</section>
@endsection
