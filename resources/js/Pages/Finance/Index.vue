
<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            balance: 0,
            totalIncome: 0,
            totalExpenses: 0,
            monthlyIncome: 0,
            monthlyExpenses: 0,
            previousMonthIncome: 0,
            previousMonthExpenses: 0,
        }),
    },
    spendingByCategory: { type: Array, default: () => [] },
    monthlyTrend: { type: Array, default: () => [] },
    budgets: { type: Array, default: () => [] },
    upcomingBills: { type: Array, default: () => [] },
    savingsGoals: { type: Array, default: () => [] },
    recentTransactions: { type: Array, default: () => [] },
})

const money = (amount) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
    }).format(Number(amount) || 0)

const dateLabel = (date) => {
    if (!date) return 'No date'

    const parsed = new Date(`${String(date).slice(0, 10)}T12:00:00`)

    return Number.isNaN(parsed.getTime())
        ? 'No date'
        : parsed.toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        })
}

const monthLabel = computed(() =>
    new Date().toLocaleDateString('en-PH', {
        month: 'long',
        year: 'numeric',
    })
)

const categoryName = (category) => category || 'Uncategorized'

const totalBudget = computed(() =>
    props.budgets.reduce((sum, item) => sum + Number(item.amount || 0), 0)
)

const totalSpent = computed(() =>
    props.budgets.reduce((sum, item) => sum + Number(item.spent || 0), 0)
)

const remainingBudget = computed(() => totalBudget.value - totalSpent.value)

const budgetUsage = computed(() =>
    totalBudget.value > 0
        ? (totalSpent.value / totalBudget.value) * 100
        : 0
)

const totalBills = computed(() =>
    props.upcomingBills.reduce((sum, bill) => sum + Number(bill.amount || 0), 0)
)

const totalSaved = computed(() =>
    props.savingsGoals.reduce((sum, goal) => sum + Number(goal.current_amount || 0), 0)
)

const totalSavingsTarget = computed(() =>
    props.savingsGoals.reduce((sum, goal) => sum + Number(goal.target_amount || 0), 0)
)

const savingsProgress = computed(() =>
    totalSavingsTarget.value > 0
        ? Math.min(100, (totalSaved.value / totalSavingsTarget.value) * 100)
        : 0
)

const largestCategory = computed(() => {
    if (!props.spendingByCategory.length) return null

    return props.spendingByCategory.reduce((largest, item) =>
        Number(item.total) > Number(largest.total) ? item : largest
    )
})

const remainingAfterBills = computed(() =>
    Number(props.summary.balance || 0) - totalBills.value
)

const budgetPercentage = (budget) =>
    Number(budget.amount) > 0
        ? (Number(budget.spent || 0) / Number(budget.amount)) * 100
        : 0

const budgetStatus = (budget) => {
    const percentage = budgetPercentage(budget)

    if (percentage >= 100) {
        return { label: 'Limit reached', class: 'status-danger' }
    }

    if (percentage >= 80) {
        return { label: 'Almost at limit', class: 'status-warning' }
    }

    return { label: 'On track', class: 'status-good' }
}

const monthlyDifference = computed(() =>
    Number(props.summary.monthlyIncome || 0) -
    Number(props.summary.monthlyExpenses || 0)
)

const monthlyMessage = computed(() => {
    if (monthlyDifference.value > 0) {
        return 'You recorded more income than expenses this month.'
    }

    if (monthlyDifference.value < 0) {
        return 'Your recorded expenses are higher than your income this month.'
    }

    return 'Your recorded income and expenses are currently equal.'
})

const transactionType = (transaction) =>
    String(transaction.type || '').toLowerCase()

// FINANCIAL ANALYTICS

const chartColors = [
    '#82977A',
    '#C78971',
    '#A394BC',
    '#C3A565',
    '#6D9290',
    '#B7A18C',
    '#A5A697',
]

const categoryChartData = computed(() => {
    const total = props.spendingByCategory.reduce(
        (sum, item) => sum + Number(item.total || 0),
        0
    )

    let currentAngle = 0

    return props.spendingByCategory.map((item, index) => {
        const amount = Number(item.total || 0)
        const percentage = total > 0 ? (amount / total) * 100 : 0

        const start = currentAngle
        const end = currentAngle + percentage

        currentAngle = end

        return {
            category: categoryName(item.category),
            amount,
            percentage,
            color: chartColors[index % chartColors.length],
            start,
            end,
        }
    })
})

const categoryDonutStyle = computed(() => {
    if (!categoryChartData.value.length) {
        return { background: '#EEEEE6' }
    }

    const segments = categoryChartData.value.map(item =>
        `${item.color} ${item.start}% ${item.end}%`
    )

    return {
        background: `conic-gradient(${segments.join(', ')})`,
    }
})

const monthlyTrendMaximum = computed(() => {
    const highest = Math.max(
        0,
        ...props.monthlyTrend.map(item =>
            Math.max(
                Number(item.income || 0),
                Number(item.expenses || 0)
            )
        )
    )

    return highest || 1
})

const expenseChange = computed(() => {
    const current = Number(props.summary.monthlyExpenses || 0)
    const previous = Number(props.summary.previousMonthExpenses || 0)

    if (previous === 0) {
        return current > 0 ? null : 0
    }

    return ((current - previous) / previous) * 100
})

const expenseComparisonMessage = computed(() => {
    const current = Number(props.summary.monthlyExpenses || 0)
    const previous = Number(props.summary.previousMonthExpenses || 0)

    if (previous === 0 && current > 0) {
        return 'You have spending recorded this month, but no expenses recorded last month for comparison.'
    }

    if (previous === 0 && current === 0) {
        return 'No expenses have been recorded for this month or last month.'
    }

    if (current > previous) {
        return `Your expenses increased by ${Math.abs(expenseChange.value).toFixed(1)}% compared with last month.`
    }

    if (current < previous) {
        return `Your expenses decreased by ${Math.abs(expenseChange.value).toFixed(1)}% compared with last month.`
    }

    return 'Your recorded expenses are the same as last month.'
})

const financialInsights = computed(() => {
    const insights = []

    if (largestCategory.value && Number(props.summary.monthlyExpenses) > 0) {
        const percentage =
            (Number(largestCategory.value.total) /
                Number(props.summary.monthlyExpenses)) * 100

        insights.push({
            icon: '↗',
            title: 'Your biggest expense',
            description: `${categoryName(largestCategory.value.category)} takes up ${percentage.toFixed(1)}% of your recorded expenses this month.`,
        })
    }

    if (
        Number(props.summary.monthlyIncome) > 0 ||
        Number(props.summary.monthlyExpenses) > 0
    ) {
        const difference =
            Number(props.summary.monthlyIncome || 0) -
            Number(props.summary.monthlyExpenses || 0)

        insights.push({
            icon: '₱',
            title: 'Income and spending',
            description: difference >= 0
                ? `You have ${money(difference)} more recorded income than expenses this month.`
                : `Your recorded expenses exceed your income by ${money(Math.abs(difference))} this month.`,
        })
    }

    insights.push({
        icon: '◷',
        title: 'Month-to-month spending',
        description: expenseComparisonMessage.value,
    })

    return insights
})

const monthlyTrendHasData = computed(() =>
    props.monthlyTrend.some(item =>
        Number(item.income) > 0 || Number(item.expenses) > 0
    )
)
</script>

<template>
    <Head title="Financial Overview" />

    <div class="finance-page">
        <!-- HEADER -->
        <header class="page-header">
            <div>
                <p class="eyebrow">YOUR MONEY, YOUR PLAN</p>
                <h1>Financial overview<span class="title-period">.</span></h1>
                <p class="subtitle">
                    A clearer picture of your spending, savings, and what's coming next.
                </p>
            </div>

            <div class="header-actions">
                <Link href="/finance/transactions/create" class="primary-button">
                    <span class="button-plus">+</span>
                    Add transaction
                </Link>
            </div>
        </header>

        <!-- MAIN BALANCE -->
        <section class="balance-panel">
            <div class="balance-main">
                <div class="balance-label">
                    <span class="balance-dot"></span>
                    TOTAL RECORDED BALANCE
                </div>

                <p class="balance-amount">{{ money(summary.balance) }}</p>

                <p class="balance-description">
                    Recorded income minus recorded expenses
                </p>

                <div class="balance-links">
                    <Link href="/finance/transactions">View transactions <span>↗</span></Link>
                    <span class="link-divider"></span>
                    <Link href="/finance/savings-goals">Your savings goals <span>↗</span></Link>
                </div>
            </div>

            <div class="balance-side">
                <div class="balance-side-icon">₱</div>
                <p class="balance-side-label">THIS MONTH</p>
                <p
                    class="balance-side-amount"
                    :class="monthlyDifference >= 0 ? 'positive' : 'negative'"
                >
                    {{ monthlyDifference >= 0 ? '+' : '−' }}{{ money(Math.abs(monthlyDifference)) }}
                </p>
                <p class="balance-side-note">{{ monthlyMessage }}</p>
            </div>
        </section>

        <!-- FINANCIAL METRICS -->
        <section class="metric-grid">
            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-icon income-icon">↗</span>
                    <span class="metric-period">THIS MONTH</span>
                </div>
                <p class="metric-label">Income recorded</p>
                <p class="metric-value">{{ money(summary.monthlyIncome) }}</p>
                <div class="metric-bottom">
                    <span class="metric-dot income-dot"></span>
                    Money received
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-icon expense-icon">↘</span>
                    <span class="metric-period">THIS MONTH</span>
                </div>
                <p class="metric-label">Expenses recorded</p>
                <p class="metric-value">{{ money(summary.monthlyExpenses) }}</p>
                <div class="metric-bottom">
                    <span class="metric-dot expense-dot"></span>
                    Money spent
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-icon budget-icon">◷</span>
                    <span class="metric-period">ACTIVE BUDGETS</span>
                </div>
                <p class="metric-label">Budget remaining</p>
                <p
                    class="metric-value"
                    :class="remainingBudget < 0 ? 'negative' : ''"
                >
                    {{ money(remainingBudget) }}
                </p>
                <div class="metric-bottom">
                    <span class="metric-dot budget-dot"></span>
                    {{ money(totalSpent) }} of {{ money(totalBudget) }} used
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-icon bills-icon">◷</span>
                    <span class="metric-period">NEXT 7 DAYS</span>
                </div>
                <p class="metric-label">Upcoming bills</p>
                <p class="metric-value">{{ money(totalBills) }}</p>
                <div class="metric-bottom">
                    <span class="metric-dot bills-dot"></span>
                    {{ upcomingBills.length }}
                    {{ upcomingBills.length === 1 ? 'payment' : 'payments' }} due
                </div>
            </article>
        </section>

        <!-- FINANCIAL ANALYTICS -->
        <section class="analytics-section">
            <div class="analytics-heading">
                <div>
                    <p class="eyebrow">YOUR FINANCIAL PATTERNS</p>
                    <h2>Spending analytics<span class="title-period">.</span></h2>
                    <p class="subtitle">
                        Understand where your money goes and how your habits change over time.
                    </p>
                </div>
            </div>

            <div class="analytics-grid">
                <!-- DONUT CHART -->
                <article class="panel analytics-panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">EXPENSE DISTRIBUTION</p>
                            <h2>Where your money goes</h2>
                            <p class="panel-subtitle">
                                Breakdown of this month's recorded expenses.
                            </p>
                        </div>
                    </div>

                    <div v-if="categoryChartData.length" class="donut-layout">
                        <div class="donut-chart" :style="categoryDonutStyle">
                            <div class="donut-center">
                                <span>TOTAL SPENT</span>
                                <strong>{{ money(summary.monthlyExpenses) }}</strong>
                                <small>{{ monthLabel }}</small>
                            </div>
                        </div>

                        <div class="donut-legend">
                            <div
                                v-for="item in categoryChartData"
                                :key="item.category"
                                class="legend-item"
                            >
                                <span
                                    class="legend-dot"
                                    :style="{ background: item.color }"
                                ></span>

                                <div class="legend-details">
                                    <strong>{{ item.category }}</strong>
                                    <span>{{ item.percentage.toFixed(1) }}%</span>
                                </div>

                                <span class="legend-amount">
                                    {{ money(item.amount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="analytics-empty">
                        <span class="empty-icon">◔</span>
                        <h3>No spending data yet</h3>
                        <p>Record an expense to see your spending distribution.</p>
                        <Link href="/finance/transactions/create" class="secondary-button">
                            Record an expense
                        </Link>
                    </div>
                </article>

                <!-- SIX-MONTH BAR CHART -->
                <article class="panel analytics-panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">SIX-MONTH OVERVIEW</p>
                            <h2>Income vs. expenses</h2>
                            <p class="panel-subtitle">
                                Compare the money you received and spent each month.
                            </p>
                        </div>
                    </div>

                    <div class="chart-legend">
                        <span>
                            <i class="chart-key income-key"></i>
                            Income
                        </span>
                        <span>
                            <i class="chart-key expense-key"></i>
                            Expenses
                        </span>
                    </div>

                    <div v-if="monthlyTrendHasData" class="monthly-chart">
                        <div
                            v-for="month in monthlyTrend"
                            :key="month.monthKey"
                            class="monthly-chart-column"
                        >
                            <div class="bar-pair">
                                <div
                                    class="chart-bar income-bar"
                                    :style="{
                                        height: Math.max(
                                            Number(month.income) > 0 ? 3 : 0,
                                            Number(month.income) / monthlyTrendMaximum * 100
                                        ) + '%'
                                    }"
                                    :title="'Income: ' + money(month.income)"
                                ></div>

                                <div
                                    class="chart-bar expense-bar"
                                    :style="{
                                        height: Math.max(
                                            Number(month.expenses) > 0 ? 3 : 0,
                                            Number(month.expenses) / monthlyTrendMaximum * 100
                                        ) + '%'
                                    }"
                                    :title="'Expenses: ' + money(month.expenses)"
                                ></div>
                            </div>

                            <span class="chart-month">{{ month.month }}</span>
                        </div>
                    </div>

                    <div v-else class="analytics-empty chart-empty">
                        <span class="empty-icon">▥</span>
                        <h3>No monthly comparison yet</h3>
                        <p>Your income and expenses will appear here as you record transactions.</p>
                    </div>

                    <div class="chart-footnote">
                        <span class="activity-dot"></span>
                        Based on your last six months of recorded transactions.
                    </div>
                </article>
            </div>

            <!-- SPENDING RANKING -->
            <article class="panel ranking-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">KNOW YOUR PRIORITIES</p>
                        <h2>Biggest spending categories<span class="title-period">.</span></h2>
                        <p class="panel-subtitle">
                            See which categories account for the largest part of your expenses.
                        </p>
                    </div>

                    <Link href="/finance/transactions" class="text-link">
                        View transactions ↗
                    </Link>
                </div>

                <div v-if="categoryChartData.length" class="ranking-list">
                    <div
                        v-for="(item, index) in categoryChartData"
                        :key="item.category"
                        class="ranking-item"
                    >
                        <div class="ranking-top">
                            <div class="ranking-category">
                                <span class="ranking-number">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                                <span
                                    class="ranking-color"
                                    :style="{ background: item.color }"
                                ></span>
                                <strong>{{ item.category }}</strong>
                            </div>

                            <div class="ranking-amount">
                                <strong>{{ money(item.amount) }}</strong>
                                <span>{{ item.percentage.toFixed(1) }}%</span>
                            </div>
                        </div>

                        <div class="progress-track ranking-track">
                            <div
                                class="progress-fill"
                                :style="{
                                    width: item.percentage + '%',
                                    background: item.color
                                }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div v-else class="analytics-empty">
                    <h3>No categories to rank yet</h3>
                    <p>Once you record expenses, your spending categories will be ranked here.</p>
                </div>
            </article>

            <!-- AUTOMATIC INSIGHTS -->
            <article class="panel insights-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">A LITTLE FINANCIAL SELF-CHECK</p>
                        <h2>What your numbers tell you<span class="title-period">.</span></h2>
                        <p class="panel-subtitle">
                            Observations based on your recorded financial activity.
                        </p>
                    </div>
                </div>

                <div class="insights-grid">
                    <div
                        v-for="(insight, index) in financialInsights"
                        :key="index"
                        class="insight-card"
                    >
                        <span class="insight-icon">{{ insight.icon }}</span>
                        <div>
                            <strong>{{ insight.title }}</strong>
                            <p>{{ insight.description }}</p>
                        </div>
                    </div>
                </div>
            </article>

            <!-- MONTHLY COMPARISON -->
            <article class="comparison-card">
                <div class="comparison-main">
                    <p class="eyebrow">MONTHLY SPENDING CHECK</p>
                    <h3>Compared with last month</h3>
                    <p>{{ expenseComparisonMessage }}</p>
                </div>

                <div
                    class="comparison-value"
                    :class="{
                        'comparison-up': expenseChange !== null && expenseChange > 0,
                        'comparison-down': expenseChange !== null && expenseChange < 0
                    }"
                >
                    <strong v-if="expenseChange !== null">
                        {{ expenseChange > 0 ? '+' : '' }}{{ expenseChange.toFixed(1) }}%
                    </strong>
                    <strong v-else>—</strong>
                    <span>Expense change</span>
                </div>
            </article>
        </section>

        <!-- SPENDING AND BUDGET -->
        <section class="content-grid">
            <article class="panel spending-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">UNDERSTAND YOUR HABITS</p>
                        <h2>Where your money goes<span class="title-period">.</span></h2>
                        <p class="panel-subtitle">
                            Spending by category for {{ monthLabel }}.
                        </p>
                    </div>

                    <Link href="/finance/transactions" class="text-link">
                        All transactions ↗
                    </Link>
                </div>

                <div v-if="spendingByCategory.length" class="category-list">
                    <div
                        v-for="(item, index) in spendingByCategory"
                        :key="item.category || index"
                        class="category-item"
                    >
                        <div class="category-row">
                            <div class="category-name-wrap">
                                <span class="category-number">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                                <span class="category-name">
                                    {{ categoryName(item.category) }}
                                </span>
                            </div>

                            <div class="category-amount">
                                <strong>{{ money(item.total) }}</strong>
                                <span>
                                    {{
                                        Number(summary.monthlyExpenses) > 0
                                            ? (Number(item.total) / Number(summary.monthlyExpenses) * 100).toFixed(1)
                                            : '0.0'
                                    }}%
                                </span>
                            </div>
                        </div>

                        <div class="progress-track">
                            <div
                                class="progress-fill category-fill"
                                :style="{
                                    width: Math.min(
                                        100,
                                        Number(summary.monthlyExpenses) > 0
                                            ? Number(item.total) / Number(summary.monthlyExpenses) * 100
                                            : 0
                                    ) + '%'
                                }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <div class="empty-icon">↗</div>
                    <h3>Your spending story starts here.</h3>
                    <p>
                        Add an expense to see which categories take up the largest
                        part of your monthly spending.
                    </p>
                    <Link href="/finance/transactions/create" class="secondary-button">
                        Record an expense
                    </Link>
                </div>

                <div v-if="largestCategory" class="insight-note">
                    <span class="insight-mark">i</span>
                    <p>
                        <strong>{{ categoryName(largestCategory.category) }}</strong>
                        is your largest recorded spending category this month,
                        at {{ money(largestCategory.total) }}.
                    </p>
                </div>
            </article>

            <!-- BUDGET HEALTH -->
            <article class="panel budget-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">STAY WITHIN YOUR PLAN</p>
                        <h2>Budget check-in<span class="title-period">.</span></h2>
                        <p class="panel-subtitle">
                            How your active budgets are holding up.
                        </p>
                    </div>

                    <Link href="/finance/budgets" class="text-link">
                        Manage ↗
                    </Link>
                </div>

                <div class="budget-summary">
                    <div>
                        <span class="small-label">TOTAL BUDGET</span>
                        <strong>{{ money(totalBudget) }}</strong>
                    </div>
                    <div class="budget-summary-right">
                        <span class="small-label">USED SO FAR</span>
                        <strong>{{ Math.round(budgetUsage) }}%</strong>
                    </div>
                </div>

                <div class="progress-track overall-track">
                    <div
                        class="progress-fill"
                        :class="budgetUsage >= 100 ? 'fill-danger' : budgetUsage >= 80 ? 'fill-warning' : ''"
                        :style="{ width: Math.min(100, budgetUsage) + '%' }"
                    ></div>
                </div>

                <div class="budget-total-caption">
                    <span>{{ money(totalSpent) }} spent</span>
                    <span>{{ money(remainingBudget) }} remaining</span>
                </div>

                <div v-if="budgets.length" class="budget-list">
                    <div
                        v-for="budget in budgets"
                        :key="budget.id"
                        class="budget-item"
                    >
                        <div class="budget-item-heading">
                            <div class="budget-name-wrap">
                                <span class="budget-category-dot"></span>
                                <div>
                                    <p class="budget-name">{{ budget.name }}</p>
                                    <p class="budget-category">{{ categoryName(budget.category) }}</p>
                                </div>
                            </div>

                            <span
                                class="status-pill"
                                :class="budgetStatus(budget).class"
                            >
                                {{ budgetStatus(budget).label }}
                            </span>
                        </div>

                        <div class="progress-track budget-track">
                            <div
                                class="progress-fill"
                                :class="budgetPercentage(budget) >= 100
                                    ? 'fill-danger'
                                    : budgetPercentage(budget) >= 80
                                        ? 'fill-warning'
                                        : ''"
                                :style="{ width: Math.min(100, budgetPercentage(budget)) + '%' }"
                            ></div>
                        </div>

                        <div class="budget-amounts">
                            <span>{{ money(budget.spent) }} spent</span>
                            <span>{{ money(Number(budget.amount) - Number(budget.spent)) }} left</span>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state compact-empty">
                    <div class="empty-icon">◷</div>
                    <h3>Give your money a plan.</h3>
                    <p>
                        Set category budgets to understand how much you can spend
                        without exceeding your limits.
                    </p>
                    <Link href="/finance/budgets/create" class="secondary-button">
                        Create a budget
                    </Link>
                </div>
            </article>
        </section>

        <!-- SAVINGS AND BILLS -->
        <section class="content-grid">
            <article class="panel savings-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">SMALL STEPS, BIG GOALS</p>
                        <h2>Your savings journey<span class="title-period">.</span></h2>
                        <p class="panel-subtitle">
                            Keep your future plans in sight.
                        </p>
                    </div>

                    <Link href="/finance/savings-goals" class="text-link">
                        View goals ↗
                    </Link>
                </div>

                <div class="savings-total-card">
                    <div class="savings-total-top">
                        <div>
                            <span class="small-label">SAVED TOWARD ACTIVE GOALS</span>
                            <p class="savings-total">{{ money(totalSaved) }}</p>
                        </div>
                        <div class="savings-symbol">✳</div>
                    </div>

                    <div class="progress-track savings-track">
                        <div
                            class="progress-fill savings-fill"
                            :style="{ width: savingsProgress + '%' }"
                        ></div>
                    </div>

                    <div class="budget-total-caption">
                        <span>{{ Math.round(savingsProgress) }}% of active goal targets</span>
                        <span>{{ money(totalSavingsTarget) }} target</span>
                    </div>
                </div>

                <div v-if="savingsGoals.length" class="goal-list">
                    <Link
                        v-for="goal in savingsGoals"
                        :key="goal.id"
                        :href="`/finance/savings-goals/${goal.id}`"
                        class="goal-item"
                    >
                        <div class="goal-marker">✳</div>

                        <div class="goal-details">
                            <div class="goal-heading">
                                <strong>{{ goal.name }}</strong>
                                <span>{{ Math.round(Number(goal.progress) || 0) }}%</span>
                            </div>

                            <div class="progress-track goal-track">
                                <div
                                    class="progress-fill savings-fill"
                                    :style="{ width: Math.min(100, Number(goal.progress) || 0) + '%' }"
                                ></div>
                            </div>

                            <div class="goal-caption">
                                <span>{{ money(goal.current_amount) }} saved</span>
                                <span v-if="goal.target_date">
                                    Due {{ dateLabel(goal.target_date) }}
                                </span>
                                <span v-else>{{ money(goal.target_amount) }} target</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="empty-state compact-empty">
                    <h3>Make room for a future goal.</h3>
                    <p>
                        Whether it's school supplies, a new laptop, or emergency
                        savings, start with a target that matters to you.
                    </p>
                    <Link href="/finance/savings-goals/create" class="secondary-button">
                        Create a savings goal
                    </Link>
                </div>
            </article>

            <article class="panel bills-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">PLAN AHEAD</p>
                        <h2>Coming up next<span class="title-period">.</span></h2>
                        <p class="panel-subtitle">
                            Unpaid bills due within the next seven days.
                        </p>
                    </div>

                    <Link href="/finance/bills" class="text-link">
                        All bills ↗
                    </Link>
                </div>

                <div class="bill-summary">
                    <div>
                        <span class="small-label">TOTAL DUE SOON</span>
                        <p>{{ money(totalBills) }}</p>
                    </div>
                    <span class="bill-count">
                        {{ upcomingBills.length }}
                        {{ upcomingBills.length === 1 ? 'bill' : 'bills' }}
                    </span>
                </div>

                <div v-if="upcomingBills.length" class="bill-list">
                    <div
                        v-for="bill in upcomingBills"
                        :key="bill.id"
                        class="bill-item"
                    >
                        <div class="bill-date">
                            <span>{{ dateLabel(bill.due_date).split(' ')[0] }}</span>
                            <strong>{{ dateLabel(bill.due_date).split(' ')[1]?.replace(',', '') }}</strong>
                        </div>

                        <div class="bill-details">
                            <strong>{{ bill.title }}</strong>
                            <span>{{ categoryName(bill.category) }}</span>
                        </div>

                        <div class="bill-amount">
                            {{ money(bill.amount) }}
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state compact-empty">
                    <div class="empty-icon">✓</div>
                    <h3>Nothing due in the next week.</h3>
                    <p>
                        You're all clear for the upcoming seven days based on
                        your current bill records.
                    </p>
                    <Link href="/finance/bills/create" class="secondary-button">
                        Add a bill
                    </Link>
                </div>

                <div class="bill-reminder">
                    <span class="reminder-icon">i</span>
                    <p>
                        Check that upcoming payments are covered before allocating
                        more money to optional spending or savings.
                    </p>
                </div>
            </article>
        </section>

        <!-- RECENT TRANSACTIONS -->
        <section class="panel activity-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">YOUR MONEY TRAIL</p>
                    <h2>Recent activity<span class="title-period">.</span></h2>
                    <p class="panel-subtitle">
                        Your latest recorded income and expenses.
                    </p>
                </div>

                <Link href="/finance/transactions" class="text-link">
                    View all transactions ↗
                </Link>
            </div>

            <div v-if="recentTransactions.length" class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="amount-column">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                        >
                            <td>
                                <div class="transaction-title">
                                    <span
                                        class="transaction-icon"
                                        :class="transactionType(transaction) === 'income'
                                            ? 'transaction-income'
                                            : 'transaction-expense'"
                                    >
                                        {{ transactionType(transaction) === 'income' ? '↙' : '↗' }}
                                    </span>
                                    <strong>{{ transaction.description || 'Transaction' }}</strong>
                                </div>
                            </td>

                            <td>
                                <span class="category-tag">
                                    {{ categoryName(transaction.category) }}
                                </span>
                            </td>

                            <td class="muted-cell">
                                {{ dateLabel(transaction.transaction_date) }}
                            </td>

                            <td>
                                <span
                                    class="type-label"
                                    :class="transactionType(transaction) === 'income'
                                        ? 'income-text'
                                        : 'expense-text'"
                                >
                                    {{ transactionType(transaction) === 'income' ? 'Income' : 'Expense' }}
                                </span>
                            </td>

                            <td
                                class="amount-column amount-cell"
                                :class="transactionType(transaction) === 'income'
                                    ? 'income-text'
                                    : 'expense-text'"
                            >
                                {{ transactionType(transaction) === 'income' ? '+' : '−' }}
                                {{ money(transaction.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="empty-state">
                <div class="empty-icon">↗</div>
                <h3>Your financial activity will appear here.</h3>
                <p>
                    Record your allowance, part-time income, or daily expenses
                    to start building your financial history.
                </p>
                <Link href="/finance/transactions/create" class="primary-button">
                    <span class="button-plus">+</span>
                    Add your first transaction
                </Link>
            </div>

            <div class="activity-footer">
                <span class="activity-dot"></span>
                Your overview is based on the records currently saved in your account.
            </div>
        </section>

        <footer class="page-footer">
            <span>Make thoughtful choices, one day at a time.</span>
            <span>{{ monthLabel }}</span>
        </footer>
    </div>
</template>

<style scoped>
/* PAGE */

.finance-page {
    width: 100%;
    min-width: 0;
    padding: 0 0 24px;
    color: #293B32;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.eyebrow {
    margin: 0 0 8px;
    color: #8B8C7D;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.17em;
    line-height: 1.5;
    text-transform: uppercase;
}

h1, h2, h3, p {
    overflow-wrap: anywhere;
}

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(30px, 4vw, 40px);
    font-weight: 500;
    letter-spacing: -1.1px;
    line-height: 1.2;
}

.title-period {
    color: #B97962;
}

.subtitle {
    max-width: 540px;
    margin: 10px 0 0;
    color: #777D73;
    font-size: 13px;
    line-height: 1.8;
}

.header-actions {
    flex-shrink: 0;
}

.primary-button,
.secondary-button {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 0 17px;
    border: 1px solid transparent;
    border-radius: 11px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    text-decoration: none;
    transition: background 180ms ease, border-color 180ms ease, transform 180ms ease;
}

.primary-button {
    background: #294B3C;
    color: #FFFEFA;
}

.primary-button:hover {
    transform: translateY(-1px);
    background: #203D30;
}

.secondary-button {
    border-color: #DCE2D6;
    background: #FFFEFA;
    color: #42614A;
}

.secondary-button:hover {
    background: #E7EBDD;
}

.button-plus {
    font-size: 18px;
    font-weight: 400;
    line-height: 1;
}

/* BALANCE */

.balance-panel {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(240px, 0.65fr);
    gap: 24px;
    margin-bottom: 16px;
    padding: 29px 30px;
    overflow: hidden;
    border: 1px solid #365B47;
    border-radius: 20px;
    background: #294B3C;
    color: #FFFEFA;
    box-shadow: 0 5px 18px rgb(41 75 60 / 7%);
}

.balance-main {
    min-width: 0;
}

.balance-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #C5D3C4;
    font-size: 11px;
    font-weight: 650;
    letter-spacing: 0.13em;
}

.balance-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #B7CBA9;
}

.balance-amount {
    margin: 17px 0 0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(30px, 4vw, 43px);
    font-weight: 500;
    letter-spacing: -1px;
    line-height: 1.2;
    overflow-wrap: anywhere;
}

.balance-description {
    margin: 8px 0 0;
    color: #C0CFC1;
    font-size: 12px;
    line-height: 1.6;
}

.balance-links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    margin-top: 23px;
}

.balance-links a {
    color: #F0F2E9;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
}

.balance-links a:hover {
    text-decoration: underline;
}

.balance-links a span {
    margin-left: 3px;
}

.link-divider {
    width: 1px;
    height: 13px;
    background: rgb(255 255 255 / 25%);
}

.balance-side {
    align-self: stretch;
    padding: 5px 0 5px 23px;
    border-left: 1px solid rgb(255 255 255 / 17%);
}

.balance-side-icon {
    display: grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border: 1px solid rgb(255 255 255 / 16%);
    border-radius: 12px;
    background: rgb(255 255 255 / 8%);
    color: #D7E0D0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 20px;
}

.balance-side-label {
    margin: 17px 0 5px;
    color: #C5D3C4;
    font-size: 11px;
    font-weight: 650;
    letter-spacing: 0.13em;
}

.balance-side-amount {
    margin: 0;
    font-size: clamp(19px, 2.5vw, 25px);
    font-weight: 700;
    letter-spacing: -0.5px;
    overflow-wrap: anywhere;
}

.balance-side-amount.positive {
    color: #D7E5C9;
}

.balance-side-amount.negative {
    color: #F0C1AC;
}

.balance-side-note {
    max-width: 230px;
    margin: 7px 0 0;
    color: #C0CFC1;
    font-size: 11px;
    line-height: 1.7;
}

/* METRICS */

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 30px;
}

.metric-card {
    min-width: 0;
    padding: 19px 18px 17px;
    border: 1px solid #EAE6DD;
    border-radius: 16px;
    background: #FFFEFA;
    box-shadow: 0 2px 8px rgb(39 58 48 / 3%);
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgb(39 58 48 / 6%);
}

.metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.metric-icon {
    display: grid;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 11px;
    font-size: 18px;
}

.income-icon {
    background: #E7EBDD;
    color: #547653;
}

.expense-icon {
    background: #F5E4DD;
    color: #A85F4C;
}

.budget-icon {
    background: #EBE5F3;
    color: #796894;
}

.bills-icon {
    background: #F3ECD9;
    color: #8E7846;
}

.metric-period {
    color: #8B8C7D;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.08em;
    text-align: right;
}

.metric-label {
    margin: 17px 0 5px;
    color: #777D73;
    font-size: 12px;
}

.metric-value {
    margin: 0;
    color: #294B3C;
    font-size: clamp(17px, 2vw, 22px);
    font-weight: 700;
    letter-spacing: -0.6px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.negative {
    color: #A85F4C !important;
}

.metric-bottom {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 11px;
    color: #85877D;
    font-size: 11px;
    line-height: 1.6;
}

.metric-dot {
    width: 7px;
    height: 7px;
    flex-shrink: 0;
    border-radius: 50%;
}

.income-dot { background: #83A07C; }
.expense-dot { background: #C78971; }
.budget-dot { background: #A394BC; }
.bills-dot { background: #C3A565; }

/* GENERAL PANELS */

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
    gap: 16px;
    margin-bottom: 16px;
}

.panel {
    min-width: 0;
    padding: 24px;
    border: 1px solid #EAE6DD;
    border-radius: 18px;
    background: #FFFEFA;
    box-shadow: 0 2px 8px rgb(39 58 48 / 3%);
}

.panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 22px;
}

.panel-heading .eyebrow {
    margin-bottom: 6px;
}

.panel-heading h2 {
    margin: 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(21px, 2.3vw, 25px);
    font-weight: 500;
    letter-spacing: -0.45px;
    line-height: 1.3;
}

.panel-subtitle {
    margin: 7px 0 0;
    color: #777D73;
    font-size: 12px;
    line-height: 1.7;
}

.text-link {
    flex-shrink: 0;
    margin-top: 4px;
    color: #54705D;
    font-size: 11px;
    font-weight: 650;
    text-decoration: none;
}

.text-link:hover {
    text-decoration: underline;
}

/* ANALYTICS */

.analytics-section {
    margin-bottom: 30px;
}

.analytics-heading {
    margin-bottom: 18px;
}

.analytics-heading h2 {
    margin: 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(25px, 3vw, 30px);
    font-weight: 500;
    letter-spacing: -0.6px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}

.analytics-panel {
    min-height: 310px;
}

.donut-layout {
    display: flex;
    align-items: center;
    gap: 23px;
}

.donut-chart {
    display: grid;
    width: 190px;
    height: 190px;
    min-width: 190px;
    place-items: center;
    border-radius: 50%;
    position: relative;
}

.donut-chart::before {
    position: absolute;
    inset: 23px;
    border-radius: 50%;
    background: #FFFEFA;
    content: '';
}

.donut-center {
    z-index: 1;
    display: flex;
    max-width: 135px;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.donut-center span {
    color: #8B8C7D;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.1em;
}

.donut-center strong {
    margin-top: 6px;
    color: #294B3C;
    font-size: 16px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.donut-center small {
    margin-top: 4px;
    color: #85877D;
    font-size: 10px;
}

.donut-legend {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 13px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.legend-dot {
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    border-radius: 3px;
}

.legend-details {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 3px;
}

.legend-details strong {
    color: #465449;
    font-size: 12px;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.legend-details span {
    color: #85877D;
    font-size: 11px;
}

.legend-amount {
    flex-shrink: 0;
    color: #465449;
    font-size: 11px;
    font-weight: 650;
    text-align: right;
}

.chart-legend {
    display: flex;
    align-items: center;
    gap: 18px;
    margin: -5px 0 17px;
}

.chart-legend span {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #777D73;
    font-size: 11px;
    font-weight: 600;
}

.chart-key {
    width: 9px;
    height: 9px;
    border-radius: 3px;
}

.income-key { background: #82977A; }
.expense-key { background: #C78971; }

.monthly-chart {
    display: flex;
    height: 190px;
    align-items: stretch;
    justify-content: space-around;
    gap: 10px;
    padding: 10px 5px 0;
    border-bottom: 1px solid #EAE6DD;
}

.monthly-chart-column {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}

.bar-pair {
    display: flex;
    width: 100%;
    height: 155px;
    align-items: flex-end;
    justify-content: center;
    gap: 5px;
}

.chart-bar {
    width: min(27%, 24px);
    min-height: 0;
    border-radius: 5px 5px 2px 2px;
    transition: height 250ms ease;
}

.income-bar { background: #82977A; }
.expense-bar { background: #C78971; }

.chart-month {
    margin-top: 9px;
    color: #777D73;
    font-size: 11px;
    font-weight: 600;
}

.chart-footnote {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    color: #85877D;
    font-size: 11px;
    line-height: 1.6;
}

.ranking-panel,
.insights-panel {
    margin-bottom: 16px;
}

.ranking-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.ranking-item {
    min-width: 0;
}

.ranking-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
}

.ranking-category {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
}

.ranking-number {
    color: #8B8C7D;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11px;
}

.ranking-color {
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    border-radius: 3px;
}

.ranking-category strong {
    color: #465449;
    font-size: 12px;
    font-weight: 650;
}

.ranking-amount {
    display: flex;
    flex-shrink: 0;
    align-items: baseline;
    gap: 10px;
}

.ranking-amount strong {
    color: #294B3C;
    font-size: 12px;
}

.ranking-amount span {
    color: #85877D;
    font-size: 11px;
}

.ranking-track {
    height: 7px !important;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.insight-card {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    min-width: 0;
    padding: 15px;
    border: 1px solid #EAE6DD;
    border-radius: 13px;
    background: #F7F7F1;
}

.insight-icon {
    display: grid;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 10px;
    background: #E7EBDD;
    color: #54705D;
    font-size: 16px;
}

.insight-card strong {
    display: block;
    color: #405143;
    font-size: 12px;
    font-weight: 650;
}

.insight-card p {
    margin: 6px 0 0;
    color: #777D73;
    font-size: 11px;
    line-height: 1.7;
}

.comparison-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px 23px;
    border: 1px solid #EAE6DD;
    border-radius: 16px;
    background: #F7F5F0;
}

.comparison-main {
    min-width: 0;
}

.comparison-main .eyebrow {
    margin-bottom: 6px;
}

.comparison-main h3 {
    margin: 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 20px;
    font-weight: 500;
}

.comparison-main > p:last-child {
    margin: 6px 0 0;
    color: #777D73;
    font-size: 12px;
    line-height: 1.7;
}

.comparison-value {
    display: flex;
    flex-shrink: 0;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.comparison-value strong {
    color: #687D60;
    font-size: 24px;
    font-weight: 700;
}

.comparison-value span {
    color: #85877D;
    font-size: 11px;
}

.comparison-up strong { color: #A85F4C; }
.comparison-down strong { color: #547653; }

.analytics-empty {
    display: flex;
    min-height: 210px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    text-align: center;
}

.analytics-empty h3 {
    margin: 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 19px;
    font-weight: 500;
}

.analytics-empty p {
    max-width: 260px;
    margin: 7px 0 13px;
    color: #777D73;
    font-size: 12px;
    line-height: 1.8;
}

.chart-empty {
    min-height: 180px;
}

/* SPENDING BREAKDOWN */

.category-list {
    display: flex;
    flex-direction: column;
    gap: 19px;
}

.category-item {
    min-width: 0;
}

.category-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
}

.category-name-wrap {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 9px;
}

.category-number {
    color: #8B8C7D;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11px;
}

.category-name {
    color: #465449;
    font-size: 12px;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.category-amount {
    display: flex;
    flex-shrink: 0;
    align-items: baseline;
    gap: 9px;
}

.category-amount strong {
    color: #294B3C;
    font-size: 12px;
    font-weight: 700;
}

.category-amount span {
    color: #85877D;
    font-size: 11px;
}

.progress-track {
    width: 100%;
    height: 6px;
    overflow: hidden;
    border-radius: 20px;
    background: #EEEEE6;
}

.progress-fill {
    height: 100%;
    border-radius: inherit;
    background: #82977A;
    transition: width 250ms ease;
}

.category-fill {
    background: #8DA183;
}

.insight-note,
.bill-reminder {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 23px;
    padding: 13px;
    border: 1px solid #EAE6DD;
    border-radius: 12px;
    background: #F7F7F1;
}

.insight-mark,
.reminder-icon {
    display: grid;
    width: 21px;
    height: 21px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 50%;
    background: #E7EBDD;
    color: #54705D;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 12px;
    font-weight: 700;
}

.insight-note p,
.bill-reminder p {
    margin: 0;
    color: #70796D;
    font-size: 11px;
    line-height: 1.7;
}

.insight-note strong {
    color: #405642;
}

/* BUDGET */

.budget-summary {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 12px;
}

.budget-summary > div {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.budget-summary-right {
    align-items: flex-end;
}

.small-label {
    color: #8B8C7D;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.1em;
}

.budget-summary strong {
    color: #294B3C;
    font-size: 19px;
    font-weight: 700;
}

.overall-track { height: 8px; }
.fill-warning { background: #C4A96C; }
.fill-danger { background: #C77E67; }

.budget-total-caption,
.goal-caption {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 8px;
    color: #85877D;
    font-size: 11px;
    line-height: 1.6;
}

.budget-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 23px;
    padding-top: 18px;
    border-top: 1px solid #EAE6DD;
}

.budget-item-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}

.budget-name-wrap {
    display: flex;
    min-width: 0;
    align-items: flex-start;
    gap: 9px;
}

.budget-category-dot {
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    margin-top: 5px;
    border-radius: 3px;
    background: #8DA183;
}

.budget-name {
    margin: 0;
    color: #405143;
    font-size: 12px;
    font-weight: 650;
}

.budget-category {
    margin: 4px 0 0;
    color: #85877D;
    font-size: 11px;
}

.status-pill {
    flex-shrink: 0;
    padding: 5px 8px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 650;
}

.status-good {
    background: #E7EBDD;
    color: #547653;
}

.status-warning {
    background: #F3ECD9;
    color: #927744;
}

.status-danger {
    background: #F5E4DD;
    color: #A45E4C;
}

.budget-track { height: 6px; }

.budget-amounts {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 7px;
    color: #85877D;
    font-size: 11px;
}

/* SAVINGS */

.savings-total-card {
    padding: 18px;
    border: 1px solid #DCE3D5;
    border-radius: 14px;
    background: #F2F4EC;
}

.savings-total-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 17px;
}

.savings-total {
    margin: 7px 0 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 29px;
    font-weight: 500;
    letter-spacing: -0.5px;
    overflow-wrap: anywhere;
}

.savings-symbol {
    display: grid;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    place-items: center;
    border: 1px solid #DCE3D5;
    border-radius: 12px;
    background: #E7EBDD;
    color: #66815F;
    font-size: 21px;
}

.savings-track,
.goal-track {
    height: 6px;
}

.savings-fill { background: #A394BC; }

.goal-list {
    display: flex;
    flex-direction: column;
    margin-top: 8px;
}

.goal-item {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 15px 0;
    border-bottom: 1px solid #EAE6DD;
    text-decoration: none;
}

.goal-item:last-child { border-bottom: 0; }

.goal-item:hover .goal-heading strong {
    color: #547653;
}

.goal-marker {
    display: grid;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 10px;
    background: #EBE5F3;
    color: #8979A1;
    font-size: 16px;
}

.goal-details {
    min-width: 0;
    flex: 1;
}

.goal-heading {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 9px;
}

.goal-heading strong {
    color: #405143;
    font-size: 12px;
    font-weight: 650;
}

.goal-heading > span {
    flex-shrink: 0;
    color: #8D7CA5;
    font-size: 11px;
    font-weight: 700;
}

.goal-caption { margin-top: 7px; }

/* BILLS */

.bill-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 17px;
    border: 1px solid #EAE2D2;
    border-radius: 14px;
    background: #FAF6ED;
}

.bill-summary > div > p {
    margin: 6px 0 0;
    color: #574B36;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 27px;
    font-weight: 500;
}

.bill-count {
    padding: 7px 10px;
    border-radius: 8px;
    background: #F0E7D5;
    color: #8B754A;
    font-size: 10px;
    font-weight: 650;
}

.bill-list { margin-top: 8px; }

.bill-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 0;
    border-bottom: 1px solid #EAE6DD;
}

.bill-item:last-child { border-bottom: 0; }

.bill-date {
    display: flex;
    width: 45px;
    min-width: 45px;
    min-height: 47px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    border: 1px solid #EAE2D2;
    border-radius: 11px;
    background: #FAF6ED;
}

.bill-date span {
    color: #9A8A68;
    font-size: 10px;
    font-weight: 650;
    text-transform: uppercase;
}

.bill-date strong {
    color: #574B36;
    font-size: 16px;
    font-weight: 700;
}

.bill-details {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 5px;
}

.bill-details strong {
    color: #405143;
    font-size: 12px;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.bill-details span {
    color: #85877D;
    font-size: 11px;
}

.bill-amount {
    flex-shrink: 0;
    color: #554B39;
    font-size: 12px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.bill-reminder {
    margin-top: 14px;
    border-color: #EAE2D2;
    background: #FAF6ED;
}

.bill-reminder .reminder-icon {
    background: #F0E7D5;
    color: #8B754A;
}

/* RECENT ACTIVITY */

.activity-panel { margin-top: 0; }

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    min-width: 620px;
    border-collapse: collapse;
    text-align: left;
}

thead th {
    padding: 0 13px 13px;
    border-bottom: 1px solid #EAE6DD;
    color: #8B8C7D;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    white-space: nowrap;
}

thead th:first-child,
tbody td:first-child {
    padding-left: 0;
}

tbody td {
    padding: 15px 13px;
    border-bottom: 1px solid #F0EEE8;
    color: #465449;
    font-size: 12px;
}

tbody tr:last-child td { border-bottom: 0; }

.transaction-title {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
}

.transaction-title strong {
    color: #405143;
    font-size: 12px;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.transaction-icon {
    display: grid;
    width: 33px;
    height: 33px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 10px;
    font-size: 15px;
}

.transaction-income {
    background: #E7EBDD;
    color: #547653;
}

.transaction-expense {
    background: #F5E4DD;
    color: #A85F4C;
}

.category-tag {
    display: inline-block;
    max-width: 150px;
    padding: 6px 8px;
    border-radius: 7px;
    background: #F2F1EB;
    color: #777F73;
    font-size: 10px;
    overflow-wrap: anywhere;
}

.muted-cell {
    color: #85877D;
    white-space: nowrap;
}

.type-label {
    font-size: 11px;
    font-weight: 650;
}

.income-text { color: #547653; }
.expense-text { color: #A56854; }

.amount-column { text-align: right; }

.amount-cell {
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.activity-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #EAE6DD;
    color: #85877D;
    font-size: 11px;
    line-height: 1.6;
}

.activity-dot {
    width: 7px;
    height: 7px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #8DA183;
}

/* EMPTY STATES */

.empty-state {
    display: flex;
    min-height: 180px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px 18px;
    border: 1px dashed #D9DDD2;
    border-radius: 14px;
    background: #FAF9F5;
    text-align: center;
}

.empty-icon {
    display: grid;
    width: 42px;
    height: 42px;
    margin-bottom: 12px;
    place-items: center;
    border-radius: 13px;
    background: #E7EBDD;
    color: #547653;
    font-size: 19px;
}

.empty-state h3 {
    margin: 0;
    color: #294B3C;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 19px;
    font-weight: 500;
}

.empty-state p {
    max-width: 320px;
    margin: 7px 0 15px;
    color: #777D73;
    font-size: 12px;
    line-height: 1.8;
}

.compact-empty {
    min-height: 150px;
    margin-top: 18px;
}

.page-footer {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 10px;
    margin-top: 22px;
    color: #8B8C7D;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 12px;
    font-style: italic;
}

/* RESPONSIVE */

@media (max-width: 950px) {
    .metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .content-grid,
    .analytics-grid {
        grid-template-columns: 1fr;
    }

    .balance-panel {
        grid-template-columns: minmax(0, 1.2fr) minmax(210px, 0.8fr);
    }

    .insights-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 650px) {
    .donut-layout {
        flex-direction: column;
    }

    .donut-legend {
        width: 100%;
    }

    .comparison-card {
        align-items: flex-start;
    }
}

@media (max-width: 600px) {
    .page-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 17px;
    }

    .header-actions,
    .header-actions .primary-button {
        width: 100%;
    }

    .balance-panel {
        grid-template-columns: 1fr;
        gap: 21px;
        padding: 22px 20px;
    }

    .balance-side {
        padding: 17px 0 0;
        border-top: 1px solid rgb(255 255 255 / 17%);
        border-left: 0;
    }

    .balance-side-icon { display: none; }

    .balance-side-label { margin-top: 0; }

    .balance-links { gap: 9px; }

    .balance-links a { font-size: 10px; }

    .metric-grid { gap: 10px; }

    .metric-card { padding: 15px 12px; }

    .metric-period {
        font-size: 9px;
        letter-spacing: 0.04em;
    }

    .metric-value { font-size: 17px; }

    .panel { padding: 19px 16px; }

    .panel-heading {
        flex-direction: column;
        gap: 9px;
        margin-bottom: 19px;
    }

    .text-link { margin-top: 0; }

    .category-amount {
        flex-direction: column;
        align-items: flex-end;
        gap: 3px;
    }

    .budget-total-caption,
    .goal-caption {
        font-size: 10px;
    }

    .bill-item { gap: 9px; }

    .bill-amount {
        max-width: 100px;
        font-size: 11px;
    }

    .comparison-card {
        flex-direction: column;
    }

    .comparison-value {
        align-items: flex-start;
    }

    .page-footer { flex-direction: column; }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
    }
}
</style>
