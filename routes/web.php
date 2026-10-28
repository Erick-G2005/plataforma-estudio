<?php

use App\Http\Controllers\HomeController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Materials\Manage as AdminMaterialsManage;
use App\Livewire\Admin\Materials\MaterialForm as AdminMaterialForm;
use App\Livewire\Admin\Quizzes\Manage as AdminQuizzesManage;
use App\Livewire\Admin\Quizzes\QuestionForm as AdminQuestionForm;
use App\Livewire\Admin\Students\Index as AdminStudentsIndex;
use App\Livewire\Admin\Students\Show as AdminStudentsShow;
use App\Livewire\Admin\Subjects\Manage as AdminSubjectsManage;
use App\Livewire\Admin\Topics\Manage as AdminTopicsManage;
use App\Livewire\Dashboard as StudentDashboard;
use App\Livewire\MyLearning;
use App\Livewire\Quizzes\Attempt as QuizAttemptPage;
use App\Livewire\Quizzes\Take as QuizTake;
use App\Livewire\Subjects\Show as SubjectsShow;
use App\Livewire\Topics\Show as TopicsShow;
use Illuminate\Support\Facades\Route;


// Página de inicio para visitantes; quien ya inició sesión pasa directo a su panel
Route::get('/', HomeController::class)->middleware('guest')->name('home');

Route::get('dashboard', StudentDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-learning', MyLearning::class)->name('my-learning');
    Route::get('/subjects/{subject}', SubjectsShow::class)->name('subjects.show');
    Route::get('/topics/{topic}', TopicsShow::class)->name('topics.show');
    Route::get('/quizzes/{quiz}', QuizTake::class)->name('quizzes.show');
    Route::get('/quizzes/{quiz}/attempts/{attempt}', QuizAttemptPage::class)->name('quizzes.attempt');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/subjects', AdminSubjectsManage::class)->name('subjects.index');
    Route::get('/subjects/{subject}/topics', AdminTopicsManage::class)->name('subjects.topics');
    Route::get('/subjects/{subject}/exam', AdminQuizzesManage::class)->name('subjects.exam');
    Route::get('/topics/{topic}/materials', AdminMaterialsManage::class)->name('topics.materials');
    Route::get('/topics/{topic}/materials/create', AdminMaterialForm::class)->name('topics.materials.create');
    Route::get('/materials/{material}/edit', AdminMaterialForm::class)->name('materials.edit');
    Route::get('/topics/{topic}/quiz', AdminQuizzesManage::class)->name('topics.quiz');
    Route::get('/quizzes/{quiz}/questions/create', AdminQuestionForm::class)->name('quizzes.questions.create');
    Route::get('/quizzes/{quiz}/questions/{question}/edit', AdminQuestionForm::class)->name('quizzes.questions.edit');
    Route::get('/students', AdminStudentsIndex::class)->name('students.index');
    Route::get('/students/{user}', AdminStudentsShow::class)->name('students.show');
});

require __DIR__.'/auth.php';
