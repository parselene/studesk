<?php

namespace App\Support;

use App\Models\SavingsGoalContribution;
use App\Models\Transaction;

class FinanceBalance
{
    public static function available(int $userId, ?int $excludeContributionId = null): float
    {
        $income = (float) Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $expenses = (float) Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $allocationsQuery = SavingsGoalContribution::where('user_id', $userId);

        if ($excludeContributionId !== null) {
            $allocationsQuery->where('id', '!=', $excludeContributionId);
        }

        $allocations = (float) $allocationsQuery->sum('amount');

        return round($income - $expenses - $allocations, 2);
    }
}
