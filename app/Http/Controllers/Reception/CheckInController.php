<?php

namespace App\Http\Controllers\Reception;

use App\Exceptions\CheckInException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteCheckInRequest;
use App\Http\Requests\LookupReservationRequest;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function scan(): View
    {
        return view('reception.scan', [
            'hotelName' => config('overlook.hotel_name'),
        ]);
    }

    public function lookup(
        LookupReservationRequest $request,
        CheckInService $checkInService,
    ): RedirectResponse {
        $lookup = $checkInService->normalizeLookup($request->validated('lookup'));

        try {
            $checkInService->findForReception($lookup);
        } catch (CheckInException $exception) {
            return back()
                ->withInput()
                ->with('reception_error', $exception->getMessage());
        }

        return redirect()->route('reception.check', $lookup);
    }

    public function show(
        Request $request,
        string $token,
        CheckInService $checkInService,
    ): View|RedirectResponse {
        $lookup = $checkInService->normalizeLookup($token);

        try {
            $reservation = $checkInService->findForReception($lookup);
        } catch (CheckInException $exception) {
            return $this->failedLookupRedirect($exception->getMessage());
        }

        $this->authorize('checkIn', $reservation);

        $foundByToken = $reservation->isTokenActive()
            && hash_equals((string) $reservation->check_in_token, $lookup);

        if ($foundByToken) {
            $checkInService->recordScan($request->user(), $reservation);
            $reservation->refresh()->loadMissing(['user', 'room.roomType', 'checkIn']);
        }

        return view('reception.check', [
            'hotelName' => config('overlook.hotel_name'),
            'reservation' => $reservation,
            'checkIn' => $reservation->checkIn,
            'token' => $lookup,
        ]);
    }

    public function verify(
        Request $request,
        string $token,
        CheckInService $checkInService,
    ): RedirectResponse {
        try {
            $reservation = $checkInService->findForReception($token);
        } catch (CheckInException $exception) {
            return $this->failedLookupRedirect($exception->getMessage());
        }

        $this->authorize('checkIn', $reservation);

        try {
            $checkInService->verifyIdentity($request->user(), $reservation);
        } catch (CheckInException $exception) {
            return back()->with('reception_error', $exception->getMessage());
        }

        return back()->with('status', __('reception.identity_verified'));
    }

    public function complete(
        CompleteCheckInRequest $request,
        string $token,
        CheckInService $checkInService,
    ): RedirectResponse {
        try {
            $reservation = $checkInService->findForReception($token);
        } catch (CheckInException $exception) {
            return $this->failedLookupRedirect($exception->getMessage());
        }

        $this->authorize('checkIn', $reservation);

        try {
            $checkInService->complete(
                $request->user(),
                $reservation,
                $request->validated('notes'),
            );
        } catch (CheckInException $exception) {
            return back()->with('reception_error', $exception->getMessage());
        }

        return back()->with('status', __('reception.check_in_completed'));
    }

    public function checkout(
        Request $request,
        string $token,
        CheckInService $checkInService,
    ): RedirectResponse {
        try {
            $reservation = $checkInService->findForReception($token);
        } catch (CheckInException $exception) {
            return $this->failedLookupRedirect($exception->getMessage());
        }

        $this->authorize('checkIn', $reservation);

        try {
            $checkInService->checkout($request->user(), $reservation);
        } catch (CheckInException $exception) {
            return back()->with('reception_error', $exception->getMessage());
        }

        return redirect()
            ->route('reception.dashboard')
            ->with('status', __('reception.checkout_completed'));
    }

    private function failedLookupRedirect(string $message): RedirectResponse
    {
        $scanUrl = route('reception.scan');
        $previous = url()->previous();

        $target = $previous === $scanUrl || str_starts_with($previous, $scanUrl.'?')
            ? $scanUrl
            : route('reception.dashboard');

        return redirect()
            ->to($target)
            ->with('reception_error', $message);
    }
}
