@extends('admissions.layout')

@section('title', 'Admissions')

@section('content')
<section class="card">
    <div class="eyebrow">School admissions</div>
    <h1>Apply to {{ $school->name }}</h1>
    <p>Submit an admission application online and keep your tracking details for updates from the school.</p>

    @if($cycle)
        <div class="notice">
            <strong>{{ $cycle->name }} is open.</strong>
            @if($cycle->ends_at) Applications close {{ $cycle->ends_at->format('d M Y') }}. @endif
        </div>
        @if(data_get($cycle->settings, 'requirements'))
            <h2 style="margin-top: 28px">Application requirements</h2>
            <ul>
                @foreach(data_get($cycle->settings, 'requirements', []) as $requirement)
                    <li>{{ $requirement }}</li>
                @endforeach
            </ul>
        @endif
        <div class="actions">
            <a class="button" href="{{ route('admissions.apply') }}">Start application</a>
            <a class="button secondary" href="{{ route('admissions.track') }}">Track an application</a>
        </div>
    @else
        <div class="notice"><strong>Applications are currently closed.</strong> Please check again later or contact the school office for the next admission date.</div>
        <div class="actions">
            <a class="button secondary" href="{{ route('admissions.track') }}">Track an existing application</a>
        </div>
    @endif
</section>
@endsection
