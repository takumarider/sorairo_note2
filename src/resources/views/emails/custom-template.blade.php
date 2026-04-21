@component('mail::message')
{!! \Illuminate\Mail\Markdown::parse($body) !!}
@endcomponent
