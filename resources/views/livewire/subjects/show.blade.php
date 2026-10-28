@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <a href="{{ route('dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Oferta académica</a>

        @if (session('subject_notice'))
            <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800">
                {{ session('subject_notice') }}
            </div>
        @endif

        <x-subject-cover :subject="$subject" class="h-48 sm:h-56 rounded-xl" letter-size="text-8xl" />

        <div class="p-6 bg-white rounded-lg shadow space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ $subject->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $subject->description }}</p>
                    <p class="text-xs text-gray-400 mt-3">
                        {{ $state->topics->count() }} {{ $state->topics->count() === 1 ? 'tema' : 'temas' }}
                        @if ($state->exam) · examen final @endif
                    </p>
                </div>

                <div class="shrink-0">
                    @if ($isEnrolled)
                        <span class="block text-center text-sm font-semibold text-green-600 bg-green-100 px-4 py-2 rounded-lg">✓ Inscrito</span>
                        <button wire:click="unenroll"
                                wire:confirm="¿Darte de baja de esta materia? Tu progreso se conserva si vuelves a inscribirte."
                                class="block w-full text-xs text-gray-400 hover:text-red-600 hover:underline mt-2">
                            Darme de baja
                        </button>
                    @else
                        <button wire:click="enroll"
                                class="text-sm font-semibold text-white bg-indigo-600 px-5 py-2.5 rounded-lg hover:bg-indigo-700">
                            Inscribirme
                        </button>
                    @endif
                </div>
            </div>

            @if ($isEnrolled && $totalSteps > 0)
                <div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $state->completed_count }} de {{ $state->topics->count() }} temas completados
                        @if ($state->exam) · examen final {{ $state->exam_passed ? 'aprobado' : 'pendiente' }} @endif
                    </p>
                </div>

                @if ($state->completed)
                    <p class="p-3 rounded-lg bg-green-50 text-sm font-semibold text-green-700">🎓 ¡Completaste esta materia!</p>
                @elseif ($state->next)
                    <a href="{{ $nextUrl }}" wire:navigate
                       class="block sm:inline-block text-center text-sm font-semibold text-white bg-indigo-600 px-5 py-2.5 rounded-lg hover:bg-indigo-700">
                        {{ $nextLabel }} &rarr;
                    </a>
                @endif
            @endif
        </div>

        <div class="space-y-3">
            <h2 class="font-semibold">Recorrido de la materia</h2>

            @unless ($canAccess)
                <p class="text-sm text-gray-500">
                    Inscríbete para acceder al contenido. Los temas se desbloquean en orden y cada uno puede incluir su quiz.
                </p>
            @endunless

            @forelse ($state->topics as $t)
                @php
                    $open = $canAccess && ($isAdmin || $t->unlocked);
                    $isNextTopic = $state->next?->topic?->id === $t->topic->id;
                    $materials = $materialCounts->get($t->topic->id, 0);

                    if ($t->completed) {
                        $status = 'done'; $marker = '✓'; $badge = 'Completado'; $tone = 'green';
                    } elseif (! $open) {
                        $status = 'locked'; $marker = '🔒'; $badge = null; $tone = 'gray';
                    } elseif ($t->studied) {
                        $status = $isNextTopic ? 'active' : 'todo'; $marker = $loop->iteration; $badge = 'Estudiado'; $tone = 'indigo';
                    } else {
                        $status = $isNextTopic ? 'active' : 'todo'; $marker = $loop->iteration; $badge = 'Pendiente'; $tone = 'gray';
                    }
                @endphp

                <div wire:key="step-topic-{{ $t->topic->id }}" class="space-y-2">
                    <x-roadmap-step
                        :href="$open ? route('topics.show', $t->topic) : null"
                        :status="$status"
                        :marker="$marker"
                        :title="$t->topic->title"
                        :subtitle="$materials . ($materials === 1 ? ' material' : ' materiales')"
                        :badge="$badge"
                        :tone="$tone" />

                    @if ($t->quiz)
                        @php
                            $best = $bestByQuiz->get($t->quiz->id);

                            if ($t->quiz_passed) {
                                $qStatus = 'done'; $qMarker = '✓'; $qBadge = 'Aprobado · ' . $fmt($best) . '%'; $qTone = 'green';
                            } elseif (! $open) {
                                $qStatus = 'locked'; $qMarker = '🔒'; $qBadge = null; $qTone = 'gray';
                            } else {
                                $qStatus = 'todo'; $qMarker = '📝';
                                $qBadge = $best !== null ? 'Mejor: ' . $fmt($best) . '%' : 'Pendiente';
                                $qTone = $best !== null ? 'amber' : 'gray';
                            }
                        @endphp

                        <x-roadmap-step
                            variant="quiz"
                            :href="$open ? route('quizzes.show', $t->quiz) : null"
                            :status="$qStatus"
                            :marker="$qMarker"
                            :title="'Quiz: ' . $t->quiz->title"
                            :subtitle="'Nota mínima ' . $t->quiz->pass_score . '%'"
                            :badge="$qBadge"
                            :tone="$qTone" />
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">Esta materia todavía no tiene temas.</p>
            @endforelse

            @if ($state->exam)
                @php
                    $examOpen = $canAccess && ($isAdmin || $state->exam_unlocked);
                    $examBest = $bestByQuiz->get($state->exam->id);

                    if ($state->exam_passed) {
                        $eStatus = 'done'; $eMarker = '🎓'; $eBadge = 'Aprobado · ' . $fmt($examBest) . '%'; $eTone = 'green';
                        $eSubtitle = 'Puedes repetirlo para practicar';
                    } elseif (! $examOpen) {
                        $eStatus = 'locked'; $eMarker = '🔒'; $eBadge = null; $eTone = 'gray';
                        $eSubtitle = 'Se desbloquea al completar todos los temas';
                    } else {
                        $eStatus = 'active'; $eMarker = '🎓';
                        $eBadge = $examBest !== null ? 'Mejor: ' . $fmt($examBest) . '%' : 'Disponible';
                        $eTone = 'purple';
                        $eSubtitle = 'Evalúa todo lo visto en la materia · nota mínima ' . $state->exam->pass_score . '%';
                    }
                @endphp

                <div wire:key="step-exam">
                    <x-roadmap-step
                        variant="exam"
                        :href="$examOpen ? route('quizzes.show', $state->exam) : null"
                        :status="$eStatus"
                        :marker="$eMarker"
                        :title="$state->exam->title"
                        :subtitle="$eSubtitle"
                        :badge="$eBadge"
                        :tone="$eTone" />
                </div>
            @endif
        </div>
    </div>
</div>
