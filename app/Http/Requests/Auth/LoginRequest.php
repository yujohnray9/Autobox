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

        if (!app()->environment('testing') && !empty(config('services.recaptcha.secret_key'))) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * Verify Google reCAPTCHA response if configured.
     */
    public function verifyRecaptcha(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $secret = config('services.recaptcha.secret_key');
        if (empty($secret)) {
            return;
        }

        $recaptchaResponse = $this->input('g-recaptcha-response');
        if (empty($recaptchaResponse)) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Please check the "I\'m not a robot" box before submitting.',
            ]);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->timeout(6)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $recaptchaResponse,
                'remoteip' => $this->ip(),
            ]);

            if (!$response->successful() || !$response->json('success')) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
                ]);
            }
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            \Illuminate\Support\Facades\Log::warning('[reCAPTCHA] Failed to contact Google verification server: ' . $e->getMessage());
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
        $this->verifyRecaptcha();

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
