<x-mail::message>
# {{ __('auth.verify_mail_heading', ['hotel' => $hotelName]) }}

{{ __('auth.verify_mail_intro', ['name' => $userName, 'hotel' => $hotelName]) }}

<x-mail::button :url="$url">
{{ __('auth.verify_mail_action') }}
</x-mail::button>

{{ __('auth.verify_mail_ignore') }}

{{ __('auth.reset_mail_regards') }},<br>
{{ $hotelName }}
</x-mail::message>
