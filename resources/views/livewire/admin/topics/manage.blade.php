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

        @if ($topics->isEmpty())
            <div class="p-6 bg-white rounded-lg shadow text-sm text-gray-500">
                No hay temas todavía para esta materia. Crea el primero con «+ Nuevo tema».
            </div>
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
                    @foreach ($topics as $topic)
                        <li wire:key="topic-{{ $topic->id }}" data-id="{{ $topic->id }}"
                            class="flex flex-col gap-3 bg-white p-4 sm:flex-row sm:items-center">

                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span data-handle title="Arrastra para reordenar"
                                      class="cursor-grab select-none text-lg leading-none text-gray-300 hover:text-gray-500">⋮⋮</span>

                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                    {{ $loop->iteration }}
                                </span>

                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ $topic->title }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $topic->materials_count }} {{ $topic->materials_count === 1 ? 'material' : 'materiales' }}
                                        ·
                                        @if ($topic->quiz)
                                            quiz con {{ $topic->quiz->questions_count }} {{ $topic->quiz->questions_count === 1 ? 'pregunta' : 'preguntas' }}
                                        @else
                                            <span class="text-amber-600">sin quiz</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <button type="button" wire:click="move({{ $topic->id }}, 'up')" @disabled($loop->first)
                                        title="Subir" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↑</button>
                                <button type="button" wire:click="move({{ $topic->id }}, 'down')" @disabled($loop->last)
                                        title="Bajar" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↓</button>

                                <a href="{{ route('admin.topics.materials', $topic) }}" wire:navigate
                                   class="text-indigo-600 hover:underline">Gestionar materiales</a>

                                <a href="{{ route('admin.topics.quiz', $topic) }}" wire:navigate
                                   class="text-indigo-600 hover:underline">{{ $topic->quiz ? 'Gestionar quiz' : 'Crear quiz' }}</a>

                                <button type="button" wire:click="openEdit({{ $topic->id }})"
                                        class="text-indigo-600 hover:underline">Editar</button>

                                <button type="button" wire:click="delete({{ $topic->id }})"
                                        wire:confirm="¿Seguro que quieres eliminar este tema? Se borrarán también sus materiales y quiz."
                                        class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-xs text-gray-400">Arrastra el icono ⋮⋮ para cambiar el orden, o usa las flechas. Los estudiantes recorren los temas en este mismo orden.</p>
        @endif
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

@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endassets
