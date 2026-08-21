<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX — Admin Login</title>

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
            <p class="text-[11px] text-slate-400 mt-1">CCSICT · Secured Admin Dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl shadow-slate-200/80 p-8">
            <h2 class="text-lg font-heading font-bold text-slate-900 mb-1">Administrator Login</h2>
            <p class="text-xs text-slate-500 mb-6">Sign in to access the real-time monitoring dashboard</p>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Email Address
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
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input id="password" type="password" name="password" required
                               class="w-full pl-9 pr-10 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('password', 'eyeIconLogin')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition-colors cursor-pointer" title="Toggle password visibility">
                            <i id="eyeIconLogin" class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                    <label for="remember_me" class="text-xs font-medium text-slate-600">Remember me on this device</label>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-3 rounded-xl gradient-violet-blue text-white font-bold text-sm shadow-lg shadow-violet-300/50 hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Sign In to AUTOBOX
                </button>

            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-[11px] text-slate-400 mt-6">
            AUTOBOX © {{ date('Y') }} · CCSICT Key Access & Monitoring System
        </p>

    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
