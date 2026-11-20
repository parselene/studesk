
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    transactions: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({ income: 0, expenses: 0, balance: 0 }),
    },
})

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

    router.delete(`/finance/transactions/${transaction.id}`)
}
</script>

<template>
    <Head title="Transactions" />

    <div class="mx-auto max-w-7xl space-y-7">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                    FINANCE / TRANSACTIONS
                </p>
                <h1 class="mt-2 text-3xl font-bold text-[#294B3C]">
                    Transactions
                </h1>
                <p class="mt-2 text-sm text-gray-500">
                    Keep track of the money coming in and going out.
                </p>
            </div>

            <Link
                href="/finance/transactions/create"
                class="inline-flex items-center justify-center rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white hover:bg-[#203C30]"
            >
                + Add transaction
            </Link>
        </header>

        <!-- Summary -->
        <section class="grid gap-4 sm:grid-cols-3">
            <article class="rounded-2xl border border-[#E8E5DD] bg-white p-5">
                <p class="text-sm text-gray-500">Total income</p>
                <p class="mt-3 text-2xl font-bold text-[#54705D]">
                    {{ money(summary.income) }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#E8E5DD] bg-white p-5">
                <p class="text-sm text-gray-500">Total expenses</p>
                <p class="mt-3 text-2xl font-bold text-[#B76C54]">
                    {{ money(summary.expenses) }}
                </p>
            </article>

            <article class="rounded-2xl bg-[#294B3C] p-5 text-white">
                <p class="text-sm text-white/75">Recorded balance</p>
                <p class="mt-3 text-2xl font-bold">
                    {{ money(summary.balance) }}
                </p>
            </article>
        </section>

        <!-- Transaction history -->
        <section class="overflow-hidden rounded-2xl border border-[#E8E5DD] bg-white">
            <div class="flex items-center justify-between border-b border-[#EEECE5] px-6 py-5">
                <div>
                    <h2 class="font-bold text-[#294B3C]">Transaction history</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ transactions.length }} recorded transaction(s)
                    </p>
                </div>
            </div>

            <div v-if="transactions.length" class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-[#F7F5F0] text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Description</th>
                            <th class="px-6 py-4 font-medium">Category</th>
                            <th class="px-6 py-4 font-medium">Date</th>
                            <th class="px-6 py-4 font-medium">Type</th>
                            <th class="px-6 py-4 text-right font-medium">Amount</th>
                            <th class="px-6 py-4 text-right font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="transaction in transactions"
                            :key="transaction.id"
                            class="border-t border-[#EEECE5] hover:bg-[#FBFAF7]"
                        >
                            <td class="px-6 py-4 font-medium text-[#294B3C]">
                                {{ transaction.description || 'Untitled transaction' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ transaction.category || 'Uncategorized' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ dateLabel(transaction.transaction_date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="transaction.type === 'income'
                                        ? 'bg-[#E7EBDD] text-[#54705D]'
                                        : 'bg-[#F5E4DD] text-[#9D624D]'"
                                    class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                >
                                    {{ transaction.type }}
                                </span>
                            </td>
                            <td
                                :class="transaction.type === 'income'
                                    ? 'text-[#54705D]'
                                    : 'text-[#294B3C]'"
                                class="px-6 py-4 text-right font-semibold whitespace-nowrap"
                            >
                                {{ transaction.type === 'income' ? '+' : '−' }}{{ money(transaction.amount) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <Link
                                        :href="`/finance/transactions/${transaction.id}/edit`"
                                        class="font-medium text-[#54705D] hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="font-medium text-[#B76C54] hover:underline"
                                        @click="deleteTransaction(transaction)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="px-6 py-14 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E7EBDD] text-2xl text-[#294B3C]">
                    ₱
                </div>
                <h3 class="mt-4 font-semibold text-[#294B3C]">
                    No transactions yet
                </h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">
                    Start by recording your allowance, income from work, or daily expenses.
                </p>
                <Link
                    href="/finance/transactions/create"
                    class="mt-5 inline-flex rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white hover:bg-[#203C30]"
                >
                    Add your first transaction
                </Link>
            </div>
        </section>
    </div>
</template>
