<!DOCTYPE html>
<html class="light" lang="es">
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
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@500;600&family=Inter:wght@400;600&family=Metropolis:wght@600;700&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: {
                error: '#ba1a1a', primary: '#003ec7', 'on-surface': '#191c1e', surface: '#f7f9fb', outline: '#737688',
                'on-background': '#191c1e', 'on-primary': '#ffffff', 'on-primary-fixed': '#001452',
                'on-surface-variant': '#434656', 'primary-container': '#0052ff',
                'secondary-container': '#d0e1fb', 'surface-container': '#eceef0',
                'surface-container-low': '#f2f4f6', 'surface-container-highest': '#e0e3e5',
                'surface-container-lowest': '#ffffff', 'outline-variant': '#c3c5d9',
                'error-container': '#ffdad6', 'on-error-container': '#93000a',
                'warning-container': '#fff4cc', 'on-warning-container': '#7a5600'
            }, fontFamily: {
                'headline-lg': ['Metropolis'], 'label-caps': ['Geist'], 'body-sm': ['Inter'],
                'headline-lg-mobile': ['Metropolis'], 'title-md': ['Inter'], 'body-md': ['Inter']
            }, fontSize: {
                'headline-lg': ['32px', { lineHeight: '40px', letterSpacing: '0', fontWeight: '600' }],
                'label-caps': ['12px', { lineHeight: '16px', letterSpacing: '0.05em', fontWeight: '600' }],
                'body-sm': ['14px', { lineHeight: '20px', fontWeight: '400' }],
                'headline-lg-mobile': ['24px', { lineHeight: '32px', fontWeight: '600' }],
                'title-md': ['18px', { lineHeight: '24px', fontWeight: '600' }],
                'body-md': ['16px', { lineHeight: '24px', fontWeight: '400' }]
            } } }
        }
    </script>
</head>
<body class="bg-surface text-on-background min-h-screen flex antialiased selection:bg-primary selection:text-on-primary">
<div class="flex flex-col md:flex-row w-full min-h-screen">
    <div class="hidden md:flex md:w-1/2 relative overflow-hidden items-end justify-start p-10 shadow-2xl z-10 {{ $variant === 'app' ? 'bg-primary-container' : 'bg-surface-container-highest' }}" style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuAKF0jUrYV6T-M3Wy61H0N2NGbcmpmDH_jFJmqfX07MM9IVzyCuD7gv1kkQ8k76Xssjf4bK_OT3VSCXEtAJ4hlEbzsCdpXY9NU_3DegrI9GOF7tUf3KfXME_XFWFmPXGAxX7FyGP92d48FXoR2sjCBYlAvC417GSUsgKJV0sgmbyGdb3yZDjvjuOZTC7TB-1Qv6XwqYhm-v6DdYzhgOn4qFzK7rfDw7v4MnqZ5TXpQTKZPPhRHEjF4t4DsrlDy1xJF0BBNfWPRhZDk');background-size:cover;background-position:center;">
        <div class="absolute inset-0 {{ $variant === 'app' ? 'bg-gradient-to-t from-on-primary-fixed/90 via-on-primary-fixed/45 to-transparent' : 'bg-gradient-to-t from-[#111827]/88 via-[#111827]/42 to-transparent' }}"></div>
        <div class="relative z-10 text-white max-w-lg mb-8 text-center">
            <img alt="SGN Logo" class="w-full max-w-[240px] mb-6 block mx-auto" src="{{ asset('logosgn1.png') }}"/>
            <p class="font-label-caps text-label-caps uppercase tracking-[0.18em] opacity-80">{{ $eyebrow }}</p>
            <h1 class="font-headline-lg text-headline-lg mt-3">{{ $title }}</h1>
            <p class="font-body-md text-body-md mt-4 opacity-80 max-w-md mx-auto">{{ $detail }}</p>
        </div>
    </div>
    <div class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-10 bg-surface relative">
        <div class="absolute top-4 left-4 md:hidden flex flex-col">
            <img alt="SGN Logo" class="h-10 w-auto mb-2 object-contain" src="{{ asset('logosgn1.png') }}"/>
            <span class="font-label-caps text-label-caps text-on-surface-variant">{{ $eyebrow }}</span>
        </div>
        <div class="w-full max-w-[460px] bg-surface-container-lowest p-8 rounded-xl border border-outline-variant/30 shadow-[0_8px_34px_-6px_rgba(15,23,42,0.10)] relative z-10 mt-16 md:mt-0">
            <div class="inline-flex items-center gap-2 rounded-full px-3 py-2 mb-6 {{ in_array($code, ['500', '503']) ? 'bg-error-container text-on-error-container' : (in_array($code, ['403', '404']) ? 'bg-secondary-container text-on-background' : 'bg-warning-container text-on-warning-container') }}">
                <span class="material-symbols-outlined text-[18px]">release_alert</span>
                <span class="font-label-caps text-label-caps">Error {{ $code }}</span>
            </div>
            <div class="mb-6">
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface mb-2">{{ $message }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $detail }}</p>
            </div>
            <div class="rounded-xl border border-outline-variant/40 bg-surface-container-low px-4 py-4 mb-6">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-[22px] mt-0.5">shield_person</span>
                    <div>
                        <p class="font-title-md text-title-md text-on-surface">SGN mantuvo tu sesion y contexto protegidos</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-2">{{ $supportText }}</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ $primaryHref }}" class="flex-1 inline-flex justify-center items-center gap-2 py-3 px-4 bg-primary text-white font-title-md text-title-md rounded-lg hover:bg-primary-container hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 active:shadow-sm transition-all duration-200">
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span><span>{{ $primaryLabel }}</span>
                </a>
                <button type="button" onclick="sgnGoBack('{{ $primaryHref }}')" class="flex-1 inline-flex justify-center items-center gap-2 py-3 px-4 bg-surface-container-low text-on-surface font-title-md text-title-md rounded-lg border border-outline-variant hover:bg-surface-container transition-all duration-200">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>{{ $secondaryLabel }}</span>
                </button>
            </div>
            <div class="mt-8 text-center border-t border-outline-variant/30 pt-4">
                <p class="font-label-caps text-label-caps text-on-surface-variant opacity-70">SGN | Uso exclusivo para personal autorizado</p>
            </div>
        </div>
    </div>
</div>
<script>function sgnGoBack(fallbackUrl){if(window.history.length>1){window.history.back();return;}window.location.href=fallbackUrl;}</script>
</body>
</html>



