<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_contains_forgot_password_link(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
        $response->assertSee('Forgot password?');
    }

    public function test_forgot_password_screen_displays_autobox_admin_branding(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('AUTOBOX');
        $response->assertSee('Admin Password Recovery');
        $response->assertSee('Forgot Password?');
        $response->assertSee('Send Password Reset Link');
        $response->assertSee('Back to Admin Login');
    }

    public function test_admin_can_request_password_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $admin->email,
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($admin, ResetPassword::class);
    }

    public function test_non_admin_cannot_request_password_reset(): void
    {
        Notification::fake();

        $faculty = User::factory()->create([
            'role' => 'faculty',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $faculty->email,
        ]);

        $response->assertSessionHasErrors(['email' => 'Password reset is only available for administrator accounts.']);
        Notification::assertNotSentTo($faculty, ResetPassword::class);
    }

    public function test_reset_password_mail_uses_custom_autobox_template(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $notification = new ResetPassword('test-token-12345');
        $mail = $notification->toMail($admin);

        $this->assertSame('AUTOBOX — Reset Administrator Password', $mail->subject);
        $this->assertSame('emails.admin_password_reset', $mail->view);
    }

    public function test_reset_password_screen_renders_with_token_and_branding(): void
    {
        $response = $this->get('/reset-password/test-token-12345?email=admin@autobox.edu.ph');

        $response->assertStatus(200);
        $response->assertSee('AUTOBOX');
        $response->assertSee('New Password');
        $response->assertSee('Reset & Update Password', false);
        $response->assertSee('test-token-12345');
    }
}
