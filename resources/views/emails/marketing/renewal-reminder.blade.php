<p>Hello {{ $lead->name ?: 'there' }},</p>
<p>This is a reminder to review your Sanfaani Schools renewal path before service access is affected.</p>
<p><a href="{{ config('sanfaani.product_url') }}">Review renewal options</a></p>
<p style="font-size: 12px; color: #666;">You can unsubscribe from marketing follow-up here: <a href="{{ $unsubscribeUrl }}">unsubscribe</a>.</p>
