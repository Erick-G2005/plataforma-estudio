@php
    $user = auth()->user();
    $enrolledCount = $user->enrolledSubjects()->count();
    $completedCount = $user->studyProgress()->where('completed', true)->count();

    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div>
                <h1 class="text-2xl font-bold">Mi perfil</h1>
                <p class="text-sm text-gray-500 mt-1">Administra tu información personal y la seguridad de tu cuenta.</p>
            </div>

            {{-- Resumen de la cuenta --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 p-6 text-white shadow sm:p-8">
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold ring-1 ring-white/30">
                            {{ $initials }}
                        </span>

                        <div class="min-w-0">
                            <p class="truncate text-xl font-extrabold">{{ $user->name }}</p>
                            <p class="truncate text-sm text-indigo-100">{{ $user->email }}</p>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-white/15 px-2.5 py-1">
                                    {{ $user->isAdmin() ? 'Administrador' : 'Estudiante' }}
                                </span>
                                <span class="text-indigo-100">
                                    Miembro desde {{ $user->created_at?->copy()->timezone('America/Bogota')->locale('es')->isoFormat('MMMM [de] YYYY') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="rounded-xl bg-white/10 px-5 py-3">
                            <p class="text-2xl font-extrabold">{{ $enrolledCount }}</p>
                            <p class="text-xs text-indigo-100">Materias inscritas</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-5 py-3">
                            <p class="text-2xl font-extrabold">{{ $completedCount }}</p>
                            <p class="text-xs text-indigo-100">Temas completados</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información personal --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </section>

            {{-- Contraseña --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </section>

            {{-- Eliminar cuenta --}}
            <section class="rounded-2xl border border-red-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
