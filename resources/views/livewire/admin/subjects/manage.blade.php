<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Panel</a>
                <h1 class="text-2xl font-bold mt-1">Materias</h1>
            </div>

            <button wire:click="openCreate"
                    class="shrink-0 text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                + Nueva materia
            </button>
        </div>

        @if ($subjects->isEmpty())
            <div class="p-6 bg-white rounded-lg shadow text-sm text-gray-500">
                No hay materias todavía. Crea la primera con «+ Nueva materia».
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
                    @foreach ($subjects as $subject)
                        <li wire:key="subject-{{ $subject->id }}" data-id="{{ $subject->id }}"
                            class="flex flex-col gap-3 bg-white p-4 sm:flex-row sm:items-center">

                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span data-handle title="Arrastra para reordenar"
                                      class="cursor-grab select-none text-lg leading-none text-gray-300 hover:text-gray-500">⋮⋮</span>

                                <x-subject-cover :subject="$subject" class="h-14 w-20 shrink-0 rounded-lg" letter-size="text-2xl" />

                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ $subject->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $subject->topics_count }} {{ $subject->topics_count === 1 ? 'tema' : 'temas' }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <button type="button" wire:click="move({{ $subject->id }}, 'up')" @disabled($loop->first)
                                        title="Subir" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↑</button>
                                <button type="button" wire:click="move({{ $subject->id }}, 'down')" @disabled($loop->last)
                                        title="Bajar" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↓</button>

                                <a href="{{ route('admin.subjects.topics', $subject) }}" wire:navigate
                                   class="text-indigo-600 hover:underline">Gestionar temas</a>

                                <button type="button" wire:click="openEdit({{ $subject->id }})"
                                        class="text-indigo-600 hover:underline">Editar</button>

                                <button type="button" wire:click="delete({{ $subject->id }})"
                                        wire:confirm="¿Seguro que quieres eliminar esta materia? Se borrarán también sus temas, materiales y quizzes."
                                        class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-xs text-gray-400">Arrastra el icono ⋮⋮ para cambiar el orden, o usa las flechas. Los estudiantes ven las materias en este mismo orden.</p>
        @endif
    </div>

    {{-- Modal de crear/editar --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 overflow-y-auto py-8" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md space-y-4">
                <h2 class="text-lg font-semibold">{{ $editing ? 'Editar materia' : 'Nueva materia' }}</h2>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Portada (opcional)</label>

                        @if ($editing?->cover_url && ! $removeCover && ! $cover)
                            <div class="mb-2 flex items-center gap-3">
                                <img src="{{ $editing->cover_url }}" alt="Portada actual" class="h-16 rounded">
                                <label class="flex items-center gap-1 text-xs text-red-600">
                                    <input type="checkbox" wire:model="removeCover"> Quitar portada actual
                                </label>
                            </div>
                        @endif

                        <input type="file" wire:model="cover" accept="image/*" class="w-full text-sm">
                        <div wire:loading wire:target="cover" class="text-xs text-gray-400">Subiendo...</div>

                        @if ($cover && $cover->isPreviewable())
                            <img src="{{ $cover->temporaryUrl() }}" alt="Vista previa de la portada" class="mt-2 h-16 rounded">
                        @endif

                        <p class="mt-1 text-xs text-gray-400">Recomendado: horizontal (16:9), máx. 2 MB.</p>
                        @error('cover') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="text-sm text-gray-600 px-4 py-2">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="cover"
                                class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-60">
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
