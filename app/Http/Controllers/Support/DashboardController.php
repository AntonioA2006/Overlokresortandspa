<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('support.dashboard', [
            'hotelName' => config('overlook.hotel_name'),
        ]);
    }
}
