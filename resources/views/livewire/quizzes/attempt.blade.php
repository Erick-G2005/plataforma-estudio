@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $tones = [
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'info' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
    ];
@endphp

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <a href="{{ route('quizzes.show', $quiz) }}" wire:navigate
           class="text-sm text-gray-500 hover:underline">&larr; {{ $quiz->title }}</a>

        {{-- Resultado --}}
        <div class="p-8 rounded-xl shadow text-center space-y-3 {{ $attempt->passed ? 'bg-green-50' : 'bg-red-50' }}">
            <p class="text-sm font-semibold uppercase tracking-wide {{ $attempt->passed ? 'text-green-700' : 'text-red-700' }}">
                {{ $attempt->passed ? '¡Aprobado!' : 'No aprobado' }}
                @if ($attempt->status === 'expired') · Se acabó el tiempo @endif
            </p>
            <p class="text-5xl font-extrabold text-gray-900">{{ $fmt($attempt->percentage) }}%</p>
            <p class="text-sm text-gray-600">
                {{ $attempt->score }} de {{ $attempt->total_questions }} correctas · nota mínima {{ $quiz->pass_score }}%
            </p>
            <p class="text-xs text-gray-400">
                {{ $attempt->completed_at?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') }}
                @if ($duration) · {{ $duration }} @endif
            </p>
        </div>

        {{-- Qué sigue --}}
        @if ($next)
            <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 {{ $tones[$next['tone']] ?? $tones['info'] }}">
                <p class="text-sm font-medium">{{ $next['message'] }}</p>
                <a href="{{ $next['url'] }}" wire:navigate
                   class="shrink-0 text-center text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                    {{ $next['label'] }}
                </a>
            </div>
        @endif

        {{-- Respuestas --}}
        @if (! $showAnswers)
            <div class="p-5 bg-white rounded-xl shadow text-sm text-gray-600">
                @if ($quiz->show_answers === 'after_pass')
                    Podrás ver las respuestas correctas y las explicaciones cuando apruebes este {{ $quiz->isFinal() ? 'examen' : 'quiz' }}.
                @else
                    Este {{ $quiz->isFinal() ? 'examen' : 'quiz' }} no muestra las respuestas correctas.
                @endif
            </div>

        @elseif ($practice)

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Repaso de las preguntas falladas</h2>
                <button wire:click="stopPractice" class="text-sm font-semibold text-indigo-600 hover:underline">Terminar repaso</button>
            </div>
            <p class="text-sm text-gray-500 -mt-3">Responde de nuevo y comprueba. Esto es solo práctica: no guarda nada ni cambia tu nota.</p>

            @foreach ($wrongRows as $row)
                @php
                    $q = $row->question;
                    $isChecked = array_key_exists($q->id, $checked);
                @endphp

                <div wire:key="practice-{{ $q->id }}" class="p-6 bg-white rounded-xl shadow space-y-4">
                    <p class="font-medium">{{ $loop->iteration }}. {{ $q->statement }}</p>

                    @if ($q->image_path)
                        <img src="{{ Storage::disk('public')->url($q->image_path) }}" alt="Imagen de la pregunta"
                             class="max-w-full rounded-lg max-h-72 object-contain">
                    @endif

                    <x-quiz-inputs :question="$q" :options="$row->options"
                                   :model="'practiceAnswers.' . $q->id" :disabled="$isChecked" />

                    @if ($isChecked)
                        <div class="p-4 rounded-lg text-sm {{ $checked[$q->id] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                            <p class="font-semibold">{{ $checked[$q->id] ? '¡Correcto!' : 'Todavía no es correcta' }}</p>

                            @unless ($checked[$q->id])
                                <p class="mt-1">
                                    Respuesta correcta:
                                    @if ($q->type === 'short_answer')
                                        <span class="font-semibold">{{ $q->correct_answer_text }}</span>
                                    @else
                                        <span class="font-semibold">{{ $row->options->where('is_correct', true)->pluck('text')->implode(', ') }}</span>
                                    @endif
                                </p>
                            @endunless

                            @if ($q->explanation)
                                <p class="mt-2 text-xs italic">{{ $q->explanation }}</p>
                            @endif
                        </div>

                        <button wire:click="retry({{ $q->id }})" class="text-sm font-semibold text-indigo-600 hover:underline">
                            Intentar otra vez
                        </button>
                    @else
                        <button wire:click="check({{ $q->id }})"
                                class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Comprobar
                        </button>
                    @endif
                </div>
            @endforeach

        @else

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Detalle de tus respuestas</h2>

                @if ($owner && $wrongRows->isNotEmpty())
                    <button wire:click="startPractice"
                            class="text-sm font-semibold text-indigo-600 hover:underline">
                        Repasar las que fallé ({{ $wrongRows->count() }})
                    </button>
                @endif
            </div>

            @foreach ($rows as $row)
                @php $q = $row->question; @endphp

                <div wire:key="row-{{ $q->id }}"
                     class="p-5 rounded-xl border space-y-3 {{ $row->correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-medium">{{ $loop->iteration }}. {{ $q->statement }}</p>
                        <span class="shrink-0 text-xs font-semibold px-2 py-1 rounded-full {{ $row->correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $row->correct ? 'Correcta' : ($row->answered ? 'Incorrecta' : 'Sin responder') }}
                        </span>
                    </div>

                    @if ($q->image_path)
                        <img src="{{ Storage::disk('public')->url($q->image_path) }}" alt="Imagen de la pregunta"
                             class="max-w-full rounded-lg max-h-64 object-contain">
                    @endif

                    @if ($q->type === 'short_answer')
                        <p class="text-sm">Tu respuesta: <span class="font-semibold">{{ $row->answered ? $row->text : '—' }}</span></p>

                        @unless ($row->correct)
                            <p class="text-sm text-green-700">Respuesta correcta: <span class="font-semibold">{{ $q->correct_answer_text }}</span></p>
                        @endunless
                    @else
                        <ul class="space-y-1">
                            @foreach ($row->options as $option)
                                @php $chosen = in_array((int) $option->id, $row->selected, true); @endphp

                                <li class="flex items-center gap-2 text-sm {{ $option->is_correct ? 'text-green-700 font-semibold' : ($chosen ? 'text-red-700' : 'text-gray-600') }}">
                                    <span>{{ $option->is_correct ? '✓' : ($chosen ? '✗' : '•') }}</span>
                                    <span>{{ $option->text }}</span>
                                    @if ($chosen) <span class="text-xs font-normal">(tu respuesta)</span> @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($q->explanation)
                        <p class="text-xs text-gray-600 italic">{{ $q->explanation }}</p>
                    @endif
                </div>
            @endforeach

        @endif
    </div>
</div>
