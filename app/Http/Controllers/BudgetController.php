<?php

namespace App\Http\Controllers;


use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{


public function index()
{
    $user = auth()->user();
    $today = now()->toDateString();

    // Load actual spending plans belonging to this user.
    $budgets = $user->budgets()
        ->orderBy('start_date')
        ->get()
        ->map(function ($budget) use ($user, $today) {
            $transactions = $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [
                    $budget->start_date->toDateString(),
                    $budget->end_date->toDateString(),
                ])
                ->when(
                    $budget->category,
                    fn ($query) => $query->where(
                        'category',
                        $budget->category
                    )
                )
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->get([
                    'id',
                    'description',
                    'category',
                    'amount',
                    'transaction_date',
                ]);

            $spent = (float) $transactions->sum('amount');
            $limit = (float) $budget->amount;

            $budget->spent = $spent;
            $budget->remaining = $limit - $spent;
            $budget->progress = $limit > 0
                ? round(($spent / $limit) * 100, 1)
                : 0;

            $budget->transactions = $transactions;
            $budget->is_active = $budget->start_date->toDateString() <= $today
                && $budget->end_date->toDateString() >= $today;

            return $budget;
        });

    // Find categories that have expenses this month but no active plan.
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd = now()->endOfMonth()->toDateString();

    $monthlyExpenses = $user->transactions()
        ->where('type', 'expense')
        ->whereBetween('transaction_date', [$monthStart, $monthEnd])
        ->orderByDesc('transaction_date')
        ->orderByDesc('id')
        ->get([
            'id',
            'description',
            'category',
            'amount',
            'transaction_date',
        ]);

    $activeCategories = $budgets
        ->filter(fn ($budget) => $budget->is_active && $budget->category)
        ->pluck('category')
        ->map(fn ($category) => mb_strtolower(trim($category)))
        ->unique()
        ->values()
        ->all();

    $unplannedCategories = $monthlyExpenses
        ->groupBy(fn ($transaction) =>
            trim($transaction->category ?: 'Uncategorized')
        )
        ->reject(fn ($transactions, $category) =>
            in_array(mb_strtolower(trim($category)), $activeCategories, true)
        )
        ->map(fn ($transactions, $category) => [
            'category' => $category,
            'spent' => (float) $transactions->sum('amount'),
            'transactions' => $transactions->values(),
        ])
        ->values();

    return Inertia::render('Budgets/Index', [
        'budgets' => $budgets,
        'unplannedCategories' => $unplannedCategories,
    ]);
}



public function create()
{
    return Inertia::render('Budgets/Create', [
        'prefillCategory' => request('category', ''),
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'category' => ['required', 'string', 'max:100'],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after_or_equal:start_date'],
    ]);

    $request->user()->budgets()->create($validated);

    return redirect()
        ->route('finance.budgets.index')
        ->with('success', 'Spending plan created successfully.');
}

public function edit(Budget $budget)
{
    abort_unless($budget->user_id === auth()->id(), 403);

    return Inertia::render('Budgets/Edit', [
        'budget' => $budget,
    ]);
}

public function update(Request $request, Budget $budget)
{
    abort_unless($budget->user_id === $request->user()->id, 403);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'category' => ['required', 'string', 'max:100'],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after_or_equal:start_date'],
    ]);

    $budget->update($validated);

    return redirect()
        ->route('finance.budgets.index')
        ->with('success', 'Spending plan updated successfully.');
}

    public function destroy(string $id)
    {
        $budget = auth()->user()->budgets()->findOrFail($id);
        $budget->delete();

        return redirect()
            ->route('finance.budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }
}
