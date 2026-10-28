<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Reglas de avance dentro de una materia:
 *  - Los temas se desbloquean en orden (el primero siempre está abierto).
 *  - Un tema está completado cuando se marcó como estudiado y, si tiene quiz, se aprobó.
 *  - El examen final se desbloquea al completar todos los temas.
 *  - Los administradores no tienen bloqueos.
 */
class CourseProgress
{
    /**
     * Estado de cada tema de la materia para este usuario, en orden.
     */
    public function topicStates(User $user, Subject $subject): Collection
    {
        $topics = $subject->topics()->with('quiz')->get();

        $progress = $user->studyProgress()
            ->whereIn('topic_id', $topics->pluck('id'))
            ->get()
            ->keyBy('topic_id');

        $quizIds = $topics->pluck('quiz.id')->filter()->values()->all();
        $passedQuizIds = $this->passedQuizIds($user, $quizIds);

        $previousCompleted = true; // el primer tema siempre está abierto

        return $topics->map(function (Topic $topic) use ($progress, $passedQuizIds, &$previousCompleted) {
            $row = $progress->get($topic->id);
            $quiz = $topic->quiz;

            $studied = (bool) $row?->studied_at;
            $quizPassed = $quiz ? in_array((int) $quiz->id, $passedQuizIds, true) : null;

            $meetsRules = $studied && ($quiz === null || $quizPassed === true);

            // Un "completado" ya guardado se respeta (temas anteriores a las reglas nuevas).
            $completed = (bool) $row?->completed || $meetsRules;

            // Si cumple las reglas pero el registro quedó sin marcar, se corrige.
            if ($meetsRules && $row && ! $row->completed) {
                $row->update(['completed' => true, 'completed_at' => now()]);
            }

            $state = (object) [
                'topic' => $topic,
                'quiz' => $quiz,
                'studied' => $studied,
                'quiz_passed' => $quizPassed,
                'completed' => $completed,
                'unlocked' => $previousCompleted,
            ];

            $previousCompleted = $completed;

            return $state;
        })->values();
    }

    /**
     * Estado global de la materia: temas, examen final y siguiente paso.
     */
    public function subjectState(User $user, Subject $subject): object
    {
        $topics = $this->topicStates($user, $subject);
        $allCompleted = $topics->every(fn ($t) => $t->completed);

        $exam = $subject->finalExam;
        $examPassed = $exam ? $this->hasPassed($user, $exam) : false;

        return (object) [
            'topics' => $topics,
            'exam' => $exam,
            'exam_unlocked' => $allCompleted,
            'exam_passed' => $examPassed,
            'completed' => $allCompleted && (! $exam || $examPassed),
            'completed_count' => $topics->where('completed', true)->count(),
            'next' => $this->nextStep($topics, $exam, $examPassed),
        ];
    }

    /**
     * Materias en las que está inscrito el usuario, con su avance y su siguiente paso.
     * El examen final cuenta como un paso más del progreso.
     */
    public function enrolledOverview(User $user): Collection
    {
        return $user->enrolledSubjects()
            ->with('finalExam')
            ->orderBy('subjects.order')
            ->get()
            ->map(function (Subject $subject) use ($user) {
                $state = $this->subjectState($user, $subject);

                $totalTopics = $state->topics->count();
                $totalSteps = $totalTopics + ($state->exam ? 1 : 0);
                $doneSteps = $state->completed_count + ($state->exam_passed ? 1 : 0);

                $subject->total_topics = $totalTopics;
                $subject->completed_topics = $state->completed_count;
                $subject->has_exam = $state->exam !== null;
                $subject->exam_passed = $state->exam_passed;
                $subject->completed = $totalSteps > 0 && $state->completed;
                $subject->progress = $totalSteps > 0 ? (int) round($doneSteps / $totalSteps * 100) : 0;
                $subject->next_step = $state->next;
                $subject->next_url = $this->stepUrl($state->next, $subject);
                $subject->next_label = $this->stepLabel($state->next);

                return $subject;
            });
    }

    public function canAccessTopic(User $user, Topic $topic): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $subject = $topic->subject;

        if (! $subject || ! $user->isEnrolledIn($subject)) {
            return false;
        }

        $state = $this->topicStates($user, $subject)
            ->first(fn ($s) => $s->topic->id === $topic->id);

        return (bool) ($state?->unlocked);
    }

    public function canAccessQuiz(User $user, Quiz $quiz): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $subject = $quiz->resolvedSubject();

        if (! $subject || ! $user->isEnrolledIn($subject)) {
            return false;
        }

        if ($quiz->isFinal()) {
            return $this->subjectState($user, $subject)->exam_unlocked;
        }

        return $quiz->topic ? $this->canAccessTopic($user, $quiz->topic) : false;
    }

    /**
     * Mensaje que explica por qué un contenido está bloqueado.
     */
    public function lockedMessage(User $user, Subject $subject, bool $forExam = false): string
    {
        if (! $user->isEnrolledIn($subject)) {
            return 'Inscríbete en la materia para acceder a su contenido.';
        }

        if ($forExam) {
            return 'Completa todos los temas de la materia para desbloquear el examen final.';
        }

        $blocking = $this->topicStates($user, $subject)->first(fn ($s) => ! $s->completed);

        return $blocking
            ? 'Para avanzar, primero completa el tema «' . $blocking->topic->title . '».'
            : 'Todavía no puedes acceder a este contenido.';
    }

    public function markStudied(User $user, Topic $topic): void
    {
        $progress = $user->studyProgress()->firstOrNew(['topic_id' => $topic->id]);

        if (! $progress->studied_at) {
            $progress->studied_at = now();
        }

        $progress->save();

        $this->syncTopicCompletion($user, $topic);
    }

    /**
     * Marca el tema como completado si ya está estudiado y (si tiene quiz) aprobado.
     */
    public function syncTopicCompletion(User $user, Topic $topic): void
    {
        $progress = $user->studyProgress()->where('topic_id', $topic->id)->first();

        if (! $progress || ! $progress->studied_at || $progress->completed) {
            return;
        }

        $quiz = $topic->quiz;

        if ($quiz && ! $this->hasPassed($user, $quiz)) {
            return;
        }

        $progress->update(['completed' => true, 'completed_at' => now()]);
    }

    public function hasPassed(User $user, Quiz $quiz): bool
    {
        return QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('passed', true)
            ->exists();
    }

    /**
     * URL a la que lleva un paso del recorrido (tema, quiz o examen); sin paso, a la materia.
     */
    public function stepUrl(?object $step, Subject $subject): string
    {
        if (! $step) {
            return route('subjects.show', $subject);
        }

        return $step->type === 'topic'
            ? route('topics.show', $step->topic)
            : route('quizzes.show', $step->quiz);
    }

    /**
     * Texto del botón "Continuar" según el siguiente paso.
     */
    public function stepLabel(?object $step): string
    {
        if (! $step) {
            return 'Repasar';
        }

        return match ($step->type) {
            'topic' => 'Continuar: ' . $step->topic->title,
            'quiz' => 'Hacer el quiz: ' . $step->topic->title,
            default => 'Presentar el examen final',
        };
    }

    protected function passedQuizIds(User $user, array $quizIds): array
    {
        if (empty($quizIds)) {
            return [];
        }

        return QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->where('passed', true)
            ->pluck('quiz_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Siguiente paso real del estudiante: un tema, un quiz o el examen final.
     */
    protected function nextStep(Collection $topics, ?Quiz $exam, bool $examPassed): ?object
    {
        $pending = $topics->first(fn ($t) => ! $t->completed);

        if ($pending) {
            // Si ya estudió el tema, lo que le falta es aprobar el quiz.
            $type = ($pending->studied && $pending->quiz) ? 'quiz' : 'topic';

            return (object) ['type' => $type, 'topic' => $pending->topic, 'quiz' => $pending->quiz];
        }

        if ($exam && ! $examPassed) {
            return (object) ['type' => 'exam', 'topic' => null, 'quiz' => $exam];
        }

        return null;
    }
}
