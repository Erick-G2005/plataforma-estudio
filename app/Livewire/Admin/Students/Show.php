<?php

namespace App\Livewire\Admin\Students;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudyProgress;
use App\Models\Subject;
use App\Models\User;
use App\Services\CourseProgress;
use App\Services\QuizService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public User $student;

    public string $newPassword = '';
    public string $enrollSubjectId = '';

    public string $notice = '';
    public string $noticeType = 'success'; // success | info | error

    public function mount(User $user)
    {
        $this->student = $user;
    }

    // ───────────────────────── cuenta ─────────────────────────

    public function setRole(string $role): void
    {
        $this->clearNotice();

        if ($this->isSelf()) {
            $this->setNotice('No puedes cambiar tu propio rol.', 'error');

            return;
        }

        if (! in_array($role, ['student', 'admin'], true)) {
            return;
        }

        $this->student->update(['role' => $role]);

        $this->setNotice($role === 'admin'
            ? "{$this->student->name} ahora es administrador."
            : "{$this->student->name} ya no es administrador.");
    }

    public function generatePassword(): void
    {
        $this->newPassword = Str::password(10, symbols: false);
    }

    public function setPassword(): void
    {
        $this->clearNotice();

        $this->validate(
            ['newPassword' => 'required|string|min:8|max:100'],
            [],
            ['newPassword' => 'contraseña']
        );

        $this->student->password = $this->newPassword;
        $this->student->setRememberToken(Str::random(60));
        $this->student->save();

        if (! $this->isSelf()) {
            $this->closeSessions();
        }

        $this->newPassword = '';

        $this->setNotice('Contraseña actualizada. Compártela con el estudiante y pídele que la cambie desde su perfil.'
            . ($this->isSelf() ? '' : ' Se cerraron sus sesiones abiertas.'));
    }

    // ───────────────────────── materias ─────────────────────────

    public function enroll(): void
    {
        $this->clearNotice();

        if ($this->enrollSubjectId === '') {
            return;
        }

        $subject = Subject::find((int) $this->enrollSubjectId);

        if (! $subject) {
            return;
        }

        $this->student->enrolledSubjects()->syncWithoutDetaching([$subject->id]);
        $this->enrollSubjectId = '';

        $this->setNotice("Inscrito en «{$subject->name}».");
    }

    public function unenroll(int $subjectId): void
    {
        $this->clearNotice();

        $subject = Subject::find($subjectId);

        if (! $subject) {
            return;
        }

        $this->student->enrolledSubjects()->detach($subject->id);

        $this->setNotice("Desinscrito de «{$subject->name}». Su progreso se conserva por si vuelve a inscribirse.", 'info');
    }

    /**
     * Borra el avance del estudiante en una materia: temas estudiados y todos sus intentos.
     */
    public function resetSubjectProgress(int $subjectId): void
    {
        $this->clearNotice();

        $subject = Subject::find($subjectId);

        if (! $subject) {
            return;
        }

        $topicIds = $subject->topics()->pluck('id');
        $quizIds = Quiz::whereIn('topic_id', $topicIds)->pluck('id');

        if ($subject->finalExam) {
            $quizIds->push($subject->finalExam->id);
        }

        StudyProgress::where('user_id', $this->student->id)->whereIn('topic_id', $topicIds)->delete();
        QuizAttempt::where('user_id', $this->student->id)->whereIn('quiz_id', $quizIds)->delete();

        $this->setNotice("Se reinició el progreso de «{$subject->name}».", 'info');
    }

    // ───────────────────────── quizzes ─────────────────────────

    /**
     * Quita el bloqueo por intentos agotados borrando los intentos no aprobados.
     */
    public function resetAttempts(int $quizId): void
    {
        $this->clearNotice();

        $quiz = Quiz::find($quizId);

        if (! $quiz) {
            return;
        }

        $hasPassed = QuizAttempt::where('user_id', $this->student->id)
            ->where('quiz_id', $quiz->id)
            ->where('passed', true)
            ->exists();

        if ($hasPassed) {
            $this->setNotice('Ya aprobó este quiz: no hace falta reiniciar sus intentos.', 'info');

            return;
        }

        QuizAttempt::where('user_id', $this->student->id)->where('quiz_id', $quiz->id)->delete();

        $this->setNotice("Intentos reiniciados en «{$quiz->title}».");
    }

    // ───────────────────────── eliminar ─────────────────────────

    public function deleteStudent()
    {
        $this->clearNotice();

        if ($this->isSelf()) {
            $this->setNotice('No puedes eliminar tu propia cuenta desde aquí.', 'error');

            return;
        }

        if ($this->student->isAdmin()) {
            $this->setNotice('Primero quítale el rol de administrador para poder eliminar la cuenta.', 'error');

            return;
        }

        $this->closeSessions();
        $this->student->delete();

        session()->flash('students_notice', 'Cuenta eliminada.');

        $this->redirectRoute('admin.students.index', navigate: true);
    }

    // ───────────────────────── internos ─────────────────────────

    protected function isSelf(): bool
    {
        return (int) $this->student->id === (int) auth()->id();
    }

    protected function clearNotice(): void
    {
        $this->notice = '';
        $this->noticeType = 'success';
    }

    protected function setNotice(string $message, string $type = 'success'): void
    {
        $this->notice = $message;
        $this->noticeType = $type;
    }

    protected function closeSessions(): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $this->student->id)
                ->delete();
        }
    }

    public function render()
    {
        $service = app(QuizService::class);
        $overview = app(CourseProgress::class)->enrolledOverview($this->student);

        $quizIds = QuizAttempt::where('user_id', $this->student->id)->distinct()->pluck('quiz_id');

        $quizRows = Quiz::with(['topic.subject', 'subject'])
            ->whereIn('id', $quizIds)
            ->get()
            ->map(function (Quiz $quiz) use ($service) {
                return (object) [
                    'quiz' => $quiz,
                    'subject' => $quiz->resolvedSubject(),
                    'status' => $service->status($this->student, $quiz),
                    'last' => QuizAttempt::where('user_id', $this->student->id)
                        ->where('quiz_id', $quiz->id)
                        ->where('status', '!=', QuizAttempt::STATUS_IN_PROGRESS)
                        ->latest('id')
                        ->first(),
                ];
            })
            ->sortBy(fn ($row) => ($row->subject?->name ?? '') . '|' . $row->quiz->title)
            ->values();

        return view('livewire.admin.students.show', [
            'isSelf' => $this->isSelf(),
            'overview' => $overview,
            'available' => Subject::whereNotIn('id', $overview->pluck('id'))->orderBy('order')->get(),
            'quizRows' => $quizRows,
        ]);
    }
}
