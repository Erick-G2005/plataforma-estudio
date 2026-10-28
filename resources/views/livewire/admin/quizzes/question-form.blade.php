@php
    $typeLabels = [
        'single_choice' => 'Selección única',
        'multiple_choice' => 'Selección múltiple',
        'true_false' => 'Verdadero / Falso',
        'short_answer' => 'Respuesta corta',
    ];
@endphp

<div class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ $quiz->adminUrl() }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; {{ $quiz->title }}</a>
            <h1 class="text-2xl font-bold mt-1">{{ $questionId ? 'Editar pregunta' : 'Nueva pregunta' }}</h1>
        </div>

        <div class="grid lg:grid-cols-5 gap-6 items-start">

            {{-- Formulario --}}
            <form wire:submit="save" class="lg:col-span-3 bg-white rounded-xl shadow p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de pregunta</label>
                    <select wire:model.live="type" class="w-full rounded-lg border-gray-300 text-sm">
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Enunciado</label>
                    <textarea wire:model.blur="statement" rows="3" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    @error('statement') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Imagen (opcional)</label>

                    @if ($existingImageUrl && ! $removeImage && ! $image)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $existingImageUrl }}" alt="Imagen actual" class="h-16 rounded">
                            <label class="flex items-center gap-1 text-xs text-red-600">
                                <input type="checkbox" wire:model.live="removeImage"> Quitar imagen actual
                            </label>
                        </div>
                    @endif

                    <input type="file" wire:model="image" accept="image/*" class="w-full text-sm">
                    <div wire:loading wire:target="image" class="text-xs text-gray-400">Subiendo...</div>
                    <p class="text-xs text-gray-400 mt-1">Máximo 2 MB.</p>
                    @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Selección única / Verdadero-Falso --}}
                @if (in_array($type, ['single_choice', 'true_false']))
                    <div>
                        <label class="block text-sm font-medium mb-1">Opciones (marca la correcta)</label>

                        <div class="space-y-2">
                            @foreach ($options as $index => $option)
                                <div wire:key="opt-{{ $index }}" class="flex items-center gap-2">
                                    <input type="radio" wire:model="correctOptionIndex" value="{{ $index }}" class="text-indigo-600">

                                    @if ($type === 'true_false')
                                        <span class="text-sm">{{ $option['text'] }}</span>
                                    @else
                                        <input type="text" wire:model.blur="options.{{ $index }}.text"
                                               placeholder="Texto de la opción"
                                               class="flex-1 rounded-lg border-gray-300 text-sm">

                                        @if (count($options) > 2)
                                            <button type="button" wire:click="removeOption({{ $index }})"
                                                    class="text-xs text-red-500 hover:text-red-700">✕</button>
                                        @endif
                                    @endif
                                </div>
                                @error('options.' . $index . '.text') <p class="text-xs text-red-600 ms-6">{{ $message }}</p> @enderror
                            @endforeach
                        </div>

                        @if ($type === 'single_choice')
                            <button type="button" wire:click="addOption"
                                    class="text-xs font-medium text-indigo-600 hover:underline mt-2">+ Agregar opción</button>
                        @endif

                        @error('correctOptionIndex') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('options') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Selección múltiple --}}
                @if ($type === 'multiple_choice')
                    <div>
                        <label class="block text-sm font-medium mb-1">Opciones (marca todas las correctas)</label>

                        <div class="space-y-2">
                            @foreach ($options as $index => $option)
                                <div wire:key="opt-{{ $index }}" class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="options.{{ $index }}.is_correct" class="rounded text-indigo-600">
                                    <input type="text" wire:model.blur="options.{{ $index }}.text"
                                           placeholder="Texto de la opción"
                                           class="flex-1 rounded-lg border-gray-300 text-sm">

                                    @if (count($options) > 2)
                                        <button type="button" wire:click="removeOption({{ $index }})"
                                                class="text-xs text-red-500 hover:text-red-700">✕</button>
                                    @endif
                                </div>
                                @error('options.' . $index . '.text') <p class="text-xs text-red-600 ms-6">{{ $message }}</p> @enderror
                            @endforeach
                        </div>

                        <button type="button" wire:click="addOption"
                                class="text-xs font-medium text-indigo-600 hover:underline mt-2">+ Agregar opción</button>

                        @error('options') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Respuesta corta --}}
                @if ($type === 'short_answer')
                    <div>
                        <label class="block text-sm font-medium mb-1">Respuesta correcta</label>
                        <input type="text" wire:model.blur="correctAnswerText" class="w-full rounded-lg border-gray-300 text-sm">
                        <p class="text-xs text-gray-400 mt-1">Se compara sin importar mayúsculas ni espacios al inicio o al final.</p>
                        @error('correctAnswerText') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-1">Explicación (opcional)</label>
                    <textarea wire:model.blur="explanation" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Se muestra al terminar el intento, según la regla de visibilidad del quiz.</p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ $quiz->adminUrl() }}" wire:navigate class="text-sm text-gray-600 px-4 py-2">Cancelar</a>

                    @unless ($questionId)
                        <button type="button" wire:click="save(true)"
                                class="text-sm font-semibold text-indigo-600 px-4 py-2 rounded-lg border border-indigo-200 hover:bg-indigo-50">
                            Guardar y agregar otra
                        </button>
                    @endunless

                    <button type="submit"
                            class="text-sm font-semibold text-white bg-indigo-600 px-5 py-2 rounded-lg hover:bg-indigo-700">
                        Guardar pregunta
                    </button>
                </div>
            </form>

            {{-- Vista previa --}}
            <div class="lg:col-span-2 lg:sticky lg:top-6 space-y-3">
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5 space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Así lo verá el estudiante</p>

                    <div class="bg-white rounded-xl shadow p-5 space-y-4">
                        <p class="font-medium">{{ $statement !== '' ? $statement : 'El enunciado aparecerá aquí' }}</p>

                        @if ($image && $image->isPreviewable())
                            <img src="{{ $image->temporaryUrl() }}" alt="Vista previa de la imagen"
                                 class="max-w-full rounded-lg max-h-56 object-contain">
                        @elseif ($existingImageUrl && ! $removeImage)
                            <img src="{{ $existingImageUrl }}" alt="Imagen de la pregunta"
                                 class="max-w-full rounded-lg max-h-56 object-contain">
                        @endif

                        @if ($type === 'short_answer')
                            <div class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-400">Escribe tu respuesta</div>
                        @else
                            <div class="space-y-2">
                                @foreach ($options as $option)
                                    <div class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
                                        <span class="mt-0.5 h-4 w-4 shrink-0 border border-gray-400 {{ $type === 'multiple_choice' ? 'rounded' : 'rounded-full' }}"></span>
                                        <span class="text-sm {{ ($option['text'] ?? '') === '' ? 'text-gray-400' : '' }}">
                                            {{ ($option['text'] ?? '') !== '' ? $option['text'] : 'Opción sin texto' }}
                                        </span>
                                    </div>
                                @endforeach

                                @if ($type === 'multiple_choice')
                                    <p class="text-xs text-gray-400">Puedes marcar varias opciones.</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500">{{ $typeLabels[$type] ?? $type }}. La vista previa se actualiza al salir de cada campo.</p>
                </div>
            </div>
        </div>
    </div>
</div>
