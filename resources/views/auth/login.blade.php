<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SGN: Servicio Gestion Novitec - Iniciar Sesion</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@500;600&family=Inter:wght@400;600&family=Metropolis:wght@600;700&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "error": "#ba1a1a",
                        "surface-dim": "#d8dadc",
                        "surface-container-low": "#f2f4f6",
                        "on-primary-fixed": "#001452",
                        "on-tertiary": "#ffffff",
                        "tertiary": "#952200",
                        "secondary-fixed-dim": "#b7c8e1",
                        "on-tertiary-container": "#ffddd5",
                        "primary": "#003ec7",
                        "surface": "#f7f9fb",
                        "on-surface-variant": "#434656",
                        "tertiary-container": "#bf3003",
                        "secondary-container": "#d0e1fb",
                        "surface-variant": "#e0e3e5",
                        "outline-variant": "#c3c5d9",
                        "background": "#f7f9fb",
                        "on-secondary-fixed-variant": "#38485d",
                        "on-primary-fixed-variant": "#0038b6",
                        "on-primary-container": "#dfe3ff",
                        "secondary-fixed": "#d3e4fe",
                        "inverse-primary": "#b7c4ff",
                        "surface-container-highest": "#e0e3e5",
                        "on-secondary": "#ffffff",
                        "on-background": "#191c1e",
                        "surface-container-lowest": "#ffffff",
                        "inverse-surface": "#2d3133",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "outline": "#737688",
                        "inverse-on-surface": "#eff1f3",
                        "tertiary-fixed": "#ffdbd2",
                        "primary-container": "#0052ff",
                        "primary-fixed-dim": "#b7c4ff",
                        "secondary": "#505f76",
                        "on-tertiary-fixed-variant": "#891e00",
                        "on-tertiary-fixed": "#3c0800",
                        "surface-container-high": "#e6e8ea",
                        "surface-bright": "#f7f9fb",
                        "primary-fixed": "#dde1ff",
                        "on-secondary-fixed": "#0b1c30",
                        "surface-tint": "#004ced",
                        "surface-container": "#eceef0",
                        "on-error-container": "#93000a",
                        "on-secondary-container": "#54647a",
                        "tertiary-fixed-dim": "#ffb4a1",
                        "on-surface": "#191c1e",
                        "error-container": "#ffdad6"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "md": "16px",
                        "margin-mobile": "16px",
                        "base": "4px",
                        "sm": "12px",
                        "xl": "32px",
                        "xs": "8px",
                        "gutter": "20px",
                        "margin-desktop": "40px",
                        "lg": "24px"
                    },
                    fontFamily: {
                        "headline-lg": ["Metropolis"],
                        "label-caps": ["Geist"],
                        "body-sm": ["Inter"],
                        "headline-lg-mobile": ["Metropolis"],
                        "display-lg": ["Metropolis"],
                        "mono-data": ["Geist"],
                        "title-md": ["Inter"],
                        "body-md": ["Inter"]
                    },
                    fontSize: {
                        "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "label-caps": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "600" }],
                        "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                        "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "mono-data": ["13px", { lineHeight: "18px", fontWeight: "500" }],
                        "title-md": ["18px", { lineHeight: "24px", fontWeight: "600" }],
                        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface text-on-surface min-h-screen flex antialiased selection:bg-primary selection:text-on-primary">
<div class="flex flex-col md:flex-row w-full min-h-screen">
    <div class="hidden md:flex md:w-1/2 relative bg-primary-container overflow-hidden items-end justify-start p-margin-desktop shadow-2xl z-10"
         style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAKF0jUrYV6T-M3Wy61H0N2NGbcmpmDH_jFJmqfX07MM9IVzyCuD7gv1kkQ8k76Xssjf4bK_OT3VSCXEtAJ4hlEbzsCdpXY9NU_3DegrI9GOF7tUf3KfXME_XFWFmPXGAxX7FyGP92d48FXoR2sjCBYlAvC417GSUsgKJV0sgmbyGdb3yZDjvjuOZTC7TB-1Qv6XwqYhm-v6DdYzhgOn4qFzK7rfDw7v4MnqZ5TXpQTKZPPhRHEjF4t4DsrlDy1xJF0BBNfWPRhZDk'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-gradient-to-t from-on-primary-fixed/90 via-on-primary-fixed/40 to-transparent"></div>
        <div class="relative z-10 text-on-primary max-w-lg mb-xl text-center">
            <img alt="SGN Logo" class="w-full max-w-[240px] mb-lg block mx-auto" src="{{ asset('logosgn1.png') }}"/>
            <p class="font-body-md text-body-md mt-md opacity-80 max-w-md">Sistema avanzado para la gestion tecnica y operativa, disenado para maximizar la eficiencia en campo.</p>
        </div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary opacity-20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
    </div>

    <div class="w-full md:w-1/2 flex items-center justify-center p-margin-mobile md:p-margin-desktop bg-surface relative">
        <div class="absolute top-margin-mobile left-margin-mobile md:hidden flex flex-col">
            <img alt="SGN Logo" class="h-10 w-auto mb-xs object-contain" src="{{ asset('logosgn1.png') }}"/>
            <span class="font-label-caps text-label-caps text-on-surface-variant">Servicio Gestion Novitec</span>
        </div>

        <div class="w-full max-w-[420px] bg-surface-container-lowest p-xl rounded-xl border border-outline-variant/30 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.05)] relative z-10">
            <div class="mb-xl text-center md:text-left">
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface mb-xs">Bienvenido de nuevo</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Accede a tu panel de gestion tecnica</p>
            </div>

            @if(request()->query('error'))
                <div class="mb-md rounded-lg border border-error/30 bg-error-container px-sm py-sm">
                    <p class="font-body-sm text-body-sm text-on-error-container">
                        @if(request()->query('error') === 'db')
                            No se pudo conectar a la base de datos. Verifique conectividad y credenciales del servidor.
                        @elseif(request()->query('error') === 'inactivo')
                            El usuario se encuentra inactivo. Contacte al administrador.
                        @else
                            Usuario o contrasena incorrectos. Intente de nuevo.
                        @endif
                    </p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-md rounded-lg border border-error/30 bg-error-container px-sm py-sm">
                    <p class="font-body-sm text-body-sm text-on-error-container">{{ $errors->first() }}</p>
                </div>
            @endif

            <form class="space-y-lg" action="{{ route('auth.validar') }}" method="POST">
                @csrf
                <div class="space-y-xs">
                    <label class="block font-label-caps text-label-caps text-on-surface-variant" for="usuario">Email o Usuario</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-sm flex items-center pointer-events-none text-on-surface-variant">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">person</span>
                        </div>
                        <input id="usuario" name="usuario" type="text" required autofocus autocomplete="username"
                               value="{{ old('usuario') }}"
                               class="w-full pl-xl pr-sm py-sm bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none"
                               placeholder="ejemplo@novitec.com"/>
                    </div>
                </div>

                <div class="space-y-xs">
                    <div class="flex justify-between items-center">
                        <label class="block font-label-caps text-label-caps text-on-surface-variant" for="clave">Contrasena</label>
                        <span class="font-body-sm text-body-sm text-primary opacity-60">Soporte interno</span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-sm flex items-center pointer-events-none text-on-surface-variant">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">lock</span>
                        </div>
                        <input id="clave" name="clave" type="password" required autocomplete="current-password"
                               class="w-full pl-xl pr-xl py-sm bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none"
                               placeholder="••••••••"/>
                        <button id="toggle-pass" aria-label="Mostrar u ocultar contrasena"
                                class="absolute inset-y-0 right-0 pr-sm flex items-center text-on-surface-variant hover:text-primary transition-colors focus:outline-none"
                                type="button">
                            <span id="toggle-pass-icon" class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary/20 bg-surface-container-low cursor-pointer"/>
                    <label class="ml-sm font-body-sm text-body-sm text-on-surface-variant cursor-pointer select-none" for="remember">Recuerdame</label>
                </div>

                <button type="submit"
                        class="w-full flex justify-center items-center gap-xs py-sm px-md bg-primary text-on-primary font-title-md text-title-md rounded-lg hover:bg-primary-container hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 active:shadow-sm transition-all duration-200 group">
                    Entrar
                    <span class="material-symbols-outlined text-on-primary opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all" style="font-variation-settings: 'FILL' 0;">arrow_forward</span>
                </button>
            </form>

            <div class="mt-xl text-center border-t border-outline-variant/30 pt-md">
                <p class="font-label-caps text-label-caps text-on-surface-variant opacity-70">© 2024 SGN. Uso exclusivo personal autorizado.</p>
            </div>
        </div>

        <div class="absolute bottom-0 right-0 w-full h-1/3 bg-gradient-to-t from-surface-variant/20 to-transparent pointer-events-none"></div>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('toggle-pass');
    const passInput = document.getElementById('clave');
    const passIcon = document.getElementById('toggle-pass-icon');

    if (toggleBtn && passInput && passIcon) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            passIcon.textContent = isPassword ? 'visibility' : 'visibility_off';
        });
    }
</script>
</body>
</html>
