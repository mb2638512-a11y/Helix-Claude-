{{ Illuminate\Mail\Markdown::parse('---') }}

Thank you,<br>
{{ config('app.name') ?? 'Helix Claude' }}

{{ Illuminate\Mail\Markdown::parse('[Contact Support](https://Helix Claude.io/docs/contact)') }}
