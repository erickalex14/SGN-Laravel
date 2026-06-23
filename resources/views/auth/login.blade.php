<!DOCTYPE html>
<html class="light" lang="es">
<head>
    @php
        $loginErrorCatalog = [
            'db' => ['title' => 'Conexion no disponible', 'message' => 'No se pudo conectar a la base de datos. Verifica conectividad y credenciales del servidor.', 'tone' => 'error', 'icon' => 'dns'],
            'throttle' => ['title' => 'Acceso pausado temporalmente', 'message' => 'Detectamos demasiados intentos fallidos. Espera 1 minuto antes de volver a intentar.', 'tone' => 'warning', 'icon' => 'timer'],
            'inactivo' => ['title' => 'Usuario inactivo', 'message' => 'Tu cuenta esta inactiva. Contacta al administrador para reactivar el acceso.', 'tone' => 'warning', 'icon' => 'person_off'],
            '1' => ['title' => 'Credenciales incorrectas', 'message' => 'Usuario o contrasena incorrectos. Revisa los datos e intenta de nuevo.', 'tone' => 'error', 'icon' => 'lock_person'],
        ];
        $loginError = $loginErrorCatalog[(string) request()->query('error', '')] ?? null;
        $validationError = $errors->any() ? ['title' => 'Datos incompletos', 'message' => $errors->first(), 'tone' => 'warning', 'icon' => 'error'] : null;
        $feedbackCard = $validationError ?? $loginError;
        $feedbackToneClasses = ['error' => 'border-error/30 bg-error-container text-on-error-container', 'warning' => 'border-[#f2d48a] bg-warning-container text-on-warning-container'];
        $feedbackBadgeClasses = ['error' => 'bg-error/10 text-error', 'warning' => 'bg-[#7a5600]/10 text-[#7a5600]'];
    @endphp
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SGN: Servicio Gestion Novitec - Iniciar Sesion</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@500;600&family=Inter:wght@400;600&family=Metropolis:wght@600;700&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: {
                error: '#ba1a1a', primary: '#003ec7', 'on-surface': '#191c1e', surface: '#f7f9fb', outline: '#737688',
                'on-surface-variant': '#434656', 'surface-container-low': '#f2f4f6',
                'surface-container-lowest': '#ffffff', 'surface-variant': '#e0e3e5',
                'outline-variant': '#c3c5d9', 'primary-container': '#0052ff', 'error-container': '#ffdad6',
                'on-error-container': '#93000a', 'warning-container': '#fff4cc', 'on-warning-container': '#7a5600'
            }, fontFamily: {
                'headline-lg-mobile': ['Metropolis'], 'label-caps': ['Geist'], 'body-sm': ['Inter'], 'title-md': ['Inter'], 'body-md': ['Inter']
            }, fontSize: {
                'label-caps': ['12px', { lineHeight: '16px', letterSpacing: '0.05em', fontWeight: '600' }],
                'body-sm': ['14px', { lineHeight: '20px', fontWeight: '400' }],
                'headline-lg-mobile': ['24px', { lineHeight: '32px', fontWeight: '600' }],
                'title-md': ['18px', { lineHeight: '24px', fontWeight: '600' }],
                'body-md': ['16px', { lineHeight: '24px', fontWeight: '400' }]
            } } }
        }
    </script>
</head>
<body class="bg-surface text-on-surface min-h-screen flex antialiased selection:bg-primary selection:text-white">
<div class="flex flex-col md:flex-row w-full min-h-screen">
    <div class="hidden md:flex md:w-1/2 relative bg-primary-container overflow-hidden items-end justify-start p-10 shadow-2xl z-10" style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuAKF0jUrYV6T-M3Wy61H0N2NGbcmpmDH_jFJmqfX07MM9IVzyCuD7gv1kkQ8k76Xssjf4bK_OT3VSCXEtAJ4hlEbzsCdpXY9NU_3DegrI9GOF7tUf3KfXME_XFWFmPXGAxX7FyGP92d48FXoR2sjCBYlAvC417GSUsgKJV0sgmbyGdb3yZDjvjuOZTC7TB-1Qv6XwqYhm-v6DdYzhgOn4qFzK7rfDw7v4MnqZ5TXpQTKZPPhRHEjF4t4DsrlDy1xJF0BBNfWPRhZDk');background-size:cover;background-position:center;">
        <div class="absolute inset-0 bg-gradient-to-t from-[#001452]/90 via-[#001452]/40 to-transparent"></div>
        <div class="relative z-10 text-white max-w-lg mb-8 text-center">
            <img alt="SGN Logo" class="w-full max-w-[240px] mb-6 block mx-auto" src="{{ asset('logosgn1.png') }}"/>
            <p class="font-body-md text-body-md mt-4 opacity-80 max-w-md">Sistema avanzado para la gestion tecnica y operativa, disenado para maximizar la eficiencia en campo.</p>
        </div>
    </div>
    <div class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-10 bg-surface relative">
        <div class="absolute top-4 left-4 md:hidden flex flex-col">
            <img alt="SGN Logo" class="h-10 w-auto mb-2 object-contain" src="{{ asset('logosgn1.png') }}"/>
            <span class="font-label-caps text-label-caps text-on-surface-variant">Servicio Gestion Novitec</span>
        </div>
        <div class="w-full max-w-[420px] bg-surface-container-lowest p-8 rounded-xl border border-outline-variant/30 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.05)] relative z-10">
            <div class="mb-8 text-center md:text-left">
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface mb-2">Bienvenido de nuevo</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Accede a tu panel de gestion tecnica</p>
            </div>
            @if($feedbackCard)
                <div class="mb-4 rounded-xl border px-4 py-4 {{ $feedbackToneClasses[$feedbackCard['tone']] ?? $feedbackToneClasses['error'] }}">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 rounded-full px-2 py-2 {{ $feedbackBadgeClasses[$feedbackCard['tone']] ?? $feedbackBadgeClasses['error'] }}">{{ $feedbackCard['icon'] }}</span>
                        <div>
                            <p class="font-title-md text-title-md">{{ $feedbackCard['title'] }}</p>
                            <p class="font-body-sm text-body-sm mt-2">{{ $feedbackCard['message'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
            <form class="space-y-6" action="{{ route('auth.validar') }}" method="POST">
                @csrf
                <div class="space-y-2">
                    <label class="block font-label-caps text-label-caps text-on-surface-variant" for="usuario">Email o Usuario</label>
                    <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant"><span class="material-symbols-outlined">person</span></div><input id="usuario" name="usuario" type="text" required autofocus autocomplete="username" value="{{ old('usuario') }}" class="w-full pl-12 pr-3 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none" placeholder="ejemplo@novitec.com"/></div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center"><label class="block font-label-caps text-label-caps text-on-surface-variant" for="clave">Contrasena</label><span class="font-body-sm text-body-sm text-primary opacity-60">Soporte interno</span></div>
                    <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant"><span class="material-symbols-outlined">lock</span></div><input id="clave" name="clave" type="password" required autocomplete="current-password" class="w-full pl-12 pr-12 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none" placeholder="........"/><button id="toggle-pass" aria-label="Mostrar u ocultar contrasena" class="absolute inset-y-0 right-0 pr-3 flex items-center text-on-surface-variant hover:text-primary transition-colors focus:outline-none" type="button"><span id="toggle-pass-icon" class="material-symbols-outlined">visibility_off</span></button></div>
                </div>
                <div class="flex items-center"><input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary/20 bg-surface-container-low cursor-pointer"/><label class="ml-3 font-body-sm text-body-sm text-on-surface-variant cursor-pointer select-none" for="remember">Recuerdame</label></div>
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 bg-primary text-white font-title-md text-title-md rounded-lg hover:bg-primary-container hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 active:shadow-sm transition-all duration-200 group">Entrar<span class="material-symbols-outlined opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span></button>
            </form>
            <div class="mt-8 text-center border-t border-outline-variant/30 pt-4"><p class="font-label-caps text-label-caps text-on-surface-variant opacity-70">© 2024 SGN. Uso exclusivo personal autorizado.</p></div>
        </div>
    </div>
</div>
<script>const toggleBtn=document.getElementById('toggle-pass');const passInput=document.getElementById('clave');const passIcon=document.getElementById('toggle-pass-icon');if(toggleBtn&&passInput&&passIcon){toggleBtn.addEventListener('click',()=>{const isPassword=passInput.type==='password';passInput.type=isPassword?'text':'password';passIcon.textContent=isPassword?'visibility':'visibility_off';});}</script>
</body>
</html>

