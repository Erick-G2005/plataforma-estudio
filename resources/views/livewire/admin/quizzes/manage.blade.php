@php
    $typeLabels = [
        'single_choice' => 'Selección única',
        'multiple_choice' => 'Selección múltiple',
        'true_false' => 'Verdadero / Falso',
        'short_answer' => 'Respuesta corta',
    ];
@endphp

<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Encabezado --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.subjects.topics', $subject) }}" wire:navigate
                   class="text-sm text-gray-500 hover:underline">&larr; Temas de {{ $subject->name }}</a>
                <h1 class="text-2xl font-bold mt-1">
                    {{ $isFinal ? 'Examen final' : 'Quiz' }} · {{ $isFinal ? $subject->name : $topic->title }}
                </h1>
            </div>

            @if ($quiz)
                <a href="{{ route('quizzes.show', $quiz) }}" target="_blank" rel="noopener"
                   class="shrink-0 text-sm font-semibold text-indigo-600 border border-indigo-200 px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Probar como estudiante ↗
                </a>
            @endif
        </div>

        @if (! $quiz)

            {{-- Todavía no existe --}}
            <div class="p-6 bg-white rounded-lg shadow space-y-4">
                <p class="text-sm text-gray-600">
                    @if ($isFinal)
                        Esta materia todavía no tiene examen final. Los estudiantes lo presentan cuando completan todos los temas.
                    @else
                        Este tema no tiene quiz. Si lo creas, el estudiante deberá aprobarlo, además de marcar el tema como estudiado, para completarlo.
                    @endif
                </p>

                <form wire:submit="createQuiz" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" wire:model="newTitle" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('newTitle') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if ($isFinal)
                        <div>
                            <label class="block text-sm font-medium mb-1">Preguntas sorteadas de los temas</label>
                            <input type="number" min="0" wire:model="newDrawn" class="w-full sm:w-40 rounded-lg border-gray-300 text-sm">
                            <p class="text-xs text-gray-500 mt-1">
                                Se toman al azar de los quizzes de los temas, repartidas parejo. Ahora hay {{ $pool }} disponibles.
                                Después puedes agregar preguntas propias del examen.
                            </p>
                            @error('newDrawn') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <button type="submit"
                            class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                        {{ $isFinal ? 'Crear examen final' : 'Crear quiz' }}
                    </button>
                </form>
            </div>

        @else

            {{-- Reglas --}}
            <div class="p-6 bg-white rounded-lg shadow space-y-5">
                <div>
                    <h2 class="font-semibold">Reglas</h2>
                    <p class="text-xs text-gray-500">Cada intento tendrá aproximadamente {{ $expected }} {{ $expected === 1 ? 'pregunta' : 'preguntas' }}.</p>
                </div>

                <form wire:submit="saveRules" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" wire:model="quizTitle" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('quizTitle') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nota mínima para aprobar (%)</label>
                            <input type="number" min="1" max="100" wire:model="passScore" class="w-full rounded-lg border-gray-300 text-sm">
                            @error('passScore') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Intentos máximos</label>
                            <input type="number" min="1" wire:model="maxAttempts" placeholder="Ilimitados" class="w-full rounded-lg border-gray-300 text-sm">
                            <p class="text-xs text-gray-400 mt-1">Vacío significa ilimitados.</p>
                            @error('maxAttempts') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Espera al agotar los intentos (horas)</label>
                            <input type="number" min="0" wire:model="cooldownHours" class="w-full rounded-lg border-gray-300 text-sm">
                            <p class="text-xs text-gray-400 mt-1">Después de este tiempo se reinician todos los intentos. 0 significa sin espera.</p>
                            @error('cooldownHours') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Tiempo límite (minutos)</label>
                            <input type="number" min="1" wire:model="timeLimit" placeholder="Sin límite" class="w-full rounded-lg border-gray-300 text-sm">
                            @error('timeLimit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Preguntas por intento</label>
                            <input type="number" min="1" wire:model="perAttempt" placeholder="Todas" class="w-full rounded-lg border-gray-300 text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                Sortea esa cantidad del banco de preguntas {{ $isFinal ? 'propias del examen' : 'de este quiz' }}. Vacío usa todas.
                            </p>
                            @error('perAttempt') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if ($isFinal)
                            <div>
                                <label class="block text-sm font-medium mb-1">Preguntas sorteadas de los temas</label>
                                <input type="number" min="0" wire:model="drawnCount" class="w-full rounded-lg border-gray-300 text-sm">
                                <p class="text-xs text-gray-400 mt-1">Hay {{ $pool }} disponibles en los quizzes de los temas.</p>
                                @error('drawnCount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-1">Mostrar respuestas correctas y explicaciones</label>
                            <select wire:model="showAnswers" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="after_attempt">Siempre, al terminar cada intento</option>
                                <option value="after_pass">Solo cuando el estudiante apruebe</option>
                                <option value="never">Nunca</option>
                            </select>
                            @error('showAnswers') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Guardar reglas
                        </button>

                        <span x-data="{ show: false }"
                              x-on:rules-saved.window="show = true; setTimeout(() => show = false, 2500)"
                              x-show="show" style="display: none;"
                              class="text-sm font-medium text-green-600">✓ Guardado</span>
                    </div>
                </form>
            </div>

            {{-- Preguntas --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-semibold">
                        {{ $isFinal ? 'Preguntas propias del examen' : 'Preguntas' }}
                        <span class="font-normal text-gray-400">({{ $questions->count() }})</span>
                    </h2>

                    <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" wire:navigate
                       class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                        + Nueva pregunta
                    </a>
                </div>

                @if ($isFinal)
                    <p class="text-xs text-gray-500">
                        Además de estas, el examen sortea {{ $quiz->drawn_questions_count }}
                        {{ $quiz->drawn_questions_count === 1 ? 'pregunta' : 'preguntas' }} de los quizzes de los temas
                        ({{ $pool }} disponibles).
                    </p>
                @endif

                @if ($expected === 0)
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800">
                        Este {{ $isFinal ? 'examen' : 'quiz' }} no tiene preguntas disponibles: los estudiantes no podrán presentarlo
                        {{ $isFinal ? 'ni completar la materia' : 'ni completar el tema' }}. Agrega al menos una pregunta.
                    </div>
                @endif

                @if ($questions->isEmpty())
                    <div class="p-6 bg-white rounded-lg shadow text-sm text-gray-500">No hay preguntas todavía.</div>
                @else
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <ul class="divide-y"
                            x-data="{
                                sortable: null,
                                init() {
                                    const boot = () => {
                                        if (! window.Sortable) { return setTimeout(boot, 100); }
                                        this.sortable = window.Sortable.create(this.$el, {
                                            handle: '[data-handle]',
                                            animation: 150,
                                            onEnd: () => {
                                                const ids = Array.from(this.$el.querySelectorAll('[data-id]')).map((el) => parseInt(el.dataset.id));
                                                $wire.reorder(ids);
                                            },
                                        });
                                    };
                                    boot();
                                },
                                destroy() { if (this.sortable) { this.sortable.destroy(); } },
                            }">
                            @foreach ($questions as $question)
                                <li wire:key="q-{{ $question->id }}" data-id="{{ $question->id }}"
                                    class="flex items-center gap-3 p-4 bg-white">
                                    <span data-handle title="Arrastra para reordenar"
                                          class="cursor-grab select-none text-lg leading-none text-gray-300 hover:text-gray-500">⋮⋮</span>

                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                        {{ $loop->iteration }}
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium truncate">{{ $question->statement }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $typeLabels[$question->type] ?? $question->type }}
                                            @if ($question->type !== 'short_answer') · {{ $question->options_count }} opciones @endif
                                            @if ($question->image_path) · con imagen @endif
                                        </p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-3 text-xs">
                                        <button type="button" wire:click="move({{ $question->id }}, 'up')" @disabled($loop->first)
                                                title="Subir" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↑</button>
                                        <button type="button" wire:click="move({{ $question->id }}, 'down')" @disabled($loop->last)
                                                title="Bajar" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↓</button>

                                        <a href="{{ route('admin.quizzes.questions.edit', ['quiz' => $quiz->id, 'question' => $question->id]) }}" wire:navigate
                                           class="font-medium text-indigo-600 hover:underline">Editar</a>

                                        <button type="button" wire:click="duplicateQuestion({{ $question->id }})"
                                                class="font-medium text-gray-600 hover:underline">Duplicar</button>

                                        <button type="button" wire:click="deleteQuestion({{ $question->id }})"
                                                wire:confirm="¿Seguro que quieres eliminar esta pregunta?"
                                                class="font-medium text-red-600 hover:underline">Eliminar</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <p class="text-xs text-gray-400">Arrastra el icono ⋮⋮ para cambiar el orden, o usa las flechas.</p>
                @endif
            </div>

            {{-- Zona de peligro --}}
            <div class="pt-4">
                <button type="button" wire:click="deleteQuiz"
                        wire:confirm="¿Eliminar {{ $isFinal ? 'el examen final' : 'el quiz' }}? Se borrarán sus preguntas y todos los intentos de los estudiantes. No se puede deshacer."
                        class="text-xs font-medium text-red-600 hover:underline">
                    Eliminar {{ $isFinal ? 'examen final' : 'quiz' }}
                </button>
            </div>

        @endif
    </div>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endassets
