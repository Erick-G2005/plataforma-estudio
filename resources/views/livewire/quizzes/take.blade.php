@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('quiz_error'))
            <div class="p-4 rounded-lg bg-red-50 text-sm text-red-700">{{ session('quiz_error') }}</div>
        @endif

        @if ($mode === 'intro')

            @if ($subject)
                <a href="{{ route('subjects.show', $subject) }}" wire:navigate
                   class="text-sm text-gray-500 hover:underline">&larr; {{ $subject->name }}</a>
            @endif

            <div class="p-6 bg-white rounded-xl shadow space-y-6">
                <div>
                    <span class="inline-block text-xs font-semibold uppercase tracking-wide px-2 py-1 rounded-full {{ $quiz->isFinal() ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700' }}">
                        {{ $quiz->isFinal() ? 'Examen final' : 'Quiz del tema' }}
                    </span>
                    <h1 class="text-2xl font-bold mt-2">{{ $quiz->title }}</h1>
                    @if (! $quiz->isFinal() && $quiz->topic)
                        <p class="text-sm text-gray-500 mt-1">Tema: {{ $quiz->topic->title }}</p>
                    @endif
                </div>

                @if ($status->passed)
                    <div class="p-4 rounded-lg bg-green-50 text-sm text-green-800">
                        <p class="font-semibold">✓ Ya aprobaste {{ $quiz->isFinal() ? 'este examen' : 'este quiz' }}</p>
                        <p class="mt-1">
                            Tu mejor nota fue {{ $fmt($status->best_percentage) }}%.
                            Puedes repetirlo para practicar: tu aprobación no cambia.
                        </p>
                    </div>
                @endif

                @if ($status->locked_until)
                    <div class="p-4 rounded-lg bg-amber-50 text-sm text-amber-800">
                        <p class="font-semibold">Agotaste tus intentos</p>
                        <p class="mt-1">
                            Podrás intentarlo de nuevo {{ $status->locked_until->copy()->locale('es')->diffForHumans() }}
                            ({{ $status->locked_until->copy()->timezone('America/Bogota')->format('d/m/Y H:i') }}).
                            Aprovecha para repasar el material.
                        </p>
                    </div>
                @endif

                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-xs text-gray-500">Preguntas</dt>
                        <dd class="font-semibold">{{ $expected }}</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-xs text-gray-500">Nota mínima</dt>
                        <dd class="font-semibold">{{ $quiz->pass_score }}%</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-xs text-gray-500">Tiempo</dt>
                        <dd class="font-semibold">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'Sin límite' }}</dd>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <dt class="text-xs text-gray-500">Intentos</dt>
                        <dd class="font-semibold">
                            @if ($status->passed || ! $quiz->max_attempts)
                                Ilimitados
                            @else
                                {{ $status->attempts_remaining }} de {{ $quiz->max_attempts }}
                            @endif
                        </dd>
                    </div>
                </dl>

                <ul class="text-xs text-gray-500 space-y-1 list-disc ps-5">
                    <li>Responderás una pregunta a la vez y podrás revisar todo antes de enviar.</li>
                    @if ($quiz->max_attempts && ! $status->passed)
                        <li>Un intento se cuenta desde que pulsas «Comenzar», aunque no lo termines.</li>
                        @if ($quiz->cooldown_hours > 0)
                            <li>Si agotas los intentos sin aprobar, podrás volver a intentarlo después de {{ $quiz->cooldown_hours }} {{ $quiz->cooldown_hours === 1 ? 'hora' : 'horas' }}.</li>
                        @endif
                    @endif
                    @if ($quiz->time_limit_minutes)
                        <li>El tiempo corre desde que empiezas y no se detiene si sales de la página.</li>
                    @endif
                </ul>

                @if ($status->can_start)
                    <button wire:click="start" wire:loading.attr="disabled" wire:target="start"
                            class="w-full flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60 transition">
                        <span wire:loading.remove wire:target="start">{{ $status->passed ? 'Practicar de nuevo' : 'Comenzar' }}</span>
                        <span wire:loading wire:target="start">Preparando...</span>
                    </button>
                @elseif (! $status->locked_until)
                    <p class="text-sm text-gray-500">Este quiz todavía no tiene preguntas disponibles.</p>
                @endif
            </div>

            @if ($history->isNotEmpty())
                <div class="p-6 bg-white rounded-xl shadow">
                    <h2 class="font-semibold mb-3">Tus intentos</h2>

                    <ul class="divide-y">
                        @foreach ($history as $past)
                            <li wire:key="past-{{ $past->id }}">
                                <a href="{{ route('quizzes.attempt', ['quiz' => $quiz->id, 'attempt' => $past->id]) }}" wire:navigate
                                   class="flex items-center justify-between py-3 -mx-2 px-2 rounded hover:bg-gray-50">
                                    <div>
                                        <p class="text-sm font-medium">{{ $past->completed_at?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $past->score }} de {{ $past->total_questions }} correctas
                                            @if ($past->status === 'expired') · tiempo agotado @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold">{{ $fmt($past->percentage) }}%</span>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $past->passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $past->passed ? 'Aprobado' : 'No aprobado' }}
                                        </span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        @else

            <div class="space-y-4">
                <div class="p-4 bg-white rounded-xl shadow space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">{{ $quiz->title }}</p>
                            <p class="font-semibold">
                                @if ($mode === 'review') Revisión final @else Pregunta {{ $index + 1 }} de {{ $total }} @endif
                            </p>
                        </div>

                        @if ($secondsLeft !== null)
                            <div wire:key="quiz-timer"
                                 x-data="{
                                    left: {{ $secondsLeft }},
                                    t: null,
                                    init() {
                                        this.t = setInterval(() => {
                                            this.left = Math.max(0, this.left - 1);
                                            if (this.left === 0) {
                                                clearInterval(this.t);
                                                $wire.submit();
                                            }
                                        }, 1000);
                                    },
                                    destroy() { clearInterval(this.t); },
                                    get label() {
                                        const m = Math.floor(this.left / 60);
                                        const s = this.left % 60;
                                        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                                    }
                                 }"
                                 x-bind:class="left <= 60 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
                                 class="shrink-0 rounded-lg px-3 py-1.5 text-sm font-semibold tabular-nums">
                                ⏱ <span x-text="label"></span>
                            </div>
                        @endif
                    </div>

                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all"
                             style="width: {{ $total > 0 ? (int) round($answeredCount / $total * 100) : 0 }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500">{{ $answeredCount }} de {{ $total }} respondidas</p>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($overview as $item)
                            <button type="button" wire:click="goTo({{ $item->index }})" wire:key="nav-{{ $item->index }}"
                                    class="h-8 w-8 rounded-md border text-xs font-semibold transition
                                        {{ ($mode === 'taking' && $item->index === $index) ? 'ring-2 ring-indigo-500 ring-offset-1' : '' }}
                                        {{ $item->answered ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                                {{ $item->index + 1 }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($mode === 'taking')

                    <div wire:key="question-{{ $question->id }}" class="p-6 bg-white rounded-xl shadow space-y-4">
                        <p class="font-medium text-lg">{{ $question->statement }}</p>

                        @if ($question->image_path)
                            <img src="{{ Storage::disk('public')->url($question->image_path) }}"
                                 alt="Imagen de la pregunta"
                                 class="max-w-full rounded-lg max-h-72 object-contain">
                        @endif

                        <x-quiz-inputs :question="$question" :options="$options" :model="'answers.' . $question->id" />
                    </div>

                    <div class="flex items-center justify-between">
                        <button wire:click="previous" @disabled($index === 0)
                                class="text-sm font-semibold text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 disabled:opacity-40 disabled:hover:bg-transparent">
                            &larr; Anterior
                        </button>

                        <button wire:click="next" wire:loading.attr="disabled" wire:target="next"
                                class="text-sm font-semibold text-white bg-indigo-600 px-5 py-2.5 rounded-lg hover:bg-indigo-700 disabled:opacity-60">
                            {{ $index + 1 >= $total ? 'Revisar y enviar' : 'Siguiente' }} &rarr;
                        </button>
                    </div>

                @else

                    <div class="p-6 bg-white rounded-xl shadow space-y-4">
                        <h2 class="text-lg font-semibold">Revisa tus respuestas antes de enviar</h2>

                        @php $pending = $total - $answeredCount; @endphp

                        @if ($pending > 0)
                            <p class="p-3 rounded-lg bg-amber-50 text-sm text-amber-800">
                                Tienes {{ $pending }} {{ $pending === 1 ? 'pregunta sin responder' : 'preguntas sin responder' }}.
                                Las que queden en blanco cuentan como incorrectas.
                            </p>
                        @else
                            <p class="p-3 rounded-lg bg-green-50 text-sm text-green-800">Respondiste todas las preguntas.</p>
                        @endif

                        <ul class="divide-y">
                            @foreach ($overview as $item)
                                <li wire:key="review-{{ $item->index }}" class="flex items-center justify-between gap-3 py-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $item->answered ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $item->index + 1 }}
                                        </span>
                                        <p class="text-sm truncate">{{ $item->question->statement }}</p>
                                    </div>

                                    <button wire:click="goTo({{ $item->index }})"
                                            class="shrink-0 text-xs font-semibold text-indigo-600 hover:underline">
                                        {{ $item->answered ? 'Cambiar' : 'Responder' }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="flex items-center justify-between">
                        <button wire:click="backToQuestions"
                                class="text-sm font-semibold text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                            &larr; Volver a las preguntas
                        </button>

                        <button wire:click="submit"
                                wire:confirm="¿Enviar el quiz? Ya no podrás cambiar tus respuestas."
                                wire:loading.attr="disabled" wire:target="submit"
                                class="text-sm font-semibold text-white bg-green-600 px-5 py-2.5 rounded-lg hover:bg-green-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="submit">Enviar quiz</span>
                            <span wire:loading wire:target="submit">Calificando...</span>
                        </button>
                    </div>

                @endif
            </div>

        @endif
    </div>
</div>
