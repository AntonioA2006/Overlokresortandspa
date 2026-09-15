<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'hotelName' => config('overlook.hotel_name'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'hotelName' => config('overlook.hotel_name'),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request, AuditService $auditService): RedirectResponse
    {
        $user = User::query()->create([
            ...$request->safe()->only(['name', 'email', 'phone']),
            'password' => $request->validated('password'),
            'role' => $request->enum('role', UserRole::class),
        ]);

        $auditService->log(AuditAction::AdminChange, $user, [
            'entity' => 'user',
            'action' => 'created',
            'role' => $user->role->value,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('admin.users_created'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'hotelName' => config('overlook.hotel_name'),
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, AuditService $auditService): RedirectResponse
    {
        $user->fill($request->safe()->only(['name', 'email', 'phone']));
        $user->role = $request->enum('role', UserRole::class);
        $user->save();

        $auditService->log(AuditAction::AdminChange, $user, [
            'entity' => 'user',
            'action' => 'updated',
            'role' => $user->role->value,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('admin.users_updated'));
    }
}
