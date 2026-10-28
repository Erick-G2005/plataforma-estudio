<div class="pb-12">
    {{-- Banner de bienvenida --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <p class="text-indigo-100 text-sm">Hola, {{ auth()->user()->name }} 👋</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-white mt-1">¿Qué quieres aprender hoy?</h1>
            <p class="text-indigo-100 mt-2 max-w-xl">Elige una materia, inscríbete y avanza a tu propio ritmo.</p>

            <div class="mt-6 max-w-xl">
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar una materia..."
                       class="w-full rounded-full border-0 px-5 py-3 text-sm shadow-lg focus:ring-2 focus:ring-white/60">
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 space-y-12">

        {{-- Continúa donde lo dejaste --}}
        @if ($continueLearning->isNotEmpty() && $search === '')
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Continúa donde lo dejaste</h2>
                    <a href="{{ route('my-learning') }}" wire:navigate
                       class="text-sm font-semibold text-indigo-600 hover:underline">Mi aprendizaje &rarr;</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($continueLearning as $subject)
                        <div wire:key="continue-{{ $subject->id }}" class="p-5 bg-white rounded-xl shadow space-y-3">
                            <h3 class="font-semibold">{{ $subject->name }}</h3>

                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $subject->progress }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ $subject->completed_topics }} de {{ $subject->total_topics }} temas · {{ $subject->progress }}%
                            </p>

                            <a href="{{ $subject->next_url }}" wire:navigate
                               class="block text-center text-sm font-semibold text-white bg-indigo-600 px-3 py-2 rounded-lg hover:bg-indigo-700">
                                {{ Str::limit($subject->next_label, 34) }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Oferta académica --}}
        <section>
            <div class="flex items-end justify-between mb-4">
                <h2 class="text-lg font-semibold">Oferta académica</h2>
                <span class="text-sm text-gray-400">{{ $subjects->total() }} {{ $subjects->total() === 1 ? 'materia' : 'materias' }}</span>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($subjects as $subject)
                    @php $isEnrolled = in_array($subject->id, $enrolledIds); @endphp

                    <a wire:key="offer-{{ $subject->id }}" href="{{ route('subjects.show', $subject) }}" wire:navigate
                       class="group bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">

                        <x-subject-cover :subject="$subject" class="h-36">
                            @if ($isEnrolled)
                                <span class="absolute top-3 right-3 text-xs font-semibold bg-white text-green-600 px-2 py-1 rounded-full">✓ Inscrito</span>
                            @endif
                        </x-subject-cover>

                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="font-semibold text-lg">{{ $subject->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1 flex-1">{{ Str::limit($subject->description, 90) }}</p>

                            <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
                                <span>📚 {{ $subject->topics_count }} temas</span>
                                <span>👥 {{ $subject->students_count }} {{ $subject->students_count === 1 ? 'estudiante' : 'estudiantes' }}</span>
                            </div>

                            @if ($isEnrolled)
                                <span class="mt-4 text-sm font-semibold text-center py-2 rounded-lg bg-green-50 text-green-700">Continuar</span>
                            @else
                                <span class="mt-4 text-sm font-semibold text-center py-2 rounded-lg bg-indigo-600 text-white">Ver materia</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 col-span-full">No se encontraron materias con ese nombre.</p>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $subjects->links() }}
            </div>
        </section>
    </div>
</div>
