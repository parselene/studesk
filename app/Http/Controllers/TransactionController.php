<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\FinanceBalance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;

        $transactions = $user->transactions()
            ->with('budget:id,name,category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $income = (float) $user->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $expenses = (float) $user->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        $savingsAllocated = (float) \App\Models\SavingsGoalContribution::where(
            'user_id',
            $userId
        )->sum('amount');

        $availableBalance = FinanceBalance::available($userId);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'summary' => [
                'income' => $income,
                'expenses' => $expenses,
                'savingsAllocated' => $savingsAllocated,
                'balance' => $availableBalance,
                'availableBalance' => $availableBalance,
            ],
        ]);
    }

    public function create()
    {
        $budgets = auth()->user()->budgets()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'category',
                'amount',
                'start_date',
                'end_date',
            ]);

        return Inertia::render('Transactions/Create', [
            'budgets' => $budgets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTransaction($request);

        $request->user()->transactions()->create($validated);

        return redirect()
            ->route('finance.transactions.index')
            ->with('success', 'Transaction added successfully.');
    }

    public function update(Request $request, string $id)
    {
        $transaction = $request->user()
            ->transactions()
            ->findOrFail($id);

        $validated = $this->validateTransaction($request);

        $transaction->update($validated);

        return redirect()
            ->route('finance.transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    private function validateTransaction(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],

            'budget_id' => [
                'nullable',
                'integer',
                Rule::exists('budgets', 'id')
                    ->where('user_id', $request->user()->id),
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
                'required_if:type,income',
            ],

            'description' => ['nullable', 'string', 'max:255'],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
            ],

            'transaction_date' => ['required', 'date'],

            'payment_method' => ['nullable', 'string', 'max:100'],

            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Income should never be assigned to a spending budget.
        if (
            $validated['type'] === 'income'
            && !empty($validated['budget_id'])
        ) {
            throw ValidationException::withMessages([
                'budget_id' => 'Income cannot be assigned to a spending budget.',
            ]);
        }

        if ($validated['type'] === 'expense') {

            if (!empty($validated['budget_id'])) {

                // Only retrieve budgets belonging to the current user.
                $budget = $request->user()
                    ->budgets()
                    ->findOrFail($validated['budget_id']);

                // The expense date must fall within the budget period.
                $date = $validated['transaction_date'];

                if (
                    $date < $budget->start_date->toDateString()
                    || $date > $budget->end_date->toDateString()
                ) {
                    throw ValidationException::withMessages([
                        'budget_id' => 'This transaction date is outside the selected budget period.',
                    ]);
                }

                // Automatically inherit the budget category.
                $validated['category'] = $budget->category;

            } else {

                // Allow expenses without a budget, but retain their category.
                if (empty($validated['category'])) {
                    throw ValidationException::withMessages([
                        'category' => 'Please select a category or assign a budget.',
                    ]);
                }
            }

        } else {
            // Income does not belong to a budget.
            $validated['budget_id'] = null;
        }

        return $validated;
    }

    public function destroy(Request $request, string $id)
    {
        $transaction = $request->user()
            ->transactions()
            ->findOrFail($id);

        $transaction->delete();

        return redirect()
            ->route('finance.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
