<!DOCTYPE html>
<html lang="es">
<head>
    @php
        $variant = trim($__env->yieldContent('variant')) ?: 'guest';
        $code = trim($__env->yieldContent('code')) ?: 'Error';
        $title = trim($__env->yieldContent('title')) ?: 'Ocurrio un problema';
        $message = trim($__env->yieldContent('message')) ?: 'No pudimos completar la accion.';
        $detail = trim($__env->yieldContent('detail')) ?: 'Intenta de nuevo en unos minutos.';
        $eyebrow = trim($__env->yieldContent('eyebrow')) ?: ($variant === 'app' ? 'Area interna SGN' : 'Acceso SGN');
        $primaryLabel = trim($__env->yieldContent('primary_label')) ?: (auth()->check() ? 'Ir al dashboard' : 'Volver al inicio');
        $primaryHref = trim($__env->yieldContent('primary_href')) ?: (auth()->check() ? route('dashboard') : route('login'));
        $secondaryLabel = trim($__env->yieldContent('secondary_label')) ?: 'Volver atras';
        $supportText = trim($__env->yieldContent('support_text')) ?: 'Si el problema persiste, contacta al administrador de SGN.';
    @endphp
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SGN - {{ $code }} - {{ $title }}</title>

    <!-- Icon/Fonts Fallback & Assets -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@500;600&family=Inter:wght@400;600&family=Metropolis:wght@600;700&display=swap" rel="stylesheet"/>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            justify-content: stretch;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        /* Split-screen layout */
        .split-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100vh;
        }

        @media (min-width: 768px) {
            .split-container {
                flex-direction: row;
            }
        }

        /* Left side cover */
        .left-col {
            display: none;
        }

        @media (min-width: 768px) {
            .left-col {
                display: flex;
                width: 50%;
                position: relative;
                overflow: hidden;
                align-items: flex-end;
                justify-content: flex-start;
                padding: 4rem;
                z-index: 10;
                background-size: cover;
                background-position: center;
                box-shadow: 12px 0 32px rgba(0,0,0,0.06);
            }
        }

        .left-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 20, 82, 0.95) 0%, rgba(0, 62, 199, 0.65) 60%, rgba(0, 62, 199, 0.3) 100%);
            z-index: 1;
        }

        .left-content {
            position: relative;
            z-index: 2;
            color: #ffffff;
            max-width: 480px;
        }

        .left-logo {
            max-width: 200px;
            margin-bottom: 2rem;
            display: block;
        }

        .left-eyebrow {
            font-family: 'Geist', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: rgba(255, 255, 255, 0.85);
        }

        .left-title {
            font-family: 'Metropolis', sans-serif;
            font-size: 2.25rem;
            font-weight: 700;
            margin-top: 0.75rem;
            line-height: 1.2;
        }

        .left-detail {
            font-size: 1.05rem;
            margin-top: 1rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Right side card container */
        .right-col {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.25rem;
            background-color: #f1f5f9;
            position: relative;
        }

        @media (min-width: 768px) {
            .right-col {
                width: 50%;
                padding: 4rem;
            }
        }

        /* Card container */
        .error-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border: 1px solid rgba(203, 213, 225, 0.5);
            border-radius: 20px;
            padding: 3rem 2.25rem;
            box-shadow: 0 10px 30px -8px rgba(15, 23, 42, 0.08);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 10;
        }

        @media (max-width: 767px) {
            .error-card {
                margin-top: 3.5rem;
            }
        }

        /* Mobile top header logo */
        .mobile-header {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        @media (min-width: 768px) {
            .mobile-header {
                display: none;
            }
        }

        .mobile-logo {
            height: 2.2rem;
            width: auto;
            object-fit: contain;
        }

        .mobile-eyebrow {
            font-family: 'Geist', sans-serif;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.1em;
            color: #64748b;
        }

        /* Custom pills */
        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 9999px;
            padding: 0.4rem 1rem;
            font-family: 'Geist', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
        }

        .badge-error {
            background-color: #fee2e2;
            color: #ba1a1a;
            border: 1px solid #fecaca;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #003ec7;
            border: 1px solid #bfdbfe;
        }

        /* Card typography */
        .error-message-title {
            font-family: 'Metropolis', sans-serif;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 0.75rem;
            text-align: left;
        }

        .error-message-detail {
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1.75rem;
            text-align: left;
        }

        /* Protected session banner */
        .shield-banner {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.75rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            text-align: left;
        }

        .shield-icon {
            color: #003ec7;
            font-size: 1.5rem;
            line-height: 1;
            margin-top: 0.1rem;
        }

        .shield-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.2rem;
        }

        .shield-text {
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.4;
        }

        /* Buttons styles */
        .btn-row {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        @media (min-width: 480px) {
            .btn-row {
                flex-direction: row;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.25rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            border: none;
            outline: none;
            width: 100%;
        }

        @media (min-width: 480px) {
            .btn {
                width: 50%;
            }
        }

        .btn-primary {
            background-color: #003ec7;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 62, 199, 0.15);
        }

        .btn-primary:hover {
            background-color: #002da0;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 62, 199, 0.2);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
            transform: translateY(-1px);
        }

        .btn-game {
            background-color: #ffffff;
            color: #003ec7;
            border: 2px solid rgba(0, 62, 199, 0.15);
            margin-top: 0.75rem;
            width: 100%;
        }

        .btn-game:hover {
            border-color: #003ec7;
            background-color: rgba(0, 62, 199, 0.04);
            transform: translateY(-1px);
        }

        .hidden {
            display: none !important;
        }

        /* Gusanito Game Section */
        .game-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0 0.25rem;
        }

        .game-title {
            font-family: 'Geist', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #003ec7;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .game-stats {
            font-size: 0.75rem;
            color: #64748b;
            font-family: 'DM Mono', monospace;
        }

        .game-stats strong {
            color: #0f172a;
        }

        #gameCanvas {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            background-color: #0f172a;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        /* D-Pad controls */
        .dpad-container {
            display: grid;
            grid-template-columns: repeat(3, 44px);
            gap: 8px;
            justify-content: center;
            margin-top: 1rem;
        }

        .dpad-btn {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            width: 44px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            outline: none;
        }

        .dpad-btn:hover, .dpad-btn:active {
            background-color: #dbeafe !important;
            border-color: rgba(0, 62, 199, 0.3) !important;
            color: #003ec7 !important;
        }

        .dpad-btn-center {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #64748b;
            width: 44px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            outline: none;
        }

        .dpad-btn-center:hover {
            background-color: #dbeafe !important;
            color: #003ec7 !important;
        }

        /* Footer */
        .card-footer {
            margin-top: 2.25rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1.25rem;
            font-family: 'Geist', sans-serif;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="split-container">
        <!-- Left Column (Desktop Hero background) -->
        <div class="left-col" style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuAKF0jUrYV6T-M3Wy61H0N2NGbcmpmDH_jFJmqfX07MM9IVzyCuD7gv1kkQ8k76Xssjf4bK_OT3VSCXEtAJ4hlEbzsCdpXY9NU_3DegrI9GOF7tUf3KfXME_XFWFmPXGAxX7FyGP92d48FXoR2sjCBYlAvC417GSUsgKJV0sgmbyGdb3yZDjvjuOZTC7TB-1Qv6XwqYhm-v6DdYzhgOn4qFzK7rfDw7v4MnqZ5TXpQTKZPPhRHEjF4t4DsrlDy1xJF0BBNfWPRhZDk');">
            <div class="left-overlay"></div>
            <div class="left-content">
                <img alt="SGN Logo" class="left-logo" src="{{ asset('logosgn1.png') }}" onerror="this.style.display='none'"/>
                <div class="left-eyebrow">{{ $eyebrow }}</div>
                <h1 class="left-title">{{ $title }}</h1>
                <p class="left-detail">{{ $detail }}</p>
            </div>
        </div>

        <!-- Right Column (Centered Card content) -->
        <div class="right-col">
            <!-- Mobile Header Only -->
            <div class="mobile-header">
                <img alt="SGN Logo" class="mobile-logo" src="{{ asset('logosgn1.png') }}" onerror="this.style.display='none'"/>
                <span class="mobile-eyebrow">{{ $eyebrow }}</span>
            </div>
            
            <div class="error-card">
                <!-- Error Details view -->
                <div id="errorDetails">
                    <div class="error-badge {{ in_array($code, ['500', '503']) ? 'badge-error' : (in_array($code, ['403', '404']) ? 'badge-info' : 'badge-warning') }}">
                        <span class="material-symbols-outlined" style="font-size: 14px;">release_alert</span>
                        <span>Error {{ $code }}</span>
                    </div>
                    
                    <h2 class="error-message-title">{{ $message }}</h2>
                    <p class="error-message-detail">{{ $detail }}</p>
                    
                    <!-- Security shield alert -->
                    <div class="shield-banner">
                        <span class="material-symbols-outlined shield-icon">shield_person</span>
                        <div>
                            <h4 class="shield-title">SGN protegió tu sesión</h4>
                            <p class="shield-text">{{ $supportText }}</p>
                        </div>
                    </div>

                    <div class="btn-row">
                        <a href="{{ $primaryHref }}" class="btn btn-primary">
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                            <span>{{ $primaryLabel }}</span>
                        </a>
                        <button type="button" onclick="sgnGoBack('{{ $primaryHref }}')" class="btn btn-secondary">
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                            <span>{{ $secondaryLabel }}</span>
                        </button>
                    </div>
                    
                    <!-- Play Game trigger -->
                    <button type="button" id="btnPlayGame" class="btn btn-game">
                        <span class="material-symbols-outlined" style="font-size: 16px;">gamepad</span>
                        <span>Jugar al Gusanito</span>
                    </button>
                </div>

                <!-- Game Section -->
                <div id="gameArea" class="hidden">
                    <div class="game-header">
                        <span class="game-title">
                            <span class="material-symbols-outlined" style="font-size: 16px;">gamepad</span> Gusanito SGN
                        </span>
                        <span class="game-stats">
                            Ptos: <strong id="gameScore">0</strong> | Record: <strong id="gameHighScore">0</strong>
                        </span>
                    </div>
                    
                    <canvas id="gameCanvas" width="320" height="320"></canvas>
                    
                    <!-- Dpad controls -->
                    <div class="dpad-container">
                        <div></div>
                        <button type="button" class="dpad-btn" onclick="changeDir('up')">
                            <span class="material-symbols-outlined">arrow_drop_up</span>
                        </button>
                        <div></div>
                        <button type="button" class="dpad-btn" onclick="changeDir('left')">
                            <span class="material-symbols-outlined">arrow_left</span>
                        </button>
                        <button type="button" class="dpad-btn-center" onclick="resetGame()">
                            <span class="material-symbols-outlined">refresh</span>
                        </button>
                        <button type="button" class="dpad-btn" onclick="changeDir('right')">
                            <span class="material-symbols-outlined">arrow_right</span>
                        </button>
                        <div></div>
                        <button type="button" class="dpad-btn" onclick="changeDir('down')">
                            <span class="material-symbols-outlined">arrow_drop_down</span>
                        </button>
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                        <button id="btnBackToError" class="btn btn-secondary" style="width: auto; padding: 0.6rem 1.5rem;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                            <span>Volver al error</span>
                        </button>
                    </div>
                </div>

                <!-- Footer text -->
                <div class="card-footer">
                    SGN | Uso exclusivo para personal autorizado
                </div>
            </div>
        </div>
    </div>

    <script>
        let canvas, ctx;
        let snake, food, dx, dy, score, highScore, gameInterval;
        const gridSize = 16;
        const tileCount = 20; // 320 / 16 = 20 tiles

        document.getElementById('btnPlayGame').addEventListener('click', function() {
            document.getElementById('errorDetails').classList.add('hidden');
            document.getElementById('gameArea').classList.remove('hidden');
            initGame();
        });

        document.getElementById('btnBackToError').addEventListener('click', function() {
            if (gameInterval) clearInterval(gameInterval);
            document.getElementById('gameArea').classList.add('hidden');
            document.getElementById('errorDetails').classList.remove('hidden');
        });

        function initGame() {
            canvas = document.getElementById('gameCanvas');
            ctx = canvas.getContext('2d');
            
            // Load High Score
            highScore = localStorage.getItem('sgn_snake_highscore') || 0;
            document.getElementById('gameHighScore').textContent = highScore;
            
            resetGame();
            
            // Listen for keys
            document.removeEventListener('keydown', handleKeyPress); // Prevent duplicate listeners
            document.addEventListener('keydown', handleKeyPress);
        }

        function resetGame() {
            snake = [
                {x: 10, y: 10},
                {x: 9, y: 10},
                {x: 8, y: 10}
            ];
            dx = 1;
            dy = 0;
            score = 0;
            document.getElementById('gameScore').textContent = score;
            spawnFood();
            
            if (gameInterval) clearInterval(gameInterval);
            gameInterval = setInterval(updateGame, 100);
        }

        function spawnFood() {
            food = {
                x: Math.floor(Math.random() * tileCount),
                y: Math.floor(Math.random() * tileCount)
            };
            // Make sure food is not on snake
            let foodOnSnake = false;
            for (let i = 0; i < snake.length; i++) {
                if (snake[i].x === food.x && snake[i].y === food.y) {
                    foodOnSnake = true;
                    break;
                }
            }
            if (foodOnSnake) {
                spawnFood();
            }
        }

        function updateGame() {
            // Move snake head
            const head = {x: snake[0].x + dx, y: snake[0].y + dy};
            
            // Check wall collision (game over) or self collision
            if (head.x < 0 || head.x >= tileCount || head.y < 0 || head.y >= tileCount || checkSelfCollision(head)) {
                gameOver();
                return;
            }
            
            snake.unshift(head);
            
            // Check food collision
            if (head.x === food.x && head.y === food.y) {
                score += 10;
                document.getElementById('gameScore').textContent = score;
                if (score > highScore) {
                    highScore = score;
                    localStorage.setItem('sgn_snake_highscore', highScore);
                    document.getElementById('gameHighScore').textContent = highScore;
                }
                spawnFood();
            } else {
                snake.pop();
            }
            
            draw();
        }

        function checkSelfCollision(head) {
            for (let i = 1; i < snake.length; i++) {
                if (snake[i].x === head.x && snake[i].y === head.y) {
                    return true;
                }
            }
            return false;
        }

        function draw() {
            // Clear canvas (Dark slate to match modern console vibe)
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw grid lines subtly
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.025)';
            ctx.lineWidth = 1;
            for(let i=0; i<tileCount; i++) {
                ctx.beginPath();
                ctx.moveTo(i * gridSize, 0);
                ctx.lineTo(i * gridSize, canvas.height);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(0, i * gridSize);
                ctx.lineTo(canvas.width, i * gridSize);
                ctx.stroke();
            }
            
            // Draw Snake
            snake.forEach((part, index) => {
                // Head is SGN cyan, body is SGN primary blue
                ctx.fillStyle = index === 0 ? '#38bdf8' : '#003ec7';
                ctx.beginPath();
                
                const x = part.x * gridSize + 1;
                const y = part.y * gridSize + 1;
                const w = gridSize - 2;
                const h = gridSize - 2;
                const r = 4;
                ctx.roundRect ? ctx.roundRect(x, y, w, h, r) : ctx.rect(x, y, w, h);
                ctx.fill();
            });
            
            // Draw Food (glowing SGN Error Red orb)
            ctx.fillStyle = '#ba1a1a';
            ctx.shadowColor = '#ba1a1a';
            ctx.shadowBlur = 8;
            ctx.beginPath();
            ctx.arc(food.x * gridSize + gridSize/2, food.y * gridSize + gridSize/2, gridSize/2 - 2, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0; // Reset shadow
        }

        function handleKeyPress(e) {
            // Prevent default scrolling keys
            if([32, 37, 38, 39, 40].indexOf(e.keyCode) > -1) {
                e.preventDefault();
            }
            
            switch(e.keyCode) {
                case 37: // Left
                case 65: // A
                    if (dx === 0) { dx = -1; dy = 0; }
                    break;
                case 38: // Up
                case 87: // W
                    if (dy === 0) { dx = 0; dy = -1; }
                    break;
                case 39: // Right
                case 68: // D
                    if (dx === 0) { dx = 1; dy = 0; }
                    break;
                case 40: // Down
                case 83: // S
                    if (dy === 0) { dx = 0; dy = 1; }
                    break;
            }
        }

        function changeDir(dir) {
            switch(dir) {
                case 'left':
                    if (dx === 0) { dx = -1; dy = 0; }
                    break;
                case 'up':
                    if (dy === 0) { dx = 0; dy = -1; }
                    break;
                case 'right':
                    if (dx === 0) { dx = 1; dy = 0; }
                    break;
                case 'down':
                    if (dy === 0) { dx = 0; dy = 1; }
                    break;
            }
        }

        function gameOver() {
            clearInterval(gameInterval);
            ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = '#ba1a1a';
            ctx.font = 'bold 20px "Geist", sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('FIN DEL JUEGO', canvas.width/2, canvas.height/2 - 10);
            
            ctx.fillStyle = '#64748b';
            ctx.font = '13px "Inter", sans-serif';
            ctx.fillText('Presiona el botón central para reiniciar', canvas.width/2, canvas.height/2 + 20);
        }

        function sgnGoBack(fallbackUrl) {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = fallbackUrl;
        }
    </script>
</body>
</html>
