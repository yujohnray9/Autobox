<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Verify if account exists and belongs to an administrator
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user && !$user->isAdmin()) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Password reset is only available for administrator accounts.']);
        }

        try {
            // Send the password reset link to this administrator
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset email error: ' . $e->getMessage());
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Unable to send password reset email at this moment. Please check mail settings or try again.']);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
