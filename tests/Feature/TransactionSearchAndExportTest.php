<?php

namespace Tests\Feature;

use App\Models\Key;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSearchAndExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user1;
    private User $user2;
    private Key $key1;
    private Key $key2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin_boss@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->user1 = User::create([
            'name' => 'Prof. Juan Dela Cruz',
            'email' => 'juan@test.com',
            'password' => bcrypt('password'),
            'role' => 'faculty',
            'employee_id' => 'EMP-2024-101',
            'is_active' => true,
        ]);

        $this->user2 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'employee_id' => 'EMP-2024-202',
            'is_active' => true,
        ]);

        $this->key1 = Key::create([
            'key_name' => 'Room 1',
            'room_name' => 'Science Lab',
            'slot_number' => 1,
            'status' => 'available',
        ]);

        $this->key2 = Key::create([
            'key_name' => 'Room 2',
            'room_name' => 'Audio Visual Room',
            'slot_number' => 2,
            'status' => 'available',
        ]);

        // Transaction 1: Science Lab, 2026-09-19
        $t1 = Transaction::create([
            'user_id' => $this->user1->id,
            'key_id' => $this->key1->id,
            'action' => 'return',
            'status' => 'success',
            'notes' => 'Returned key after lab class',
            'borrowed_at' => '2026-09-19 08:00:00',
            'returned_at' => '2026-09-19 12:00:00',
        ]);
        $t1->timestamps = false;
        $t1->created_at = '2026-09-19 12:00:00';
        $t1->save();

        // Transaction 2: AVR, 2026-10-02
        $t2 = Transaction::create([
            'user_id' => $this->user2->id,
            'key_id' => $this->key2->id,
            'action' => 'borrow',
            'status' => 'success',
            'notes' => 'Key borrowed for conference',
            'borrowed_at' => '2026-10-02 09:30:00',
        ]);
        $t2->timestamps = false;
        $t2->created_at = '2026-10-02 09:30:00';
        $t2->save();
    }

    public function test_can_search_transactions_by_room(): void
    {
        // Filter by key_id
        $response = $this->actingAs($this->admin)->get(route('transactions.index', ['key_id' => $this->key1->id]));
        $response->assertStatus(200);
        $response->assertSee('Prof. Juan Dela Cruz');
        $response->assertDontSee('Maria Santos');

        // Or search room by keyword
        $response2 = $this->actingAs($this->admin)->get(route('transactions.index', ['search' => 'Audio Visual']));
        $response2->assertStatus(200);
        $response2->assertSee('Maria Santos');
        $response2->assertDontSee('Prof. Juan Dela Cruz');
    }

    public function test_can_search_transactions_by_date(): void
    {
        // Date filter for 2026-09-19
        $response = $this->actingAs($this->admin)->get(route('transactions.index', ['date' => '2026-09-19']));
        $response->assertStatus(200);
        $response->assertSee('Prof. Juan Dela Cruz');
        $response->assertDontSee('Maria Santos');

        // Date filter for 2026-10-02
        $response2 = $this->actingAs($this->admin)->get(route('transactions.index', ['date' => '2026-10-02']));
        $response2->assertStatus(200);
        $response2->assertSee('Maria Santos');
        $response2->assertDontSee('Prof. Juan Dela Cruz');
    }

    public function test_csv_export_contains_transaction_date_and_formatted_dates(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Check header has Transaction Date
        $this->assertStringContainsString('Transaction Date', $content);
        $this->assertStringContainsString('Borrowed At', $content);
        $this->assertStringContainsString('Returned At', $content);

        // Check content has readable dates
        $this->assertStringContainsString('2026-09-19', $content);
        $this->assertStringContainsString('2026-10-02', $content);
        $this->assertStringContainsString('Science Lab', $content);
        $this->assertStringContainsString('Audio Visual Room', $content);
    }
}
