<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Services\CheckInService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(CheckInService $checkInService): View
    {
        return view('reception.dashboard', [
            'hotelName' => config('overlook.hotel_name'),
            'arrivals' => $checkInService->arrivalsForDate(now()),
        ]);
    }
}
