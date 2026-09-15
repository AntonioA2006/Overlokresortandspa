<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRoomTypeRequest;
use App\Models\RoomType;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', RoomType::class);

        return view('admin.room-types.index', [
            'hotelName' => config('overlook.hotel_name'),
            'roomTypes' => RoomType::query()
                ->withCount('rooms')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(RoomType $roomType): View
    {
        $this->authorize('update', $roomType);

        return view('admin.room-types.edit', [
            'hotelName' => config('overlook.hotel_name'),
            'roomType' => $roomType,
        ]);
    }

    public function update(UpdateRoomTypeRequest $request, RoomType $roomType, AuditService $auditService): RedirectResponse
    {
        $roomType->fill($request->safe()->only([
            'name',
            'base_price_per_night',
            'max_guests',
            'is_active',
            'description',
        ]));
        $roomType->save();

        $auditService->log(AuditAction::AdminChange, $roomType, [
            'entity' => 'room_type',
            'action' => 'updated',
            'price' => $roomType->base_price_per_night,
        ]);

        return redirect()
            ->route('admin.room-types.index')
            ->with('status', __('admin.rates_updated'));
    }
}
