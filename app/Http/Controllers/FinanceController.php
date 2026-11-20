<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\SavingsGoalContribution;
use App\Models\Transaction;
use App\Support\FinanceBalance;
use Carbon\Carbon;
use Inertia\Inertia;

class FinanceController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();

        // Total recorded income and ordinary expenses
        $totalIncome = (float) Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = (float) Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        // Savings are allocations, not ordinary expenses
        $totalSavingsAllocated = (float) SavingsGoalContribution::where('user_id', $userId)
            ->sum('amount');

        // Monthly income and ordinary expenses
        $monthlyIncome = (float) Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthlyExpenses = (float) Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthlySavingsAllocated = (float) SavingsGoalContribution::where('user_id', $userId)
            ->whereBetween('contribution_date', [$monthStart, $monthEnd])
            ->sum('amount');

        // Spending grouped by category
        $spendingByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Budgets currently in effect
        $budgets = Budget::where('user_id', $userId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get()
            ->map(function ($budget) use ($userId) {
                $spent = Transaction::where('user_id', $userId)
                    ->where('type', 'expense')
                    ->where('category', $budget->category)
                    ->whereDate('transaction_date', '>=', $budget->start_date)
                    ->whereDate('transaction_date', '<=', $budget->end_date)
                    ->sum('amount');

                $limit = (float) $budget->amount;
                $spent = (float) $spent;

                return [
                    'id' => $budget->id,
                    'name' => $budget->name,
                    'category' => $budget->category,
                    'amount' => $limit,
                    'spent' => $spent,
                    'remaining' => $limit - $spent,
                    'percentage' => $limit > 0
                        ? round(($spent / $limit) * 100, 1)
                        : 0,
                ];
            });

        // Unpaid bills due within the next seven days
        $upcomingBills = Bill::where('user_id', $userId)
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->orderBy('due_date')
            ->get();

        // Savings goals
        $savingsGoals = SavingsGoal::where('user_id', $userId)
            ->where('status', '!=', 'completed')
            ->orderBy('target_date')
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => (float) $goal->target_amount,
                    'current_amount' => (float) $goal->current_amount,
                    'target_date' => $goal->target_date,
                    'progress' => $goal->progress_percentage,
                ];
            });

        // Recent ordinary transactions
        $recentTransactions = Transaction::where('user_id', $userId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $availableBalance = FinanceBalance::available($userId);

        return Inertia::render('Finance/Index', [
            'summary' => [
                // Keep "balance" for compatibility with your existing Vue page.
                'balance' => $availableBalance,
                'availableBalance' => $availableBalance,
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'totalSavingsAllocated' => $totalSavingsAllocated,
                'monthlyIncome' => $monthlyIncome,
                'monthlyExpenses' => $monthlyExpenses,
                'monthlySavingsAllocated' => $monthlySavingsAllocated,
            ],
            'spendingByCategory' => $spendingByCategory,
            'budgets' => $budgets,
            'upcomingBills' => $upcomingBills,
            'savingsGoals' => $savingsGoals,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
