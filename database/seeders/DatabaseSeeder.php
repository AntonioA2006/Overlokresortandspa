<?php

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStaffUsers();
        $this->seedRooms();
    }

    private function seedStaffUsers(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@overlook.test'],
            [
                'name' => 'Administrador Overlook',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'phone' => '6670000001',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'reception@overlook.test'],
            [
                'name' => 'Recepción Overlook',
                'password' => Hash::make('password'),
                'role' => UserRole::Reception,
                'phone' => '6670000002',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'support@overlook.test'],
            [
                'name' => 'Soporte Overlook',
                'password' => Hash::make('password'),
                'role' => UserRole::Support,
                'phone' => '6670000003',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'guest@overlook.test'],
            [
                'name' => 'Huésped Demo',
                'password' => Hash::make('password'),
                'role' => UserRole::Guest,
                'phone' => '6670000004',
            ]
        );
    }

    private function seedRooms(): void
    {
        $amenities = collect([
            'WiFi de alta velocidad',
            'Aire acondicionado',
            'Minibar',
            'Balcón con vista',
            'Room service',
            'Smart TV',
        ])->map(fn (string $name) => Amenity::query()->firstOrCreate(['name' => $name]));

        $types = [
            [
                'name' => 'Suite Ocean View',
                'slug' => 'suite-ocean-view',
                'description' => 'Suite amplia con vista al mar y terraza privada.',
                'base_price_per_night' => 2450,
                'max_guests' => 3,
            ],
            [
                'name' => 'Deluxe Garden',
                'slug' => 'deluxe-garden',
                'description' => 'Habitación deluxe rodeada de jardines tropicales.',
                'base_price_per_night' => 1850,
                'max_guests' => 2,
            ],
            [
                'name' => 'Standard Comfort',
                'slug' => 'standard-comfort',
                'description' => 'Opción confortable ideal para estancias cortas.',
                'base_price_per_night' => 1250,
                'max_guests' => 2,
            ],
        ];

        foreach ($types as $index => $typeData) {
            $roomType = RoomType::query()->updateOrCreate(
                ['slug' => $typeData['slug']],
                [
                    'name' => $typeData['name'],
                    'description' => $typeData['description'],
                    'base_price_per_night' => $typeData['base_price_per_night'],
                    'max_guests' => $typeData['max_guests'],
                    'is_active' => true,
                ]
            );

            $roomType->amenities()->syncWithoutDetaching(
                $amenities->slice($index, 4)->pluck('id')->all()
            );

            Room::query()->updateOrCreate(
                ['number' => (string) (101 + $index)],
                [
                    'room_type_id' => $roomType->id,
                    'floor' => 1,
                    'status' => RoomStatus::Available,
                    'description' => $typeData['description'],
                ]
            );
        }
    }
}
