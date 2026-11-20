
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
        }),
    },
    spendingByCategory: { type: Array, default: () => [] },
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
</script>

<template>
    <Head title="Financial Overview" />

    <div class="finance-page">
        <!-- Header -->
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

        <!-- Main balance -->
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

        <!-- Income and expenses -->
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

        <!-- Monthly overview -->
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

            <!-- Budget health -->
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

        <!-- Savings and bills -->
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

        <!-- Recent transactions -->
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
.finance-page {
    width: 100%;
    max-width: 1120px;
    margin: 0 auto;
    padding: 32px 24px 48px;
    color: #354638;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.eyebrow {
    margin: 0 0 8px;
    color: #8a9385;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.7px;
    line-height: 1.5;
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
    color: #2b4033;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(30px, 4vw, 40px);
    font-weight: 500;
    letter-spacing: -1.1px;
    line-height: 1.2;
}

.title-period {
    color: #bb806a;
}

.subtitle {
    max-width: 540px;
    margin: 10px 0 0;
    color: #81867c;
    font-size: 12px;
    line-height: 1.8;
}

.header-actions {
    flex-shrink: 0;
}

.primary-button, .secondary-button {
    display: inline-flex;
    min-height: 41px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 0 16px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: background 180ms ease, border-color 180ms ease, transform 180ms ease;
}

.primary-button {
    background: #294b3c;
    color: #fffefa;
}

.primary-button:hover {
    transform: translateY(-1px);
    background: #203d30;
}

.secondary-button {
    border-color: #e2e2d8;
    background: #fffefa;
    color: #4a5d4e;
}

.secondary-button:hover {
    background: #f0f1e9;
}

.button-plus {
    font-size: 18px;
    font-weight: 400;
    line-height: 1;
}

/* Balance panel */

.balance-panel {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(240px, 0.65fr);
    gap: 24px;
    margin-bottom: 15px;
    padding: 27px 29px;
    overflow: hidden;
    border: 1px solid #365b47;
    border-radius: 18px;
    background: #294b3c;
    color: #fffefa;
    box-shadow: 0 5px 18px rgb(41 75 60 / 7%);
}

.balance-main {
    min-width: 0;
}

.balance-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #c5d3c4;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 1.3px;
}

.balance-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #b7cba9;
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
    color: #c0cfc1;
    font-size: 11px;
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
    color: #f0f2e9;
    font-size: 10px;
    font-weight: 650;
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
    width: 35px;
    height: 35px;
    place-items: center;
    border: 1px solid rgb(255 255 255 / 16%);
    border-radius: 11px;
    background: rgb(255 255 255 / 8%);
    color: #d7e0d0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 19px;
}

.balance-side-label {
    margin: 17px 0 5px;
    color: #c5d3c4;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 1.2px;
}

.balance-side-amount {
    margin: 0;
    font-size: clamp(19px, 2.5vw, 25px);
    font-weight: 700;
    letter-spacing: -0.5px;
    overflow-wrap: anywhere;
}

.balance-side-amount.positive {
    color: #d7e5c9;
}

.balance-side-amount.negative {
    color: #f0c1ac;
}

.balance-side-note {
    max-width: 230px;
    margin: 7px 0 0;
    color: #c0cfc1;
    font-size: 10px;
    line-height: 1.7;
}

/* Metric cards */

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 13px;
    margin-bottom: 26px;
}

.metric-card {
    min-width: 0;
    padding: 17px 17px 16px;
    border: 1px solid #e8e7de;
    border-radius: 14px;
    background: #fffefa;
    box-shadow: 0 2px 7px rgb(39 58 48 / 2%);
}

.metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.metric-icon {
    display: grid;
    width: 33px;
    height: 33px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 10px;
    font-size: 17px;
}

.income-icon {
    background: #e5eee1;
    color: #547653;
}

.expense-icon {
    background: #f7e8e1;
    color: #ad7059;
}

.budget-icon {
    background: #eee9f3;
    color: #82719a;
}

.bills-icon {
    background: #f1ecdf;
    color: #9a8250;
}

.metric-period {
    color: #9a9d91;
    font-size: 8px;
    font-weight: 750;
    letter-spacing: 0.8px;
    text-align: right;
}

.metric-label {
    margin: 17px 0 5px;
    color: #81867c;
    font-size: 10px;
}

.metric-value {
    margin: 0;
    color: #2b4033;
    font-size: clamp(17px, 2vw, 22px);
    font-weight: 750;
    letter-spacing: -0.6px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.negative {
    color: #a85f4c !important;
}

.metric-bottom {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 11px;
    color: #929488;
    font-size: 9px;
    line-height: 1.6;
}

.metric-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
}

.income-dot {
    background: #83a07c;
}

.expense-dot {
    background: #c78971;
}

.budget-dot {
    background: #a394bc;
}

.bills-dot {
    background: #c3a565;
}

/* General panels */

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
    gap: 16px;
    margin-bottom: 16px;
}

.panel {
    min-width: 0;
    padding: 23px;
    border: 1px solid #e8e7de;
    border-radius: 16px;
    background: #fffefa;
    box-shadow: 0 2px 8px rgb(39 58 48 / 2%);
}

.panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 22px;
}

.panel-heading .eyebrow {
    margin-bottom: 5px;
}

.panel-heading h2 {
    margin: 0;
    color: #2b4033;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(20px, 2.3vw, 25px);
    font-weight: 500;
    letter-spacing: -0.45px;
    line-height: 1.3;
}

.panel-subtitle {
    margin: 7px 0 0;
    color: #8a8d82;
    font-size: 10px;
    line-height: 1.7;
}

.text-link {
    flex-shrink: 0;
    margin-top: 4px;
    color: #54705d;
    font-size: 10px;
    font-weight: 750;
    text-decoration: none;
}

.text-link:hover {
    text-decoration: underline;
}

/* Spending breakdown */

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
    color: #a5a697;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 10px;
}

.category-name {
    color: #465449;
    font-size: 11px;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.category-amount {
    display: flex;
    flex-shrink: 0;
    align-items: baseline;
    gap: 9px;
}

.category-amount strong {
    color: #344739;
    font-size: 11px;
    font-weight: 750;
}

.category-amount span {
    color: #929488;
    font-size: 9px;
}

.progress-track {
    width: 100%;
    height: 6px;
    overflow: hidden;
    border-radius: 20px;
    background: #eeeee6;
}

.progress-fill {
    height: 100%;
    border-radius: inherit;
    background: #82977a;
    transition: width 250ms ease;
}

.category-fill {
    background: #8da183;
}

.insight-note, .bill-reminder {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 23px;
    padding: 12px;
    border: 1px solid #e8eadd;
    border-radius: 10px;
    background: #f5f6ef;
}

.insight-mark, .reminder-icon {
    display: grid;
    width: 19px;
    height: 19px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 50%;
    background: #dfe7d7;
    color: #54705d;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11px;
    font-weight: 700;
}

.insight-note p, .bill-reminder p {
    margin: 0;
    color: #70796d;
    font-size: 10px;
    line-height: 1.7;
}

.insight-note strong {
    color: #405642;
}

/* Budget check-in */

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
    color: #96998d;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 1px;
}

.budget-summary strong {
    color: #354638;
    font-size: 17px;
    font-weight: 750;
}

.overall-track {
    height: 8px;
}

.fill-warning {
    background: #c4a96c;
}

.fill-danger {
    background: #c77e67;
}

.budget-total-caption, .goal-caption {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 8px;
    color: #929488;
    font-size: 9px;
    line-height: 1.6;
}

.budget-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 23px;
    padding-top: 18px;
    border-top: 1px solid #eeece5;
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
    margin-top: 4px;
    border-radius: 3px;
    background: #8da183;
}

.budget-name {
    margin: 0;
    color: #405143;
    font-size: 11px;
    font-weight: 700;
}

.budget-category {
    margin: 4px 0 0;
    color: #929488;
    font-size: 9px;
}

.status-pill {
    flex-shrink: 0;
    padding: 5px 7px;
    border-radius: 6px;
    font-size: 8px;
    font-weight: 750;
}

.status-good {
    background: #e8eee3;
    color: #547653;
}

.status-warning {
    background: #f3eddd;
    color: #927744;
}

.status-danger {
    background: #f7e8e1;
    color: #a45e4c;
}

.budget-track {
    height: 5px;
}

.budget-amounts {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 7px;
    color: #929488;
    font-size: 9px;
}

/* Savings */

.savings-total-card {
    padding: 17px;
    border: 1px solid #e3e7db;
    border-radius: 12px;
    background: #f2f4ec;
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
    color: #294b3c;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 27px;
    font-weight: 500;
    letter-spacing: -0.5px;
    overflow-wrap: anywhere;
}

.savings-symbol {
    display: grid;
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    place-items: center;
    border: 1px solid #dfe5d6;
    border-radius: 12px;
    background: #e6ebde;
    color: #66815f;
    font-size: 20px;
}

.savings-track, .goal-track {
    height: 6px;
}

.savings-fill {
    background: #a394bc;
}

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
    border-bottom: 1px solid #efeee7;
    text-decoration: none;
}

.goal-item:last-child {
    border-bottom: 0;
}

.goal-marker {
    display: grid;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 10px;
    background: #eee9f3;
    color: #8979a1;
    font-size: 15px;
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
    font-size: 11px;
    font-weight: 700;
}

.goal-heading > span {
    flex-shrink: 0;
    color: #8d7ca5;
    font-size: 10px;
    font-weight: 750;
}

.goal-caption {
    margin-top: 7px;
}

/* Bills */

.bill-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px;
    border: 1px solid #efe7d7;
    border-radius: 12px;
    background: #faf6ed;
}

.bill-summary > div > p {
    margin: 6px 0 0;
    color: #574b36;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 25px;
    font-weight: 500;
}

.bill-count {
    padding: 6px 9px;
    border-radius: 8px;
    background: #f0e7d5;
    color: #8b754a;
    font-size: 9px;
    font-weight: 750;
}

.bill-list {
    margin-top: 8px;
}

.bill-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 0;
    border-bottom: 1px solid #efeee7;
}

.bill-item:last-child {
    border-bottom: 0;
}

.bill-date {
    display: flex;
    width: 43px;
    min-width: 43px;
    min-height: 45px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    border: 1px solid #eee8db;
    border-radius: 10px;
    background: #faf6ed;
}

.bill-date span {
    color: #9a8a68;
    font-size: 8px;
    font-weight: 750;
    text-transform: uppercase;
}

.bill-date strong {
    color: #574b36;
    font-size: 15px;
    font-weight: 750;
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
    font-size: 11px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.bill-details span {
    color: #929488;
    font-size: 9px;
}

.bill-amount {
    flex-shrink: 0;
    color: #554b39;
    font-size: 11px;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.bill-reminder {
    margin-top: 14px;
    border-color: #efe7d7;
    background: #faf6ed;
}

.bill-reminder .reminder-icon {
    background: #f0e7d5;
    color: #8b754a;
}

/* Recent activity */

.activity-panel {
    margin-top: 0;
}

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
    padding: 0 13px 12px;
    border-bottom: 1px solid #eae9e1;
    color: #9a9d91;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    white-space: nowrap;
}

thead th:first-child, tbody td:first-child {
    padding-left: 0;
}

tbody td {
    padding: 14px 13px;
    border-bottom: 1px solid #f0efe9;
    color: #465449;
    font-size: 10px;
}

tbody tr:last-child td {
    border-bottom: 0;
}

.transaction-title {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
}

.transaction-title strong {
    color: #405143;
    font-size: 10px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.transaction-icon {
    display: grid;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 9px;
    font-size: 14px;
}

.transaction-income {
    background: #e8eee3;
    color: #547653;
}

.transaction-expense {
    background: #f7e8e1;
    color: #ad7059;
}

.category-tag {
    display: inline-block;
    max-width: 150px;
    padding: 5px 7px;
    border-radius: 6px;
    background: #f2f1eb;
    color: #777f73;
    font-size: 9px;
    overflow-wrap: anywhere;
}

.muted-cell {
    color: #929488;
    white-space: nowrap;
}

.type-label {
    font-size: 9px;
    font-weight: 700;
}

.income-text {
    color: #547653;
}

.expense-text {
    color: #a56854;
}

.amount-column {
    text-align: right;
}

.amount-cell {
    font-size: 10px;
    font-weight: 750;
    white-space: nowrap;
}

.activity-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #efeee7;
    color: #929488;
    font-size: 9px;
    line-height: 1.6;
}

.activity-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #8da183;
}

/* Empty states */

.empty-state {
    display: flex;
    min-height: 180px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px 18px;
    border: 1px dashed #dedfd4;
    border-radius: 12px;
    background: #faf9f5;
    text-align: center;
}

.empty-icon {
    display: grid;
    width: 39px;
    height: 39px;
    margin-bottom: 12px;
    place-items: center;
    border-radius: 12px;
    background: #e8eee2;
    color: #557653;
    font-size: 18px;
}

.empty-state h3 {
    margin: 0;
    color: #354638;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 18px;
    font-weight: 500;
}

.empty-state p {
    max-width: 320px;
    margin: 7px 0 15px;
    color: #888b81;
    font-size: 10px;
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
    margin-top: 21px;
    color: #a0a194;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11px;
    font-style: italic;
}

/* Responsive layout */

@media (max-width: 950px) {
    .metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .balance-panel {
        grid-template-columns: minmax(0, 1.2fr) minmax(210px, 0.8fr);
    }
}

@media (max-width: 600px) {
    .finance-page {
        padding: 23px 14px 34px;
    }

    .page-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 17px;
    }

    .header-actions, .header-actions .primary-button {
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

    .balance-side-icon {
        display: none;
    }

    .balance-side-label {
        margin-top: 0;
    }

    .balance-links {
        gap: 9px;
    }

    .balance-links a {
        font-size: 9px;
    }

    .metric-grid {
        gap: 10px;
    }

    .metric-card {
        padding: 14px 12px;
    }

    .metric-period {
        font-size: 7px;
        letter-spacing: 0.4px;
    }

    .metric-value {
        font-size: 17px;
    }

    .panel {
        padding: 18px 15px;
    }

    .panel-heading {
        flex-direction: column;
        gap: 9px;
        margin-bottom: 19px;
    }

    .text-link {
        margin-top: 0;
    }

    .category-amount {
        flex-direction: column;
        align-items: flex-end;
        gap: 3px;
    }

    .budget-total-caption, .goal-caption {
        font-size: 8px;
    }

    .bill-item {
        gap: 9px;
    }

    .bill-amount {
        max-width: 100px;
        font-size: 10px;
    }

    .page-footer {
        flex-direction: column;
    }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
    }
}
</style>
