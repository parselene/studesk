<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $bills = Bill::where('user_id', $request->user()->id)
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' AND due_date < CURDATE() THEN 0
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'overdue' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('due_date')
            ->get()
            ->map(function ($bill) {
                // Show overdue status for pending bills past their due date.
                if (
                    $bill->status === 'pending'
                    && $bill->due_date->isPast()
                    && !$bill->due_date->isToday()
                ) {
                    $bill->display_status = 'overdue';
                } else {
                    $bill->display_status = $bill->status;
                }

                return $bill;
            });

        return Inertia::render('Bills/Index', [
            'bills' => $bills,
        ]);
    }

    public function create()
    {
        return Inertia::render('Bills/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'is_recurring' => ['required', 'boolean'],
            'recurrence_frequency' => [
                'nullable',
                'required_if:is_recurring,1',
                'in:weekly,monthly,quarterly,yearly',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        if (!$validated['is_recurring']) {
            $validated['recurrence_frequency'] = null;
        }

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';
        $validated['paid_at'] = null;

        Bill::create($validated);

        return redirect()
            ->route('finance.bills.index')
            ->with('success', 'Bill added successfully.');
    }

    public function edit(Bill $bill)
    {
        abort_unless($bill->user_id === auth()->id(), 403);

        return Inertia::render('Bills/Edit', [
            'bill' => $bill,
        ]);
    }

    public function update(Request $request, Bill $bill)
    {
        abort_unless($bill->user_id === $request->user()->id, 403);

        // Paid bills cannot be changed back to pending through this form.
        if ($bill->status === 'paid') {
            return back()->withErrors([
                'bill' => 'Paid bills cannot be edited.',
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'is_recurring' => ['required', 'boolean'],
            'recurrence_frequency' => [
                'nullable',
                'required_if:is_recurring,1',
                'in:weekly,monthly,quarterly,yearly',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        if (!$validated['is_recurring']) {
            $validated['recurrence_frequency'] = null;
        }

        $bill->update($validated);

        return redirect()
            ->route('finance.bills.index')
            ->with('success', 'Bill updated successfully.');
    }


public function markPaid(Request $request, Bill $bill)
{
    abort_unless($bill->user_id === auth()->id(), 403);

    \Illuminate\Support\Facades\DB::transaction(function () use ($bill) {
        // Lock the bill to prevent duplicate payments.
        $bill = Bill::where('user_id', auth()->id())
            ->whereKey($bill->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Do not record the expense or generate another bill twice.
        if ($bill->status === 'paid') {
            return;
        }

        // Record the actual payment as an expense.
        \App\Models\Transaction::create([
            'user_id' => auth()->id(),
            'type' => 'expense',
            'category' => $bill->category ?: 'Bills',
            'description' => $bill->title,
            'amount' => $bill->amount,
            'transaction_date' => now()->toDateString(),
        ]);

        // Mark the current bill as paid.
        $bill->update([
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
        ]);

        // Stop here if this is not a recurring bill.
        if (! $bill->is_recurring || ! $bill->recurrence_frequency) {
            return;
        }

        // Calculate the next due date.
        $nextDueDate = \Carbon\Carbon::parse($bill->due_date);

        switch (strtolower($bill->recurrence_frequency)) {
            case 'weekly':
                $nextDueDate->addWeek();
                break;

            case 'monthly':
                $nextDueDate->addMonthNoOverflow();
                break;

            case 'yearly':
                $nextDueDate->addYearNoOverflow();
                break;

            default:
                // Do not generate a bill for an unsupported frequency.
                return;
        }

        // Create the next unpaid bill.
        Bill::create([
            'user_id' => $bill->user_id,
            'title' => $bill->title,
            'category' => $bill->category,
            'amount' => $bill->amount,
            'due_date' => $nextDueDate->toDateString(),
            'status' => 'pending',
            'is_recurring' => true,
            'recurrence_frequency' => $bill->recurrence_frequency,
            'paid_at' => null,
            'notes' => $bill->notes,
        ]);
    });

    return back()->with(
        'success',
        'Bill marked as paid. Your next recurring bill has been generated if applicable.'
    );
}


    public function destroy(Request $request, Bill $bill)
    {
        abort_unless($bill->user_id === $request->user()->id, 403);

        $bill->delete();

        return redirect()
            ->route('finance.bills.index')
            ->with('success', 'Bill deleted successfully.');
    }
}
