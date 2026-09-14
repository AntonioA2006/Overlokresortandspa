<?php

namespace App\Http\Controllers\Guest;

use App\Enums\ReservationStatus;
use App\Exceptions\ReservationCancellationException;
use App\Exceptions\ReservationUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\SearchAvailabilityRequest;
use App\Http\Requests\ShowRoomRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use App\Services\ReservationService;
use App\Services\ReservationTokenService;
use App\Support\ReservationPresentation;
use App\Support\RoomTypePresentation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function search(): View
    {
        return view('guest.reservations.search', [
            'hotelName' => config('overlook.hotel_name'),
            'maxGuests' => (int) config('overlook.max_guests_per_search', 8),
        ]);
    }

    public function results(
        SearchAvailabilityRequest $request,
        AvailabilityService $availabilityService,
        PricingService $pricingService,
    ): View {
        $checkIn = Carbon::parse($request->input('check_in_date'))->startOfDay();
        $checkOut = Carbon::parse($request->input('check_out_date'))->startOfDay();
        $adults = (int) $request->input('adults');
        $children = (int) $request->input('children', 0);
        $searchQuery = $request->only(['check_in_date', 'check_out_date', 'adults', 'children']);

        $resultGroups = $this->presentResultGroups(
            $availabilityService->availableRoomsGroupedByType(
                $checkIn,
                $checkOut,
                $request->guestsCount(),
            ),
            $checkIn,
            $checkOut,
            $searchQuery,
            $pricingService,
        );

        return view('guest.reservations.results', [
            'hotelName' => config('overlook.hotel_name'),
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'adults' => $adults,
            'children' => $children,
            'guestsCount' => $request->guestsCount(),
            'dateSummary' => ReservationPresentation::dateRangeSummary($checkIn, $checkOut),
            'guestSummary' => ReservationPresentation::guestsSummary($adults, $children),
            'resultGroups' => $resultGroups,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function showRoom(
        ShowRoomRequest $request,
        RoomType $roomType,
        AvailabilityService $availabilityService,
        PricingService $pricingService,
    ): View {
        abort_unless($roomType->is_active, 404);

        $roomType->loadMissing(['amenities', 'rooms.photos']);

        $hasSearchContext = $request->hasSearchContext();
        $searchQuery = [];
        $dateSummary = null;
        $guestSummary = null;
        $guestsCount = 0;
        $availableRooms = collect();
        $selectedRoom = null;
        $pricing = null;
        $isAvailable = false;
        $unavailableReason = null;
        $reserveUrl = null;

        if ($hasSearchContext) {
            $checkIn = Carbon::parse($request->input('check_in_date'))->startOfDay();
            $checkOut = Carbon::parse($request->input('check_out_date'))->startOfDay();
            $adults = (int) $request->input('adults');
            $children = (int) $request->input('children', 0);
            $guestsCount = $request->guestsCount();
            $searchQuery = $request->only(['check_in_date', 'check_out_date', 'adults', 'children']);
            $dateSummary = ReservationPresentation::dateRangeSummary($checkIn, $checkOut);
            $guestSummary = ReservationPresentation::guestsSummary($adults, $children);

            if ($guestsCount > $roomType->max_guests) {
                $unavailableReason = 'capacity';
            } else {
                $availableRooms = $availabilityService->availableRoomsForType(
                    $roomType,
                    $checkIn,
                    $checkOut,
                    $guestsCount,
                );
                $isAvailable = $availableRooms->isNotEmpty();

                if (! $isAvailable) {
                    $unavailableReason = 'dates';
                }
            }

            if ($isAvailable) {
                /** @var Room $selectedRoom */
                $selectedRoom = $availableRooms->first();
                $pricing = $pricingService->calculateTotal($selectedRoom, $checkIn, $checkOut);
                $reserveUrl = route('guest.reservations.checkout', array_merge(
                    ['room' => $selectedRoom->id],
                    $searchQuery,
                ));
            }
        }

        $galleryRooms = $availableRooms->isNotEmpty()
            ? $availableRooms
            : $roomType->rooms;

        return view('guest.reservations.rooms.show', [
            'roomType' => $roomType,
            'gallery' => RoomTypePresentation::resolveGallery($galleryRooms, $roomType),
            'hasSearchContext' => $hasSearchContext,
            'searchQuery' => $searchQuery,
            'dateSummary' => $dateSummary,
            'guestSummary' => $guestSummary,
            'guestsCount' => $guestsCount,
            'pricing' => $pricing,
            'isAvailable' => $isAvailable,
            'unavailableReason' => $unavailableReason,
            'reserveUrl' => $reserveUrl,
            'resultsUrl' => $hasSearchContext
                ? route('guest.reservations.results', $searchQuery)
                : route('guest.reservations.search'),
        ]);
    }

    public function index(Request $request, ReservationService $reservationService): View
    {
        $this->authorize('viewAny', Reservation::class);

        $groups = $reservationService->groupedForUser($request->user());

        return view('guest.reservations.index', [
            'currentReservations' => $groups['current'],
            'upcomingReservations' => $groups['upcoming'],
            'pastReservations' => $groups['past'],
        ]);
    }

    public function checkout(
        CheckoutRequest $request,
        Room $room,
        AvailabilityService $availabilityService,
        PricingService $pricingService,
        ReservationService $reservationService,
    ): View|RedirectResponse {
        $this->authorize('create', Reservation::class);

        $room->loadMissing(['roomType.amenities', 'photos']);
        abort_unless($room->roomType?->is_active, 404);

        $checkIn = Carbon::parse($request->input('check_in_date'))->startOfDay();
        $checkOut = Carbon::parse($request->input('check_out_date'))->startOfDay();
        $adults = (int) $request->input('adults');
        $children = (int) $request->input('children', 0);
        $guestsCount = $request->guestsCount();
        $searchQuery = $request->only(['check_in_date', 'check_out_date', 'adults', 'children']);

        if ($guestsCount > $room->roomType->max_guests
            || ! $availabilityService->isRoomAvailable($room, $checkIn, $checkOut, $guestsCount)) {
            return redirect()
                ->route('guest.reservations.rooms.show', array_merge(
                    ['roomType' => $room->roomType->slug],
                    $searchQuery,
                ))
                ->with('reservation_error', __('reservations.errors.unavailable'));
        }

        $pricing = $pricingService->calculateTotal($room, $checkIn, $checkOut);
        $idempotencyKey = $reservationService->buildIdempotencyKey(
            $request->user(),
            $room,
            $checkIn,
            $checkOut,
            $guestsCount,
        );

        $photo = RoomTypePresentation::resolvePhoto(collect([$room]), $room->roomType)
            ?? RoomTypePresentation::fallbackPhoto($room->roomType);

        return view('guest.reservations.checkout', [
            'room' => $room,
            'roomType' => $room->roomType,
            'photo' => $photo,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'adults' => $adults,
            'children' => $children,
            'guestsCount' => $guestsCount,
            'dateSummary' => ReservationPresentation::dateRangeSummary($checkIn, $checkOut),
            'guestSummary' => ReservationPresentation::guestsSummary($adults, $children),
            'pricing' => $pricing,
            'searchQuery' => $searchQuery,
            'idempotencyKey' => $idempotencyKey,
            'roomShowUrl' => route('guest.reservations.rooms.show', array_merge(
                ['roomType' => $room->roomType->slug],
                $searchQuery,
            )),
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        ReservationService $reservationService,
    ): RedirectResponse {
        $room = Room::query()->findOrFail($request->integer('room_id'));
        $checkIn = Carbon::parse($request->input('check_in_date'))->startOfDay();
        $checkOut = Carbon::parse($request->input('check_out_date'))->startOfDay();
        $searchQuery = $request->only(['check_in_date', 'check_out_date', 'adults', 'children']);

        try {
            $reservation = $reservationService->create(
                $request->user(),
                $room,
                $checkIn,
                $checkOut,
                $request->guestsCount(),
                $request->string('idempotency_key')->toString(),
            );
        } catch (ReservationUnavailableException) {
            return redirect()
                ->route('guest.reservations.checkout', array_merge(['room' => $room->id], $searchQuery))
                ->with('reservation_error', __('reservations.errors.unavailable'));
        }

        return redirect()
            ->route('guest.reservations.show', $reservation)
            ->with('status', __('reservations.created_confirmed'));
    }

    public function show(
        Reservation $reservation,
        ReservationTokenService $tokenService,
    ): View {
        $this->authorize('view', $reservation);

        $reservation->loadMissing(['room.roomType.amenities']);

        $photo = RoomTypePresentation::resolvePhoto(collect([$reservation->room]), $reservation->room->roomType)
            ?? RoomTypePresentation::fallbackPhoto($reservation->room->roomType);

        $checkInUrl = null;

        if (in_array($reservation->status, [ReservationStatus::Confirmed, ReservationStatus::CheckedIn], true)) {
            if ($reservation->isTokenActive()) {
                $checkInUrl = $tokenService->buildCheckUrl($reservation->check_in_token);
            } elseif ($reservation->status === ReservationStatus::Confirmed) {
                $token = $tokenService->ensureActiveToken($reservation);
                $checkInUrl = $tokenService->buildCheckUrl($token);
            }
        }

        return view('guest.reservations.show', [
            'reservation' => $reservation,
            'roomType' => $reservation->room->roomType,
            'photo' => $photo,
            'checkInUrl' => $checkInUrl,
            'dateSummary' => ReservationPresentation::dateRangeSummary(
                $reservation->check_in_date,
                $reservation->check_out_date,
            ),
            'guestSummary' => ReservationPresentation::guestsCountSummary($reservation),
        ]);
    }

    public function cancel(
        Request $request,
        Reservation $reservation,
        ReservationService $reservationService,
    ): RedirectResponse {
        $this->authorize('cancel', $reservation);

        try {
            $reservationService->cancel($request->user(), $reservation);
        } catch (ReservationCancellationException) {
            return back()->with('reservation_error', __('reservations.errors.cancel_not_allowed'));
        }

        return redirect()
            ->route('guest.reservations.show', $reservation)
            ->with('status', __('reservations.cancelled_success'));
    }

    /**
     * @param  Collection<int, array{room_type: RoomType, available_count: int, rooms: Collection<int, Room>}>  $groups
     * @param  array<string, mixed>  $searchQuery
     * @return Collection<int, array<string, mixed>>
     */
    private function presentResultGroups(
        Collection $groups,
        Carbon $checkIn,
        Carbon $checkOut,
        array $searchQuery,
        PricingService $pricingService,
    ): Collection {
        return $groups->map(function (array $group) use ($checkIn, $checkOut, $searchQuery, $pricingService): array {
            /** @var Room $room */
            $room = $group['rooms']->first();
            $roomType = $group['room_type'];
            $roomType->loadMissing('amenities');
            $pricing = $pricingService->calculateTotal($room, $checkIn, $checkOut);
            $photo = RoomTypePresentation::resolvePhoto($group['rooms'], $roomType)
                ?? RoomTypePresentation::fallbackPhoto($roomType);

            return [
                'room_type' => $roomType,
                'available_count' => $group['available_count'],
                'rooms' => $group['rooms'],
                'pricing' => $pricing,
                'photo' => $photo,
                'featured_amenities' => $roomType->amenities->take(4),
                'show_url' => route('guest.reservations.rooms.show', array_merge(
                    ['roomType' => $roomType->slug],
                    $searchQuery,
                )),
            ];
        });
    }
}
