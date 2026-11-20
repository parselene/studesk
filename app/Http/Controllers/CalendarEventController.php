<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\CalendarEvent;
use App\Models\SavingsGoal;
use App\Models\Subject;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        return Inertia::render('Calendar/Index', [
            'events' => CalendarEvent::where('user_id', $userId)
                ->orderBy('starts_at')
                ->get(),

            'subjects' => Subject::where('user_id', $userId)
                ->get([
                    'id',
                    'name',
                    'code',
                    'class_days',
                    'start_time',
                    'end_time',
                    'classroom',
                ]),

            'tasks' => Task::where('user_id', $userId)
                ->with('subject:id,name,code')
                ->whereNotNull('due_at')
                ->get([
                    'id',
                    'subject_id',
                    'title',
                    'description',
                    'task_type',
                    'priority',
                    'status',
                    'due_at',
                    'completed_at',
                ]),

            'bills' => Bill::where('user_id', $userId)
                ->get([
                    'id',
                    'title',
                    'category',
                    'amount',
                    'due_date',
                    'status',
                    'paid_at',
                    'is_recurring',
                ]),

            'savingsGoals' => SavingsGoal::where('user_id', $userId)
                ->whereNotNull('target_date')
                ->get([
                    'id',
                    'name',
                    'target_amount',
                    'current_amount',
                    'target_date',
                    'status',
                ]),
        ]);
    }

    public function create()
    {
        return redirect()->route('calendar.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => [
                'required',
                'string',
                Rule::in(['academic', 'financial', 'personal']),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $request->user()->calendarEvents()->create($validated);

        return redirect()->route('calendar.index')
            ->with('success', 'Calendar event created successfully.');
    }

    public function show(CalendarEvent $calendarEvent)
    {
        abort_unless(
            $calendarEvent->user_id === auth()->id(),
            403
        );

        return response()->json($calendarEvent);
    }

    public function edit(CalendarEvent $calendarEvent)
    {
        abort_unless(
            $calendarEvent->user_id === auth()->id(),
            403
        );

        return redirect()->route('calendar.index');
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        abort_unless(
            $calendarEvent->user_id === $request->user()->id,
            403
        );

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => [
                'required',
                'string',
                Rule::in(['academic', 'financial', 'personal']),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $calendarEvent->update($validated);

        return redirect()->route('calendar.index')
            ->with('success', 'Calendar event updated successfully.');
    }

    public function destroy(Request $request, CalendarEvent $calendarEvent)
    {
        abort_unless(
            $calendarEvent->user_id === $request->user()->id,
            403
        );

        $calendarEvent->delete();

        return redirect()->route('calendar.index')
            ->with('success', 'Calendar event deleted successfully.');
    }
}
