
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import {
    Utensils,
    Car,
    BookOpen,
    ShoppingBag,
    House,
    Heart,
    MoreHorizontal,
    Wallet,
    BriefcaseBusiness,
    GraduationCap,
    Gift,
    Tag,
    Plus,
    ArrowUpRight,
    ArrowDownRight,
    WalletCards,
    CalendarDays,
    Pencil,
    Trash2,
    ReceiptText,
} from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const props = defineProps({
    transactions: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({ income: 0, expenses: 0, balance: 0 }),
    },
})

const categoryIcons = {
    Food: Utensils,
    Transportation: Car,
    'School Expenses': BookOpen,
    Supplies: ShoppingBag,
    Bills: House,
    Personal: Heart,
    Other: MoreHorizontal,
    Allowance: Wallet,
    Salary: BriefcaseBusiness,
    'Part-time Work': BriefcaseBusiness,
    Scholarship: GraduationCap,
    'Other Income': Gift,
}

function getCategoryIcon(category) {
    return categoryIcons[category] || Tag
}

const money = (amount) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
    }).format(Number(amount) || 0)

const dateLabel = (date) => {
    if (!date) return '—'

    const parsed = new Date(`${String(date).slice(0, 10)}T00:00:00`)

    return Number.isNaN(parsed.getTime())
        ? '—'
        : parsed.toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        })
}

function deleteTransaction(transaction) {
    if (!confirm('Are you sure you want to delete this transaction?')) return

    router.delete(`/finance/transactions/${transaction.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Transactions" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                    FINANCE / TRANSACTIONS
                </p>

                <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                    Transactions
                </h1>

                <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                    Keep track of the money coming in and going out.
                </p>
            </div>

            <Link
                href="/finance/transactions/create"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#203D30]"
            >
                <Plus :size="17" :strokeWidth="2" />
                Add transaction
            </Link>
        </header>

        <!-- Summary Cards -->
        <section class="mb-7 grid gap-4 sm:grid-cols-3">
            <!-- Income -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-[#777D73]">
                        Total income
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#54705D]">
                        <ArrowDownRight :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight text-[#54705D] sm:text-2xl">
                    {{ money(summary.income) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Money received
                </p>
            </article>

            <!-- Expenses -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-[#777D73]">
                        Total expenses
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5E4DD] text-[#A85F4C]">
                        <ArrowUpRight :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight text-[#A85F4C] sm:text-2xl">
                    {{ money(summary.expenses) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Money spent
                </p>
            </article>

            <!-- Balance -->
            <article class="rounded-2xl border border-[#294B3C] bg-[#294B3C] p-5 text-white shadow-[0_3px_12px_rgba(41,59,50,0.08)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-white/75">
                        Recorded balance
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white">
                        <WalletCards :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight sm:text-2xl">
                    {{ money(summary.balance) }}
                </p>

                <p class="mt-1 text-xs text-white/60">
                    Income minus expenses
                </p>
            </article>
        </section>

        <!-- Transaction History -->
        <section class="overflow-hidden rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
            <!-- Section Header -->
            <div class="flex flex-col gap-2 border-b border-[#EAE6DD] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#54705D]">
                            <ReceiptText :size="18" :strokeWidth="1.8" />
                        </div>

                        <h2 class="font-serif text-xl font-semibold text-[#294B3C]">
                            Transaction history
                        </h2>
                    </div>

                    <p class="mt-2 text-xs text-[#85877D]">
                        {{ transactions.length }} recorded transaction(s)
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div v-if="transactions.length" class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#F7F5F0]">
                        <tr class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#85877D]">
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="transaction in transactions"
                            :key="transaction.id"
                            class="border-t border-[#EEECE5] transition hover:bg-[#FAF9F5]"
                        >
                            <!-- Description -->
                            <td class="max-w-[220px] px-6 py-4">
                                <p class="truncate font-semibold text-[#294B3C]">
                                    {{ transaction.description || 'Untitled transaction' }}
                                </p>

                                <p
                                    v-if="transaction.payment_method"
                                    class="mt-1 text-xs text-[#92948A]"
                                >
                                    {{ transaction.payment_method }}
                                </p>
                            </td>

                            <!-- Category -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                                        :class="transaction.type === 'income'
                                            ? 'bg-[#E7EBDD] text-[#54705D]'
                                            : 'bg-[#F5E4DD] text-[#A85F4C]'"
                                    >
                                        <component
                                            :is="getCategoryIcon(transaction.category)"
                                            :size="17"
                                            :strokeWidth="1.8"
                                        />
                                    </div>

                                    <span class="text-[#697469]">
                                        {{ transaction.category || 'Uncategorized' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-[#777D73]">
                                    <CalendarDays
                                        :size="14"
                                        :strokeWidth="1.8"
                                        class="text-[#A1A194]"
                                    />
                                    {{ dateLabel(transaction.transaction_date) }}
                                </span>
                            </td>

                            <!-- Type -->
                            <td class="px-6 py-4">
                                <span
                                    :class="transaction.type === 'income'
                                        ? 'border-[#C8D2C1] bg-[#E7EBDD] text-[#54705D]'
                                        : 'border-[#E8C9BC] bg-[#F5E4DD] text-[#A85F4C]'"
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold capitalize"
                                >
                                    {{ transaction.type }}
                                </span>
                            </td>

                            <!-- Amount -->
                            <td
                                :class="transaction.type === 'income'
                                    ? 'text-[#54705D]'
                                    : 'text-[#A85F4C]'"
                                class="whitespace-nowrap px-6 py-4 text-right font-semibold"
                            >
                                {{ transaction.type === 'income' ? '+' : '−' }}{{ money(transaction.amount) }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <Link
                                        :href="`/finance/transactions/${transaction.id}/edit`"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#54705D] transition hover:text-[#294B3C]"
                                    >
                                        <Pencil :size="14" />
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#B97962] transition hover:text-[#9D5543]"
                                        @click="deleteTransaction(transaction)"
                                    >
                                        <Trash2 :size="14" />
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[#54705D]">
                    <ReceiptText :size="25" :strokeWidth="1.6" />
                </div>

                <h3 class="mt-4 font-serif text-xl font-semibold text-[#294B3C]">
                    No transactions yet
                </h3>

                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-[#85877D]">
                    Start by recording your allowance, income from work, or daily expenses.
                    Your transactions will appear here.
                </p>

                <Link
                    href="/finance/transactions/create"
                    class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30]"
                >
                    <Plus :size="16" />
                    Add your first transaction
                </Link>
            </div>
        </section>
    </div>
</template>
