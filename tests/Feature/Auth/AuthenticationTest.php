<?php

namespace Tests\Feature\Auth;

use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('g-recaptcha');
        $response->assertSee('https://www.google.com/recaptcha/api.js');
    }

    public function test_valid_login_redirects_to_otp_and_sends_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login.otp'));
        $response->assertSessionHas('login.user_id', $user->id);

        $user->refresh();
        $this->assertNotNull($user->login_otp_code);
        $this->assertNotNull($user->login_otp_expires_at);

        Mail::assertSent(LoginOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->otp === $user->login_otp_code;
        });
    }

    public function test_user_can_authenticate_with_valid_otp(): void
    {
        $user = User::factory()->create();
        $otp = $user->generateLoginOtp();

        $response = $this->withSession(['login.user_id' => $user->id])
            ->post('/login/otp', [
                'otp' => $otp,
            ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();
        $this->assertNull($user->login_otp_code);
        $this->assertNull($user->login_otp_expires_at);
    }

    public function test_user_cannot_authenticate_with_invalid_otp(): void
    {
        $user = User::factory()->create();
        $user->generateLoginOtp();

        $response = $this->withSession(['login.user_id' => $user->id])
            ->post('/login/otp', [
                'otp' => '999999',
            ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('otp');
    }

    public function test_user_cannot_authenticate_with_expired_otp(): void
    {
        $user = User::factory()->create();
        $otp = $user->generateLoginOtp();

        // Expire the OTP
        $user->update(['login_otp_expires_at' => now()->subMinute()]);

        $response = $this->withSession(['login.user_id' => $user->id])
            ->post('/login/otp', [
                'otp' => $otp,
            ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('otp');
    }

    public function test_user_can_resend_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $oldOtp = $user->generateLoginOtp();

        $response = $this->withSession(['login.user_id' => $user->id])
            ->post('/login/otp/resend');

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertNotNull($user->login_otp_code);
        Mail::assertSent(LoginOtpMail::class);
    }

    public function test_user_can_cancel_login(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['login.user_id' => $user->id])
            ->get('/login/cancel');

        $response->assertRedirect(route('login'));
        $response->assertSessionMissing('login.user_id');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}

