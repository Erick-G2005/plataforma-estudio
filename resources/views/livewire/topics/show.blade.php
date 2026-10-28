@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <a href="{{ route('subjects.show', $topic->subject) }}" wire:navigate
           class="text-sm text-gray-500 hover:underline">&larr; {{ $topic->subject->name }}</a>

        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold">{{ $topic->title }}</h1>

            @if ($state->completed)
                <span class="shrink-0 text-sm font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">✓ Completado</span>
            @elseif ($state->studied)
                <span class="shrink-0 text-sm font-semibold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">Estudiado</span>
            @else
                <span class="shrink-0 text-sm font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Pendiente</span>
            @endif
        </div>

        <div class="space-y-4">
            @foreach ($materials as $material)
                <x-material-view :material="$material" />
            @endforeach
        </div>

        {{-- Cómo completar el tema --}}
        <div class="p-6 bg-white rounded-xl shadow space-y-4">
            <h2 class="font-semibold">Para completar este tema</h2>

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $state->studied ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $state->studied ? '✓' : '1' }}
                    </span>
                    <div>
                        <p class="text-sm font-medium">Estudiar los materiales</p>
                        <p class="text-xs text-gray-500">Léelos con calma y márcalo cuando termines.</p>
                    </div>
                </div>

                @unless ($state->studied)
                    <button wire:click="markAsStudied"
                            class="shrink-0 text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                        Marcar como estudiado
                    </button>
                @endunless
            </div>

            @if ($quiz)
                <div class="flex items-center justify-between gap-4 pt-4 border-t">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $state->quiz_passed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $state->quiz_passed ? '✓' : '2' }}
                        </span>
                        <div>
                            <p class="text-sm font-medium">Aprobar el quiz ({{ $quiz->pass_score }}% o más)</p>
                            <p class="text-xs text-gray-500">
                                @if ($quizStatus->passed)
                                    Aprobado · mejor nota {{ $fmt($quizStatus->best_percentage) }}%
                                @elseif ($quizStatus->in_progress)
                                    Tienes un intento en curso
                                @elseif ($quizStatus->locked_until)
                                    Intentos agotados: podrás volver a intentarlo {{ $quizStatus->locked_until->copy()->locale('es')->diffForHumans() }}
                                @elseif ($quizStatus->attempts_remaining !== null)
                                    Te quedan {{ $quizStatus->attempts_remaining }} {{ $quizStatus->attempts_remaining === 1 ? 'intento' : 'intentos' }}
                                @else
                                    Intentos ilimitados
                                @endif
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('quizzes.show', $quiz) }}" wire:navigate
                       class="shrink-0 text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                        {{ $quizStatus->in_progress ? 'Continuar intento' : ($quizStatus->passed ? 'Ver quiz' : 'Ir al quiz') }}
                    </a>
                </div>
            @else
                <p class="text-xs text-gray-500 pt-4 border-t">Este tema no tiene quiz: se completa al marcarlo como estudiado.</p>
            @endif
        </div>

        @if ($state->completed)
            <a href="{{ $nextUrl }}" wire:navigate
               class="block text-center text-sm font-semibold text-white bg-indigo-600 px-5 py-3 rounded-xl hover:bg-indigo-700">
                {{ $nextLabel }} &rarr;
            </a>
        @endif
    </div>
</div>
