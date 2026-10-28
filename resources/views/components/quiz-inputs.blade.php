@props(['question', 'options', 'model', 'disabled' => false])

@php $inputName = str_replace('.', '_', $model); @endphp

@if (in_array($question->type, ['single_choice', 'true_false']))
    <div class="space-y-2">
        @foreach ($options as $option)
            <label wire:key="{{ $inputName }}-{{ $option->id }}"
                   class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                <input type="radio" name="{{ $inputName }}" value="{{ $option->id }}"
                       wire:model="{{ $model }}" @disabled($disabled)
                       class="mt-1 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm">{{ $option->text }}</span>
            </label>
        @endforeach
    </div>
@elseif ($question->type === 'multiple_choice')
    <div class="space-y-2">
        @foreach ($options as $option)
            <label wire:key="{{ $inputName }}-{{ $option->id }}"
                   class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer transition hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                <input type="checkbox" value="{{ $option->id }}"
                       wire:model="{{ $model }}" @disabled($disabled)
                       class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm">{{ $option->text }}</span>
            </label>
        @endforeach
        <p class="text-xs text-gray-400">Puedes marcar varias opciones.</p>
    </div>
@else
    <input type="text" wire:model="{{ $model }}" @disabled($disabled)
           placeholder="Escribe tu respuesta"
           class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
@endif
