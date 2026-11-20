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
    pendingBills.value.reduce((sum, bill) => sum + Number(bill.amount), 0)
)

const totalOverdue = computed(() =>
    overdueBills.value.reduce((sum, bill) => sum + Number(bill.amount), 0)
)

const totalPaid = computed(() =>
    paidBills.value.reduce((sum, bill) => sum + Number(bill.amount), 0)
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
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                    FINANCE / BILLS & PAYMENTS
                </p>
                    <h1 class="text-3xl font-semibold tracking-tight">
                        Bills & Payments
                    </h1>
                    <p class="mt-2 text-sm text-[#788477]">
                        Keep track of upcoming payments and stay on top of due dates.
                    </p>
                </div>

                <Link
                    href="/finance/bills/create"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#354C3B] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#26392C]"
                >
                    <Plus :size="18" />
                    Add a bill
                </Link>
            </div>

            <!-- Success message -->
            <div
                v-if="page.props.flash?.success"
                class="mb-6 rounded-xl border border-[#C9DDC9] bg-[#EAF3E8] px-4 py-3 text-sm text-[#354C3B]"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- Summary cards -->
            <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-2xl border border-[#E7E7DD] bg-white p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm text-[#788477]">Upcoming payments</span>
                        <Clock :size="19" class="text-[#8D7445]" />
                    </div>
                    <p class="text-2xl font-semibold">
                        {{ money(totalPending) }}
                    </p>
                    <p class="mt-2 text-sm text-[#788477]">
                        {{ pendingBills.length }} unpaid bill(s) not yet overdue
                    </p>
                </div>

                <div class="rounded-2xl border border-[#E7D5CE] bg-[#FBF2EE] p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm text-[#986A5A]">Overdue payments</span>
                        <AlertCircle :size="19" class="text-[#A45F49]" />
                    </div>
                    <p class="text-2xl font-semibold text-[#8C4937]">
                        {{ money(totalOverdue) }}
                    </p>
                    <p class="mt-2 text-sm text-[#986A5A]">
                        {{ overdueBills.length }} overdue bill(s)
                    </p>
                </div>

                <div class="rounded-2xl border border-[#D9E5D5] bg-[#EDF4E9] p-5 sm:col-span-2 xl:col-span-1">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm text-[#657A61]">Paid bills</span>
                        <CircleCheck :size="19" class="text-[#4D7650]" />
                    </div>
                    <p class="text-2xl font-semibold text-[#354C3B]">
                        {{ money(totalPaid) }}
                    </p>
                    <p class="mt-2 text-sm text-[#657A61]">
                        {{ paidBills.length }} bill(s) marked as paid
                    </p>
                </div>
            </div>

            <!-- Bills list -->
            <div class="overflow-hidden rounded-2xl border border-[#E7E7DD] bg-white">
                <div class="flex items-center justify-between border-b border-[#EEEEEA] px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="font-semibold">All bills</h2>
                        <p class="mt-1 text-sm text-[#788477]">
                            Your payment obligations in one place
                        </p>
                    </div>

                    <span class="rounded-full bg-[#F1F1E9] px-3 py-1 text-xs font-medium">
                        {{ bills.length }} total
                    </span>
                </div>

                <div v-if="bills.length" class="divide-y divide-[#EEEEEA]">
                    <div
                        v-for="bill in bills"
                        :key="bill.id"
                        class="flex flex-col gap-4 px-5 py-5 transition hover:bg-[#FCFCF9] sm:flex-row sm:items-center sm:justify-between sm:px-6"
                    >
                        <div class="flex min-w-0 items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#F1F1E9]">
                                <Receipt :size="20" class="text-[#52664F]" />
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-medium">{{ bill.title }}</h3>

                                <p class="mt-1 text-sm text-[#788477]">
                                    {{ bill.category || 'Uncategorized' }}
                                    <span v-if="bill.is_recurring">
                                        · {{ bill.recurrence_frequency }} recurring
                                    </span>
                                </p>

                                <p class="mt-2 text-xs text-[#788477]">
                                    Due {{ formatDate(bill.due_date) }}

                                    <span v-if="bill.status === 'paid' && bill.paid_at">
                                        · Paid {{ formatDate(bill.paid_at) }}
                                    </span>
                                </p>

                                <p
                                    v-if="bill.notes"
                                    class="mt-2 max-w-md whitespace-pre-line text-sm text-[#788477]"
                                >
                                    {{ bill.notes }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                            <div class="mr-auto text-left sm:mr-3 sm:text-right">
                                <p class="font-semibold">
                                    {{ money(bill.amount) }}
                                </p>

                                <span
                                    class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-[#F8E8E2] text-[#9B503B]': isOverdue(bill),
                                        'bg-[#F5EFDC] text-[#856D31]': bill.status !== 'paid' && !isOverdue(bill),
                                        'bg-[#E5F0E2] text-[#426A43]': bill.status === 'paid',
                                    }"
                                >
                                    {{ displayStatus(bill) }}
                                </span>
                            </div>

                            <button
                                v-if="bill.status !== 'paid'"
                                type="button"
                                title="Mark as paid"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-[#354C3B] px-3 py-2 text-xs font-medium text-white hover:bg-[#26392C]"
                                @click="markPaid(bill)"
                            >
                                <Check :size="15" />
                                Mark paid
                            </button>

                            <Link
                                v-if="bill.status !== 'paid'"
                                :href="`/finance/bills/${bill.id}/edit`"
                                title="Edit bill"
                                class="rounded-lg border border-[#E3E4DB] p-2 text-[#52664F] hover:bg-[#F4F4ED]"
                            >
                                <Pencil :size="16" />
                            </Link>

                            <button
                                type="button"
                                title="Delete bill"
                                class="rounded-lg border border-[#E3D7D2] p-2 text-[#A45F49] hover:bg-[#FBF2EE]"
                                @click="deleteBill(bill)"
                            >
                                <Trash2 :size="16" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="px-6 py-16 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F1F1E9]">
                        <Receipt :size="25" class="text-[#52664F]" />
                    </div>

                    <h3 class="font-semibold">No bills yet</h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm text-[#788477]">
                        Add your tuition, subscriptions, utilities, or other payments to keep them organized.
                    </p>

                    <Link
                        href="/finance/bills/create"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#354C3B] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#26392C]"
                    >
                        <Plus :size="17" />
                        Add your first bill
                    </Link>
                </div>
            </div>

    </div>
</template>
