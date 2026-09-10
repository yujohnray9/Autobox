<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX — 2-Step Verification (OTP)</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('build/assets/logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3 { font-family: 'Outfit', sans-serif; }
        .gradient-violet-blue { background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%); }
        .text-gradient {
            background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .login-bg {
            background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 100%);
        }
    </style>
</head>
<body class="h-full login-bg flex items-center justify-center p-4">

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-200 rounded-full opacity-30 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-200 rounded-full opacity-30 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/4 w-40 h-40 bg-violet-300 rounded-full opacity-20 blur-2xl"></div>
    </div>

    <div class="relative w-full max-w-sm">

        <!-- Logo Card -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white shadow-xl shadow-violet-300/40 p-1 mb-3 border-2 border-violet-100 ring-4 ring-violet-500/10">
                <img src="{{ asset('build/assets/logo.jpg') }}" alt="AutoBox CCSICT Logo" class="w-full h-full object-contain rounded-full">
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">AUTO<span class="text-gradient">BOX</span></h1>
            <p class="text-[11px] font-semibold text-slate-500 mt-0.5 uppercase tracking-widest">Two-Factor Authentication</p>
        </div>

        <!-- OTP Verification Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl shadow-slate-200/80 p-8">
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl mx-auto mb-3 border border-violet-100 shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2 class="text-lg font-heading font-bold text-slate-900 mb-1">Enter Verification Code</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    We sent a 6-digit code to:<br>
                    <strong class="text-violet-700 font-semibold">{{ $maskedEmail }}</strong>
                </p>
            </div>

            <!-- Session Status / Flash Messages -->
            @if (session('status'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.otp.verify') }}" class="space-y-5">
                @csrf

                <!-- OTP Input -->
                <div>
                    <label for="otp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-center">
                        6-Digit Security Code
                    </label>
                    <input id="otp" type="text" name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required autofocus
                           class="w-full py-3 px-4 text-center text-2xl font-mono font-extrabold tracking-[0.5em] rounded-2xl border-2 border-slate-200 bg-slate-50 focus:bg-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 transition-all placeholder:text-slate-300"
                           placeholder="••••••" autocomplete="one-time-code">
                    @error('otp')
                        <p class="text-xs text-rose-600 mt-2 text-center font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 1-Minute Resend Timer / Action Area -->
                <div class="py-1">
                    <div id="resendWaitingBox" class="flex items-center justify-center gap-2 text-xs text-slate-500">
                        <i class="fa-solid fa-arrow-rotate-right text-violet-500 text-[11px] fa-spin" style="--fa-animation-duration: 3s;"></i>
                        <span>Resend code in:</span>
                        <span id="resendCountdown" class="font-mono font-bold text-violet-700 bg-violet-50 px-2.5 py-0.5 rounded-md border border-violet-200">
                            00:60
                        </span>
                    </div>

                    <div id="resendActionBox" class="text-center hidden">
                        <span class="text-xs text-slate-500">Didn't receive the email?</span>
                        <form method="POST" action="{{ route('login.otp.resend') }}" id="resendInlineForm" class="inline ml-1">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-violet-600 hover:text-violet-800 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-arrow-rotate-right text-[10px]"></i>
                                Resend Code
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                        class="w-full py-3 rounded-xl gradient-violet-blue text-white font-bold text-sm shadow-lg shadow-violet-300/50 hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock-open"></i>
                    Verify & Sign In
                </button>

                <!-- Code Expiration Notice -->
                <p class="text-[11px] text-slate-400 text-center flex items-center justify-center gap-1.5 pt-0.5">
                    <i class="fa-regular fa-clock text-[10px]"></i>
                    <span>Code expires in:</span>
                    <span id="expiryDisplay" class="font-mono font-semibold text-slate-600">10:00</span>
                </p>
            </form>

            <!-- Bottom Cancel Link -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-center text-xs">
                <a href="{{ route('login.cancel') }}" class="font-semibold text-slate-500 hover:text-slate-700 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    Cancel & Return to Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-[11px] text-slate-400 mt-6">
            AUTOBOX © {{ date('Y') }} · CCSICT Key Access & Monitoring System
        </p>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-focus and filter input to numbers only
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                });
            }

            // Clean integer truncation to prevent floating-point decimal artifacts
            let expirySecs = Math.max(0, Math.floor({{ $secondsRemaining ?? 600 }}));
            let resendSecs = Math.max(0, Math.floor({{ $resendSecondsRemaining ?? 60 }}));

            const resendWaitingBox = document.getElementById('resendWaitingBox');
            const resendCountdown = document.getElementById('resendCountdown');
            const resendActionBox = document.getElementById('resendActionBox');
            const expiryDisplay = document.getElementById('expiryDisplay');
            const submitBtn = document.getElementById('submitBtn');

            function formatTime(totalSecs) {
                const total = Math.max(0, Math.floor(totalSecs));
                const mins = Math.floor(total / 60);
                const secs = total % 60;
                return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }

            function tick() {
                // Update 1-minute Resend Cooldown
                if (resendSecs > 0) {
                    if (resendWaitingBox) resendWaitingBox.classList.remove('hidden');
                    if (resendActionBox) resendActionBox.classList.add('hidden');
                    if (resendCountdown) resendCountdown.textContent = formatTime(resendSecs);
                    resendSecs--;
                } else {
                    if (resendWaitingBox) resendWaitingBox.classList.add('hidden');
                    if (resendActionBox) resendActionBox.classList.remove('hidden');
                }

                // Update 10-minute Expiry
                if (expirySecs > 0) {
                    if (expiryDisplay) expiryDisplay.textContent = formatTime(expirySecs);
                    expirySecs--;
                } else {
                    if (expiryDisplay) {
                        expiryDisplay.textContent = "Expired";
                        expiryDisplay.className = "font-mono font-bold text-rose-600 animate-pulse";
                    }
                    if (otpInput) {
                        otpInput.disabled = true;
                        otpInput.classList.add('bg-slate-100', 'text-slate-400');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }
            }

            tick();
            setInterval(tick, 1000);
        });
    </script>
</body>
</html>
