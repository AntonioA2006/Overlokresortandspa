<x-mail::message>
# {{ __('mail.reservation_cancelled_heading') }}

{{ __('mail.reservation_cancelled_intro', ['name' => $userName, 'hotel' => $hotelName, 'code' => $code]) }}

{{ __('mail.stay_details', ['room' => $roomName, 'check_in' => $checkIn, 'check_out' => $checkOut]) }}

<x-mail::button :url="$url">
{{ __('mail.view_reservation') }}
</x-mail::button>

{{ __('mail.reservation_cancelled_help') }}

{{ __('auth.reset_mail_regards') }},<br>
{{ $hotelName }}
</x-mail::message>
