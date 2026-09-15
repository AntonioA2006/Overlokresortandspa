<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Http\Requests\Admin\UpdateRoomStatusRequest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AuditService;
use App\Services\RoomMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Room::class);

        return view('admin.rooms.index', [
            'hotelName' => config('overlook.hotel_name'),
            'rooms' => Room::query()
                ->with('roomType')
                ->orderBy('number')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Room::class);

        return view('admin.rooms.create', [
            'hotelName' => config('overlook.hotel_name'),
            'roomTypes' => RoomType::query()->orderBy('name')->get(),
            'statuses' => RoomStatus::cases(),
        ]);
    }

    public function store(StoreRoomRequest $request, AuditService $auditService, RoomMediaService $roomMediaService): RedirectResponse
    {
        $room = Room::query()->create($request->safe()->only([
            'room_type_id',
            'number',
            'floor',
            'status',
            'description',
        ]));

        $this->storePhoto($request, $room, $roomMediaService);

        $auditService->log(AuditAction::AdminChange, $room, [
            'entity' => 'room',
            'action' => 'created',
            'number' => $room->number,
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('status', __('admin.rooms_created'));
    }

    public function edit(Room $room): View
    {
        $this->authorize('update', $room);

        return view('admin.rooms.edit', [
            'hotelName' => config('overlook.hotel_name'),
            'room' => $room->load('photos'),
            'roomTypes' => RoomType::query()->orderBy('name')->get(),
            'statuses' => RoomStatus::cases(),
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room, AuditService $auditService, RoomMediaService $roomMediaService): RedirectResponse
    {
        $room->fill($request->safe()->only([
            'room_type_id',
            'number',
            'floor',
            'status',
            'description',
        ]));
        $room->save();

        $this->storePhoto($request, $room, $roomMediaService);

        $auditService->log(AuditAction::AdminChange, $room, [
            'entity' => 'room',
            'action' => 'updated',
            'number' => $room->number,
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('status', __('admin.rooms_updated'));
    }

    public function updateStatus(UpdateRoomStatusRequest $request, Room $room, AuditService $auditService): RedirectResponse
    {
        $room->forceFill([
            'status' => $request->enum('status', RoomStatus::class),
        ])->save();

        $auditService->log(AuditAction::AdminChange, $room, [
            'entity' => 'room',
            'action' => 'status',
            'status' => $room->status->value,
        ]);

        return back()->with('status', __('admin.rooms_status_updated'));
    }

    private function storePhoto(
        StoreRoomRequest|UpdateRoomRequest $request,
        Room $room,
        RoomMediaService $roomMediaService,
    ): void {
        $photo = $request->file('photo');

        if ($photo === null) {
            return;
        }

        $room->loadMissing('roomType');

        $hasPrimary = $room->photos()->where('is_primary', true)->exists();

        $room->photos()->create([
            'path' => $roomMediaService->storePublicImage($photo, 'room-photos'),
            'alt_text' => __('reservations.photo_alt', ['name' => $room->roomType?->name ?? $room->number]),
            'sort_order' => (int) $room->photos()->max('sort_order') + 1,
            'is_primary' => ! $hasPrimary,
        ]);
    }
}
