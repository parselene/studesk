
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import {
    Utensils,
    Car,
    BookOpen,
    ShoppingBag,
    House,
    Heart,
    PiggyBank,
    MoreHorizontal,
    Wallet,
    BriefcaseBusiness,
    GraduationCap,
    Gift,
    Tag,
    ArrowLeft,
    Minus,
    Plus,
    CalendarDays,
    Save,
    ReceiptText,
} from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
    budgets: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    type: props.transaction.type || 'expense',
    budget_id: props.transaction.budget_id
        ? String(props.transaction.budget_id)
        : '',
    category: props.transaction.category || '',
    description: props.transaction.description || '',
    amount: props.transaction.amount ?? '',
    transaction_date: props.transaction.transaction_date
        ? String(props.transaction.transaction_date).slice(0, 10)
        : new Date().toLocaleDateString('en-CA'),
    payment_method: props.transaction.payment_method || '',
    notes: props.transaction.notes || '',
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

const categoryIcons = {
    Food: Utensils,
    Transportation: Car,
    'School Expenses': BookOpen,
    Supplies: ShoppingBag,
    Bills: House,
    Personal: Heart,
    Savings: PiggyBank,
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
        const budget = props.budgets.find(
            budget => String(budget.id) === String(form.budget_id)
        )

        if (
            budget &&
            (
                budget.start_date > form.transaction_date ||
                budget.end_date < form.transaction_date
            )
        ) {
            form.budget_id = ''
            form.category = ''
        }
    }
)

watch(
    () => form.type,
    (newType, oldType) => {
        if (newType !== oldType) {
            form.category = ''
            form.budget_id = ''
        }
    }
)

function submit() {
    form.put(`/finance/transactions/${props.transaction.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Edit Transaction" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7">
            <Link
                href="/finance/transactions"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-[#54705D] transition hover:text-[#294B3C]"
            >
                <ArrowLeft :size="16" />
                Back to transactions
            </Link>

            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                FINANCE / TRANSACTIONS
            </p>

            <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-4xl">
                Edit transaction
            </h1>

            <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                Update the details of your recorded income or expense.
            </p>
        </header>

        <!-- Form -->
        <form
            class="space-y-7 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_3px_14px_rgba(41,59,50,0.035)] sm:p-7"
            @submit.prevent="submit"
        >
            <!-- Transaction type -->
            <section>
                <label class="form-label">
                    Transaction type
                </label>


<div class="grid grid-cols-2 gap-3">
    <button
        type="button"
        @click="form.type = 'expense'"
        :class="form.type === 'expense'
            ? 'border-[#B97962] bg-[#F5E4DD] text-[#A85F4C] shadow-sm'
            : 'border-[#EAE6DD] bg-white text-[#777D73] hover:border-[#D8CFC2] hover:bg-[#F7F5F0]'"
        class="flex items-center justify-center gap-2.5 rounded-xl border px-4 py-4 text-sm font-semibold transition duration-200"
    >
        <Minus :size="17" :strokeWidth="2" />
        Expense
    </button>

    <button
        type="button"
        @click="form.type = 'income'"
        :class="form.type === 'income'
            ? 'border-[#8B9D80] bg-[#E7EBDD] text-[#294B3C] shadow-sm'
            : 'border-[#EAE6DD] bg-white text-[#777D73] hover:border-[#D8CFC2] hover:bg-[#F7F5F0]'"
        class="flex items-center justify-center gap-2.5 rounded-xl border px-4 py-4 text-sm font-semibold transition duration-200"
    >
        <Plus :size="17" :strokeWidth="2" />
        Income
    </button>
</div>


                <p v-if="form.errors.type" class="form-error">
                    {{ form.errors.type }}
                </p>
            </section>

            <div class="form-divider"></div>

            <!-- Description -->
            <section>
                <label for="description" class="form-label">
                    Description
                    <span class="font-normal text-[#A3A397]">(optional)</span>
                </label>

                <input
                    id="description"
                    v-model="form.description"
                    type="text"
                    maxlength="255"
                    placeholder="e.g. Weekly allowance or lunch"
                    class="form-input"
                />

                <p v-if="form.errors.description" class="form-error">
                    {{ form.errors.description }}
                </p>
            </section>

            <!-- Amount and date -->
            <section class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="amount" class="form-label">
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
                        class="form-input"
                    />

                    <p v-if="form.errors.amount" class="form-error">
                        {{ form.errors.amount }}
                    </p>
                </div>

                <div>
                    <label
                        for="transaction_date"
                        class="form-label"
                    >
                        <span class="inline-flex items-center gap-2">
                            <CalendarDays :size="15" class="text-[#8B8C7D]" />
                            Transaction date
                        </span>
                    </label>

                    <input
                        id="transaction_date"
                        v-model="form.transaction_date"
                        type="date"
                        required
                        class="form-input"
                    />

                    <p v-if="form.errors.transaction_date" class="form-error">
                        {{ form.errors.transaction_date }}
                    </p>
                </div>
            </section>

            <!-- Budget and category -->
            <section class="grid gap-5 sm:grid-cols-2">
                <!-- Budget -->
                <div v-if="form.type === 'expense'">
                    <label for="budget_id" class="form-label">
                        Assign to budget
                        <span class="font-normal text-[#A3A397]">(optional)</span>
                    </label>

                    <select
                        id="budget_id"
                        v-model="form.budget_id"
                        @change="form.category = ''"
                        class="form-input"
                    >
                        <option value="">
                            No budget / Unplanned expense
                        </option>

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
                        class="mt-2 text-xs leading-relaxed text-[#A17B43]"
                    >
                        You don't have an active budget covering this transaction date.
                        You can still record this as an unplanned expense.
                    </p>

                    <p v-if="form.errors.budget_id" class="form-error">
                        {{ form.errors.budget_id }}
                    </p>
                </div>

                <!-- Category selection -->
                <div v-if="form.type === 'income' || !form.budget_id">
                    <label for="category" class="form-label">
                        Category
                    </label>

                    <select
                        id="category"
                        v-model="form.category"
                        required
                        class="form-input"
                    >
                        <option value="" disabled>
                            Select a category
                        </option>

                        <option
                            v-for="category in categories[form.type]"
                            :key="category"
                            :value="category"
                        >
                            {{ category }}
                        </option>
                    </select>

                    <!-- Selected category preview -->
                    <div
                        v-if="form.category"
                        class="mt-3 flex items-center gap-3 rounded-xl border border-[#EAE6DD] bg-[#F7F5F0] p-3"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="form.type === 'income'
                                ? 'bg-[#E7EBDD] text-[#54705D]'
                                : 'bg-[#F5E4DD] text-[#A85F4C]'"
                        >
                            <component
                                :is="getCategoryIcon(form.category)"
                                :size="19"
                                :strokeWidth="1.8"
                            />
                        </div>

                        <div>
                            <p class="text-xs text-[#85877D]">
                                Selected category
                            </p>

                            <p class="text-sm font-semibold text-[#294B3C]">
                                {{ form.category }}
                            </p>
                        </div>
                    </div>

                    <p v-if="form.errors.category" class="form-error">
                        {{ form.errors.category }}
                    </p>
                </div>

                <!-- Automatically assigned category -->
                <div
                    v-else
                    class="rounded-xl border border-[#EAE6DD] bg-[#F7F5F0] p-4"
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#8B8C7D]">
                        Assigned category
                    </p>

                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F5E4DD] text-[#A85F4C]">
                            <component
                                :is="getCategoryIcon(selectedBudget?.category)"
                                :size="19"
                                :strokeWidth="1.8"
                            />
                        </div>

                        <p class="text-sm font-semibold text-[#294B3C]">
                            {{ selectedBudget?.category || 'No category assigned' }}
                        </p>
                    </div>

                    <p class="mt-3 text-xs leading-relaxed text-[#85877D]">
                        This expense will automatically use the selected budget's category.
                    </p>
                </div>

                <!-- Payment method -->
                <div>
                    <label for="payment_method" class="form-label">
                        Payment method
                    </label>

                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="form-input"
                    >
                        <option value="">
                            Select method (optional)
                        </option>
                        <option value="Cash">Cash</option>
                        <option value="GCash">GCash</option>
                        <option value="Maya">Maya</option>
                        <option value="Bank Transfer">Bank transfer</option>
                        <option value="Debit/Credit Card">Debit/credit card</option>
                        <option value="Other">Other</option>
                    </select>

                    <p v-if="form.errors.payment_method" class="form-error">
                        {{ form.errors.payment_method }}
                    </p>
                </div>
            </section>

            <!-- Notes -->
            <section>
                <label for="notes" class="form-label">
                    Notes
                    <span class="font-normal text-[#A3A397]">(optional)</span>
                </label>

                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="2000"
                    placeholder="Add any extra details..."
                    class="form-input min-h-[100px] resize-y"
                ></textarea>

                <p v-if="form.errors.notes" class="form-error">
                    {{ form.errors.notes }}
                </p>
            </section>

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 border-t border-[#EAE6DD] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/transactions"
                    class="inline-flex items-center justify-center rounded-xl border border-[#E2DED4] bg-white px-5 py-3 text-sm font-semibold text-[#777D73] transition hover:border-[#C8D2C1] hover:bg-[#F7F5F0] hover:text-[#294B3C]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Save :size="16" />
                    {{ form.processing ? 'Updating...' : 'Update transaction' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.form-label {
    display: block;
    margin-bottom: 9px;
    color: #294B3C;
    font-size: 13px;
    font-weight: 600;
}

.form-input {
    width: 100%;
    min-width: 0;
    border: 1px solid #DEDCD4;
    border-radius: 11px;
    background: #FFFEFA;
    padding: 12px 14px;
    color: #293B32;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input::placeholder {
    color: #AAA99E;
}

.form-input:focus {
    border-color: #82977A;
    box-shadow: 0 0 0 3px rgba(130, 151, 122, 0.13);
}

.form-input:disabled {
    cursor: not-allowed;
    background: #F7F5F0;
    opacity: 0.7;
}

.form-error {
    margin-top: 7px;
    color: #B45345;
    font-size: 12px;
    line-height: 1.5;
}

.form-divider {
    height: 1px;
    background: #EAE6DD;
}

@media (max-width: 640px) {
    .form-input {
        font-size: 16px;
    }
}
</style>
