<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * Validates credentials, creates a 6-digit OTP, sends it via email,
     * and redirects to the OTP verification view.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->validateCredentials();

        $otp = $user->generateLoginOtp();

        $request->session()->put('login.user_id', $user->id);
        $request->session()->put('login.remember', $request->boolean('remember'));

        try {
            Mail::to($user->email)->send(new LoginOtpMail($user, $otp, $request->ip()));
        } catch (\Throwable $e) {
            Log::error('[Login OTP] Failed to send email: ' . $e->getMessage());
        }

        return redirect()->route('login.otp');
    }

    /**
     * Display the OTP verification view.
     */
    public function showOtpForm(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('login.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget(['login.user_id', 'login.remember']);
            return redirect()->route('login');
        }

        $secondsRemaining = 0;
        $resendSecondsRemaining = 0;
        if ($user->login_otp_expires_at) {
            $secondsRemaining = max(0, (int) round(now()->diffInSeconds($user->login_otp_expires_at, false)));
            $elapsed = 600 - $secondsRemaining;
            $resendSecondsRemaining = max(0, 60 - $elapsed);
        }

        // Mask user email (e.g. j***n@domain.com)
        $parts = explode('@', $user->email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        if (strlen($name) <= 3) {
            $maskedName = substr($name, 0, 1) . '***';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(1, strlen($name) - 4)) . substr($name, -2);
        }
        $maskedEmail = $maskedName . '@' . $domain;

        return view('auth.verify-otp', [
            'maskedEmail'            => $maskedEmail,
            'secondsRemaining'       => $secondsRemaining,
            'resendSecondsRemaining' => $resendSecondsRemaining,
        ]);
    }

    /**
     * Verify the entered 6-digit OTP and complete authentication.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('login.user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your login session has expired. Please log in again.',
            ]);
        }

        $user = User::find($userId);
        if (!$user || !$user->isLoginOtpValid($request->otp)) {
            throw ValidationException::withMessages([
                'otp' => 'The provided OTP is invalid or has expired. Please request a new one.',
            ]);
        }

        // Clear OTP code
        $user->clearLoginOtp();

        // Retrieve remember preference & log user in
        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resend a fresh OTP to the user's email.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget(['login.user_id', 'login.remember']);
            return redirect()->route('login');
        }

        $otp = $user->generateLoginOtp();

        try {
            Mail::to($user->email)->send(new LoginOtpMail($user, $otp, $request->ip()));
        } catch (\Throwable $e) {
            Log::error('[Login OTP Resend] Failed to send email: ' . $e->getMessage());
            return back()->withErrors([
                'otp' => 'Failed to send OTP email. Please check your mail settings or try again.',
            ]);
        }

        return back()->with('status', 'A new 6-digit OTP code has been sent to your email.');
    }

    /**
     * Cancel the pending 2FA login attempt and return to login.
     */
    public function cancelLogin(Request $request): RedirectResponse
    {
        $request->session()->forget(['login.user_id', 'login.remember']);
        return redirect()->route('login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

