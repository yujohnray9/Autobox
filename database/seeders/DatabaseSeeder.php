<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Key;
use App\Models\Transaction;
use App\Models\AccessLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::create([
            'name'        => 'CCSICT Admin',
            'email'       => 'autobox002026@gmail.com',
            'password'    => Hash::make('autobox123456'),
            'role'        => 'admin',
            'department'  => 'CCSICT',
            'employee_id' => 'EMP-2024-001',
            'qr_token'    => Str::uuid()->toString(),
            'is_active'   => true,
        ]);

        // 2. Create Faculty / Staff Users
        $faculty1 = User::create([
            'name'        => 'Prof. Juan Dela Cruz',
            'email'       => 'juan.delacruz@autobox.edu.ph',
            'password'    => Hash::make('password'),
            'role'        => 'faculty',
            'department'  => 'Computer Science',
            'employee_id' => 'EMP-2024-002',
            'qr_token'    => Str::uuid()->toString(),
            'is_active'   => true,
        ]);

        $faculty2 = User::create([
            'name'        => 'Engr. Maria Santos',
            'email'       => 'maria.santos@autobox.edu.ph',
            'password'    => Hash::make('password'),
            'role'        => 'faculty',
            'department'  => 'Information Technology',
            'employee_id' => 'EMP-2024-003',
            'qr_token'    => Str::uuid()->toString(),
            'is_active'   => true,
        ]);

        $staff1 = User::create([
            'name'        => 'Mr. Robert Reyes',
            'email'       => 'robert.reyes@autobox.edu.ph',
            'password'    => Hash::make('password'),
            'role'        => 'staff',
            'department'  => 'Laboratory Custodian',
            'employee_id' => 'EMP-2024-004',
            'qr_token'    => Str::uuid()->toString(),
            'is_active'   => true,
        ]);

        // 3. Create Only 3 Keys (Room 1, Room 2, Room 3)
        $keys = [
            ['key_name' => 'Room 1', 'room_name' => 'Room 1', 'description' => 'Programming & Multi-Media Lab', 'slot_number' => 1, 'status' => 'available'],
            ['key_name' => 'Room 2', 'room_name' => 'Room 2', 'description' => 'Networking & Cisco Lab', 'slot_number' => 2, 'status' => 'available'],
            ['key_name' => 'Room 3', 'room_name' => 'Room 3', 'description' => 'Hardware & IoT Lab', 'slot_number' => 3, 'status' => 'available'],
        ];

        $createdKeys = [];
        foreach ($keys as $k) {
            $createdKeys[] = Key::create($k);
        }

        // 4. Create Historical (Returned) Transactions
        Transaction::create([
            'user_id'     => $faculty1->id,
            'key_id'      => $createdKeys[0]->id, // Lab 1 Key
            'action'      => 'return',
            'status'      => 'success',
            'notes'       => 'Returned key after lab class',
            'borrowed_at' => now()->subHours(5),
            'returned_at' => now()->subHours(1),
        ]);

        // 5. Access Logs
        AccessLog::create([
            'user_id'    => $faculty1->id,
            'qr_token'   => $faculty1->qr_token,
            'action'     => 'scan',
            'result'     => 'granted',
            'reason'     => 'Valid QR Token & Active Account',
            'ip_address' => '192.168.1.105',
        ]);

        AccessLog::create([
            'user_id'    => null,
            'qr_token'   => 'INVALID-TOKEN-999',
            'action'     => 'scan',
            'result'     => 'denied',
            'reason'     => 'Unregistered QR Code',
            'ip_address' => '192.168.1.105',
        ]);
    }
}
