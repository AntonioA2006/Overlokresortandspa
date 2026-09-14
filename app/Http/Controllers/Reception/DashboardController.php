<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('reception.dashboard', [
            'hotelName' => config('overlook.hotel_name'),
        ]);
    }
}
