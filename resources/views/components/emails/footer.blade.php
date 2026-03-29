{{ Illuminate\Mail\Markdown::parse('---') }}

Thank you,<br>
{{ config('app.name') ?? 'HelixClaude' }}

{{ Illuminate\Mail\Markdown::parse('[Contact Support](https://HelixClaude.io/docs/contact)') }}
