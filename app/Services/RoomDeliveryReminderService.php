<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RoomDeliveryReminderService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @return list<int>
     */
    public function intervals(): array
    {
        return config('overlook.notification_intervals', [8, 4, 2, 1]);
    }

    public function checkInDateTime(Reservation $reservation): Carbon
    {
        $time = config('overlook.default_check_in_time', '15:00');

        return Carbon::parse(
            $reservation->check_in_date->format('Y-m-d').' '.$time,
            config('overlook.timezone')
        );
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function dueReservations(): Collection
    {
        return Reservation::query()
            ->with('user')
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', '>=', now()->toDateString())
            ->get()
            ->filter(fn (Reservation $reservation) => $this->hasDueReminder($reservation));
    }

    public function processDueReminders(): int
    {
        $sent = 0;

        foreach ($this->dueReservations() as $reservation) {
            foreach ($this->intervalsDueNow($reservation) as $interval) {
                $this->notificationService->sendRoomDeliveryReminder($reservation, $interval);
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * @return list<int>
     */
    public function intervalsDueNow(Reservation $reservation): array
    {
        $due = [];
        $checkInAt = $this->checkInDateTime($reservation);
        $now = now(config('overlook.timezone'));

        foreach ($this->intervals() as $hours) {
            $target = $checkInAt->copy()->subHours($hours);

            if ($now->between($target->copy()->subMinutes(5), $target->copy()->addMinutes(5))
                && ! $this->alreadySent($reservation, $hours)) {
                $due[] = $hours;
            }
        }

        return $due;
    }

    private function hasDueReminder(Reservation $reservation): bool
    {
        return count($this->intervalsDueNow($reservation)) > 0;
    }

    private function alreadySent(Reservation $reservation, int $intervalHours): bool
    {
        $dedupeKey = $this->dedupeKey($reservation, $intervalHours);

        return UserNotification::query()->where('dedupe_key', $dedupeKey)->exists();
    }

    public function dedupeKey(Reservation $reservation, int $intervalHours): string
    {
        return sprintf(
            'room_delivery:%d:%s:%dh',
            $reservation->id,
            $reservation->check_in_date->format('Y-m-d'),
            $intervalHours
        );
    }
}
