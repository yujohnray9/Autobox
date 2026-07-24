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
        // 1. Create Admin User
        $admin = User::create([
            'name'        => 'CCSICT Admin',
            'email'       => 'admin@autobox.edu.ph',
            'password'    => Hash::make('admin123'),
            'role'        => 'admin',
            'department'  => 'CCSICT',
            'employee_id' => 'EMP-0001',
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
            'employee_id' => 'EMP-1002',
            'qr_token'    => 'AUTOBOX-QR-FAC-001',
            'is_active'   => true,
        ]);

        $faculty2 = User::create([
            'name'        => 'Engr. Maria Santos',
            'email'       => 'maria.santos@autobox.edu.ph',
            'password'    => Hash::make('password'),
            'role'        => 'faculty',
            'department'  => 'Information Technology',
            'employee_id' => 'EMP-1003',
            'qr_token'    => 'AUTOBOX-QR-FAC-002',
            'is_active'   => true,
        ]);

        $staff1 = User::create([
            'name'        => 'Mr. Robert Reyes',
            'email'       => 'robert.reyes@autobox.edu.ph',
            'password'    => Hash::make('password'),
            'role'        => 'staff',
            'department'  => 'Laboratory Custodian',
            'employee_id' => 'EMP-2001',
            'qr_token'    => 'AUTOBOX-QR-STAFF-001',
            'is_active'   => true,
        ]);

        // 3. Create Keys (Slots 1 to 10 for CCSICT rooms)
        $keys = [
            ['key_name' => 'Lab 1 Key', 'room_name' => 'ComLab 101', 'description' => 'Programming & Multi-Media Lab', 'slot_number' => 1, 'status' => 'available'],
            ['key_name' => 'Lab 2 Key', 'room_name' => 'ComLab 102', 'description' => 'Networking & Cisco Lab', 'slot_number' => 2, 'status' => 'borrowed'],
            ['key_name' => 'Lab 3 Key', 'room_name' => 'ComLab 103', 'description' => 'Hardware & IoT Lab', 'slot_number' => 3, 'status' => 'available'],
            ['key_name' => 'Lecture 201 Key', 'room_name' => 'RM 201', 'description' => 'Lecture Hall A', 'slot_number' => 4, 'status' => 'available'],
            ['key_name' => 'Lecture 202 Key', 'room_name' => 'RM 202', 'description' => 'Lecture Hall B', 'slot_number' => 5, 'status' => 'borrowed'],
            ['key_name' => 'Faculty Office Key', 'room_name' => 'CCSICT Faculty', 'description' => 'Main Faculty Room', 'slot_number' => 6, 'status' => 'available'],
            ['key_name' => 'Server Room Key', 'room_name' => 'Server RM', 'description' => 'Network & Server Rack Room', 'slot_number' => 7, 'status' => 'missing'],
            ['key_name' => 'Dean Office Key', 'room_name' => 'Dean Office', 'description' => 'CCSICT Dean Office', 'slot_number' => 8, 'status' => 'available'],
        ];

        $createdKeys = [];
        foreach ($keys as $k) {
            $createdKeys[] = Key::create($k);
        }

        // 4. Create Schedules
        Schedule::create([
            'user_id'     => $faculty1->id,
            'key_id'      => $createdKeys[0]->id, // Lab 1 Key
            'day_of_week' => 'monday',
            'start_time'  => '08:00:00',
            'end_time'    => '12:00:00',
            'is_active'   => true,
        ]);

        Schedule::create([
            'user_id'     => $faculty1->id,
            'key_id'      => $createdKeys[1]->id, // Lab 2 Key
            'day_of_week' => 'tuesday',
            'start_time'  => '13:00:00',
            'end_time'    => '17:00:00',
            'is_active'   => true,
        ]);

        Schedule::create([
            'user_id'     => $faculty2->id,
            'key_id'      => $createdKeys[1]->id, // Lab 2 Key
            'day_of_week' => 'wednesday',
            'start_time'  => '09:00:00',
            'end_time'    => '12:00:00',
            'is_active'   => true,
        ]);

        // 5. Create Transactions
        Transaction::create([
            'user_id'     => $faculty1->id,
            'key_id'      => $createdKeys[1]->id, // Lab 2 Key (currently borrowed)
            'action'      => 'borrow',
            'status'      => 'success',
            'notes'       => 'Borrowed for Networking class',
            'borrowed_at' => now()->subHours(2),
            'returned_at' => null,
        ]);

        Transaction::create([
            'user_id'     => $faculty2->id,
            'key_id'      => $createdKeys[4]->id, // Lecture 202 Key (currently borrowed)
            'action'      => 'borrow',
            'status'      => 'success',
            'notes'       => 'Borrowed for IT101 Lecture',
            'borrowed_at' => now()->subMinutes(45),
            'returned_at' => null,
        ]);

        Transaction::create([
            'user_id'     => $faculty1->id,
            'key_id'      => $createdKeys[0]->id, // Lab 1 Key (returned earlier)
            'action'      => 'return',
            'status'      => 'success',
            'notes'       => 'Returned key after lab class',
            'borrowed_at' => now()->subHours(5),
            'returned_at' => now()->subHours(1),
        ]);

        // 6. Access Logs
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
