<x-mail::message>
# {{ __('auth.reset_mail_heading', ['hotel' => $hotelName]) }}

{{ __('auth.reset_mail_intro', ['name' => $userName, 'hotel' => $hotelName]) }}

<x-mail::button :url="$url">
{{ __('auth.reset_password') }}
</x-mail::button>

{{ __('auth.reset_mail_expire', ['count' => $expire]) }}

{{ __('auth.reset_mail_ignore') }}

{{ __('auth.reset_mail_regards') }},<br>
{{ $hotelName }}
</x-mail::message>
