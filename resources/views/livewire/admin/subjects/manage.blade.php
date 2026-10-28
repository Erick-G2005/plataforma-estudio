<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Panel</a>
                <h1 class="text-2xl font-bold mt-1">Materias</h1>
            </div>

            <button wire:click="openCreate"
                    class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                + Nueva materia
            </button>
        </div>

        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($subjects as $subject)
                <div class="flex items-center justify-between p-4">
<div class="flex items-center gap-4">
    <x-subject-cover :subject="$subject" class="h-14 w-20 rounded-lg shrink-0" letter-size="text-2xl" />
    <div>
        <p class="font-medium">{{ $subject->name }}</p>
        <p class="text-sm text-gray-500">{{ $subject->topics_count }} temas · orden: {{ $subject->order }}</p>
    </div>
</div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.subjects.topics', $subject) }}" wire:navigate
                        class="text-sm text-indigo-600 hover:underline">Gestionar temas</a>

                        <button wire:click="openEdit({{ $subject->id }})"
                                class="text-sm text-indigo-600 hover:underline">Editar</button>

                        <button wire:click="delete({{ $subject->id }})"
                                wire:confirm="¿Seguro que quieres eliminar esta materia? Se borrarán también sus temas, materiales y quizzes."
                                class="text-sm text-red-600 hover:underline">Eliminar</button>
                    </div>
                </div>
            @empty
                <p class="p-4 text-sm text-gray-500">No hay materias todavía.</p>
            @endforelse
        </div>
    </div>

    {{-- Modal de crear/editar --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
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
                        <label class="block text-sm font-medium mb-1">Orden</label>
                        <input type="number" wire:model="order" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('order') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Portada (opcional)</label>

                        @if ($editing?->cover_url && ! $removeCover && ! $cover)
                            <div class="mb-2 flex items-center gap-3">
                                <img src="{{ $editing->cover_url }}" class="h-16 rounded">
                                <label class="text-xs text-red-600 flex items-center gap-1">
                                    <input type="checkbox" wire:model="removeCover"> Quitar portada actual
                                </label>
                            </div>
                        @endif

                        <input type="file" wire:model="cover" accept="image/*" class="w-full text-sm">
                        <div wire:loading wire:target="cover" class="text-xs text-gray-400">Subiendo...</div>

                        @if ($cover)
                            <img src="{{ $cover->temporaryUrl() }}" class="h-16 rounded mt-2">
                        @endif

                        <p class="text-xs text-gray-400 mt-1">Recomendado: horizontal (16:9), máx. 2 MB.</p>
                        @error('cover') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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