<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- Panel de marca (solo en pantallas grandes) --}}
        <div
            class="hidden lg:flex relative flex-col justify-between overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 p-12 text-white">
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-purple-500/20"></div>

            <a href="/" wire:navigate class="relative flex items-center gap-3">
                <x-application-logo class="h-10 w-10 fill-current text-white" />
                <span class="text-xl font-semibold">{{ config('app.name') }}</span>
            </a>

            <div class="relative space-y-6">
                <h2 class="text-4xl font-bold leading-tight">Aprende a tu propio ritmo.</h2>
                <p class="text-lg text-indigo-100 max-w-md">
                    Elige tus materias, avanza tema por tema y mide tu progreso sin presión.
                </p>

                <ul class="space-y-3 text-indigo-50">
                    <li class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 text-indigo-200" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Contenido en texto, video y PDF
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 text-indigo-200" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Quizzes de autoevaluación opcionales
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 text-indigo-200" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Tu progreso guardado en cada tema
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-indigo-200">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        {{-- Panel del formulario --}}
        <div class="flex flex-col justify-center items-center bg-white px-6 py-12">
            {{-- Logo (solo en móvil, donde el panel de marca está oculto) --}}
            <a href="/" wire:navigate class="lg:hidden mb-8 flex flex-col items-center gap-2">
                <x-application-logo class="h-14 w-14 fill-current text-indigo-600" />
                <span class="text-lg font-semibold text-gray-700">{{ config('app.name') }}</span>
            </a>

            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
