@php
    use App\Enums\ReservationStatus;
    use App\Support\ReservationPresentation;
    use App\Support\RoomTypePresentation;

    $reservation->loadMissing(['room.roomType', 'room.photos']);
    $roomType = $reservation->room->roomType;
    $photo = RoomTypePresentation::resolvePhoto(collect([$reservation->room]), $roomType)
        ?? RoomTypePresentation::fallbackPhoto($roomType);
    $showUrl = route('guest.reservations.show', $reservation);
    $hasQr = in_array($reservation->status, [ReservationStatus::Confirmed, ReservationStatus::CheckedIn], true)
        && $reservation->isTokenActive();
@endphp

<article class="my-stays-card" data-reveal>
    <a href="{{ $showUrl }}" class="my-stays-card__media" tabindex="-1" aria-hidden="true">
        <img
            src="{{ $photo['url'] }}"
            alt=""
            width="640"
            height="480"
            loading="lazy"
            decoding="async"
        >
    </a>

    <div class="my-stays-card__body">
        <div class="my-stays-card__header">
            <div class="my-stays-card__heading">
                <h3 class="my-stays-card__title">
                    <a href="{{ $showUrl }}">{{ $roomType->name }}</a>
                </h3>
                <p class="my-stays-card__code">{{ __('reservations.reservation_code', ['code' => $reservation->code]) }}</p>
            </div>

            <span @class(['badge', ReservationPresentation::statusBadgeClass($reservation->status)])>
                {{ ReservationPresentation::statusLabel($reservation->status) }}
            </span>
        </div>

        <dl class="my-stays-card__meta">
            <div class="my-stays-card__meta-item">
                <dt>{{ __('reservations.your_stay') }}</dt>
                <dd>{{ ReservationPresentation::dateRangeSummary($reservation->check_in_date, $reservation->check_out_date) }}</dd>
            </div>
            <div class="my-stays-card__meta-item">
                <dt>{{ __('reservations.guests_field') }}</dt>
                <dd>{{ ReservationPresentation::guestsCountSummary($reservation) }}</dd>
            </div>
            <div class="my-stays-card__meta-item">
                <dt>{{ __('reservations.total_label') }}</dt>
                <dd>
                    ${{ number_format((float) $reservation->price_total, 0, '.', ',') }}
                    {{ config('overlook.currency', 'MXN') }}
                </dd>
            </div>
        </dl>

        <div class="my-stays-card__actions">
            <x-button href="{{ $showUrl }}" variant="secondary" size="small">
                {{ __('reservations.view_details') }}
            </x-button>

            @if ($hasQr)
                <a href="{{ $showUrl }}#reservation-qr" class="link-cta my-stays-card__qr-link">
                    {{ __('reservations.show_qr') }}
                </a>
            @endif
        </div>
    </div>
</article>
