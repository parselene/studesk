
<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Wallet,
    Plus,
    Receipt,
    TrendingUp,
    CalendarDays,
    ChevronDown,
    ChevronUp,
    CircleAlert,
    ArrowUpRight,
} from 'lucide-vue-next';

defineOptions({ layout: AppLayout });

const props = defineProps({
    budgets: { type: Array, default: () => [] },
    unplannedCategories: { type: Array, default: () => [] },
});

const expanded = ref([]);
const search = ref('');

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(value || 0));

const date = (value) => {
    if (!value) return 'Not set';

    return new Date(`${String(value).slice(0, 10)}T00:00:00`)
        .toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
};

const activeBudgets = computed(() =>
    props.budgets.filter((budget) => budget.is_active)
);

const plannedTotal = computed(() =>
    activeBudgets.value.reduce(
        (sum, budget) => sum + Number(budget.amount || 0),
        0
    )
);

const plannedSpent = computed(() =>
    activeBudgets.value.reduce(
        (sum, budget) => sum + Number(budget.spent || 0),
        0
    )
);

const unplannedTotal = computed(() =>
    props.unplannedCategories.reduce(
        (sum, category) => sum + Number(category.spent || 0),
        0
    )
);

const filteredBudgets = computed(() => {
    const query = search.value.trim().toLowerCase();

    return props.budgets.filter((budget) =>
        `${budget.name || ''} ${budget.category || ''}`
            .toLowerCase()
            .includes(query)
    );
});

function toggle(id) {
    expanded.value = expanded.value.includes(id)
        ? expanded.value.filter((item) => item !== id)
        : [...expanded.value, id];
}

function isExpanded(id) {
    return expanded.value.includes(id);
}

function progress(budget) {
    return Number(budget.amount) > 0
        ? Math.round(Number(budget.spent || 0) / Number(budget.amount) * 100)
        : 0;
}
</script>

<template>
    <Head title="Budgets" />

    <main class="mx-auto max-w-7xl space-y-7">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                    FINANCE / BUDGETS
                </p>
                <h1 class="mt-2 text-3xl font-bold text-[#294B3C]">
                    Spending Plans
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-gray-500">
                    Set limits for your spending and track them automatically
                    using your existing transactions.
                </p>
            </div>

            <Link
                href="/finance/budgets/create"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#526B50] px-5 py-3 text-sm font-medium text-white hover:bg-[#40563F]"
            >
                <Plus :size="18" />
                Create spending plan
            </Link>
        </header>

        <!-- Summary -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-2xl border border-[#E8E9E1] bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-[#7D8579]">Planned this period</p>
                    <Wallet :size="20" class="text-[#668260]" />
                </div>
                <p class="mt-3 text-2xl font-semibold text-[#294B3C]">
                    {{ money(plannedTotal) }}
                </p>
                <p class="mt-1 text-xs text-[#92998E]">
                    {{ activeBudgets.length }} active spending plan(s)
                </p>
            </article>

            <article class="rounded-2xl border border-[#E8E9E1] bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-[#7D8579]">Spent against plans</p>
                    <TrendingUp :size="20" class="text-[#C58A69]" />
                </div>
                <p class="mt-3 text-2xl font-semibold text-[#294B3C]">
                    {{ money(plannedSpent) }}
                </p>
                <p class="mt-1 text-xs text-[#92998E]">
                    Actual expenses within active plan dates
                </p>
            </article>

            <article class="rounded-2xl border border-[#E8E9E1] bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-[#7D8579]">Outside spending plans</p>
                    <CircleAlert :size="20" class="text-[#927BAE]" />
                </div>
                <p class="mt-3 text-2xl font-semibold text-[#294B3C]">
                    {{ money(unplannedTotal) }}
                </p>
                <p class="mt-1 text-xs text-[#92998E]">
                    Expenses this month without an active category plan
                </p>
            </article>
        </section>

        <!-- Saved plans -->
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-[#294B3C]">
                    Your spending plans
                </h2>
                <p class="mt-1 text-sm text-[#858D80]">
                    Transactions are counted automatically when their category
                    and date match a plan.
                </p>
            </div>

            <input
                v-model="search"
                type="search"
                placeholder="Search plans or categories..."
                class="w-full rounded-xl border border-[#E1E5DC] bg-white px-4 py-3 text-sm outline-none focus:border-[#829B7B] sm:max-w-md"
            />

            <div v-if="filteredBudgets.length" class="space-y-4">
                <article
                    v-for="budget in filteredBudgets"
                    :key="budget.id"
                    class="overflow-hidden rounded-2xl border border-[#E8E9E1] bg-white"
                >
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row">
                            <div>
                                <h3 class="text-lg font-semibold text-[#294B3C]">
                                    {{ budget.name }}
                                </h3>
                                <p class="mt-1 text-sm text-[#858D80]">
                                    {{ budget.category || 'All expense categories' }}
                                    <span class="mx-1">·</span>
                                    {{ budget.is_active ? 'Active' : 'Outside current dates' }}
                                </p>
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-[#92998E]">
                                    <CalendarDays :size="14" />
                                    {{ date(budget.start_date) }} – {{ date(budget.end_date) }}
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <p class="text-xs text-[#858D80]">Spent / limit</p>
                                <p class="mt-1 text-lg font-semibold text-[#294B3C]">
                                    {{ money(budget.spent) }}
                                    <span class="font-normal text-[#858D80]">
                                        / {{ money(budget.amount) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="flex justify-between gap-3 text-xs">
                                <span class="text-[#526B50]">
                                    {{ progress(budget) }}% used
                                </span>
                                <span
                                    :class="Number(budget.remaining) < 0
                                        ? 'font-medium text-red-600'
                                        : 'text-[#858D80]'"
                                >
                                    {{ Number(budget.remaining) < 0 ? 'Over limit by ' : 'Remaining ' }}
                                    {{ money(Math.abs(Number(budget.remaining || 0))) }}
                                </span>
                            </div>

                            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-[#EDF0E9]">
                                <div
                                    class="h-full rounded-full"
                                    :class="progress(budget) > 100 ? 'bg-[#C76E5E]' : 'bg-[#829B7B]'"
                                    :style="{ width: `${Math.min(progress(budget), 100)}%` }"
                                />
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="toggle(budget.id)"
                                class="inline-flex items-center gap-2 rounded-lg border border-[#E1E5DC] px-4 py-2.5 text-sm font-medium text-[#526B50] hover:bg-[#F7F8F4]"
                            >
                                <Receipt :size="16" />
                                Purchases ({{ (budget.transactions || []).length }})
                                <ChevronUp v-if="isExpanded(budget.id)" :size="16" />
                                <ChevronDown v-else :size="16" />
                            </button>

                            <Link
                                :href="`/finance/budgets/${budget.id}/edit`"
                                class="inline-flex items-center gap-2 rounded-lg border border-[#E1E5DC] px-4 py-2.5 text-sm text-[#526B50] hover:bg-[#F7F8F4]"
                            >
                                Edit plan
                                <ArrowUpRight :size="15" />
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="isExpanded(budget.id)"
                        class="border-t border-[#E8E9E1] bg-[#FCFCFA]"
                    >
                        <div
                            v-if="(budget.transactions || []).length"
                            class="divide-y divide-[#ECEEE7]"
                        >
                            <div
                                v-for="transaction in budget.transactions"
                                :key="transaction.id"
                                class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-[#294B3C]">
                                        {{ transaction.description || 'Expense' }}
                                    </p>
                                    <p class="mt-1 text-xs text-[#92998E]">
                                        {{ transaction.category }} · {{ date(transaction.transaction_date) }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-[#C58A69]">
                                    −{{ money(transaction.amount) }}
                                </p>
                            </div>
                        </div>

                        <p v-else class="px-5 py-6 text-sm text-[#858D80] sm:px-6">
                            No matching transactions in this plan's date range yet.
                        </p>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-[#D9DED3] bg-white px-6 py-10 text-center"
            >
                <Wallet :size="28" class="mx-auto text-[#829B7B]" />
                <h3 class="mt-3 font-semibold text-[#294B3C]">
                    No spending plans yet
                </h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#858D80]">
                    Your expenses are still recorded in Transactions. Create a plan
                    when you want to set a spending limit and monitor your progress.
                </p>
                <Link
                    href="/finance/budgets/create"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#526B50] px-5 py-3 text-sm font-medium text-white hover:bg-[#40563F]"
                >
                    <Plus :size="17" />
                    Create spending plan
                </Link>
            </div>
        </section>

        <!-- Unplanned expenses -->
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-[#294B3C]">
                    Outside your plans
                </h2>
                <p class="mt-1 text-sm text-[#858D80]">
                    These categories have expenses this month but no active
                    category-specific spending plan.
                </p>
            </div>

            <div
                v-if="unplannedCategories.length"
                class="overflow-hidden rounded-2xl border border-[#E8E9E1] bg-white"
            >
                <div
                    v-for="category in unplannedCategories"
                    :key="category.category"
                    class="flex flex-col justify-between gap-3 border-b border-[#ECEEE7] p-5 last:border-b-0 sm:flex-row sm:items-center"
                >
                    <div>
                        <h3 class="font-semibold text-[#294B3C]">
                            {{ category.category }}
                        </h3>
                        <p class="mt-1 text-xs text-[#858D80]">
                            {{ category.transactions.length }}
                            purchase(s) this month
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <p class="font-semibold text-[#294B3C]">
                            {{ money(category.spent) }}
                        </p>
                        <Link
                            :href="`/finance/budgets/create?category=${encodeURIComponent(category.category)}`"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-[#D9E2D4] px-3 py-2 text-sm font-medium text-[#526B50] hover:bg-[#EDF2E9]"
                        >
                            <Plus :size="15" />
                            Plan
                        </Link>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="rounded-2xl border border-[#E8E9E1] bg-white p-5 text-sm text-[#858D80]"
            >
                No unplanned expense categories this month. Nice work keeping your
                spending organized.
            </div>
        </section>

        <footer class="text-xs leading-5 text-[#92998E]">
            Expenses are recorded in Transactions. Spending plans only set limits
            and summarize matching expenses; they do not create duplicate transactions.
        </footer>
    </main>
</template>
