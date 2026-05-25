<p>Hello {{ $lead->name ?: 'there' }},</p>
<p>Your trial is a good time to review school setup, role access, result workflows, and communication tools.</p>
<p><a href="{{ config('sanfaani.product_url') }}">Continue exploring</a></p>
<p style="font-size: 12px; color: #666;">You can unsubscribe from marketing follow-up here: <a href="{{ $unsubscribeUrl }}">unsubscribe</a>.</p>
