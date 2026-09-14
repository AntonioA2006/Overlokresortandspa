<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchAvailabilityRequest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use App\Support\ReservationPresentation;
use App\Support\RoomTypePresentation;
use Carbon\Carbon;
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

    public function showRoom(RoomType $roomType): View
    {
        abort_unless($roomType->is_active, 404);

        return view('guest.reservations.rooms.show', [
            'roomType' => $roomType,
        ]);
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
