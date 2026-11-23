<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Bill;
use App\Models\CalendarEvent;
use App\Notifications\StuDeskReminder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SendDueDateReminders extends Command
{
    protected $signature = 'studesk:send-reminders';

    protected $description = 'Send task, bill, and calendar event reminders';

    public function handle(): int
    {
        $now = now()->startOfMinute();

        $this->sendTaskReminders($now);
        $this->sendBillReminders($now);
        $this->sendCalendarReminders($now);

        $this->info('StuDesk reminders checked successfully.');

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | Task Reminders
    |--------------------------------------------------------------------------
    */


    private function sendTaskReminders(Carbon $now): void
    {
        $tasks = Task::with('user')
            ->whereNotNull('due_at')
            ->where('due_at', '>=', $now)
            ->where('due_at', '<=', $now->copy()->addDay()->addMinute())
            ->where('status', '!=', 'completed')
            ->whereNull('completed_at')
            ->get();

        foreach ($tasks as $task) {
            $dueAt = Carbon::parse($task->due_at);

            $reminders = [
                [
                    'time' => $dueAt->copy()->subDay(),
                    'key' => 'one_day_before',
                    'title' => 'Task Due Tomorrow: ',
                    'message' => 'Your task is due tomorrow.',
                ],
                [
                    'time' => $dueAt->copy()->subHours(3),
                    'key' => 'three_hours_before',
                    'title' => 'Task Due in 3 Hours: ',
                    'message' => 'Your task is due in 3 hours.',
                ],
                [
                    'time' => $dueAt->copy()->subHour(),
                    'key' => 'one_hour_before',
                    'title' => 'Task Due in 1 Hour: ',
                    'message' => 'Your task is due in 1 hour.',
                ],
                [
                    'time' => $dueAt->copy(),
                    'key' => 'due_now',
                    'title' => 'Task Due Now: ',
                    'message' => 'Your task is due now.',
                ],
            ];

            foreach ($reminders as $reminder) {
                if (!$this->isCurrentMinute($reminder['time'], $now)) {
                    continue;
                }

                $this->sendReminder(
                    $task,
                    $reminder['title'] . $task->title,
                    $reminder['message'] . ' Task: "' . $task->title . '".',
                    url('/tasks'),
                    'task:' . $task->id . ':' . $reminder['key']
                );
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Bill Reminders
    |--------------------------------------------------------------------------
    */

    private function sendBillReminders(Carbon $now): void
    {
        $bills = Bill::with('user')
            ->whereBetween('due_date', [
                $now->toDateString(),
                $now->copy()->addDays(3)->toDateString(),
            ])
            ->where('status', '!=', 'paid')
            ->get();

        foreach ($bills as $bill) {
            $dueDate = Carbon::parse($bill->due_date)->startOfDay();

            $reminders = [
                3 => 'three_days_before',
                1 => 'one_day_before',
                0 => 'due_today',
            ];

            foreach ($reminders as $days => $key) {
                $reminderDate = $dueDate->copy()->subDays($days);

                if (!$this->isBillReminderTime($reminderDate, $now)) {
                    continue;
                }

                $amount = number_format((float) $bill->amount, 2);

                $message = match ($days) {
                    3 => 'Your bill "' . $bill->title . '" is due in 3 days. Amount: ₱' . $amount . '.',
                    1 => 'Your bill "' . $bill->title . '" is due tomorrow. Amount: ₱' . $amount . '.',
                    default => 'Your bill "' . $bill->title . '" is due today. Amount: ₱' . $amount . '.',
                };

                $this->sendReminder(
                    $bill,
                    'Bill Reminder: ' . $bill->title,
                    $message,
                    url('/finance/bills'),
                    'bill:' . $bill->id . ':' . $key
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calendar Event Reminders
    |--------------------------------------------------------------------------
    */

    private function sendCalendarReminders(Carbon $now): void
    {
        $events = CalendarEvent::with('user')
            ->where('starts_at', '>=', $now)
            ->where('starts_at', '<=', $now->copy()->addDay()->addMinute())
            ->whereNull('eventable_type')
            ->whereNull('eventable_id')
            ->get();

        foreach ($events as $event) {
            $startsAt = Carbon::parse($event->starts_at);

            // One day before the event.
            $oneDayBefore = $startsAt->copy()->subDay();

            if ($this->isCurrentMinute($oneDayBefore, $now)) {
                $this->sendReminder(
                    $event,
                    'Upcoming Event Tomorrow: ' . $event->title,
                    'Your calendar event "' . $event->title . '" is scheduled for tomorrow.',
                    url('/calendar'),
                    'calendar_event:' . $event->id . ':one_day_before'
                );
            }

            // One hour before the event.
            $oneHourBefore = $startsAt->copy()->subHour();

            if ($this->isCurrentMinute($oneHourBefore, $now)) {
                $this->sendReminder(
                    $event,
                    'Upcoming Event in 1 Hour: ' . $event->title,
                    'Your calendar event "' . $event->title . '" starts in one hour.',
                    url('/calendar'),
                    'calendar_event:' . $event->id . ':one_hour_before'
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Shared Reminder Helpers
    |--------------------------------------------------------------------------
    */

    private function isCurrentMinute(Carbon $target, Carbon $now): bool
    {
        return $target->greaterThanOrEqualTo($now)
            && $target->lessThan($now->copy()->addMinute());
    }

    private function isBillReminderTime(Carbon $date, Carbon $now): bool
    {
        if (!$date->isSameDay($now)) {
            return false;
        }

        $reminderStart = $now->copy()->setTime(9, 0);
        $reminderEnd = $now->copy()->setTime(9, 5);

        return $now->betweenIncluded($reminderStart, $reminderEnd);
    }

    private function sendReminder(
        Model $record,
        string $title,
        string $message,
        string $url,
        string $reminderKey
    ): void {
        $user = $record->user;

        if (!$user) {
            return;
        }

        // Prevent sending the same reminder more than once.
        $alreadySent = $user->notifications()
            ->where('data->reminder_key', $reminderKey)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $user->notify(new StuDeskReminder(
            title: $title,
            message: $message,
            url: $url,
            reminderKey: $reminderKey,
        ));

        $this->line('Reminder sent: ' . $title . ' to ' . $user->email);
    }
}
