<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Models\SavingsGoalContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Support\FinanceBalance;
use Illuminate\Validation\ValidationException;

class SavingsGoalController extends Controller
{
    public function index()
    {
        $goals = SavingsGoal::where('user_id', auth()->id())
            ->with('contributions')
            ->latest()
            ->get()
            ->map(function ($goal) {
                $goal->current_amount = $goal->contributions->sum('amount');
                $goal->progress_percentage = $goal->progress_percentage;

                return $goal;
            });

        return Inertia::render('SavingsGoals/Index', [
            'goals' => $goals,
        ]);
    }

    public function create()
    {
        return Inertia::render('SavingsGoals/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $goal = SavingsGoal::create([
            ...$validated,
            'user_id' => auth()->id(),
            'current_amount' => 0,
            'status' => 'active',
        ]);

        return redirect()
            ->route('finance.savings-goals.index')
            ->with('success', 'Savings goal created successfully.');
    }

    public function show(string $id)
    {
        $goal = $this->findUserGoal($id);

        $goal->load('contributions');

        return Inertia::render('SavingsGoals/Show', [
            'goal' => $goal,
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('SavingsGoals/Edit', [
            'goal' => $this->findUserGoal($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $goal = $this->findUserGoal($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'target_date' => ['nullable', 'date'],
        ]);

        $goal->update($validated);

        if ((float) $goal->current_amount >= (float) $goal->target_amount) {
            $goal->update(['status' => 'completed']);
        } elseif ($goal->status === 'completed') {
            $goal->update(['status' => 'active']);
        }

        return redirect()
            ->route('finance.savings-goals.index')
            ->with('success', 'Savings goal updated successfully.');
    }

    public function destroy(string $id)
    {
        $goal = $this->findUserGoal($id);

        $goal->delete();

        return redirect()
            ->route('finance.savings-goals.index')
            ->with('success', 'Savings goal deleted successfully.');
    }


public function contribute(Request $request, string $id)
{
    $goal = $this->findUserGoal($id);

    $validated = $request->validate([
        'amount' => [
            'required',
            'numeric',
            'min:0.01',
            'max:9999999999.99',
        ],
        'contribution_date' => [
            'required',
            'date',
            'before_or_equal:today',
        ],
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    DB::transaction(function () use ($goal, $validated) {
        // Lock the student's row to help serialize concurrent allocations.
        $user = \App\Models\User::whereKey(auth()->id())
            ->lockForUpdate()
            ->firstOrFail();

        $lockedGoal = SavingsGoal::where('user_id', $user->id)
            ->whereKey($goal->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($lockedGoal->status === 'completed') {
            throw ValidationException::withMessages([
                'amount' => 'This savings goal is already completed.',
            ]);
        }

        $amount = round((float) $validated['amount'], 2);
        $availableBalance = FinanceBalance::available($user->id);

        if ($amount > $availableBalance) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Insufficient available balance. You currently have ₱%s available.',
                    number_format(max(0, $availableBalance), 2)
                ),
            ]);
        }

        SavingsGoalContribution::create([
            'savings_goal_id' => $lockedGoal->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'contribution_date' => $validated['contribution_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $totalSaved = (float) $lockedGoal->contributions()->sum('amount');

        $lockedGoal->update([
            'current_amount' => $totalSaved,
            'status' => $totalSaved >= (float) $lockedGoal->target_amount
                ? 'completed'
                : 'active',
        ]);
    });

    return redirect()
        ->route('finance.savings-goals.show', $goal->id)
        ->with('success', 'Savings allocation added successfully.');
}


    private function findUserGoal(string $id): SavingsGoal
    {
        return SavingsGoal::where('user_id', auth()->id())
            ->findOrFail($id);
    }
}
