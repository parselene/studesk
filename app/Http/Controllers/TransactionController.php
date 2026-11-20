<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\FinanceBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;

        $transactions = $user->transactions()
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $income = (float) $user->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $expenses = (float) $user->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        $savingsAllocated = (float) \App\Models\SavingsGoalContribution::where('user_id', $userId)
            ->sum('amount');

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
        return Inertia::render('Transactions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

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

        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $transaction->update($validated);

        return redirect()
            ->route('finance.transactions.index')
            ->with('success', 'Transaction updated successfully.');
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
