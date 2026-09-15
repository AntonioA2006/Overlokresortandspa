<x-mail::message>
# {{ __('mail.room_delivery_heading') }}

{{ __('mail.room_delivery_intro', ['name' => $userName, 'hotel' => $hotelName, 'hours' => $hours, 'time' => $time]) }}

{{ __('mail.room_delivery_details', ['code' => $code, 'room' => $roomName, 'check_in' => $checkIn]) }}

<x-mail::button :url="$url">
{{ __('mail.view_reservation') }}
</x-mail::button>

{{ __('auth.reset_mail_regards') }},<br>
{{ $hotelName }}
</x-mail::message>
