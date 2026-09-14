<?php

namespace App\Console\Commands;

use App\Services\RoomDeliveryReminderService;
use Illuminate\Console\Command;

class SendRoomDeliveryReminders extends Command
{
    protected $signature = 'overlook:send-room-delivery-reminders';

    protected $description = 'Envía recordatorios de entrega de habitación según los intervalos configurados';

    public function handle(RoomDeliveryReminderService $reminderService): int
    {
        $sent = $reminderService->processDueReminders();

        $this->info("Recordatorios procesados: {$sent}");

        return self::SUCCESS;
    }
}
