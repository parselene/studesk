<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $today = now()->startOfDay();
        $nextWeek = now()->addDays(7)->endOfDay();

        // Financial totals
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $availableBalance = $totalIncome - $totalExpenses;

        $spentThisMonth = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        // Tasks due within the next seven days, excluding completed tasks
        $tasksDueSoon = Task::where('user_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$today, $nextWeek])
            ->count();

        // Pending bills due today or within the next seven days
        $paymentsComingUp = Bill::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereBetween('due_date', [
                $today->toDateString(),
                $nextWeek->toDateString(),
            ])
            ->count();

        // Upcoming unfinished tasks
        $tasks = Task::where('user_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_at')
            ->where('due_at', '>=', $today)
            ->get()
            ->map(function ($task) {
                $date = Carbon::parse($task->due_at);

                return [
                    'id' => 'task-' . $task->id,
                    'date' => $date->toIso8601String(),
                    'day' => $date->format('d'),
                    'month' => strtoupper($date->format('M')),
                    'title' => $task->title,
                    'subject' => ucfirst(str_replace('_', ' ', $task->task_type)),
                    'type' => 'Academic',
                    'tone' => 'peach',
                ];
            });

        // Upcoming unpaid bills
        $bills = Bill::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereDate('due_date', '>=', $today->toDateString())
            ->get()
            ->map(function ($bill) {
                $date = Carbon::parse($bill->due_date);

                return [
                    'id' => 'bill-' . $bill->id,
                    'date' => $date->toIso8601String(),
                    'day' => $date->format('d'),
                    'month' => strtoupper($date->format('M')),
                    'title' => $bill->title,
                    'subject' => '₱' . number_format((float) $bill->amount, 2) . ' payment due',
                    'type' => 'Finance',
                    'tone' => 'sage',
                ];
            });

        // Standalone calendar events.
        // Task and bill records are already added above, so avoid duplicates
        // from calendar events linked to those records.
        $calendarEvents = CalendarEvent::where('user_id', $userId)
            ->where('starts_at', '>=', $today)
            ->where(function ($query) {
                $query->whereNull('eventable_type')
                    ->orWhereNotIn('eventable_type', [
                        Task::class,
                        Bill::class,
                    ]);
            })
            ->get()
            ->map(function ($event) {
                $date = Carbon::parse($event->starts_at);

                $tone = match ($event->event_type) {
                    'academic' => 'peach',
                    'financial' => 'sage',
                    default => 'lavender',
                };

                return [
                    'id' => 'event-' . $event->id,
                    'date' => $date->toIso8601String(),
                    'day' => $date->format('d'),
                    'month' => strtoupper($date->format('M')),
                    'title' => $event->title,
                    'subject' => ucfirst($event->event_type) . ' event',
                    'type' => ucfirst($event->event_type),
                    'tone' => $tone,
                ];
            });

        // Combine and sort all upcoming items by date.
        $events = $tasks
            ->concat($bills)
            ->concat($calendarEvents)
            ->sortBy('date')
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'statsData' => [
                'availableBalance' => number_format($availableBalance, 2, '.', ''),
                'tasksDueSoon' => $tasksDueSoon,
                'spentThisMonth' => number_format($spentThisMonth, 2, '.', ''),
                'paymentsComingUp' => $paymentsComingUp,
            ],
            'events' => $events,
        ]);
    }
}
