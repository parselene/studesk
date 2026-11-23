
<script setup>
import { computed } from 'vue'
import { router, Link, usePage, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import {
    Plus,
    Receipt,
    Clock,
    CircleCheck,
    AlertCircle,
    Pencil,
    Trash2,
    Check,
} from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const props = defineProps({
    bills: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

// Use the local calendar date to avoid timezone-related date shifts.
const now = new Date()
const today = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, '0'),
    String(now.getDate()).padStart(2, '0'),
].join('-')

const isOverdue = bill =>
    bill.status !== 'paid' && String(bill.due_date).slice(0, 10) < today

const pendingBills = computed(() =>
    props.bills.filter(bill =>
        bill.status !== 'paid' && !isOverdue(bill)
    )
)

const overdueBills = computed(() =>
    props.bills.filter(bill => isOverdue(bill))
)

const paidBills = computed(() =>
    props.bills.filter(bill => bill.status === 'paid')
)

const totalPending = computed(() =>
    pendingBills.value.reduce((sum, bill) => sum + Number(bill.amount || 0), 0)
)

const totalOverdue = computed(() =>
    overdueBills.value.reduce((sum, bill) => sum + Number(bill.amount || 0), 0)
)

const totalPaid = computed(() =>
    paidBills.value.reduce((sum, bill) => sum + Number(bill.amount || 0), 0)
)

const money = amount =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(amount || 0))

const formatDate = date => {
    if (!date) return '—'

    const cleanDate = String(date).slice(0, 10)
    const [year, month, day] = cleanDate.split('-')

    return new Date(Number(year), Number(month) - 1, Number(day))
        .toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        })
}

const displayStatus = bill => {
    if (bill.status === 'paid') return 'Paid'
    if (isOverdue(bill)) return 'Overdue'
    return 'Pending'
}

const markPaid = bill => {
    if (bill.status === 'paid') return

    if (
        confirm(
            `Mark "${bill.title}" as paid? This will record an expense transaction.`
        )
    ) {
        router.patch(`/finance/bills/${bill.id}/pay`)
    }
}

const deleteBill = bill => {
    if (confirm(`Delete "${bill.title}"? This cannot be undone.`)) {
        router.delete(`/finance/bills/${bill.id}`)
    }
}
</script>

<template>
    <Head title="Bills & Payments" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                    FINANCE / BILLS & PAYMENTS
                </p>

                <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                    Bills & Payments
                </h1>

                <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                    Keep track of upcoming payments and stay on top of due dates.
                </p>
            </div>

            <Link
                href="/finance/bills/create"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30]"
            >
                <Plus :size="17" :strokeWidth="2" class="shrink-0" />
                Add a bill
            </Link>
        </header>

        <!-- Success message -->
        <div
            v-if="page.props.flash?.success"
            class="mb-6 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] px-4 py-3 text-sm text-[#294B3C]"
        >
            {{ page.props.flash.success }}
        </div>

        <!-- Summary cards -->
        <div class="mb-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <!-- Upcoming payments -->
            <div class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-[#777D73]">
                        Upcoming payments
                    </span>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F3ECD9] text-[#8E7846]">
                        <Clock :size="18" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="text-2xl font-semibold tracking-tight text-[#294B3C]">
                    {{ money(totalPending) }}
                </p>

                <p class="mt-2 text-xs text-[#85877D]">
                    {{ pendingBills.length }} unpaid bill(s) not yet overdue
                </p>
            </div>

            <!-- Overdue payments -->
            <div class="rounded-2xl border border-[#EAD9D1] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-[#A85F4C]">
                        Overdue payments
                    </span>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F5E4DD] text-[#A85F4C]">
                        <AlertCircle :size="18" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="text-2xl font-semibold tracking-tight text-[#A85F4C]">
                    {{ money(totalOverdue) }}
                </p>

                <p class="mt-2 text-xs text-[#A87868]">
                    {{ overdueBills.length }} overdue bill(s)
                </p>
            </div>

            <!-- Paid bills -->
            <div class="rounded-2xl border border-[#C8D2C1] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:col-span-2 xl:col-span-1">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-[#657A61]">
                        Paid bills
                    </span>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42614A]">
                        <CircleCheck :size="18" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="text-2xl font-semibold tracking-tight text-[#294B3C]">
                    {{ money(totalPaid) }}
                </p>

                <p class="mt-2 text-xs text-[#85877D]">
                    {{ paidBills.length }} bill(s) marked as paid
                </p>
            </div>
        </div>

        <!-- Bills list -->
        <section class="overflow-hidden rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
            <!-- Section header -->
            <div class="flex flex-col justify-between gap-3 border-b border-[#EAE6DD] px-5 py-5 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <h2 class="font-serif text-xl font-semibold text-[#294B3C]">
                        All bills
                    </h2>

                    <p class="mt-1 text-sm text-[#777D73]">
                        Your payment obligations in one place
                    </p>
                </div>

                <span class="w-fit rounded-full bg-[#E7EBDD] px-3 py-1.5 text-xs font-semibold text-[#42614A]">
                    {{ props.bills.length }} total
                </span>
            </div>

            <!-- Bill items -->
            <div v-if="props.bills.length" class="divide-y divide-[#EAE6DD]">
                <div
                    v-for="bill in props.bills"
                    :key="bill.id"
                    class="flex flex-col gap-4 px-5 py-5 transition hover:bg-[#F7F5F0]/60 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <!-- Bill details -->
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42614A]">
                            <Receipt :size="19" :strokeWidth="1.8" />
                        </div>

                        <div class="min-w-0">
                            <h3 class="break-words text-sm font-semibold text-[#294B3C]">
                                {{ bill.title }}
                            </h3>

                            <p class="mt-1 text-xs text-[#85877D]">
                                {{ bill.category || 'Uncategorized' }}

                                <span v-if="bill.is_recurring">
                                    · {{ bill.recurrence_frequency }} recurring
                                </span>
                            </p>

                            <p class="mt-2 text-xs text-[#777D73]">
                                Due {{ formatDate(bill.due_date) }}

                                <span v-if="bill.status === 'paid' && bill.paid_at">
                                    · Paid {{ formatDate(bill.paid_at) }}
                                </span>
                            </p>

                            <p
                                v-if="bill.notes"
                                class="mt-2 max-w-md whitespace-pre-line text-xs leading-relaxed text-[#85877D]"
                            >
                                {{ bill.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Amount, status, and actions -->
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <div class="mr-auto min-w-[110px] text-left sm:mr-3 sm:text-right">
                            <p class="text-sm font-semibold text-[#294B3C]">
                                {{ money(bill.amount) }}
                            </p>

                            <span
                                class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-[#F5E4DD] text-[#A85F4C]': isOverdue(bill),
                                    'bg-[#F3ECD9] text-[#8E7846]': bill.status !== 'paid' && !isOverdue(bill),
                                    'bg-[#E7EBDD] text-[#42614A]': bill.status === 'paid',
                                }"
                            >
                                {{ displayStatus(bill) }}
                            </span>
                        </div>

                        <button
                            v-if="bill.status !== 'paid'"
                            type="button"
                            title="Mark as paid"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#294B3C] px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-[#203D30]"
                            @click="markPaid(bill)"
                        >
                            <Check :size="15" :strokeWidth="2" class="shrink-0" />
                            Mark paid
                        </button>

                        <Link
                            v-if="bill.status !== 'paid'"
                            :href="`/finance/bills/${bill.id}/edit`"
                            title="Edit bill"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#EAE6DD] text-[#52664F] transition hover:border-[#C8D2C1] hover:bg-[#E7EBDD]"
                        >
                            <Pencil :size="15" :strokeWidth="1.8" />
                        </Link>

                        <button
                            type="button"
                            title="Delete bill"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#EAD9D1] text-[#A85F4C] transition hover:bg-[#F5E4DD]"
                            @click="deleteBill(bill)"
                        >
                            <Trash2 :size="15" :strokeWidth="1.8" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[#42614A]">
                    <Receipt :size="25" :strokeWidth="1.7" />
                </div>

                <h3 class="font-serif text-xl font-semibold text-[#294B3C]">
                    No bills yet
                </h3>

                <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-[#777D73]">
                    Add your tuition, subscriptions, utilities, or other payments to keep them organized.
                </p>

                <Link
                    href="/finance/bills/create"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#203D30]"
                >
                    <Plus :size="16" :strokeWidth="2" />
                    Add your first bill
                </Link>
            </div>
        </section>
    </div>
</template>
