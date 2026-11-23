
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    budgets: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    type: 'expense',
    budget_id: '',
    category: '',
    description: '',
    amount: '',
    transaction_date: new Date().toLocaleDateString('en-CA'),
    payment_method: '',
    notes: '',
})

const categories = {
    income: [
        'Allowance',
        'Salary',
        'Part-time Work',
        'Scholarship',
        'Other Income',
    ],
    expense: [
        'Food',
        'Transportation',
        'School Expenses',
        'Supplies',
        'Bills',
        'Personal',
        'Savings',
        'Other',
    ],
}

const availableBudgets = computed(() => {
    return props.budgets.filter(budget =>
        budget.start_date <= form.transaction_date &&
        budget.end_date >= form.transaction_date
    )
})

const selectedBudget = computed(() => {
    return props.budgets.find(
        budget => String(budget.id) === String(form.budget_id)
    )
})

watch(
    () => form.transaction_date,
    () => {
        const selectedBudget = props.budgets.find(
            budget => String(budget.id) === String(form.budget_id)
        )

        if (
            selectedBudget &&
            (
                selectedBudget.start_date > form.transaction_date ||
                selectedBudget.end_date < form.transaction_date
            )
        ) {
            form.budget_id = ''
        }
    }
)

function submit() {
    form.post('/finance/transactions')
}
</script>

<template>
    <Head title="Add Transaction" />

    <div class="mx-auto max-w-3xl space-y-7">
        <header>
            <Link
                href="/finance/transactions"
                class="text-sm font-medium text-[#54705D] hover:underline"
            >
                ← Back to transactions
            </Link>

            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                FINANCE / TRANSACTIONS
            </p>
            <h1 class="mt-2 text-3xl font-bold text-[#294B3C]">
                Add transaction
            </h1>
            <p class="mt-2 text-sm text-gray-500">
                Record money received or an expense you've made.
            </p>
        </header>

        <form
            class="space-y-6 rounded-2xl border border-[#E8E5DD] bg-white p-6 sm:p-8"
            @submit.prevent="submit"
        >
            <!-- Transaction type -->
            <div>
                <label class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Transaction type
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="form.type = 'expense'; form.category = ''; form.budget_id = ''"a
                        :class="form.type === 'expense'
                            ? 'border-[#294B3C] bg-[#294B3C] text-white'
                            : 'border-[#E8E5DD] bg-white text-gray-600 hover:bg-[#F7F5F0]'"
                        class="rounded-xl border px-4 py-3 text-sm font-semibold transition"
                    >
                        − Expense
                    </button>

                    <button
                        type="button"
                        @click="form.type = 'income'; form.category = ''; form.budget_id = ''"
                        :class="form.type === 'income'
                            ? 'border-[#54705D] bg-[#E7EBDD] text-[#294B3C]'
                            : 'border-[#E8E5DD] bg-white text-gray-600 hover:bg-[#F7F5F0]'"
                        class="rounded-xl border px-4 py-3 text-sm font-semibold transition"
                    >
                        + Income
                    </button>
                </div>

                <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">
                    {{ form.errors.type }}
                </p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Description <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <input
                    id="description"
                    v-model="form.description"
                    type="text"
                    maxlength="255"
                    placeholder="e.g. Weekly allowance or lunch"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                    {{ form.errors.description }}
                </p>
            </div>

            <!-- Amount and date -->
            <div class="grid gap-5 sm:grid-cols-2">
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

                <div>
                    <label for="transaction_date" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        Transaction date
                    </label>
                    <input
                        id="transaction_date"
                        v-model="form.transaction_date"
                        type="date"
                        required
                        class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    />
                    <p v-if="form.errors.transaction_date" class="mt-2 text-sm text-red-600">
                        {{ form.errors.transaction_date }}
                    </p>
                </div>
            </div>

            <!-- Category and payment method -->
            <div class="grid gap-5 sm:grid-cols-2">
                <!-- Budget selection -->
                <div v-if="form.type === 'expense'">
                    <label
                        for="budget_id"
                        class="mb-2 block text-sm font-semibold text-[#294B3C]"
                    >
                        Assign to budget
                        <span class="font-normal text-gray-400">(optional)</span>
                    </label>

                    <select
                        id="budget_id"
                        v-model="form.budget_id"
                        @change="form.category = ''"
                        class="w-full rounded-xl border border-[#DEDCD4] bg-white px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    >
                        <option value="">No budget / Unplanned expense</option>

                        <option
                            v-for="budget in availableBudgets"
                            :key="budget.id"
                            :value="String(budget.id)"
                        >
                            {{ budget.name }} — {{ budget.category }}
                        </option>
                    </select>

                    <p
                        v-if="availableBudgets.length === 0"
                        class="mt-2 text-xs text-amber-700"
                    >
                        You don't have an active budget covering this transaction date.
                        You can still record this as an unplanned expense.
                    </p>

                    <p v-if="form.errors.budget_id" class="mt-2 text-sm text-red-600">
                        {{ form.errors.budget_id }}
                    </p>
                </div>

                <!-- Category -->
                <div v-if="form.type === 'income' || !form.budget_id">
                    <label
                        for="category"
                        class="mb-2 block text-sm font-semibold text-[#294B3C]"
                    >
                        Category
                    </label>

                    <select
                        id="category"
                        v-model="form.category"
                        required
                        class="w-full rounded-xl border border-[#DEDCD4] bg-white px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    >
                        <option value="" disabled>Select a category</option>

                        <option
                            v-for="category in categories[form.type]"
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

                <!-- Automatically assigned category -->
                <div
                    v-else
                    class="rounded-xl border border-[#E8E5DD] bg-[#F7F5F0] p-4"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#758477]">
                        Assigned category
                    </p>

                    <p class="mt-1 font-semibold text-[#294B3C]">
                        {{ selectedBudget?.category || 'No category assigned' }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        This expense will automatically use the selected budget's category.
                    </p>
                </div>

                <div>
                    <label for="payment_method" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        Payment method
                    </label>
                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="w-full rounded-xl border border-[#DEDCD4] bg-white px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    >
                        <option value="">Select method (optional)</option>
                        <option value="Cash">Cash</option>
                        <option value="GCash">GCash</option>
                        <option value="Maya">Maya</option>
                        <option value="Bank Transfer">Bank transfer</option>
                        <option value="Debit/Credit Card">Debit/credit card</option>
                        <option value="Other">Other</option>
                    </select>
                    <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">
                        {{ form.errors.payment_method }}
                    </p>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Notes <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="2000"
                    placeholder="Add any extra details..."
                    class="w-full resize-y rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                ></textarea>
                <p v-if="form.errors.notes" class="mt-2 text-sm text-red-600">
                    {{ form.errors.notes }}
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#EEECE5] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/transactions"
                    class="rounded-xl border border-[#DEDCD4] px-5 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-xl bg-[#294B3C] px-6 py-3 text-sm font-semibold text-white hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save transaction' }}
                </button>
            </div>
        </form>
    </div>
</template>
