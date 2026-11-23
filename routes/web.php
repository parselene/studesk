<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('subjects', SubjectController::class);
    Route::resource('tasks', TaskController::class);
    Route::get('/calendar', [CalendarEventController::class, 'index'])
    ->name('calendar.index');

    Route::post('/calendar-events', [CalendarEventController::class, 'store'])
    ->name('calendar-events.store');

    Route::put('/calendar-events/{calendar_event}', [CalendarEventController::class, 'update'])
        ->name('calendar-events.update');

    Route::patch('/calendar-events/{calendar_event}', [CalendarEventController::class, 'update'])
        ->name('calendar-events.patch');

    Route::delete('/calendar-events/{calendar_event}', [CalendarEventController::class, 'destroy'])
        ->name('calendar-events.destroy');

    Route::patch('/notifications/{id}/read', [
        NotificationController::class,
        'markAsRead',
    ])->name('notifications.read');

    Route::post('/notifications/read-all', [
        NotificationController::class,
        'markAllAsRead',
    ])->name('notifications.read-all');
});

Route::middleware(['auth'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'index'])->name('index');

    Route::resource('transactions', TransactionController::class);
    Route::resource('savings-goals', SavingsGoalController::class);
    Route::resource('budgets', BudgetController::class);Route::patch('bills/{bill}/pay', [BillController::class, 'markPaid'])
    ->name('bills.pay');
    Route::resource('bills', BillController::class);
    Route::post('savings-goals/{id}/contribute', [
        \App\Http\Controllers\SavingsGoalController::class,
        'contribute',
    ])->name('savings-goals.contribute');
});

require __DIR__.'/auth.php';
