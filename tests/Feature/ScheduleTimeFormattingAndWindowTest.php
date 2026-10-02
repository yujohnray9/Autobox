<?php

namespace Tests\Feature;

use App\Models\Key;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTimeFormattingAndWindowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $faculty;
    private Key $key;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->faculty = User::create([
            'name' => 'Prof. Alan Turing',
            'email' => 'turing@autobox.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'faculty',
            'employee_id' => 'EMP-2024-500',
            'is_active' => true,
        ]);

        $this->key = Key::create([
            'key_name' => 'Lab 101',
            'room_name' => 'Computer Lab 1',
            'slot_number' => 1,
            'status' => 'available',
        ]);
    }

    public function test_schedule_can_be_created_within_7am_to_5pm_window(): void
    {
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'user_id' => $this->faculty->id,
            'key_id' => $this->key->id,
            'days' => ['monday'],
            'start_time' => '13:00',
            'end_time' => '14:00',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('schedules.index'));

        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->faculty->id,
            'key_id' => $this->key->id,
            'day_of_week' => 'monday',
            'start_time' => '13:00',
            'end_time' => '14:00',
        ]);
    }

    public function test_schedule_starting_before_7am_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'user_id' => $this->faculty->id,
            'key_id' => $this->key->id,
            'days' => ['monday'],
            'start_time' => '06:30',
            'end_time' => '08:00',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    public function test_schedule_ending_after_5pm_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'user_id' => $this->faculty->id,
            'key_id' => $this->key->id,
            'days' => ['monday'],
            'start_time' => '16:00',
            'end_time' => '17:30',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_schedules_index_displays_12_hour_time_instead_of_military_time(): void
    {
        Schedule::create([
            'user_id' => $this->faculty->id,
            'key_id' => $this->key->id,
            'day_of_week' => 'monday',
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('schedules.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Must display 12-hour format "1:00 PM" and "to 2:00 PM"
        $this->assertStringContainsString('1:00 PM', $content);
        $this->assertStringContainsString('to 2:00 PM', $content);

        // Must NOT display military format "1300" or "to 1400"
        $this->assertStringNotContainsString('1300', $content);
        $this->assertStringNotContainsString('to 1400', $content);
    }
}
