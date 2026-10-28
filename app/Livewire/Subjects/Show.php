<?php

namespace App\Livewire\Subjects;

use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Services\CourseProgress;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Subject $subject;

    public function mount(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function enroll(): void
    {
        auth()->user()->enrolledSubjects()->syncWithoutDetaching([$this->subject->id]);
    }

    public function unenroll(): void
    {
        auth()->user()->enrolledSubjects()->detach($this->subject->id);
    }

    public function render()
    {
        $user = auth()->user();
        $progress = app(CourseProgress::class);

        $isEnrolled = $user->isEnrolledIn($this->subject);
        $isAdmin = $user->isAdmin();

        $state = $progress->subjectState($user, $this->subject);

        $quizIds = $state->topics->map(fn ($t) => $t->quiz?->id)->filter()->values();

        if ($state->exam) {
            $quizIds->push($state->exam->id);
        }

        // Mejor nota del estudiante en cada quiz con intentos terminados.
        $bestByQuiz = $quizIds->isEmpty()
            ? collect()
            : QuizAttempt::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds->all())
                ->where('status', '!=', QuizAttempt::STATUS_IN_PROGRESS)
                ->selectRaw('quiz_id, MAX(percentage) as best')
                ->groupBy('quiz_id')
                ->pluck('best', 'quiz_id');

        $totalSteps = $state->topics->count() + ($state->exam ? 1 : 0);
        $doneSteps = $state->completed_count + ($state->exam_passed ? 1 : 0);

        return view('livewire.subjects.show', [
            'state' => $state,
            'isEnrolled' => $isEnrolled,
            'isAdmin' => $isAdmin,
            'canAccess' => $isEnrolled || $isAdmin,
            'bestByQuiz' => $bestByQuiz,
            'materialCounts' => $this->subject->topics()->withCount('materials')->pluck('materials_count', 'id'),
            'totalSteps' => $totalSteps,
            'progressPercent' => $totalSteps > 0 ? (int) round($doneSteps / $totalSteps * 100) : 0,
            'nextUrl' => $progress->stepUrl($state->next, $this->subject),
            'nextLabel' => $progress->stepLabel($state->next),
        ]);
    }
}
