
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const form = useForm({
    title: '',
    category: '',
    amount: '',
    due_date: '',
    is_recurring: false,
    recurrence_frequency: '',
    notes: '',
})

const categories = [
    'Education',
    'Utilities',
    'Housing',
    'Internet',
    'Subscriptions',
    'Transportation',
    'Healthcare',
    'Insurance',
    'Other',
]

function submit() {
    form.post('/finance/bills')
}
</script>

<template>
    <Head title="Add Bill" />

    <div class="mx-auto max-w-3xl space-y-7">
        <header>
            <Link
                href="/finance/bills"
                class="text-sm font-medium text-[#54705D] hover:underline"
            >
                ← Back to bills
            </Link>

            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                FINANCE / BILLS
            </p>

            <h1 class="mt-2 text-3xl font-bold text-[#294B3C]">
                Add bill
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Record a payment obligation and keep track of its due date.
            </p>
        </header>

        <form
            class="space-y-6 rounded-2xl border border-[#E8E5DD] bg-white p-6 sm:p-8"
            @submit.prevent="submit"
        >
            <div>
                <label for="title" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Bill name
                </label>
                <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    maxlength="255"
                    required
                    placeholder="e.g. Electricity bill"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">
                    {{ form.errors.title }}
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="category" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        Category
                    </label>
                    <select
                        id="category"
                        v-model="form.category"
                        class="w-full rounded-xl border border-[#DEDCD4] bg-white px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    >
                        <option value="">Select a category</option>
                        <option
                            v-for="category in categories"
                            :key="category"
                            :value="category"
                        >
                            {{ category }}
                        </option>
                    </select>
                    <p v-if="form.errors.category" class="mt-2 text-sm text-red-600">
                        {{ form.errors.category }}
                    </p>
                </div>

                <div>
                    <label for="amount" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        Amount (₱)
                    </label>
                    <input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        min="0.01"
                        max="9999999999.99"
                        step="0.01"
                        required
                        placeholder="0.00"
                        class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    />
                    <p v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                        {{ form.errors.amount }}
                    </p>
                </div>
            </div>

            <div>
                <label for="due_date" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Due date
                </label>
                <input
                    id="due_date"
                    v-model="form.due_date"
                    type="date"
                    required
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.due_date" class="mt-2 text-sm text-red-600">
                    {{ form.errors.due_date }}
                </p>
            </div>

            <div class="rounded-xl border border-[#E8E5DD] bg-[#F7F5F0] p-4">
                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        v-model="form.is_recurring"
                        type="checkbox"
                        class="mt-1 h-4 w-4 accent-[#294B3C]"
                    />
                    <span>
                        <span class="block text-sm font-semibold text-[#294B3C]">
                            Recurring bill
                        </span>
                        <span class="mt-1 block text-sm leading-5 text-gray-500">
                            Enable this for payments that repeat regularly.
                        </span>
                    </span>
                </label>

                <div v-if="form.is_recurring" class="mt-4">
                    <label for="recurrence_frequency" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        Recurrence frequency
                    </label>
                    <select
                        id="recurrence_frequency"
                        v-model="form.recurrence_frequency"
                        required
                        class="w-full rounded-xl border border-[#DEDCD4] bg-white px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    >
                        <option value="">Select frequency</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Every 3 months</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <p v-if="form.errors.recurrence_frequency" class="mt-2 text-sm text-red-600">
                        {{ form.errors.recurrence_frequency }}
                    </p>
                </div>
            </div>

            <div>
                <label for="notes" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Notes <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="5000"
                    placeholder="Add payment instructions or reminders..."
                    class="w-full resize-y rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.notes" class="mt-2 text-sm text-red-600">
                    {{ form.errors.notes }}
                </p>
            </div>

            <div class="rounded-xl bg-[#F5F5EF] p-4 text-sm leading-6 text-[#54705D]">
                Saving a bill records the obligation. An expense transaction is created
                when you mark the bill as paid.
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#EEECE5] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/bills"
                    class="rounded-xl border border-[#DEDCD4] px-5 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-xl bg-[#294B3C] px-6 py-3 text-sm font-semibold text-white hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save bill' }}
                </button>
            </div>
        </form>
    </div>
</template>
