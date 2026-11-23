
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
    ReceiptText,
    Save,
} from 'lucide-vue-next'
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

const displayedCategory = computed(() => {
    if (form.type === 'expense' && selectedBudget.value) {
        return selectedBudget.value.category
    }

    return form.category
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

function submit() {
    form.post('/finance/transactions')
}
</script>

<template>
    <Head title="Add Transaction" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7">
            <Link
                href="/finance/transactions"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-[#71816F] transition hover:text-[#294B3C]"
            >
                <ArrowLeft :size="16" :strokeWidth="1.8" />
                Back to transactions
            </Link>

            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                FINANCE / TRANSACTIONS
            </p>

            <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                Add transaction
            </h1>

            <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                Record money received or an expense you've made.
            </p>
        </header>

        <!-- Form Card -->
        <form
            class="space-y-7 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:p-7 lg:p-8"
            @submit.prevent="submit"
        >
            <!-- Transaction Type -->
            <section>
                <label class="mb-3 block text-sm font-semibold text-[#294B3C]">
                    Transaction type
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="form.type = 'expense'; form.category = ''; form.budget_id = ''"
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
                        @click="form.type = 'income'; form.category = ''; form.budget_id = ''"
                        :class="form.type === 'income'
                            ? 'border-[#8B9D80] bg-[#E7EBDD] text-[#294B3C] shadow-sm'
                            : 'border-[#EAE6DD] bg-white text-[#777D73] hover:border-[#D8CFC2] hover:bg-[#F7F5F0]'"
                        class="flex items-center justify-center gap-2.5 rounded-xl border px-4 py-4 text-sm font-semibold transition duration-200"
                    >
                        <Plus :size="17" :strokeWidth="2" />
                        Income
                    </button>
                </div>

                <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">
                    {{ form.errors.type }}
                </p>
            </section>

            <div class="border-t border-[#EEECE5]"></div>

            <!-- Description -->
            <section>
                <label for="description" class="form-label">
                    Description
                    <span class="font-normal text-[#99998F]">(optional)</span>
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

            <!-- Amount and Date -->
            <section class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="amount" class="form-label">
                        Amount (₱)
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-[#92948A]">
                            ₱
                        </span>

                        <input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            min="0.01"
                            max="9999999999.99"
                            step="0.01"
                            required
                            placeholder="0.00"
                            class="form-input pl-9"
                        />
                    </div>

                    <p v-if="form.errors.amount" class="form-error">
                        {{ form.errors.amount }}
                    </p>
                </div>

                <div>
                    <label for="transaction_date" class="form-label">
                        Transaction date
                    </label>

                    <div class="relative">
                        <CalendarDays
                            :size="17"
                            :strokeWidth="1.8"
                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#92948A]"
                        />

                        <input
                            id="transaction_date"
                            v-model="form.transaction_date"
                            type="date"
                            required
                            class="form-input pl-11"
                        />
                    </div>

                    <p v-if="form.errors.transaction_date" class="form-error">
                        {{ form.errors.transaction_date }}
                    </p>
                </div>
            </section>

            <!-- Budget and Category -->
            <section class="grid gap-5 sm:grid-cols-2">
                <!-- Budget Selection -->
                <div v-if="form.type === 'expense'">
                    <label for="budget_id" class="form-label">
                        Assign to budget
                        <span class="font-normal text-[#99998F]">(optional)</span>
                    </label>

                    <select
                        id="budget_id"
                        v-model="form.budget_id"
                        @change="form.category = ''"
                        class="form-input"
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
                        class="mt-2 text-xs leading-relaxed text-[#9B7A43]"
                    >
                        You don't have an active budget covering this transaction date.
                        You can still record this as an unplanned expense.
                    </p>

                    <p v-if="form.errors.budget_id" class="form-error">
                        {{ form.errors.budget_id }}
                    </p>
                </div>

                <!-- Category Selection -->
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
                        <option value="" disabled>Select a category</option>

                        <option
                            v-for="category in categories[form.type]"
                            :key="category"
                            :value="category"
                        >
                            {{ category }}
                        </option>
                    </select>

                    <!-- Category Preview -->
                    <div
                        v-if="form.category"
                        class="mt-3 flex items-center gap-3 rounded-xl border border-[#EAE6DD] bg-[#F7F5F0] p-3"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="form.type === 'income'
                                ? 'bg-[#E7EBDD] text-[#42614A]'
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

                <!-- Automatically Assigned Category -->
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

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="form-label">
                        Payment method
                        <span class="font-normal text-[#99998F]">(optional)</span>
                    </label>

                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="form-input"
                    >
                        <option value="">Select method</option>
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

            <div class="border-t border-[#EEECE5]"></div>

            <!-- Notes -->
            <section>
                <label for="notes" class="form-label">
                    Notes
                    <span class="font-normal text-[#99998F]">(optional)</span>
                </label>

                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="2000"
                    placeholder="Add any extra details..."
                    class="form-input min-h-[105px] resize-y"
                ></textarea>

                <p v-if="form.errors.notes" class="form-error">
                    {{ form.errors.notes }}
                </p>
            </section>

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 border-t border-[#EEECE5] pt-6 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/transactions"
                    class="inline-flex items-center justify-center rounded-xl border border-[#DEDCD4] bg-white px-6 py-3 text-sm font-semibold text-[#697469] transition hover:border-[#C8D2C1] hover:bg-[#F7F5F0] hover:text-[#294B3C]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Save :size="16" :strokeWidth="1.9" />
                    {{ form.processing ? 'Saving...' : 'Save transaction' }}
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
    border: 1px solid #E3E0D7;
    border-radius: 12px;
    background-color: #FFFEFA;
    padding: 12px 15px;
    color: #293B32;
    font-size: 13px;
    line-height: 1.5;
    outline: none;
    transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
}

.form-input::placeholder {
    color: #A2A197;
}

.form-input:focus {
    border-color: #8B9D80;
    background-color: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(139, 157, 128, 0.13);
}

.form-input:disabled {
    cursor: not-allowed;
    background-color: #F7F5F0;
    opacity: 0.7;
}

.form-error {
    margin-top: 7px;
    color: #B4534B;
    font-size: 12px;
    line-height: 1.5;
}

select.form-input {
    min-height: 45px;
}

textarea.form-input {
    min-height: 105px;
}

@media (max-width: 640px) {
    .form-input {
        font-size: 16px;
    }
}
</style>
