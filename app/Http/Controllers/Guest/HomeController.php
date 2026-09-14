<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('guest.home', [
            'hotelName' => config('overlook.hotel_name'),
            'maxGuests' => (int) config('overlook.max_guests_per_search', 8),
            'roomTypes' => RoomType::query()
                ->where('is_active', true)
                ->orderBy('base_price_per_night')
                ->get(),
        ]);
    }
}
