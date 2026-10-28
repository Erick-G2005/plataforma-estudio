<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.subjects.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Materias</a>
                <h1 class="text-2xl font-bold mt-1">Temas de {{ $subject->name }}</h1>
            </div>

            <button wire:click="openCreate"
                    class="shrink-0 text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                + Nuevo tema
            </button>
        </div>

        {{-- Examen final de la materia --}}
        <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="font-medium text-purple-900">🎓 Examen final</p>
                <p class="text-xs text-purple-700">
                    @if ($exam)
                        {{ $exam->questions_count }} {{ $exam->questions_count === 1 ? 'pregunta propia' : 'preguntas propias' }}
                        · {{ $exam->drawn_questions_count }} sorteadas de los temas · nota mínima {{ $exam->pass_score }}%
                    @else
                        Todavía no existe. Los estudiantes lo presentan al completar todos los temas.
                    @endif
                </p>
            </div>

            <a href="{{ route('admin.subjects.exam', $subject) }}" wire:navigate
               class="shrink-0 text-center text-sm font-semibold text-purple-700 border border-purple-300 px-4 py-2 rounded-lg hover:bg-purple-100">
                {{ $exam ? 'Gestionar examen' : 'Crear examen final' }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($topics as $topic)
                <div wire:key="topic-{{ $topic->id }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4">
                    <div>
                        <p class="font-medium">{{ $topic->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $topic->materials_count }} materiales · orden: {{ $topic->order }}
                            ·
                            @if ($topic->quiz)
                                quiz con {{ $topic->quiz->questions_count }} {{ $topic->quiz->questions_count === 1 ? 'pregunta' : 'preguntas' }}
                            @else
                                <span class="text-amber-600">sin quiz</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.topics.materials', $topic) }}" wire:navigate
                           class="text-sm text-indigo-600 hover:underline">Gestionar materiales</a>

                        <a href="{{ route('admin.topics.quiz', $topic) }}" wire:navigate
                           class="text-sm text-indigo-600 hover:underline">{{ $topic->quiz ? 'Gestionar quiz' : 'Crear quiz' }}</a>

                        <button wire:click="openEdit({{ $topic->id }})"
                                class="text-sm text-indigo-600 hover:underline">Editar</button>

                        <button wire:click="delete({{ $topic->id }})"
                                wire:confirm="¿Seguro que quieres eliminar este tema? Se borrarán también sus materiales y quiz."
                                class="text-sm text-red-600 hover:underline">Eliminar</button>
                    </div>
                </div>
            @empty
                <p class="p-4 text-sm text-gray-500">No hay temas todavía para esta materia.</p>
            @endforelse
        </div>
    </div>

    {{-- Modal de crear/editar --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md space-y-4">
                <h2 class="text-lg font-semibold">{{ $editing ? 'Editar tema' : 'Nuevo tema' }}</h2>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" wire:model="title" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Orden</label>
                        <input type="number" wire:model="order" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('order') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="text-sm text-gray-600 px-4 py-2">Cancelar</button>
                        <button type="submit"
                                class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
