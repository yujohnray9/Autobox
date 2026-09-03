<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX — Forgot Admin Password</title>

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
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl gradient-violet-blue text-white shadow-xl shadow-violet-300/50 mb-4">
                <i class="fa-solid fa-key text-2xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">AUTO<span class="text-gradient">BOX</span></h1>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-widest">Key Access & Monitoring System</p>
            <p class="text-[11px] text-slate-400 mt-1">CCSICT · Admin Password Recovery</p>
        </div>

        <!-- Forgot Password Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl shadow-slate-200/80 p-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-unlock-keyhole"></i>
                </div>
                <h2 class="text-lg font-heading font-bold text-slate-900">Forgot Password?</h2>
            </div>
            <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                Enter your registered administrator email address and we'll send you a secure link to reset your password.
            </p>

            <!-- Session Status Alert -->
            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-emerald-900">Reset Link Sent</p>
                        <p class="text-emerald-700 mt-0.5">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Admin Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                               placeholder="admin@autobox.edu.ph">
                    </div>
                    @error('email')
                        <div class="mt-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation flex-shrink-0 text-rose-500"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 rounded-xl gradient-violet-blue text-white font-bold text-sm shadow-lg shadow-violet-300/50 hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Send Password Reset Link
                </button>

                <!-- Back to Login -->
                <div class="pt-2 text-center">
                    <a href="{{ route('login') }}"
                       class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[11px]"></i>
                        Back to Admin Login
                    </a>
                </div>

            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-[11px] text-slate-400 mt-6">
            AUTOBOX © {{ date('Y') }} · CCSICT Key Access & Monitoring System
        </p>

    </div>

</body>
</html>
