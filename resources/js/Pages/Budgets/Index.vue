
<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
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
    Search,
    CheckCircle2,
    Target,
} from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const props = defineProps({
    budgets: { type: Array, default: () => [] },
    unplannedCategories: { type: Array, default: () => [] },
})

const expanded = ref([])
const search = ref('')

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(value || 0))

const date = (value) => {
    if (!value) return 'Not set'

    return new Date(`${String(value).slice(0, 10)}T00:00:00`)
        .toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        })
}

const activeBudgets = computed(() =>
    props.budgets.filter((budget) => budget.is_active)
)

const plannedTotal = computed(() =>
    activeBudgets.value.reduce(
        (sum, budget) => sum + Number(budget.amount || 0),
        0
    )
)

const plannedSpent = computed(() =>
    activeBudgets.value.reduce(
        (sum, budget) => sum + Number(budget.spent || 0),
        0
    )
)

const unplannedTotal = computed(() =>
    props.unplannedCategories.reduce(
        (sum, category) => sum + Number(category.spent || 0),
        0
    )
)

const filteredBudgets = computed(() => {
    const query = search.value.trim().toLowerCase()

    return props.budgets.filter((budget) =>
        `${budget.name || ''} ${budget.category || ''}`
            .toLowerCase()
            .includes(query)
    )
})

function toggle(id) {
    expanded.value = expanded.value.includes(id)
        ? expanded.value.filter((item) => item !== id)
        : [...expanded.value, id]
}

function isExpanded(id) {
    return expanded.value.includes(id)
}

function progress(budget) {
    const amount = Number(budget.amount || 0)
    const spent = Number(budget.spent || 0)

    return amount > 0
        ? Math.round((spent / amount) * 100)
        : 0
}
</script>

<template>
    <Head title="Budgets" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                    FINANCE / BUDGETS
                </p>

                <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                    Spending Plans
                </h1>

                <p class="mt-2 max-w-xl text-sm leading-relaxed text-[#777D73]">
                    Set limits for your spending and track them automatically using your existing transactions.
                </p>
            </div>

            <Link
                href="/finance/budgets/create"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30]"
            >
                <Plus :size="17" />
                Create spending plan
            </Link>
        </header>

        <!-- Summary -->
        <section class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Planned -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-medium text-[#85877D]">
                        Planned this period
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42614A]">
                        <Wallet :size="18" />
                    </div>
                </div>

                <p class="mt-3 text-2xl font-semibold tracking-tight text-[#294B3C]">
                    {{ money(plannedTotal) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    {{ activeBudgets.length }} active spending plan(s)
                </p>
            </article>

            <!-- Spent -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-medium text-[#85877D]">
                        Spent against plans
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F5E4DD] text-[#A85F4C]">
                        <TrendingUp :size="18" />
                    </div>
                </div>

                <p class="mt-3 text-2xl font-semibold tracking-tight text-[#294B3C]">
                    {{ money(plannedSpent) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Actual expenses within active plan dates
                </p>
            </article>

            <!-- Unplanned -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-medium text-[#85877D]">
                        Outside spending plans
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#EBE5F3] text-[#796894]">
                        <CircleAlert :size="18" />
                    </div>
                </div>

                <p class="mt-3 text-2xl font-semibold tracking-tight text-[#294B3C]">
                    {{ money(unplannedTotal) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Expenses this month without an active category plan
                </p>
            </article>
        </section>

        <!-- Spending plans -->
        <section class="mb-8 space-y-5">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-[#294B3C]">
                    Your spending plans
                </h2>

                <p class="mt-1 text-sm leading-relaxed text-[#777D73]">
                    Transactions are counted automatically when their category and date match a plan.
                </p>
            </div>

            <!-- Search -->
            <div class="relative w-full sm:max-w-md">
                <Search
                    :size="17"
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#8B8C7D]"
                />

                <input
                    v-model="search"
                    type="search"
                    placeholder="Search plans or categories..."
                    class="form-input pl-11"
                />
            </div>

            <!-- Budget list -->
            <div v-if="filteredBudgets.length" class="space-y-4">
                <article
                    v-for="budget in filteredBudgets"
                    :key="budget.id"
                    class="overflow-hidden rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] shadow-[0_2px_10px_rgba(41,59,50,0.025)]"
                >
                    <div class="p-5 sm:p-6">
                        <!-- Plan heading -->
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="break-words text-lg font-semibold text-[#294B3C]">
                                        {{ budget.name }}
                                    </h3>

                                    <span
                                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        :class="budget.is_active
                                            ? 'bg-[#E7EBDD] text-[#42614A]'
                                            : 'bg-[#F3ECD9] text-[#8E7846]'"
                                    >
                                        {{ budget.is_active ? 'Active' : 'Outside current dates' }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-[#777D73]">
                                    {{ budget.category || 'All expense categories' }}
                                </p>

                                <p class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-[#85877D]">
                                    <CalendarDays :size="14" />
                                    {{ date(budget.start_date) }} – {{ date(budget.end_date) }}
                                </p>
                            </div>

                            <div class="sm:shrink-0 sm:text-right">
                                <p class="text-xs text-[#85877D]">
                                    Spent / limit
                                </p>

                                <p class="mt-1 text-lg font-semibold text-[#294B3C]">
                                    {{ money(budget.spent) }}
                                    <span class="font-normal text-[#85877D]">
                                        / {{ money(budget.amount) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="mt-6">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                <span
                                    class="font-semibold"
                                    :class="progress(budget) > 100
                                        ? 'text-[#B97962]'
                                        : 'text-[#42614A]'"
                                >
                                    {{ progress(budget) }}% used
                                </span>

                                <span
                                    :class="Number(budget.remaining || 0) < 0
                                        ? 'font-semibold text-[#B4534B]'
                                        : 'text-[#777D73]'"
                                >
                                    {{ Number(budget.remaining || 0) < 0 ? 'Over limit by ' : 'Remaining ' }}
                                    {{ money(Math.abs(Number(budget.remaining || 0))) }}
                                </span>
                            </div>

                            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-[#EEEEE6]">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="progress(budget) > 100
                                        ? 'bg-[#B97962]'
                                        : 'bg-[#82977A]'"
                                    :style="{ width: `${Math.min(progress(budget), 100)}%` }"
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-5 flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="toggle(budget.id)"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#EAE6DD] px-4 py-2.5 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                            >
                                <Receipt :size="16" />
                                Purchases ({{ (budget.transactions || []).length }})

                                <ChevronUp v-if="isExpanded(budget.id)" :size="15" />
                                <ChevronDown v-else :size="15" />
                            </button>

                            <Link
                                :href="`/finance/budgets/${budget.id}/edit`"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#EAE6DD] px-4 py-2.5 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                            >
                                Edit plan
                                <ArrowUpRight :size="15" />
                            </Link>
                        </div>
                    </div>

                    <!-- Expanded purchases -->
                    <div
                        v-if="isExpanded(budget.id)"
                        class="border-t border-[#EAE6DD] bg-[#FAF9F5]"
                    >
                        <div
                            v-if="(budget.transactions || []).length"
                            class="divide-y divide-[#EAE6DD]"
                        >
                            <div
                                v-for="transaction in budget.transactions || []"
                                :key="transaction.id"
                                class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-[#294B3C]">
                                        {{ transaction.description || 'Expense' }}
                                    </p>

                                    <p class="mt-1 text-xs text-[#85877D]">
                                        {{ transaction.category }} ·
                                        {{ date(transaction.transaction_date) }}
                                    </p>
                                </div>

                                <p class="shrink-0 text-sm font-semibold text-[#B97962]">
                                    −{{ money(transaction.amount) }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="px-5 py-7 text-center sm:px-6">
                            <Receipt :size="22" class="mx-auto text-[#A2A197]" />

                            <p class="mt-2 text-sm font-medium text-[#294B3C]">
                                No matching purchases
                            </p>

                            <p class="mt-1 text-xs text-[#85877D]">
                                No matching transactions in this plan's date range yet.
                            </p>
                        </div>
                    </div>
                </article>
            </div>

            <!-- No matching plans -->
            <div
                v-else
                class="rounded-2xl border border-dashed border-[#D9D8CE] bg-[#FFFEFA] px-6 py-12 text-center"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[#42614A]">
                    <Wallet :size="23" />
                </div>

                <h3 class="mt-4 font-serif text-xl font-semibold text-[#294B3C]">
                    {{ search ? 'No matching spending plans' : 'No spending plans yet' }}
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-[#777D73]">
                    {{ search
                        ? 'Try another plan name or category.'
                        : 'Your expenses are still recorded in Transactions. Create a plan when you want to set a spending limit and monitor your progress.' }}
                </p>

                <Link
                    v-if="!search"
                    href="/finance/budgets/create"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30]"
                >
                    <Plus :size="17" />
                    Create spending plan
                </Link>
            </div>
        </section>

        <!-- Unplanned expenses -->
        <section class="mb-7 space-y-4">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-[#294B3C]">
                    Outside your plans
                </h2>

                <p class="mt-1 text-sm leading-relaxed text-[#777D73]">
                    These categories have expenses this month but no active category-specific spending plan.
                </p>
            </div>

            <div
                v-if="unplannedCategories.length"
                class="overflow-hidden rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] shadow-[0_2px_10px_rgba(41,59,50,0.025)]"
            >
                <div
                    v-for="category in unplannedCategories"
                    :key="category.category"
                    class="flex flex-col justify-between gap-3 border-b border-[#EAE6DD] p-5 last:border-b-0 sm:flex-row sm:items-center sm:px-6"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F5E4DD] text-[#A85F4C]">
                            <CircleAlert :size="18" />
                        </div>

                        <div class="min-w-0">
                            <h3 class="break-words text-sm font-semibold text-[#294B3C]">
                                {{ category.category }}
                            </h3>

                            <p class="mt-1 text-xs text-[#85877D]">
                                {{ (category.transactions || []).length }}
                                purchase(s) this month
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <p class="text-sm font-semibold text-[#294B3C]">
                            {{ money(category.spent) }}
                        </p>

                        <Link
                            :href="`/finance/budgets/create?category=${encodeURIComponent(category.category)}`"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] px-3 py-2 text-sm font-semibold text-[#42614A] transition hover:bg-[#DCE5D6]"
                        >
                            <Plus :size="15" />
                            Plan
                        </Link>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="flex items-start gap-3 rounded-2xl border border-[#C8D2C1] bg-[#E7EBDD] p-5"
            >
                <CheckCircle2 :size="19" class="mt-0.5 shrink-0 text-[#42614A]" />

                <div>
                    <p class="text-sm font-semibold text-[#294B3C]">
                        All expenses are accounted for
                    </p>

                    <p class="mt-1 text-xs leading-relaxed text-[#777D73]">
                        No unplanned expense categories this month. Nice work keeping your spending organized.
                    </p>
                </div>
            </div>
        </section>

        <!-- Explanation -->
        <footer class="flex items-start gap-2 border-t border-[#EAE6DD] pt-4 text-xs leading-relaxed text-[#85877D]">
            <Target :size="15" class="mt-0.5 shrink-0" />

            <p>
                Expenses are recorded in Transactions. Spending plans only set limits and summarize matching expenses; they do not create duplicate transactions.
            </p>
        </footer>
    </div>
</template>

<style scoped>
.form-input {
    width: 100%;
    min-width: 0;
    border: 1px solid #E3E0D7;
    border-radius: 12px;
    background-color: #FFFEFA;
    padding: 12px 15px;
    color: #293B32;
    font-size: 13px;
    line-height: 1.5;
    outline: none;
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        background-color 180ms ease;
}

/* Keep enough space for the search icon */
.form-input.pl-11 {
    padding-left: 44px;
}

.form-input::placeholder {
    color: #A2A197;
}

.form-input:focus {
    border-color: #8B9D80;
    background-color: #FFF;
    box-shadow: 0 0 0 3px rgba(139, 157, 128, 0.13);
}

@media (max-width: 640px) {
    .form-input {
        font-size: 16px;
    }
}
</style>
