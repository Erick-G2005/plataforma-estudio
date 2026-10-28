@php
    $appName = config('app.name');

    $statItems = [
        ['value' => $stats['subjects'], 'label' => $stats['subjects'] === 1 ? 'Materia' : 'Materias'],
        ['value' => $stats['topics'], 'label' => $stats['topics'] === 1 ? 'Tema' : 'Temas'],
        ['value' => $stats['materials'], 'label' => 'Materiales de estudio'],
        ['value' => $stats['quizzes'], 'label' => 'Quizzes y exámenes'],
    ];

    $steps = [
        ['n' => 1, 'icon' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM12 8v8M8 12h8', 'title' => 'Crea tu cuenta', 'text' => 'Solo necesitas tu nombre, un correo y una contraseña.'],
        ['n' => 2, 'icon' => 'M21 21l-4.34-4.34M19 10.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z', 'title' => 'Elige tu materia', 'text' => 'Explora la oferta académica e inscríbete en las que quieras.'],
        ['n' => 3, 'icon' => 'M4 5.5A2.5 2.5 0 0 1 6.5 3H20v18H6.5A2.5 2.5 0 0 1 4 18.5v-13ZM8 8h8M8 12h6', 'title' => 'Estudia y practica', 'text' => 'Avanza tema por tema. Al aprobar el quiz de cada uno se abre el siguiente.'],
        ['n' => 4, 'icon' => 'M8 21h8m-4-4v4M7 4h10l2 3-7 10-7-10 2-3Z', 'title' => 'Presenta el examen final', 'text' => 'Cuando completes todos los temas, presenta el examen y termina la materia.'],
    ];

    $features = [
        ['icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'bg' => 'bg-indigo-50 text-indigo-600', 'title' => 'A tu propio ritmo', 'text' => 'Sin fechas límite ni clases en vivo. Estudias cuando puedes y retomas justo donde lo dejaste.'],
        ['icon' => 'M9 12l2 2 4-4m5.6-2A10 10 0 0 1 12 3a10 10 0 0 1-8.6 5A10 10 0 0 0 12 21a10 10 0 0 0 8.6-13Z', 'bg' => 'bg-violet-50 text-violet-600', 'title' => 'Una ruta clara', 'text' => 'Los temas se desbloquean en orden, así siempre sabes cuál es tu siguiente paso.'],
        ['icon' => 'M15 10l4.5-3v10L15 14m-9 5h7a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z', 'bg' => 'bg-pink-50 text-pink-600', 'title' => 'Contenido variado', 'text' => 'Textos con formato, videos, PDF e imágenes dentro de cada tema.'],
        ['icon' => 'M3 3v18h18M7 14l4-4 4 4 6-7', 'bg' => 'bg-emerald-50 text-emerald-600', 'title' => 'Tu progreso a la vista', 'text' => 'Barras de avance por materia y un botón Continuar que te lleva a lo que sigue.'],
        ['icon' => 'M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z', 'bg' => 'bg-amber-50 text-amber-600', 'title' => 'Quizzes que enseñan', 'text' => 'Varios tipos de pregunta, explicaciones y un repaso de las que fallaste.'],
        ['icon' => 'M8 21h8m-4-4v4M7 4h10l2 3-7 10-7-10 2-3Z', 'bg' => 'bg-sky-50 text-sky-600', 'title' => 'Examen final', 'text' => 'Una evaluación que reúne lo visto en toda la materia para cerrar tu aprendizaje.'],
    ];

    $faqs = [
        ['q' => '¿Tengo que estudiar en horarios fijos?', 'a' => 'No. Entras cuando quieras, estudias el tiempo que puedas y tu avance queda guardado para retomarlo después.'],
        ['q' => '¿Cómo se desbloquean los temas?', 'a' => 'En orden. Para pasar al siguiente tema debes completar el actual: marcarlo como estudiado y, si tiene quiz, aprobarlo.'],
        ['q' => '¿Qué pasa si repruebo un quiz?', 'a' => 'Puedes volver a intentarlo. Cada quiz puede tener un límite de intentos y una espera antes de reintentar; mientras tanto repasa el material y las preguntas que fallaste.'],
        ['q' => '¿Puedo estudiar desde el celular?', 'a' => 'Sí. La plataforma se adapta a la pantalla del celular, la tableta o el computador.'],
        ['q' => '¿Qué hago si olvidé mi contraseña?', 'a' => 'En la pantalla de inicio de sesión pulsa ¿Olvidaste tu contraseña?. Te enviaremos un enlace al correo para crear una nueva.'],
    ];

    $words = ['a tu ritmo', 'paso a paso', 'sin presión', 'a tu manera'];

    $moreSubjects = max(0, $stats['subjects'] - $subjects->count());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">

    <title>{{ $appName }} · Aprende a tu propio ritmo</title>

    <meta name="description" content="{{ $appName }} es una plataforma de estudio para aprender a tu ritmo, con rutas guiadas, quizzes y examen final.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $appName }} · Aprende a tu propio ritmo">
    <meta property="og:description" content="Rutas de estudio guiadas, quizzes y examen final. Aprende a tu ritmo.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --brand-primary: #4f46e5;
            --brand-dark: #3730a3;
            --brand-soft: #eef2ff;
            --text-main: #111827;
            --text-muted: #64748b;
            --border-soft: #e5e7eb;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 84px;
        }

        body {
            color: var(--text-main);
            font-family: 'Figtree', sans-serif;
        }

        /* Animaciones de entrada */
        .motion-ready [data-reveal] {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .65s ease, transform .65s ease;
            transition-delay: var(--d, 0ms);
        }

        .motion-ready [data-reveal].is-visible {
            opacity: 1;
            transform: none;
        }

        /* Barras de progreso */
        .bar-fill {
            transition: width 1.4s cubic-bezier(.22, 1, .36, 1);
        }

        .motion-ready .bar-fill:not(.is-on) {
            width: 0 !important;
        }

        /* Elementos flotantes */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-9px); }
        }

        .float-slow {
            animation: float 6s ease-in-out infinite;
        }

        .float-fast {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(22px, -25px) scale(1.06); }
            66% { transform: translate(-15px, 15px) scale(.97); }
        }

        .blob {
            animation: blob 14s ease-in-out infinite;
        }

        @keyframes gradient-x {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .gradient-text {
            display: inline-block;
            background: linear-gradient(90deg, #4338ca, #7c3aed, #db2777, #4338ca);
            background-size: 300% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradient-x 8s ease infinite;
        }

        .word-swap {
            transition: opacity .3s ease, transform .3s ease;
        }

        .word-swap.out {
            opacity: 0;
            transform: translateY(8px);
        }

        /* Barra de navegación fija */
        #site-header {
            transition: background-color .25s ease, box-shadow .25s ease;
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
        }

        #site-header.nav-solid {
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 8px 30px rgba(15, 23, 42, .07);
        }

        .nav-link {
            transition: color .2s ease, background-color .2s ease;
        }

        .nav-link:hover,
        .nav-link.is-active {
            color: var(--brand-primary);
            background: var(--brand-soft);
        }

        #scroll-progress {
            transform: scaleX(0);
            transform-origin: left;
        }

        /* Botones */
        .btn-primary {
            box-shadow: 0 8px 20px rgba(79, 70, 229, .17);
            transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(79, 70, 229, .25);
        }

        .btn-secondary {
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            border-color: #a5b4fc;
        }

        /* Tarjetas */
        .feature-card,
        .step-card {
            border: 1px solid #eef0f5;
            border-radius: 1.25rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }

        .feature-card:hover,
        .step-card:hover {
            transform: translateY(-5px);
            border-color: #c7d2fe;
            box-shadow: 0 18px 36px rgba(79, 70, 229, .09);
        }

        /* Materias */
        .subject-card {
            border: 1px solid var(--border-soft);
            border-radius: 1.25rem;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .subject-card:hover {
            transform: translateY(-4px);
            border-color: #c7d2fe;
            box-shadow: 0 18px 35px rgba(15, 23, 42, .09);
        }

        /* Pestañas */
        .tab-btn {
            color: rgba(255, 255, 255, .8);
            transition: background-color .2s ease, color .2s ease;
        }

        .tab-btn:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .tab-btn.tab-on {
            background: #fff;
            color: #4338ca;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .08);
        }

        /* Preguntas frecuentes */
        details > summary {
            list-style: none;
        }

        details > summary::-webkit-details-marker {
            display: none;
        }

        details .chev {
            display: inline-block;
            transition: transform .25s ease;
        }

        details[open] .chev {
            transform: rotate(180deg);
        }

        #preguntas details {
            border: 1px solid #eef0f5;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        #preguntas details[open] {
            border-color: #c7d2fe;
            box-shadow: 0 8px 24px rgba(79, 70, 229, .06);
        }

        /* Pie de página */
        .footer-link {
            transition: color .2s ease, transform .2s ease;
        }

        .footer-link:hover {
            color: #a5b4fc;
            transform: translateX(3px);
        }

        /* Accesibilidad */
        a:focus-visible,
        button:focus-visible,
        summary:focus-visible {
            outline: 3px solid #a5b4fc;
            outline-offset: 4px;
        }

        @media (max-width: 640px) {
            html {
                scroll-padding-top: 76px;
            }

            .word-swap {
                display: inline;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body class="bg-white font-sans text-gray-800 antialiased">

    <a href="#contenido"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-indigo-700 focus:shadow-lg">
        Saltar al contenido
    </a>

    {{-- ═══════════════ BARRA DE NAVEGACIÓN (siempre visible) ═══════════════ --}}
    <header id="site-header" class="sticky top-0 z-50 border-b border-gray-200/70 bg-white/90">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">

            {{-- Logo y nombre --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $appName }}, ir al inicio">
                <x-application-logo class="h-8 w-8 fill-current text-indigo-600 sm:h-9 sm:w-9" />
                <span class="hidden text-lg font-extrabold tracking-tight text-gray-900 sm:inline">{{ $appName }}</span>
            </a>

            {{-- Secciones (escritorio) --}}
            <nav class="hidden items-center gap-1 text-sm font-semibold text-gray-600 lg:flex" aria-label="Secciones de la página">
                <a href="#como-funciona" data-spy class="nav-link whitespace-nowrap rounded-full px-4 py-2">Cómo funciona</a>
                <a href="#funciones" data-spy class="nav-link whitespace-nowrap rounded-full px-4 py-2">Funciones</a>
                @if ($subjects->isNotEmpty())
                    <a href="#materias" data-spy class="nav-link whitespace-nowrap rounded-full px-4 py-2">Materias</a>
                @endif
                <a href="#preguntas" data-spy class="nav-link whitespace-nowrap rounded-full px-4 py-2">Preguntas</a>
            </nav>

            {{-- Acceso: siempre visible --}}
            <div class="flex items-center gap-1.5 sm:gap-3">
                <a href="{{ route('login') }}"
                   class="whitespace-nowrap rounded-full px-3 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-100 hover:text-indigo-700 sm:px-4 sm:text-sm">
                    Iniciar sesión
                </a>

                <a href="{{ route('register') }}"
                   class="btn-primary inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-indigo-700 sm:px-5 sm:py-2.5 sm:text-sm">
                    Crear cuenta
                    <svg class="hidden h-4 w-4 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                    </svg>
                </a>

                <button id="menu-btn" type="button"
                        aria-label="Abrir menú" aria-controls="mobile-menu" aria-expanded="false"
                        class="rounded-xl p-2 text-gray-700 transition hover:bg-indigo-50 hover:text-indigo-600 lg:hidden">
                    <svg id="menu-open-icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="menu-close-icon" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Barra de progreso de lectura --}}
        <div id="scroll-progress" class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-pink-500" aria-hidden="true"></div>

        {{-- Secciones (celular y tableta) --}}
        <div id="mobile-menu" class="hidden border-t border-gray-100 bg-white lg:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 text-sm font-semibold text-gray-700 sm:px-6" aria-label="Secciones de la página (móvil)">
                <a href="#como-funciona" data-spy class="nav-link rounded-xl px-4 py-3">Cómo funciona</a>
                <a href="#funciones" data-spy class="nav-link rounded-xl px-4 py-3">Funciones</a>
                @if ($subjects->isNotEmpty())
                    <a href="#materias" data-spy class="nav-link rounded-xl px-4 py-3">Materias</a>
                @endif
                <a href="#preguntas" data-spy class="nav-link rounded-xl px-4 py-3">Preguntas frecuentes</a>
            </nav>
        </div>
    </header>

    <main id="contenido">

        {{-- ═══════════════ PORTADA ═══════════════ --}}
        <section id="inicio" class="relative isolate overflow-hidden bg-gradient-to-b from-indigo-50/80 via-white to-white pb-20 pt-12 sm:pt-16 lg:pb-28 lg:pt-20">

            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                <div class="blob absolute -left-32 top-12 h-80 w-80 rounded-full bg-indigo-200/40 blur-3xl"></div>
                <div class="blob absolute -right-20 top-32 h-96 w-96 rounded-full bg-purple-200/30 blur-3xl" style="animation-delay:-5s"></div>
                <div class="blob absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-pink-100/40 blur-3xl" style="animation-delay:-9s"></div>
            </div>

            <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">

                <div data-reveal>
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white/80 px-4 py-2 text-xs font-bold tracking-wide text-indigo-700 shadow-sm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.5 5.5L20 11l-5.5 2.5L12 19l-2.5-5.5L4 11l5.5-2.5L12 3Z"/>
                        </svg>
                        APRENDE A TU MANERA
                    </div>

                    <h1 class="mt-7 max-w-2xl text-4xl font-extrabold leading-tight tracking-tight text-gray-950 sm:text-5xl lg:text-6xl xl:text-7xl">
                        Tu aprendizaje,
                        <span class="mt-1 block">
                            <span id="word-swap" class="word-swap gradient-text" data-words="{{ json_encode($words, JSON_UNESCAPED_UNICODE) }}">{{ $words[0] }}</span>
                        </span>
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-8 text-gray-600 sm:text-lg">
                        {{ $appName }} te acompaña con una ruta de aprendizaje clara, materiales de estudio, quizzes y evaluaciones. Aprende a tu ritmo y avanza paso a paso hasta completar cada materia.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-7 py-3.5 text-base font-bold text-white hover:bg-indigo-700">
                            Comenzar a aprender
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="#como-funciona" class="btn-secondary inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 bg-white px-7 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50">
                            Conocer la plataforma
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-gray-600">
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            </svg>
                            Sin horarios fijos
                        </span>

                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            </svg>
                            Progreso guardado
                        </span>
                    </div>
                </div>

                {{-- Panel ilustrativo --}}
                <div data-reveal style="--d:150ms" class="relative mx-auto w-full max-w-lg">

                    <div class="absolute -inset-5 -z-10 rounded-[2rem] bg-gradient-to-tr from-indigo-300/40 via-purple-200/30 to-pink-200/40 blur-2xl"></div>

                    <div class="float-slow relative overflow-hidden rounded-3xl border border-white bg-white p-5 shadow-2xl shadow-indigo-950/10 sm:p-7">

                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="font-extrabold text-gray-900">Mi aprendizaje</p>
                                    <p class="mt-0.5 text-xs text-gray-500">Tu espacio de estudio</p>
                                </div>
                            </div>

                            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                Ejemplo
                            </span>
                        </div>

                        <div class="mt-7 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 p-5 text-white">
                            <p class="text-sm font-medium text-indigo-100">¡Sigue avanzando!</p>
                            <h2 class="mt-1 text-xl font-extrabold">Cada paso cuenta.</h2>
                            <p class="mt-2 text-sm leading-relaxed text-indigo-100">
                                Estudia, practica y descubre todo lo que puedes aprender.
                            </p>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900">Tus materias</h3>
                            <span class="text-xs font-medium text-gray-400">Vista ilustrativa</span>
                        </div>

                        <div class="mt-4 space-y-5">

                            <div>
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="font-semibold text-gray-800">Matemáticas Básicas</span>
                                    <span class="font-bold text-indigo-600">72%</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div class="bar-fill h-full rounded-full bg-indigo-600" data-width="72" style="width:72%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="font-semibold text-gray-800">Inglés Básico</span>
                                    <span class="font-bold text-violet-600">45%</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div class="bar-fill h-full rounded-full bg-violet-500" data-width="45" style="width:45%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="font-semibold text-gray-800">Ciencias Naturales</span>
                                    <span class="font-bold text-pink-600">18%</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div class="bar-fill h-full rounded-full bg-pink-500" data-width="18" style="width:18%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-7 flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50/70 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                                        <circle cx="12" cy="12" r="9"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-bold text-gray-900">Continúa aprendiendo</p>
                                    <p class="mt-0.5 text-xs text-gray-500">Avanza a tu propio ritmo</p>
                                </div>
                            </div>

                            <svg class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </div>
                    </div>

                    <div class="float-fast absolute -left-3 top-16 hidden items-center gap-2 rounded-2xl border border-gray-100 bg-white px-4 py-3 text-sm font-bold text-gray-700 shadow-xl sm:flex">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>
                        Avanza paso a paso
                    </div>

                    <div class="float-slow absolute -right-3 bottom-20 hidden items-center gap-2 rounded-2xl border border-gray-100 bg-white px-4 py-3 text-sm font-bold text-gray-700 shadow-xl sm:flex" style="animation-delay:-2s">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/>
                            </svg>
                        </span>
                        Aprende y practica
                    </div>

                    <p class="mt-5 text-center text-xs text-gray-400">
                        Interfaz ilustrativa; no representa datos personales reales.
                    </p>
                </div>
            </div>
        </section>

        {{-- ═══════════════ ESTADÍSTICAS REALES ═══════════════ --}}
        <section class="border-y border-gray-100 bg-gray-50/80 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center" data-reveal>
                    <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Nuestra plataforma</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-gray-900 sm:text-3xl">
                        Recursos para seguir aprendiendo
                    </h2>
                </div>

                <div class="grid grid-cols-2 gap-6 lg:grid-cols-4 lg:gap-8">
                    @foreach ($statItems as $item)
                        <div data-reveal style="--d:{{ $loop->index * 80 }}ms" class="rounded-2xl border border-gray-100 bg-white px-4 py-6 text-center shadow-sm">
                            <p class="text-3xl font-extrabold tracking-tight text-indigo-600 sm:text-4xl">
                                <span data-count="{{ $item['value'] }}">{{ $item['value'] }}</span>
                            </p>
                            <p class="mt-2 text-sm font-medium text-gray-500">{{ $item['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════ CÓMO FUNCIONA ═══════════════ --}}
        <section id="como-funciona" class="py-20 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-bold uppercase tracking-widest text-indigo-600">Empieza hoy</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                        Aprender nunca fue tan sencillo
                    </h2>
                    <p class="mt-4 text-base leading-7 text-gray-600 sm:text-lg">
                        Sigue una ruta organizada que te acompaña desde el primer tema hasta la evaluación final.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($steps as $step)
                        <article data-reveal style="--d:{{ $loop->index * 90 }}ms" class="step-card relative bg-white p-6 sm:p-7">
                            <div class="flex items-center justify-between">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                                    </svg>
                                </span>
                                <span class="text-4xl font-extrabold text-indigo-100">
                                    0{{ $step['n'] }}
                                </span>
                            </div>

                            <h3 class="mt-6 text-lg font-bold text-gray-900">{{ $step['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-gray-600">{{ $step['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════ FUNCIONES ═══════════════ --}}
        <section id="funciones" class="bg-gray-50 py-20 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-bold uppercase tracking-widest text-indigo-600">Todo en un solo lugar</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                        Herramientas para aprender mejor
                    </h2>
                    <p class="mt-4 text-base leading-7 text-gray-600 sm:text-lg">
                        Una experiencia de estudio diseñada para ayudarte a comprender, practicar y avanzar.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $feature)
                        <article data-reveal style="--d:{{ ($loop->index % 3) * 80 }}ms" class="feature-card bg-white p-6 sm:p-7">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $feature['bg'] }}">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                                </svg>
                            </span>

                            <h3 class="mt-5 text-lg font-bold text-gray-900">{{ $feature['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-gray-600">{{ $feature['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════ DEMOSTRACIÓN INTERACTIVA ═══════════════ --}}
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-700 to-violet-800 py-20 text-white sm:py-24">

            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                <div class="blob absolute -right-20 top-0 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
                <div class="blob absolute -left-20 bottom-0 h-72 w-72 rounded-full bg-purple-300/10 blur-3xl" style="animation-delay:-6s"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-bold uppercase tracking-widest text-indigo-200">Descubre la experiencia</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Así funciona el aprendizaje
                    </h2>
                    <p class="mt-4 leading-7 text-indigo-100">
                        Explora estas demostraciones ilustrativas y descubre cómo puedes organizar tu estudio.
                    </p>
                </div>

                <div data-reveal class="mt-10">

                    <div class="mx-auto flex w-fit max-w-full flex-wrap justify-center gap-1.5 rounded-full border border-white/10 bg-white/10 p-1.5" role="tablist" aria-label="Demostraciones de la plataforma">

                        <button type="button" id="tab-panel" data-tab="panel" role="tab" aria-selected="true" aria-controls="demo-panel" class="tab-btn tab-on rounded-full px-5 py-2.5 text-sm font-bold">
                            Mi panel
                        </button>

                        <button type="button" id="tab-ruta" data-tab="ruta" role="tab" aria-selected="false" aria-controls="demo-ruta" tabindex="-1" class="tab-btn rounded-full px-5 py-2.5 text-sm font-bold">
                            Ruta de estudio
                        </button>

                        <button type="button" id="tab-quiz" data-tab="quiz" role="tab" aria-selected="false" aria-controls="demo-quiz" tabindex="-1" class="tab-btn rounded-full px-5 py-2.5 text-sm font-bold">
                            Quiz
                        </button>
                    </div>

                    <div class="mx-auto mt-7 max-w-4xl rounded-3xl border border-white/50 bg-white p-5 text-gray-800 shadow-2xl sm:p-8">

                        {{-- Panel --}}
                        <div id="demo-panel" data-panel="panel" role="tabpanel" aria-labelledby="tab-panel">
                            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Panel de estudiante</p>
                                    <h3 class="mt-1 text-xl font-extrabold text-gray-900">Continúa donde lo dejaste</h3>
                                </div>
                                <span class="w-fit rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">Vista ilustrativa</span>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-gray-100 p-4">
                                    <div class="flex h-20 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white">
                                        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.5A6.5 6.5 0 0 0 5.5 12H3v9h9v-2.5ZM12 18.5A6.5 6.5 0 0 1 18.5 12H21v9h-9v-2.5ZM12 18.5V5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3h14l-2 5H7L5 3Z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-gray-900">Matemáticas Básicas</p>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full w-3/4 rounded-full bg-indigo-600"></div></div>
                                    <p class="mt-2 text-xs text-gray-500">3 de 4 temas · Ejemplo</p>
                                </div>

                                <div class="rounded-2xl border border-gray-100 p-4">
                                    <div class="flex h-20 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
                                        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v18H6.5A2.5 2.5 0 0 1 4 18.5v-13Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 8h8M8 12h6"/>
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-gray-900">Inglés Básico</p>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full w-1/2 rounded-full bg-emerald-500"></div></div>
                                    <p class="mt-2 text-xs text-gray-500">1 de 2 temas · Ejemplo</p>
                                </div>

                                <div class="rounded-2xl border border-gray-100 p-4">
                                    <div class="flex h-20 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-orange-500 text-white">
                                        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 14.5 9.5 21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5L12 3Z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-gray-900">Ciencias Naturales</p>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full w-1/5 rounded-full bg-rose-500"></div></div>
                                    <p class="mt-2 text-xs text-gray-500">Progreso ilustrativo</p>
                                </div>
                            </div>
                        </div>

                        {{-- Ruta --}}
                        <div id="demo-ruta" data-panel="ruta" role="tabpanel" aria-labelledby="tab-ruta" class="hidden">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Organiza tu estudio</p>
                                <h3 class="mt-1 text-xl font-extrabold text-gray-900">Recorrido de una materia</h3>
                                <p class="mt-2 text-sm text-gray-500">Ejemplo ilustrativo de una ruta de aprendizaje.</p>
                            </div>

                            <ol class="mt-6 space-y-3">
                                <li class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700">✓</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-900">Tema 1 · Suma y resta</p>
                                        <p class="mt-1 text-xs text-emerald-700">Tema completado</p>
                                    </div>
                                </li>

                                <li class="ms-5 flex items-center gap-3 rounded-xl border border-emerald-100 p-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700">✓</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-900">Quiz del tema</p>
                                        <p class="mt-1 text-xs text-emerald-700">Aprobado · Ejemplo 85%</p>
                                    </div>
                                </li>

                                <li class="flex items-center gap-3 rounded-xl border-2 border-indigo-200 bg-indigo-50 p-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 font-bold text-white">2</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-900">Tema 2 · Multiplicación</p>
                                        <p class="mt-1 text-xs font-semibold text-indigo-700">Siguiente paso</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-indigo-700">En curso</span>
                                </li>

                                <li class="flex items-center gap-3 rounded-xl border border-gray-200 p-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <rect x="5" y="10" width="14" height="11" rx="2"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-500">Tema 3 · División</p>
                                        <p class="mt-1 text-xs text-gray-400">Se habilita al avanzar</p>
                                    </div>
                                </li>

                                <li class="flex items-center gap-3 rounded-xl border border-violet-100 bg-violet-50 p-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-violet-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4M7 4h10l2 3-7 10-7-10 2-3Z"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-800">Examen final</p>
                                        <p class="mt-1 text-xs text-violet-600">Cierre de la materia</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        {{-- Quiz --}}
                        <div id="demo-quiz" data-panel="quiz" role="tabpanel" aria-labelledby="tab-quiz" class="hidden">
                            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Evaluación de ejemplo</p>
                                    <h3 class="mt-1 text-xl font-extrabold text-gray-900">Quiz: Multiplicación</h3>
                                    <p class="mt-1 text-sm text-gray-500">Pregunta 2 de 4</p>
                                </div>

                                <span class="w-fit rounded-full bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700">Demostración</span>
                            </div>

                            <div class="mt-5 flex gap-2" aria-label="Progreso ilustrativo de la evaluación">
                                <span class="h-2 flex-1 rounded-full bg-indigo-600"></span>
                                <span class="h-2 flex-1 rounded-full bg-indigo-600"></span>
                                <span class="h-2 flex-1 rounded-full bg-gray-200"></span>
                                <span class="h-2 flex-1 rounded-full bg-gray-200"></span>
                            </div>

                            <p class="mt-7 text-lg font-bold text-gray-900">¿Cuánto es 7 × 6?</p>

                            <div class="mt-5 space-y-3">
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
                                    <span class="h-4 w-4 rounded-full border-2 border-gray-300"></span>
                                    36
                                </div>

                                <div class="flex items-center gap-3 rounded-xl border-2 border-indigo-500 bg-indigo-50 p-4 text-sm font-bold text-indigo-800">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full border-4 border-indigo-600 bg-white"></span>
                                    42
                                    <span class="ml-auto text-xs font-bold text-indigo-600">Opción ilustrativa</span>
                                </div>

                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
                                    <span class="h-4 w-4 rounded-full border-2 border-gray-300"></span>
                                    48
                                </div>
                            </div>

                            <p class="mt-5 rounded-xl bg-gray-50 p-4 text-sm leading-6 text-gray-600">
                                En los quizzes de la plataforma puedes practicar los contenidos y comprobar lo que has aprendido.
                            </p>
                        </div>
                    </div>

                    <p class="mt-5 text-center text-xs text-indigo-200">
                        Los nombres, porcentajes y respuestas de esta demostración son ilustrativos.
                    </p>
                </div>
            </div>
        </section>

        {{-- ═══════════════ MATERIAS DISPONIBLES ═══════════════ --}}
        @if ($subjects->isNotEmpty())
            <section id="materias" class="py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    <div data-reveal class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                        <div class="max-w-2xl">
                            <span class="text-sm font-bold uppercase tracking-widest text-indigo-600">Explora y descubre</span>
                            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                                Materias disponibles
                            </h2>
                            <p class="mt-4 leading-7 text-gray-600">
                                Encuentra tu próxima oportunidad de aprender y empieza a construir nuevos conocimientos.
                            </p>
                        </div>

                        <span class="w-fit rounded-full border border-indigo-100 bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700">
                            {{ $stats['subjects'] }} {{ $stats['subjects'] === 1 ? 'materia disponible' : 'materias disponibles' }}
                        </span>
                    </div>

                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($subjects as $subject)
                            <article data-reveal style="--d:{{ ($loop->index % 3) * 80 }}ms" class="h-full">
                                <a href="{{ route('register') }}" class="subject-card group flex h-full flex-col overflow-hidden bg-white">

                                    <div class="relative overflow-hidden">
                                        <x-subject-cover :subject="$subject" class="h-44 transition duration-300 group-hover:scale-[1.03]" />

                                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-gray-950/20 to-transparent"></div>

                                        <span class="absolute right-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-indigo-700 shadow-sm">
                                            Disponible
                                        </span>
                                    </div>

                                    <div class="flex flex-1 flex-col p-6">
                                        <h3 class="text-lg font-extrabold text-gray-900 transition group-hover:text-indigo-700">
                                            {{ $subject->name }}
                                        </h3>

                                        <p class="mt-3 flex-1 text-sm leading-7 text-gray-600">
                                            {{ Str::limit($subject->description ?? '', 120) }}
                                        </p>

                                        <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
                                            <span class="flex items-center gap-2 text-sm font-medium text-gray-500">
                                                <svg class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v18H6.5A2.5 2.5 0 0 1 4 18.5v-13Z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 8h8M8 12h6"/>
                                                </svg>

                                                {{ $subject->topics_count }} {{ $subject->topics_count === 1 ? 'tema' : 'temas' }}
                                            </span>

                                            <span class="inline-flex items-center gap-1 text-sm font-bold text-indigo-600">
                                                Explorar
                                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>

                    <div data-reveal class="mt-10 text-center">
                        @if ($moreSubjects > 0)
                            <p class="mb-4 text-sm text-gray-500">
                                Y {{ $moreSubjects }} {{ $moreSubjects === 1 ? 'materia más te espera' : 'materias más te esperan' }} dentro de la plataforma.
                            </p>
                        @endif

                        <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-7 py-3.5 font-bold text-white hover:bg-indigo-700">
                            Inscribirme y comenzar
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══════════════ PREGUNTAS FRECUENTES ═══════════════ --}}
        <section id="preguntas" class="bg-gray-50 py-20 sm:py-24">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

                <div data-reveal class="text-center">
                    <span class="text-sm font-bold uppercase tracking-widest text-indigo-600">Estamos para ayudarte</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                        Preguntas frecuentes
                    </h2>
                    <p class="mt-4 leading-7 text-gray-600">
                        Resolvemos tus dudas para que puedas empezar a aprender con confianza.
                    </p>
                </div>

                <div class="mt-10 space-y-4">
                    @foreach ($faqs as $faq)
                        <details data-reveal style="--d:{{ ($loop->index % 3) * 60 }}ms" class="rounded-2xl bg-white p-5 shadow-sm sm:p-6">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 font-bold leading-6 text-gray-900">
                                <span>{{ $faq['q'] }}</span>

                                <span class="chev flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </span>
                            </summary>

                            <p class="mt-4 border-t border-gray-100 pt-4 text-sm leading-7 text-gray-600">
                                {{ $faq['a'] }}
                            </p>
                        </details>
                    @endforeach
                </div>

                <div data-reveal class="mt-8 rounded-2xl border border-indigo-100 bg-indigo-50/70 p-6 text-center">
                    <p class="font-bold text-gray-900">¿Listo para comenzar?</p>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Crea tu cuenta y descubre las materias que puedes estudiar.
                    </p>
                    <a href="{{ route('register') }}" class="mt-4 inline-flex items-center gap-2 font-bold text-indigo-700 hover:text-indigo-900">
                        Crear mi cuenta
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        {{-- ═══════════════ LLAMADO A LA ACCIÓN ═══════════════ --}}
        <section class="px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
            <div data-reveal class="relative isolate mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-700 px-6 py-14 text-center text-white shadow-xl shadow-indigo-950/10 sm:px-12 sm:py-20">

                <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                    <div class="blob absolute -left-16 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="blob absolute -bottom-20 -right-10 h-72 w-72 rounded-full bg-purple-300/20 blur-3xl" style="animation-delay:-6s"></div>
                </div>

                <div class="relative mx-auto max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-indigo-100">
                        Tu próxima aventura comienza aquí
                    </span>

                    <h2 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">
                        Tu próxima materia empieza hoy
                    </h2>

                    <p class="mx-auto mt-5 max-w-2xl text-base leading-8 text-indigo-100 sm:text-lg">
                        Da el primer paso, explora nuevas materias y desarrolla tus conocimientos con una experiencia de estudio a tu medida.
                    </p>

                    <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 font-bold text-indigo-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-indigo-50">
                            Crear mi cuenta
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/30 px-7 py-3.5 font-bold text-white transition hover:bg-white/10">
                            Ya tengo una cuenta
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- ═══════════════ PIE DE PÁGINA ═══════════════ --}}
    <footer class="relative overflow-hidden bg-slate-950 text-black-300">

        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-600/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-72 w-72 rounded-full bg-violet-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 pb-8 pt-16 sm:px-6 lg:px-8">

            <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-12 lg:gap-8">

                {{-- Marca --}}
                <div class="sm:col-span-2 lg:col-span-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" aria-label="{{ $appName }}, ir al inicio">
                        <x-application-logo class="h-10 w-10 fill-current text-indigo-400" />
                        <span class="text-xl font-extrabold tracking-tight text-white">{{ $appName }}</span>
                    </a>

                    <p class="mt-5 max-w-sm text-sm leading-7 text-gray-400">
                        Una plataforma de estudio para aprender a tu propio ritmo, con rutas guiadas, quizzes y evaluaciones que te acompañan en cada materia.
                    </p>

                    <ul class="mt-6 space-y-3 text-sm text-gray-300">
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                            </span>
                            Sin horarios fijos
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                            </span>
                            Tu progreso siempre guardado
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                            </span>
                            Disponible en celular, tableta y computador
                        </li>
                    </ul>
                </div>

                {{-- Plataforma --}}
                <nav class="lg:col-span-2 lg:col-start-6" aria-label="Plataforma">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Plataforma</h3>

                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="#como-funciona" class="footer-link inline-block">Cómo funciona</a></li>
                        <li><a href="#funciones" class="footer-link inline-block">Funciones</a></li>
                        @if ($subjects->isNotEmpty())
                            <li><a href="#materias" class="footer-link inline-block">Materias</a></li>
                        @endif
                        <li><a href="#preguntas" class="footer-link inline-block">Preguntas frecuentes</a></li>
                    </ul>
                </nav>

                {{-- Tu cuenta --}}
                <nav class="lg:col-span-2" aria-label="Tu cuenta">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Tu cuenta</h3>

                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('login') }}" class="footer-link inline-block">Iniciar sesión</a></li>
                        <li><a href="{{ route('register') }}" class="footer-link inline-block">Crear cuenta</a></li>
                        @if (Route::has('password.request'))
                            <li><a href="{{ route('password.request') }}" class="footer-link inline-block">¿Olvidaste tu contraseña?</a></li>
                        @endif
                    </ul>
                    <br>
                      {{-- Franja inferior --}}
                    <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-6 text-center sm:flex-row sm:text-left">
                        <p class="text-xs leading-6 text-gray-500">
                            &copy; {{ date('Y') }} {{ $appName }}. Todos los derechos reservados.
                        </p>
                    </div>   
                </nav>


            </div>
        </div>
    </footer>

    {{-- ═══════════════ FUNCIONALIDADES DE LA PÁGINA ═══════════════ --}}
    <script>
        (function () {
            'use strict';

            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var header = document.getElementById('site-header');
            var progress = document.getElementById('scroll-progress');

            /* Barra de navegación y progreso de lectura al desplazarse */
            function onScroll() {
                header.classList.toggle('nav-solid', window.scrollY > 10);

                var max = document.documentElement.scrollHeight - window.innerHeight;
                var ratio = max > 0 ? Math.min(window.scrollY / max, 1) : 0;

                progress.style.transform = 'scaleX(' + ratio + ')';
            }

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll);

            /* Menú del celular */
            var menuBtn = document.getElementById('menu-btn');
            var menu = document.getElementById('mobile-menu');
            var openIcon = document.getElementById('menu-open-icon');
            var closeIcon = document.getElementById('menu-close-icon');

            function closeMenu() {
                menu.classList.add('hidden');
                menuBtn.setAttribute('aria-expanded', 'false');
                menuBtn.setAttribute('aria-label', 'Abrir menú');
                openIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }

            menuBtn.addEventListener('click', function () {
                var isOpening = menu.classList.contains('hidden');

                menu.classList.toggle('hidden', !isOpening);
                menuBtn.setAttribute('aria-expanded', String(isOpening));
                menuBtn.setAttribute('aria-label', isOpening ? 'Cerrar menú' : 'Abrir menú');
                openIcon.classList.toggle('hidden', isOpening);
                closeIcon.classList.toggle('hidden', !isOpening);
            });

            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    closeMenu();
                }
            });

            /* Resalta en el menú la sección que se está viendo */
            var spyLinks = Array.from(document.querySelectorAll('[data-spy]'));
            var spyTargets = [document.getElementById('inicio')].concat(
                spyLinks.map(function (link) {
                    return document.querySelector(link.getAttribute('href'));
                })
            ).filter(Boolean);

            if ('IntersectionObserver' in window && spyTargets.length > 0) {
                var spy = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        var current = '#' + entry.target.id;

                        spyLinks.forEach(function (link) {
                            var active = link.getAttribute('href') === current;

                            link.classList.toggle('is-active', active);

                            if (active) {
                                link.setAttribute('aria-current', 'true');
                            } else {
                                link.removeAttribute('aria-current');
                            }
                        });
                    });
                }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });

                spyTargets.forEach(function (target) {
                    spy.observe(target);
                });
            }

            /* Contadores de estadísticas */
            function runCounter(element) {
                var target = parseInt(element.dataset.count, 10) || 0;

                if (reduceMotion || target === 0) {
                    element.textContent = target.toLocaleString('es-CO');
                    return;
                }

                var duration = 1200;
                var start = performance.now();

                function tick(now) {
                    var progressValue = Math.min((now - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - progressValue, 3);

                    element.textContent = Math.round(target * eased).toLocaleString('es-CO');

                    if (progressValue < 1) {
                        requestAnimationFrame(tick);
                    }
                }

                requestAnimationFrame(tick);
            }

            /* Animaciones de entrada y barras de progreso */
            var revealElements = document.querySelectorAll('[data-reveal]');

            function reveal(element) {
                element.classList.add('is-visible');

                element.querySelectorAll('.bar-fill').forEach(function (bar) {
                    bar.style.width = bar.dataset.width + '%';
                    bar.classList.add('is-on');
                });

                element.querySelectorAll('[data-count]').forEach(runCounter);
            }

            if (!reduceMotion && 'IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            reveal(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });

                /* La ocultación solo se activa cuando el observador está listo */
                document.documentElement.classList.add('motion-ready');

                revealElements.forEach(function (element) {
                    observer.observe(element);
                });
            } else {
                revealElements.forEach(reveal);
            }

            /* Palabras que cambian en el título */
            var swap = document.getElementById('word-swap');

            if (swap && !reduceMotion) {
                try {
                    var words = JSON.parse(swap.dataset.words);
                    var index = 0;

                    if (Array.isArray(words) && words.length > 1) {
                        window.setInterval(function () {
                            swap.classList.add('out');

                            window.setTimeout(function () {
                                index = (index + 1) % words.length;
                                swap.textContent = words[index];
                                swap.classList.remove('out');
                            }, 300);
                        }, 2800);
                    }
                } catch (error) {
                    console.warn('No fue posible iniciar la animación del título.');
                }
            }

            /* Pestañas de la demostración */
            var tabs = Array.from(document.querySelectorAll('[data-tab]'));
            var panels = Array.from(document.querySelectorAll('[data-panel]'));

            function activateTab(tab) {
                var selected = tab.dataset.tab;

                tabs.forEach(function (item) {
                    var active = item === tab;

                    item.classList.toggle('tab-on', active);
                    item.setAttribute('aria-selected', String(active));
                    item.tabIndex = active ? 0 : -1;
                });

                panels.forEach(function (panel) {
                    var active = panel.dataset.panel === selected;

                    panel.classList.toggle('hidden', !active);
                    panel.hidden = !active;
                });
            }

            tabs.forEach(function (tab, tabIndex) {
                tab.addEventListener('click', function () {
                    activateTab(tab);
                });

                tab.addEventListener('keydown', function (event) {
                    var nextIndex = tabIndex;

                    if (event.key === 'ArrowRight') {
                        nextIndex = (tabIndex + 1) % tabs.length;
                    } else if (event.key === 'ArrowLeft') {
                        nextIndex = (tabIndex - 1 + tabs.length) % tabs.length;
                    } else if (event.key === 'Home') {
                        nextIndex = 0;
                    } else if (event.key === 'End') {
                        nextIndex = tabs.length - 1;
                    } else {
                        return;
                    }

                    event.preventDefault();
                    tabs[nextIndex].focus();
                    activateTab(tabs[nextIndex]);
                });
            });

            if (tabs.length > 0) {
                activateTab(tabs.find(function (tab) {
                    return tab.getAttribute('aria-selected') === 'true';
                }) || tabs[0]);
            }

        })();
    </script>

</body>
</html>