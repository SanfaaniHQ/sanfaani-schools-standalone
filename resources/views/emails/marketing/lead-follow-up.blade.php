<p>Hello {{ $lead->name ?: 'there' }},</p>
<p>Thank you for your interest in {{ config('app.name', 'Sanfaani Schools') }}. Our team can help you review the right setup path for your school.</p>
<p><a href="{{ config('sanfaani.product_url') }}">Review Sanfaani Schools</a></p>
<p style="font-size: 12px; color: #666;">You can unsubscribe from marketing follow-up here: <a href="{{ $unsubscribeUrl }}">unsubscribe</a>.</p>
@include('emails.partials.brand-footer')
