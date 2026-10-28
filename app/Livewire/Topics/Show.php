<?php

namespace App\Livewire\Topics;

use App\Models\Topic;
use App\Services\CourseProgress;
use App\Services\QuizService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Topic $topic;

    public function mount(Topic $topic)
    {
        $this->topic = $topic;

        $user = auth()->user();
        $progress = app(CourseProgress::class);

        // Inscripción y orden de la materia: el tema debe estar desbloqueado.
        if (! $progress->canAccessTopic($user, $topic)) {
            session()->flash('subject_notice', $progress->lockedMessage($user, $topic->subject));

            $this->redirectRoute('subjects.show', ['subject' => $topic->subject->id], navigate: true);
        }
    }

    public function markAsStudied(): void
    {
        app(CourseProgress::class)->markStudied(auth()->user(), $this->topic);
    }

    public function render()
    {
        $user = auth()->user();
        $progress = app(CourseProgress::class);
        $subject = $this->topic->subject;

        $subjectState = $progress->subjectState($user, $subject);

        $state = $subjectState->topics->first(fn ($s) => $s->topic->id === $this->topic->id);
        $quiz = $state?->quiz;

        return view('livewire.topics.show', [
            'materials' => $this->topic->materials,
            'state' => $state,
            'quiz' => $quiz,
            'quizStatus' => $quiz ? app(QuizService::class)->status($user, $quiz) : null,
            'nextUrl' => $progress->stepUrl($subjectState->next, $subject),
            'nextLabel' => $subjectState->next
                ? $progress->stepLabel($subjectState->next)
                : 'Volver a la materia',
        ]);
    }
}
