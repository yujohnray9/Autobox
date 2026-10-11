<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX — Admin Login</title>
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
        @keyframes shimmerSlide {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .slide-shimmer-text {
            background: linear-gradient(90deg, #64748b 0%, #8b5cf6 50%, #64748b 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmerSlide 2.6s infinite linear;
        }
        @keyframes shakeSlider {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake-verify {
            animation: shakeSlider 0.4s ease-in-out;
        }
        #puzzleHandleCheck {
            display: none !important;
        }
        #puzzleSliderHandle.is-verified #puzzleHandleCheck {
            display: block !important;
        }
        #puzzleSliderHandle.is-verified #puzzleHandleBars {
            display: none !important;
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
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-white shadow-xl shadow-violet-300/40 p-1 mb-4 border-2 border-violet-100 ring-4 ring-violet-500/10">
                <img src="{{ asset('build/assets/logo.jpg') }}" alt="AutoBox CCSICT Logo" class="w-full h-full object-contain rounded-full">
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">AUTO<span class="text-gradient">BOX</span></h1>
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
                               placeholder="Enter Email Address">
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

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between gap-2 pt-0.5">
                    <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                        <span class="text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-semibold text-violet-600 hover:text-violet-700 hover:underline transition-colors flex items-center gap-1">
                        Forgot password?
                    </a>
                </div>

                <!-- Invisible Honeypot Field (Traps automated spam/scrapers) -->
                <div class="hidden" aria-hidden="true" style="display:none !important;">
                    <input type="text" name="autobox_security_hp" tabindex="-1" autocomplete="off">
                </div>

                <!-- Hidden Slide Verification Token & Timing Telemetry -->
                <input type="hidden" name="slide_token" id="slide_token" value="">
                <input type="hidden" name="slide_duration" id="slide_duration" value="0">

                @php
                    $slideSeed = $slideSeed ?? session('slide_challenge_seed');
                    if (empty($slideSeed)) {
                        $slideSeed = \Illuminate\Support\Str::random(32);
                        session(['slide_challenge_seed' => $slideSeed]);
                    }
                    $slideTokenExpected = hash('sha256', $slideSeed . csrf_token() . 'human');
                @endphp

                <!-- Modern Interactive Jigsaw Puzzle Slider (Offline-Ready) -->
                <div class="pt-2">
                    <div id="puzzleVerifyContainer"
                         class="bg-slate-50/90 rounded-2xl border border-slate-200 p-3 shadow-xs space-y-2.5 transition-all duration-200 @error('slide_verify') border-rose-400 ring-2 ring-rose-200 @enderror">
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between px-0.5">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-puzzle-piece text-violet-600"></i>
                                Verification
                            </span>
                            <span id="puzzleStatusBadge" class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">
                                Puzzle Required
                            </span>
                        </div>

                        <!-- Picture & Puzzle Frame -->
                        <div id="puzzleFrame"
                             class="relative w-full h-[155px] rounded-xl overflow-hidden bg-slate-800 border border-slate-200/90 shadow-inner select-none touch-none">
                            <!-- Background Canvas with cutout hole -->
                            <canvas id="puzzleBgCanvas" class="absolute inset-0 w-full h-full block pointer-events-none select-none"></canvas>

                            <!-- Sliding Puzzle Piece Canvas -->
                            <canvas id="puzzlePieceCanvas" class="absolute top-0 left-0 h-full pointer-events-none select-none will-change-transform"></canvas>

                            <!-- Loading overlay -->
                            <div id="puzzleLoading" class="absolute inset-0 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs text-white text-xs font-semibold gap-2 z-20">
                                <i class="fa-solid fa-circle-notch fa-spin text-violet-400"></i>
                                <span>Loading puzzle...</span>
                            </div>

                            <!-- Success Banner Overlay -->
                            <div id="puzzleSuccessOverlay" class="absolute inset-x-0 bottom-0 py-2 bg-emerald-600/95 backdrop-blur-xs text-white text-center text-xs font-extrabold flex items-center justify-center gap-1.5 opacity-0 pointer-events-none transition-all duration-300 z-30">
                                <i class="fa-solid fa-circle-check text-sm"></i>
                                <span id="puzzleSuccessMsg">Verification Succeeded!</span>
                            </div>
                        </div>

                        <!-- Slider Track -->
                        <div id="puzzleSliderTrack"
                             class="relative w-full h-11 bg-slate-200/80 rounded-full border border-slate-300/80 overflow-hidden select-none touch-none">
                            
                            <!-- Fill -->
                            <div id="puzzleSliderFill"
                                 class="absolute top-0 left-0 bottom-0 w-0 bg-violet-200/80 rounded-full pointer-events-none transition-all"></div>

                            <!-- Instruction Text -->
                            <div id="puzzleSliderText"
                                 class="absolute inset-0 flex items-center justify-center text-[11px] font-semibold text-slate-600 pointer-events-none select-none px-4 text-center">
                                <span>Drag the slider to fit the puzzle piece</span>
                            </div>

                            <!-- Slider Handle (|||) -->
                            <div id="puzzleSliderHandle"
                                 role="slider"
                                 tabindex="0"
                                 aria-label="Drag the slider to fit the puzzle piece"
                                 aria-valuemin="0"
                                 aria-valuemax="100"
                                 aria-valuenow="0"
                                 class="absolute top-1 left-1 bottom-1 w-12 bg-white rounded-full shadow-md shadow-slate-400/40 flex items-center justify-center cursor-grab active:cursor-grabbing text-violet-600 border border-slate-200 hover:border-violet-400 select-none z-10 transition-transform">
                                <div id="puzzleHandleBars" class="flex items-center gap-1">
                                    <span class="w-1 h-3.5 bg-violet-600 rounded-full"></span>
                                    <span class="w-1 h-3.5 bg-violet-600 rounded-full"></span>
                                    <span class="w-1 h-3.5 bg-violet-600 rounded-full"></span>
                                </div>
                                <i id="puzzleHandleCheck" class="fa-solid fa-check text-emerald-600 text-sm font-bold" style="display: none !important;"></i>
                            </div>
                        </div>

                        <!-- Bottom Controls -->
                        <div class="flex items-center justify-between pt-0.5 text-xs px-0.5">
                            <span class="text-[11px] text-slate-400 font-medium">Secured by AUTOBOX</span>
                            <button type="button" id="puzzleRefreshBtn"
                                    class="text-violet-600 hover:text-violet-800 font-semibold flex items-center gap-1.5 transition-colors cursor-pointer select-none">
                                <i id="puzzleRefreshIcon" class="fa-solid fa-arrows-rotate text-[11px]"></i>
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>

                    @error('slide_verify')
                        <p class="text-xs text-rose-600 mt-1.5 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Submit -->
                <button type="submit" id="loginSubmitBtn"
                        class="w-full py-3 rounded-xl gradient-violet-blue text-white font-bold text-sm shadow-lg shadow-violet-300/50 hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2 mt-2 opacity-60 cursor-not-allowed"
                        disabled>
                    <i id="loginSubmitIcon" class="fa-solid fa-right-to-bracket"></i>
                    <span id="loginSubmitText">Sign In to AUTOBOX</span>
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

        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.querySelector('form');
            const container = document.getElementById('puzzleVerifyContainer');
            const frame = document.getElementById('puzzleFrame');
            const bgCanvas = document.getElementById('puzzleBgCanvas');
            const pieceCanvas = document.getElementById('puzzlePieceCanvas');
            const loadingOverlay = document.getElementById('puzzleLoading');
            const successOverlay = document.getElementById('puzzleSuccessOverlay');
            const successMsg = document.getElementById('puzzleSuccessMsg');
            const statusBadge = document.getElementById('puzzleStatusBadge');
            const track = document.getElementById('puzzleSliderTrack');
            const handle = document.getElementById('puzzleSliderHandle');
            const fill = document.getElementById('puzzleSliderFill');
            const text = document.getElementById('puzzleSliderText');
            const handleBars = document.getElementById('puzzleHandleBars');
            const handleCheck = document.getElementById('puzzleHandleCheck');
            const refreshBtn = document.getElementById('puzzleRefreshBtn');
            const refreshIcon = document.getElementById('puzzleRefreshIcon');
            const tokenInput = document.getElementById('slide_token');
            const durationInput = document.getElementById('slide_duration');
            const submitBtn = document.getElementById('loginSubmitBtn');

            const expectedToken = @json($slideTokenExpected);

            // High-res puzzle pictures
            const puzzleImages = [
                "{{ asset('images/captcha/bg2.jpg') }}", // Lakeside road & dam (matching user screenshot)
                "{{ asset('images/captcha/bg1.jpg') }}"  // Cyber server lab
            ];
            let currentImgIndex = 0;
            let loadedImage = null;

            let targetX = 0;
            let targetY = 0;
            const puzzleSize = 42;
            const tabR = 7;
            const localPieceX = tabR + 2;

            let isDragging = false;
            let startPointerX = 0;
            let startTime = 0;
            let isVerified = false;

            // Draw a classic jigsaw puzzle shape
            function drawPuzzleShape(ctx, x, y, size, r) {
                ctx.beginPath();
                ctx.moveTo(x, y);
                // Top side with tab protruding upwards
                ctx.lineTo(x + (size / 2) - r, y);
                ctx.arc(x + (size / 2), y - r + 1, r, Math.PI * 0.8, Math.PI * 0.2, false);
                ctx.lineTo(x + size, y);
                // Right side with tab protruding right
                ctx.lineTo(x + size, y + (size / 2) - r);
                ctx.arc(x + size + r - 1, y + (size / 2), r, -Math.PI * 0.3, Math.PI * 0.3, false);
                ctx.lineTo(x + size, y + size);
                // Bottom side with tab indent
                ctx.lineTo(x + (size / 2) + r, y + size);
                ctx.arc(x + (size / 2), y + size - r + 1, r, Math.PI * 0.2, Math.PI * 0.8, true);
                ctx.lineTo(x, y + size);
                // Left side with tab indent
                ctx.lineTo(x, y + (size / 2) + r);
                ctx.arc(x - r + 1, y + (size / 2), r, Math.PI * 0.3, -Math.PI * 0.3, true);
                ctx.lineTo(x, y);
                ctx.closePath();
            }

            function initPuzzle() {
                if (isVerified || !frame) return;

                const W = frame.clientWidth || 300;
                const H = frame.clientHeight || 155;

                bgCanvas.width = W;
                bgCanvas.height = H;

                const pieceW = puzzleSize + tabR * 2 + 8;
                pieceCanvas.width = pieceW;
                pieceCanvas.height = H;
                pieceCanvas.style.width = pieceW + 'px';
                pieceCanvas.style.height = H + 'px';

                // Pick random target coordinates
                targetX = Math.floor(Math.random() * (W - puzzleSize - tabR * 2 - 80)) + 70;
                targetY = Math.floor(Math.random() * (H - puzzleSize - tabR * 2 - 25)) + 15;

                // Load image
                if (loadingOverlay) loadingOverlay.classList.remove('hidden');

                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.src = puzzleImages[currentImgIndex];
                img.onload = function () {
                    loadedImage = img;
                    if (loadingOverlay) loadingOverlay.classList.add('hidden');
                    renderPuzzle(W, H);
                };
                img.onerror = function () {
                    // Fallback to local canvas gradient pattern if image fails
                    if (loadingOverlay) loadingOverlay.classList.add('hidden');
                    renderFallbackPattern(W, H);
                };
            }

            function renderPuzzle(W, H) {
                const bgCtx = bgCanvas.getContext('2d');
                const pieceCtx = pieceCanvas.getContext('2d');

                // 1. Draw background image
                bgCtx.clearRect(0, 0, W, H);
                bgCtx.drawImage(loadedImage, 0, 0, W, H);

                // 2. Draw dark cutout target hole with glowing border
                bgCtx.save();
                drawPuzzleShape(bgCtx, targetX, targetY, puzzleSize, tabR);
                bgCtx.fillStyle = 'rgba(0, 0, 0, 0.65)';
                bgCtx.fill();
                bgCtx.strokeStyle = 'rgba(255, 255, 255, 0.85)';
                bgCtx.lineWidth = 2;
                bgCtx.stroke();
                bgCtx.restore();

                // 3. Draw sliding puzzle piece
                pieceCtx.clearRect(0, 0, pieceCanvas.width, H);
                pieceCtx.save();
                drawPuzzleShape(pieceCtx, localPieceX, targetY, puzzleSize, tabR);
                pieceCtx.clip();
                // Draw slice of image matching original position
                pieceCtx.drawImage(loadedImage, localPieceX - targetX, 0, W, H);
                pieceCtx.strokeStyle = 'rgba(255, 255, 255, 0.95)';
                pieceCtx.lineWidth = 2.5;
                pieceCtx.shadowColor = 'rgba(0, 0, 0, 0.7)';
                pieceCtx.shadowBlur = 8;
                pieceCtx.stroke();
                pieceCtx.restore();

                // Reset piece position to left edge
                resetPiecePosition();
            }

            function renderFallbackPattern(W, H) {
                const bgCtx = bgCanvas.getContext('2d');
                const pieceCtx = pieceCanvas.getContext('2d');

                const grad = bgCtx.createLinearGradient(0, 0, W, H);
                grad.addColorStop(0, '#4c1d95');
                grad.addColorStop(0.5, '#2563eb');
                grad.addColorStop(1, '#06b6d4');
                bgCtx.fillStyle = grad;
                bgCtx.fillRect(0, 0, W, H);

                bgCtx.save();
                drawPuzzleShape(bgCtx, targetX, targetY, puzzleSize, tabR);
                bgCtx.fillStyle = 'rgba(0, 0, 0, 0.65)';
                bgCtx.fill();
                bgCtx.strokeStyle = 'rgba(255, 255, 255, 0.85)';
                bgCtx.lineWidth = 2;
                bgCtx.stroke();
                bgCtx.restore();

                pieceCtx.clearRect(0, 0, pieceCanvas.width, H);
                pieceCtx.save();
                drawPuzzleShape(pieceCtx, localPieceX, targetY, puzzleSize, tabR);
                pieceCtx.clip();
                pieceCtx.fillStyle = grad;
                pieceCtx.fillRect(localPieceX - targetX, 0, W, H);
                pieceCtx.strokeStyle = 'rgba(255, 255, 255, 0.95)';
                pieceCtx.lineWidth = 2.5;
                pieceCtx.stroke();
                pieceCtx.restore();

                resetPiecePosition();
            }

            function resetPiecePosition() {
                pieceCanvas.style.transform = 'translateX(0px)';
                handle.style.transform = 'translateX(0px)';
                fill.style.width = '0px';
                text.style.opacity = '1';
                handle.setAttribute('aria-valuenow', '0');
                handle.classList.remove('is-verified', 'border-emerald-400');
                if (handleBars) handleBars.style.setProperty('display', 'flex', 'important');
                if (handleCheck) handleCheck.style.setProperty('display', 'none', 'important');
            }

            function getMaxSliderX() {
                if (!track || !handle) return 0;
                return Math.max(0, track.clientWidth - handle.offsetWidth - 8);
            }

            function getMaxPieceDragX() {
                if (!frame) return 0;
                const W = frame.clientWidth;
                return Math.max(0, W - (puzzleSize + tabR * 2 + 12));
            }

            // Dragging interactions (Pointer Events for Touch / Mouse / Stylus)
            if (handle) {
                handle.addEventListener('pointerdown', function (e) {
                    if (isVerified) return;
                    isDragging = true;
                    startPointerX = e.clientX;
                    startTime = performance.now();
                    try { handle.setPointerCapture(e.pointerId); } catch (_) {}
                    handle.classList.add('scale-105', 'shadow-violet-500/40');
                    handle.classList.remove('transition-all');
                    fill.classList.remove('transition-all');
                    pieceCanvas.classList.remove('transition-transform');
                });

                handle.addEventListener('pointermove', function (e) {
                    if (!isDragging || isVerified) return;
                    const maxSliderX = getMaxSliderX();
                    if (maxSliderX <= 0) return;

                    const deltaX = e.clientX - startPointerX;
                    const currentSliderX = Math.max(0, Math.min(deltaX, maxSliderX));

                    handle.style.transform = `translateX(${currentSliderX}px)`;
                    fill.style.width = `${currentSliderX + handle.offsetWidth}px`;

                    const progress = currentSliderX / maxSliderX;
                    handle.setAttribute('aria-valuenow', Math.round(progress * 100));
                    text.style.opacity = (1 - progress * 1.5).toString();

                    // Calculate corresponding piece position on image
                    const maxPieceDragX = getMaxPieceDragX();
                    const pieceDragX = progress * maxPieceDragX;
                    pieceCanvas.style.transform = `translateX(${pieceDragX}px)`;
                });

                function handleDragEnd(e) {
                    if (!isDragging || isVerified) return;
                    isDragging = false;
                    try { handle.releasePointerCapture(e.pointerId); } catch (_) {}
                    handle.classList.remove('scale-105', 'shadow-violet-500/40');

                    const maxSliderX = getMaxSliderX();
                    const currentSliderX = parseFloat((handle.style.transform.match(/translateX\(([^p]+)px\)/) || [0, 0])[1]);
                    const progress = maxSliderX > 0 ? (currentSliderX / maxSliderX) : 0;
                    const pieceDragX = progress * getMaxPieceDragX();

                    // The piece aligns exactly with the hole when pieceDragX + localPieceX == targetX
                    const correctPieceX = targetX - localPieceX;
                    const diff = Math.abs(pieceDragX - correctPieceX);

                    // Tolerance: 6 pixels
                    if (diff <= 6) {
                        completePuzzleVerification(correctPieceX);
                    } else {
                        failPuzzleVerification();
                    }
                }

                handle.addEventListener('pointerup', handleDragEnd);
                handle.addEventListener('pointercancel', handleDragEnd);

                // Keyboard support
                handle.addEventListener('keydown', function (e) {
                    if (isVerified) return;
                    if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        const correctPieceX = targetX - localPieceX;
                        completePuzzleVerification(correctPieceX);
                    }
                });
            }

            function completePuzzleVerification(correctPieceX) {
                isVerified = true;
                const elapsedMs = Math.round(performance.now() - startTime);

                // Snap puzzle piece to target hole
                pieceCanvas.classList.add('transition-transform', 'duration-200');
                pieceCanvas.style.transform = `translateX(${correctPieceX}px)`;

                // Snap handle to matching position or end
                handle.classList.add('transition-all', 'duration-200');
                fill.classList.add('transition-all', 'duration-200');
                fill.className = 'absolute top-0 left-0 bottom-0 w-full bg-emerald-500 rounded-full transition-all duration-300';

                handle.classList.remove('cursor-grab', 'active:cursor-grabbing');
                handle.classList.add('is-verified', 'cursor-default', 'border-emerald-400');
                if (handleBars) handleBars.style.setProperty('display', 'none', 'important');
                if (handleCheck) handleCheck.style.setProperty('display', 'block', 'important');

                // Header status badge
                statusBadge.className = 'text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 flex items-center gap-1';
                statusBadge.innerHTML = '<i class="fa-solid fa-check text-[9px]"></i> Verified';

                // Banner overlay on image
                const seconds = (Math.max(300, elapsedMs) / 1000).toFixed(1);
                successMsg.textContent = `Verified in ${seconds}s!`;
                successOverlay.classList.remove('opacity-0');
                successOverlay.classList.add('opacity-100');

                // Tokens
                tokenInput.value = expectedToken;
                durationInput.value = Math.max(250, elapsedMs);

                // Unlock Sign In button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                    submitBtn.classList.add('hover:opacity-95', 'active:scale-[0.98]', 'cursor-pointer');
                }

                if (navigator.vibrate) {
                    try { navigator.vibrate(40); } catch (_) {}
                }
            }

            function failPuzzleVerification() {
                if (container) {
                    container.classList.add('shake-verify', 'border-rose-400', 'ring-2', 'ring-rose-200');
                    setTimeout(() => container.classList.remove('shake-verify'), 450);
                }

                // Snap back smoothly
                handle.classList.add('transition-all', 'duration-300');
                fill.classList.add('transition-all', 'duration-300');
                pieceCanvas.classList.add('transition-transform', 'duration-300');

                resetPiecePosition();

                // Re-randomize puzzle position after brief moment so bots cannot guess
                setTimeout(() => {
                    if (!isVerified) {
                        initPuzzle();
                    }
                }, 400);
            }

            // Refresh button
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function () {
                    if (isVerified) return;
                    if (refreshIcon) refreshIcon.classList.add('fa-spin');
                    currentImgIndex = (currentImgIndex + 1) % puzzleImages.length;
                    initPuzzle();
                    setTimeout(() => {
                        if (refreshIcon) refreshIcon.classList.remove('fa-spin');
                    }, 400);
                });
            }

            // Handle submission prevention if not verified
            if (loginForm) {
                loginForm.addEventListener('submit', function (e) {
                    if (!isVerified) {
                        e.preventDefault();
                        if (container) {
                            container.classList.add('shake-verify', 'border-rose-400', 'ring-2', 'ring-rose-200');
                            setTimeout(() => container.classList.remove('shake-verify'), 450);
                        }
                        return false;
                    }

                    const btn = document.getElementById('loginSubmitBtn');
                    const icon = document.getElementById('loginSubmitIcon');
                    const textNode = document.getElementById('loginSubmitText');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
                    }
                    if (icon) {
                        icon.className = 'fa-solid fa-circle-notch fa-spin text-sm';
                    }
                    if (textNode) {
                        textNode.textContent = 'Signing In...';
                    }
                });
            }

            // Initial load
            initPuzzle();
            window.addEventListener('resize', () => {
                if (!isVerified) initPuzzle();
            });
        });
    </script>
</body>
</html>
