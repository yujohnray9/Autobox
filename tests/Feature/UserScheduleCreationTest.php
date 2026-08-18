<?php

namespace Tests\Feature;

use App\Models\Key;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserScheduleCreationTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_be_created_with_schedule_and_redirects_to_qr_badge(): void
    {
        $admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $key = Key::first() ?? Key::create([
            'key_name' => 'Room 1',
            'room_name' => 'Room 1',
            'slot_number' => 1,
            'status' => 'available',
        ]);

        $userData = [
            'name'            => 'Prof. Schedule Test',
            'email'           => 'prof_sched_' . uniqid() . '@autobox.edu.ph',
            'role'            => 'faculty',
            'department'      => 'Computer Science',
            'employee_id'     => 'EMP-2024-' . rand(500, 999),
            'assign_schedule' => '1',
            'key_id'          => $key->id,
            'days'            => ['monday', 'wednesday'],
            'start_time'      => '08:00',
            'end_time'        => '12:00',
        ];

        // Ensure no conflicting schedule for test
        Schedule::where('key_id', $key->id)->whereIn('day_of_week', ['monday', 'wednesday'])->delete();

        $response = $this->actingAs($admin)->post(route('users.store'), $userData);
        $response->assertSessionHasNoErrors();

        $createdUser = User::where('email', $userData['email'])->first();
        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->qr_token);

        // Verify schedules were created
        $schedules = Schedule::where('user_id', $createdUser->id)->get();
        $this->assertCount(2, $schedules);
        $this->assertTrue($schedules->pluck('day_of_week')->contains('monday'));
        $this->assertTrue($schedules->pluck('day_of_week')->contains('wednesday'));

        // Verify redirection to QR page
        $response->assertRedirect(route('users.qr', $createdUser));

        // Verify QR view renders and contains schedule info
        $qrViewResponse = $this->actingAs($admin)->get(route('users.qr', $createdUser));
        $qrViewResponse->assertStatus(200);
        $qrViewResponse->assertSee($createdUser->name);
        $qrViewResponse->assertSee($createdUser->employee_id);
        $qrViewResponse->assertSee('Slot #' . $key->slot_number);
    }

    public function test_conflict_detection_prevents_overlapping_key_schedule(): void
    {
        $admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_conflict@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $key = Key::first() ?? Key::create([
            'key_name' => 'Room 1',
            'room_name' => 'Room 1',
            'slot_number' => 1,
            'status' => 'available',
        ]);

        // Create an existing user with schedule on Monday 08:00 - 12:00
        $existingUser = User::create([
            'name' => 'Existing Faculty',
            'email' => 'existing_fac@autobox.edu.ph',
            'role' => 'faculty',
            'employee_id' => 'EMP-2024-100',
            'password' => bcrypt('password'),
            'qr_token' => 'test-token-100',
            'is_active' => true,
        ]);

        Schedule::create([
            'user_id' => $existingUser->id,
            'key_id' => $key->id,
            'day_of_week' => 'monday',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'is_active' => true,
        ]);

        // Attempt to create a new user with overlapping Monday 09:00 - 11:00 schedule on the same key
        $conflictData = [
            'name' => 'Conflicting Faculty',
            'email' => 'conflict@autobox.edu.ph',
            'role' => 'faculty',
            'employee_id' => 'EMP-2024-101',
            'assign_schedule' => '1',
            'key_id' => $key->id,
            'days' => ['monday'],
            'start_time' => '09:00',
            'end_time' => '11:00',
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $conflictData);
        $response->assertSessionHas('conflict_error');

        // Verify the conflicting user was not created
        $this->assertNull(User::where('email', 'conflict@autobox.edu.ph')->first());
    }

    public function test_user_can_be_created_without_schedule(): void
    {
        $admin = User::first() ?? User::create([
            'name' => 'Admin Test',
            'email' => 'admin_nosched@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $userData = [
            'name' => 'Pure Staff',
            'email' => 'staff_nosched@autobox.edu.ph',
            'role' => 'staff',
            'employee_id' => 'EMP-2024-102',
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $userData);
        $response->assertSessionHasNoErrors();

        $createdUser = User::where('email', $userData['email'])->first();
        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->qr_token);
        $this->assertCount(0, Schedule::where('user_id', $createdUser->id)->get());
    }
}
