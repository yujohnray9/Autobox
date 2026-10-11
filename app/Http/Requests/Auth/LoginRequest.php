<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        if (!app()->environment('testing')) {
            $rules['slide_token'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * Verify the interactive slide-to-verify challenge and invisible honeypot bot trap.
     * Offline-capable with zero external dependencies.
     */
    public function verifySlideCaptcha(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // 1. Invisible Honeypot check (catches automated scraper/spambots that auto-fill inputs)
        if (!empty($this->input('autobox_security_hp'))) {
            throw ValidationException::withMessages([
                'slide_verify' => 'Automated bot activity detected. Access denied.',
            ]);
        }

        // 2. Slide verification token signature check
        $seed = session('slide_challenge_seed');
        $expectedToken = !empty($seed) ? hash('sha256', $seed . csrf_token() . 'human') : null;
        $submittedToken = $this->input('slide_token');

        if (empty($submittedToken) || empty($expectedToken) || !hash_equals($expectedToken, (string) $submittedToken)) {
            throw ValidationException::withMessages([
                'slide_verify' => 'Please complete the slide verification before submitting.',
            ]);
        }

        // 3. Human interaction timing check (minimum 180ms to drag across, max 10 minutes)
        $duration = (int) $this->input('slide_duration', 0);
        if ($duration < 180 || $duration > 600000) {
            throw ValidationException::withMessages([
                'slide_verify' => 'Unusual slide timing detected. Please slide again.',
            ]);
        }
    }

    /**
     * Validate user credentials and return the user without logging in yet.
     *
     * @throws ValidationException
     */
    public function validateCredentials(): \App\Models\User
    {
        $this->ensureIsNotRateLimited();
        $this->verifySlideCaptcha();

        if (! Auth::validate($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = \App\Models\User::where('email', $this->string('email'))->first();

        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Legacy authenticate (calls validate and attempts login directly).
     */
    public function authenticate(): void
    {
        $user = $this->validateCredentials();
        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
