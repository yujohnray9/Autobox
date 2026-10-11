<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existing = User::where('email', 'yujohnray96@gmail.com')->first();

        User::updateOrCreate(
            ['email' => 'yujohnray96@gmail.com'],
            [
                'name'                 => 'CCSICT Admin',
                'gender'               => 'male',
                'password'             => Hash::make('password123'),
                'role'                 => 'admin',
                'department'           => 'CCSICT',
                'employee_id'          => 'EMP-2024-007',
                'qr_token'             => $existing?->qr_token ?? Str::uuid()->toString(),
                'is_active'            => true,
                'login_otp_code'       => null,
                'login_otp_expires_at' => null,
            ]
        );
    }
}
