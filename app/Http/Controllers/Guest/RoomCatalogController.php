<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Support\RoomTypePresentation;
use Illuminate\View\View;

class RoomCatalogController extends Controller
{
    public function index(): View
    {
        $roomTypes = RoomType::query()
            ->where('is_active', true)
            ->with(['amenities', 'rooms.photos'])
            ->orderBy('base_price_per_night')
            ->get()
            ->map(function (RoomType $roomType): array {
                return [
                    'room_type' => $roomType,
                    'photo' => RoomTypePresentation::resolvePhoto($roomType->rooms, $roomType)
                        ?? RoomTypePresentation::fallbackPhoto($roomType),
                    'show_url' => route('guest.reservations.rooms.show', $roomType),
                ];
            });

        return view('guest.rooms.index', [
            'hotelName' => config('overlook.hotel_name'),
            'roomTypes' => $roomTypes,
        ]);
    }
}
