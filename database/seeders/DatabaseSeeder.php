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
                'department'  => 'CCSICT',
                'employee_id' => 'EMP-2024-001',
                'qr_token'    => Str::uuid()->toString(),
                'is_active'   => true,
            ]
        );

        // 2. Create Faculty / Staff Users
        $faculty1 = User::firstOrCreate(
            ['email' => 'juan.delacruz@autobox.edu.ph'],
            [
                'name'        => 'Prof. Juan Dela Cruz',
                'gender'      => 'male',
                'password'    => Hash::make('password'),
                'role'        => 'faculty',
                'department'  => 'Computer Science',
                'employee_id' => 'EMP-2024-002',
                'qr_token'    => Str::uuid()->toString(),
                'is_active'   => true,
            ]
        );

        $faculty2 = User::firstOrCreate(
            ['email' => 'maria.santos@autobox.edu.ph'],
            [
                'name'        => 'Engr. Maria Santos',
                'gender'      => 'female',
                'password'    => Hash::make('password'),
                'role'        => 'faculty',
                'department'  => 'Information Technology',
                'employee_id' => 'EMP-2024-003',
                'qr_token'    => Str::uuid()->toString(),
                'is_active'   => true,
            ]
        );

        $staff1 = User::firstOrCreate(
            ['email' => 'robert.reyes@autobox.edu.ph'],
            [
                'name'        => 'Mr. Robert Reyes',
                'gender'      => 'male',
                'password'    => Hash::make('password'),
                'role'        => 'staff',
                'department'  => 'Laboratory Custodian',
                'employee_id' => 'EMP-2024-004',
                'qr_token'    => Str::uuid()->toString(),
                'is_active'   => true,
            ]
        );

        $tech1 = User::firstOrCreate(
            ['email' => 'tech.diaz@autobox.edu.ph'],
            [
                'name'        => 'Engr. Marcos Diaz',
                'gender'      => 'male',
                'password'    => Hash::make('password'),
                'role'        => 'technician',
                'department'  => 'Hardware & IoT Maintenance',
                'employee_id' => 'EMP-2024-005',
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

        // 4. Create Schedules
        $defaultSchedules = [
            [
                'user_id'     => $faculty2->id,
                'key_id'      => $createdKeys[0]->id,
                'day_of_week' => 'monday',
                'start_time'  => '08:00',
                'end_time'    => '10:00',
                'is_active'   => true,
            ],
            [
                'user_id'     => $faculty2->id,
                'key_id'      => $createdKeys[0]->id,
                'day_of_week' => 'wednesday',
                'start_time'  => '13:00',
                'end_time'    => '15:00',
                'is_active'   => true,
            ],
            [
                'user_id'     => $faculty2->id,
                'key_id'      => $createdKeys[0]->id,
                'day_of_week' => 'friday',
                'start_time'  => '13:00',
                'end_time'    => '15:00',
                'is_active'   => true,
            ],
            [
                'user_id'     => $faculty1->id,
                'key_id'      => $createdKeys[1]->id,
                'day_of_week' => 'tuesday',
                'start_time'  => '09:00',
                'end_time'    => '11:00',
                'is_active'   => true,
            ],
            [
                'user_id'     => $faculty1->id,
                'key_id'      => $createdKeys[1]->id,
                'day_of_week' => 'thursday',
                'start_time'  => '14:00',
                'end_time'    => '16:00',
                'is_active'   => true,
            ],
        ];

        foreach ($defaultSchedules as $sched) {
            Schedule::firstOrCreate(
                [
                    'user_id'     => $sched['user_id'],
                    'key_id'      => $sched['key_id'],
                    'day_of_week' => $sched['day_of_week'],
                    'start_time'  => $sched['start_time'],
                ],
                $sched
            );
        }

        // 5. Create Historical (Returned) Transactions
        if (Transaction::where('user_id', $faculty1->id)->where('key_id', $createdKeys[0]->id)->doesntExist()) {
            Transaction::create([
                'user_id'     => $faculty1->id,
                'key_id'      => $createdKeys[0]->id, // Lab 1 Key
                'action'      => 'return',
                'status'      => 'success',
                'notes'       => 'Returned key after lab class',
                'borrowed_at' => now()->subHours(5),
                'returned_at' => now()->subHours(1),
            ]);
        }

        // 6. Access Logs
        if (AccessLog::where('qr_token', $faculty1->qr_token)->doesntExist()) {
            AccessLog::create([
                'user_id'    => $faculty1->id,
                'qr_token'   => $faculty1->qr_token,
                'action'     => 'scan',
                'result'     => 'granted',
                'reason'     => 'Valid QR Token & Active Account',
                'ip_address' => '192.168.1.105',
            ]);
        }

        if (AccessLog::where('qr_token', 'INVALID-TOKEN-999')->doesntExist()) {
            AccessLog::create([
                'user_id'    => null,
                'qr_token'   => 'INVALID-TOKEN-999',
                'action'     => 'scan',
                'result'     => 'denied',
                'reason'     => 'Unregistered QR Code',
                'ip_address' => '192.168.1.105',
            ]);
        }

        // 7. Seed Other Modules & Demo Data
        $this->call([
            UserGenderSeeder::class,
            UnauthorizedAccessDemoSeeder::class,
            OfflineRecordDemoSeeder::class,
        ]);
    }
}
