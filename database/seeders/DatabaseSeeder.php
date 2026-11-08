<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Key;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\AccessLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create / Update Admin Users
        $this->call(AdminUserSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'autobox002026@gmail.com'],
            [
                'name'        => 'CCSICT Admin',
                'gender'      => 'male',
                'password'    => Hash::make('autobox123456'),
                'role'        => 'admin',
                'position'    => 'System Administrator',
                'department'  => 'CCSICT',
                'employee_id' => 'EMP-2024-001',
                'qr_token'    => Str::uuid()->toString(),
                'is_active'   => true,
            ]
        );

        // 3. Create Only 3 Keys (Room 1, Room 2, Room 3)
        $keys = [
            ['key_name' => 'Room 1', 'room_name' => 'Room 1', 'description' => 'Programming & Multi-Media Lab', 'slot_number' => 1, 'status' => 'available'],
            ['key_name' => 'Room 2', 'room_name' => 'Room 2', 'description' => 'Networking & Cisco Lab', 'slot_number' => 2, 'status' => 'available'],
            ['key_name' => 'Room 3', 'room_name' => 'Room 3', 'description' => 'Hardware & IoT Lab', 'slot_number' => 3, 'status' => 'available'],
        ];
        
         $createdKeys = [];
        foreach ($keys as $k) {
            $createdKeys[] = Key::firstOrCreate(
                ['slot_number' => $k['slot_number']],
                $k
            );
        }


    }
}
