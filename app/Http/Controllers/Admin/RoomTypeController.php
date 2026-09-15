<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomTypeRequest;
use App\Http\Requests\Admin\UpdateRoomTypeRequest;
use App\Models\RoomType;
use App\Services\AuditService;
use App\Services\RoomMediaService;
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

    public function create(): View
    {
        $this->authorize('create', RoomType::class);

        return view('admin.room-types.create', [
            'hotelName' => config('overlook.hotel_name'),
        ]);
    }

    public function store(
        StoreRoomTypeRequest $request,
        AuditService $auditService,
        RoomMediaService $roomMediaService,
    ): RedirectResponse {
        $roomType = RoomType::query()->create([
            ...$request->safe()->only([
                'name',
                'base_price_per_night',
                'max_guests',
                'is_active',
                'description',
            ]),
            'slug' => RoomType::uniqueSlug($request->validated('name')),
        ]);

        $this->syncCover($request, $roomType, $roomMediaService);

        $auditService->log(AuditAction::AdminChange, $roomType, [
            'entity' => 'room_type',
            'action' => 'created',
            'price' => $roomType->base_price_per_night,
        ]);

        return redirect()
            ->route('admin.room-types.index')
            ->with('status', __('admin.rates_created'));
    }

    public function edit(RoomType $roomType): View
    {
        $this->authorize('update', $roomType);

        return view('admin.room-types.edit', [
            'hotelName' => config('overlook.hotel_name'),
            'roomType' => $roomType,
        ]);
    }

    public function update(
        UpdateRoomTypeRequest $request,
        RoomType $roomType,
        AuditService $auditService,
        RoomMediaService $roomMediaService,
    ): RedirectResponse {
        $roomType->fill($request->safe()->only([
            'name',
            'base_price_per_night',
            'max_guests',
            'is_active',
            'description',
        ]));
        $roomType->save();

        $this->syncCover($request, $roomType, $roomMediaService);

        $auditService->log(AuditAction::AdminChange, $roomType, [
            'entity' => 'room_type',
            'action' => 'updated',
            'price' => $roomType->base_price_per_night,
        ]);

        return redirect()
            ->route('admin.room-types.index')
            ->with('status', __('admin.rates_updated'));
    }

    private function syncCover(
        StoreRoomTypeRequest|UpdateRoomTypeRequest $request,
        RoomType $roomType,
        RoomMediaService $roomMediaService,
    ): void {
        $cover = $request->file('cover');

        if ($cover === null) {
            return;
        }

        $roomMediaService->deletePublicPath($roomType->cover_path);
        $roomType->forceFill([
            'cover_path' => $roomMediaService->storePublicImage($cover, 'room-type-covers'),
        ])->save();
    }
}
