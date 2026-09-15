<?php

namespace Tests\Unit\Enums;

use App\Enums\AuditAction;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use Tests\TestCase;

class EnumLabelTest extends TestCase
{
    public function test_role_room_status_and_audit_labels_follow_the_app_locale(): void
    {
        app()->setLocale('es');

        $this->assertSame('Huésped', UserRole::Guest->label());
        $this->assertSame('Disponible', RoomStatus::Available->label());
        $this->assertSame('Reservación creada', AuditAction::ReservationCreated->label());

        app()->setLocale('en');

        $this->assertSame('Guest', UserRole::Guest->label());
        $this->assertSame('Available', RoomStatus::Available->label());
        $this->assertSame('Reservation created', AuditAction::ReservationCreated->label());
    }
}
