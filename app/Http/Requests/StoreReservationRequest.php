<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use App\Models\Room;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Reservation::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxGuests = (int) config('overlook.max_guests_per_search', 8);

        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:'.$maxGuests],
            'children' => ['nullable', 'integer', 'min:0', 'max:'.$maxGuests],
            'idempotency_key' => ['required', 'string', 'max:128'],
            'price_total' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $adults = (int) $this->input('adults', 0);
            $children = (int) $this->input('children', 0);
            $totalGuests = $adults + $children;
            $maxGuests = (int) config('overlook.max_guests_per_search', 8);

            if ($totalGuests < 1) {
                $validator->errors()->add('adults', __('reservations.validation.guests_min'));
            }

            if ($totalGuests > $maxGuests) {
                $validator->errors()->add(
                    'adults',
                    __('reservations.validation.guests_max', ['max' => $maxGuests]),
                );
            }

            $room = Room::query()->with('roomType')->find($this->input('room_id'));

            if ($room === null || $room->roomType === null) {
                return;
            }

            if ($totalGuests > $room->roomType->max_guests) {
                $validator->errors()->add('adults', __('reservations.validation.room_capacity_exceeded', [
                    'count' => $room->roomType->max_guests,
                ]));
            }

            $checkIn = Carbon::parse($this->input('check_in_date'))->startOfDay();
            $checkOut = Carbon::parse($this->input('check_out_date'))->startOfDay();

            $expectedKey = app(ReservationService::class)->buildIdempotencyKey(
                $this->user(),
                $room,
                $checkIn,
                $checkOut,
                $totalGuests,
            );

            if ($this->input('idempotency_key') !== $expectedKey) {
                $validator->errors()->add('idempotency_key', __('reservations.validation.idempotency_invalid'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'check_in_date.required' => __('reservations.validation.check_in_required'),
            'check_in_date.after_or_equal' => __('reservations.validation.check_in_past'),
            'check_out_date.required' => __('reservations.validation.check_out_required'),
            'check_out_date.after' => __('reservations.validation.check_out_after_check_in'),
            'adults.required' => __('reservations.validation.adults_required'),
            'adults.min' => __('reservations.validation.adults_min'),
            'children.min' => __('reservations.validation.children_min'),
            'room_id.required' => __('reservations.validation.room_required'),
            'room_id.exists' => __('reservations.validation.room_invalid'),
            'idempotency_key.required' => __('reservations.validation.idempotency_required'),
            'price_total.prohibited' => __('reservations.validation.price_prohibited'),
        ];
    }

    public function guestsCount(): int
    {
        return (int) $this->input('adults') + (int) $this->input('children', 0);
    }
}
