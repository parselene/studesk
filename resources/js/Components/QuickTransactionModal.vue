
<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
    Plus,
    X,
    Wallet,
    ArrowDownLeft,
    ArrowUpRight,
    Receipt,
    PiggyBank,
    Utensils,
    Car,
    BookOpen,
    ShoppingBag,
    House,
    Heart,
    MoreHorizontal,
    BriefcaseBusiness,
    GraduationCap,
    Gift,
    CalendarDays,
    LoaderCircle,
} from 'lucide-vue-next'

const isOpen = ref(false)
const isLoading = ref(false)
const isSubmitting = ref(false)
const loadError = ref('')

const activeType = ref('expense')

const budgets = ref([])
const bills = ref([])
const savingsGoals = ref([])

const selectedBillId = ref('')
const selectedGoalId = ref('')

const form = useForm({
    type: 'expense',
    category: '',
    description: '',
    amount: '',
    transaction_date: new Date().toLocaleDateString('en-CA'),
    budget_id: '',
    payment_method: '',
    notes: '',
})

const expenseCategories = [
    { name: 'Food', icon: Utensils },
    { name: 'Transportation', icon: Car },
    { name: 'School Expenses', icon: BookOpen },
    { name: 'Supplies', icon: ShoppingBag },
    { name: 'Bills', icon: House },
    { name: 'Personal', icon: Heart },
    { name: 'Other', icon: MoreHorizontal },
]

const incomeCategories = [
    { name: 'Allowance', icon: Wallet },
    { name: 'Salary', icon: BriefcaseBusiness },
    { name: 'Part-time Work', icon: BriefcaseBusiness },
    { name: 'Scholarship', icon: GraduationCap },
    { name: 'Other Income', icon: Gift },
]

const categories = computed(() => {
    return activeType.value === 'income'
        ? incomeCategories
        : expenseCategories
})

const availableBudgets = computed(() => {
    return budgets.value.filter((budget) => {
        return (
            budget.start_date <= form.transaction_date &&
            budget.end_date >= form.transaction_date
        )
    })
})

const selectedBudget = computed(() => {
    return budgets.value.find(
        budget => String(budget.id) === String(form.budget_id)
    )
})

const selectedBill = computed(() => {
    return bills.value.find(
        bill => String(bill.id) === String(selectedBillId.value)
    )
})

const selectedGoal = computed(() => {
    return savingsGoals.value.find(
        goal => String(goal.id) === String(selectedGoalId.value)
    )
})

const formattedAmount = computed(() => {
    const amount = Number(form.amount || 0)

    return amount.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
})

function formatCurrency(amount) {
    return Number(amount || 0).toLocaleString('en-PH', {
        style: 'currency',
        currency: 'PHP',
    })
}

async function loadOptions() {
    isLoading.value = true
    loadError.value = ''

    try {
        const response = await fetch('/finance/quick-add-options', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            throw new Error('Unable to load your finance information.')
        }

        const data = await response.json()

        budgets.value = data.budgets ?? []
        bills.value = data.bills ?? []
        savingsGoals.value = data.savingsGoals ?? []
    } catch (error) {
        loadError.value = error.message || 'Something went wrong.'
    } finally {
        isLoading.value = false
    }
}

function openModal() {
    isOpen.value = true

    form.reset()
    form.clearErrors()

    activeType.value = 'expense'
    form.type = 'expense'
    form.transaction_date = new Date().toLocaleDateString('en-CA')

    selectedBillId.value = ''
    selectedGoalId.value = ''

    loadOptions()
}

function closeModal() {
    if (isSubmitting.value || form.processing) return

    isOpen.value = false
}

function changeType(type) {
    activeType.value = type

    form.type = type === 'income' ? 'income' : 'expense'
    form.category = ''
    form.budget_id = ''
    form.amount = ''
    form.description = ''
    form.notes = ''

    selectedBillId.value = ''
    selectedGoalId.value = ''

    form.clearErrors()
}

function selectCategory(category) {
    form.category = category
}

watch(
    () => form.transaction_date,
    () => {
        const budget = budgets.value.find(
            item => String(item.id) === String(form.budget_id)
        )

        if (
            budget &&
            (
                budget.start_date > form.transaction_date ||
                budget.end_date < form.transaction_date
            )
        ) {
            form.budget_id = ''
        }
    }
)

function submitTransaction() {
    form.post('/finance/transactions', {
        preserveScroll: true,

        onSuccess: () => {
            isOpen.value = false
            form.reset()
        },
    })
}

function submitBillPayment() {
    if (!selectedBill.value) return

    isSubmitting.value = true

    router.patch(
        `/finance/bills/${selectedBill.value.id}/pay`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                isOpen.value = false
                selectedBillId.value = ''
            },

            onFinish: () => {
                isSubmitting.value = false
            },
        }
    )
}

function submitSavingsContribution() {
    if (!selectedGoal.value) return

    form.transform((data) => ({
        amount: data.amount,
        contribution_date: data.transaction_date,
        notes: data.notes,
    })).post(
        `/finance/savings-goals/${selectedGoal.value.id}/contribute`,
        {
            preserveScroll: true,

            onSuccess: () => {
                isOpen.value = false
                selectedGoalId.value = ''
                form.reset()
            },

            onFinish: () => {
                form.transform(data => data)
            },
        }
    )
}

function submit() {
    if (activeType.value === 'bill') {
        submitBillPayment()
        return
    }

    if (activeType.value === 'savings') {
        submitSavingsContribution()
        return
    }

    submitTransaction()
}
</script>

<template>
    <!-- Floating Quick Add Button -->
    <button
        type="button"
        @click="openModal"
        aria-label="Quick add transaction"
        class="fixed bottom-6 right-6 z-40 flex h-14 w-14
               items-center justify-center rounded-full
               bg-[#294B3C] text-white shadow-lg
               transition hover:bg-[#1D392D] hover:shadow-xl
               active:scale-95"
    >
        <Plus class="h-7 w-7" />
    </button>

    <!-- Modal -->
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-[100] flex items-end
                   justify-center bg-[#18251E]/50 p-0
                   backdrop-blur-[2px] sm:items-center sm:p-5"
            @click.self="closeModal"
        >
            <div
                class="relative max-h-[92vh] w-full max-w-lg
                       overflow-y-auto rounded-t-3xl bg-[#F7F5F0]
                       shadow-2xl sm:rounded-3xl"
            >
                <!-- Header -->
                <div
                    class="sticky top-0 z-10 flex items-center
                           justify-between border-b border-[#E8E5DD]
                           bg-[#F7F5F0] px-5 py-4 sm:px-6"
                >
                    <div>
                        <p class="text-xs font-semibold uppercase
                                  tracking-[0.16em] text-[#8A9589]">
                            STUDesk / QUICK ENTRY
                        </p>

                        <h2 class="mt-1 text-xl font-bold text-[#294B3C]">
                            Add something
                        </h2>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        class="flex h-9 w-9 items-center justify-center
                               rounded-xl text-[#68756B] transition
                               hover:bg-[#EAE8E0]"
                        aria-label="Close quick add"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <!-- Loading -->
                    <div
                        v-if="isLoading"
                        class="flex items-center justify-center gap-2
                               py-5 text-sm text-[#758477]"
                    >
                        <LoaderCircle class="h-5 w-5 animate-spin" />
                        Loading your finance information...
                    </div>

                    <div
                        v-else-if="loadError"
                        class="rounded-xl border border-red-200
                               bg-red-50 p-4 text-sm text-red-700"
                    >
                        {{ loadError }}

                        <button
                            type="button"
                            @click="loadOptions"
                            class="ml-2 font-semibold underline"
                        >
                            Try again
                        </button>
                    </div>

                    <template v-else>
                        <!-- Transaction Type -->
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="item in [
                                    { value: 'expense', label: 'Expense', icon: ArrowUpRight },
                                    { value: 'income', label: 'Income', icon: ArrowDownLeft },
                                    { value: 'bill', label: 'Bill', icon: Receipt },
                                    { value: 'savings', label: 'Savings', icon: PiggyBank },
                                ]"
                                :key="item.value"
                                type="button"
                                @click="changeType(item.value)"
                                class="flex flex-col items-center gap-2
                                       rounded-xl border px-2 py-3
                                       text-xs font-semibold transition"
                                :class="
                                    activeType === item.value
                                        ? 'border-[#294B3C] bg-[#E7EBDD] text-[#294B3C]'
                                        : 'border-[#E8E5DD] bg-white text-[#7B857B] hover:bg-[#F0EEE7]'
                                "
                            >
                                <component :is="item.icon" class="h-5 w-5" />
                                {{ item.label }}
                            </button>
                        </div>

                        <!-- Expense / Income -->
                        <template
                            v-if="activeType === 'expense' || activeType === 'income'"
                        >
                            <!-- Amount -->
                            <div
                                class="rounded-2xl border border-[#E8E5DD]
                                       bg-white px-5 py-5 text-center"
                            >
                                <label
                                    for="quick-amount"
                                    class="text-xs font-semibold uppercase
                                           tracking-[0.15em] text-[#899388]"
                                >
                                    Amount
                                </label>

                                <div class="mt-2 flex items-center justify-center">
                                    <span class="mr-2 text-2xl font-semibold
                                                 text-[#A1AA9E]">
                                        ₱
                                    </span>

                                    <input
                                        id="quick-amount"
                                        v-model="form.amount"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-52 border-0 bg-transparent
                                               p-0 text-center text-4xl
                                               font-bold text-[#294B3C]
                                               outline-none ring-0
                                               placeholder:text-[#D4D8D0]
                                               focus:ring-0"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.amount"
                                    class="mt-2 text-xs text-red-600"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </div>

                            <!-- Category -->
                            <div>
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-[#294B3C]">
                                        Choose a category
                                    </h3>

                                    <span class="text-xs text-[#92998F]">
                                        Required
                                    </span>
                                </div>

                                <div class="grid grid-cols-4 gap-2">
                                    <button
                                        v-for="item in categories"
                                        :key="item.name"
                                        type="button"
                                        @click="selectCategory(item.name)"
                                        class="flex min-h-[76px] flex-col
                                               items-center justify-center
                                               gap-2 rounded-xl border p-2
                                               text-center transition"
                                        :class="
                                            form.category === item.name
                                                ? 'border-[#294B3C] bg-[#E7EBDD] text-[#294B3C]'
                                                : 'border-[#E8E5DD] bg-white text-[#7D897D] hover:bg-[#F0EEE7]'
                                        "
                                    >
                                        <component :is="item.icon" class="h-5 w-5" />

                                        <span class="text-[10px] font-medium leading-tight">
                                            {{ item.name }}
                                        </span>
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.category"
                                    class="mt-2 text-xs text-red-600"
                                >
                                    {{ form.errors.category }}
                                </p>
                            </div>

                            <!-- Budget -->
                            <div v-if="activeType === 'expense'">
                                <label
                                    for="quick-budget"
                                    class="mb-2 block text-sm font-semibold
                                           text-[#294B3C]"
                                >
                                    Assign to a budget
                                    <span class="font-normal text-[#92998F]">
                                        (optional)
                                    </span>
                                </label>

                                <select
                                    id="quick-budget"
                                    v-model="form.budget_id"
                                    class="w-full rounded-xl border
                                           border-[#E8E5DD] bg-white
                                           px-4 py-3 text-sm text-[#394B40]
                                           focus:border-[#294B3C]
                                           focus:ring-[#294B3C]"
                                >
                                    <option value="">No budget assigned</option>

                                    <option
                                        v-for="budget in availableBudgets"
                                        :key="budget.id"
                                        :value="budget.id"
                                    >
                                        {{ budget.name }} — {{ budget.category }}
                                    </option>
                                </select>

                                <p
                                    v-if="selectedBudget"
                                    class="mt-2 text-xs text-[#758477]"
                                >
                                    Category will be set to
                                    <strong>{{ selectedBudget.category }}</strong>
                                    based on this budget.
                                </p>

                                <p
                                    v-if="form.errors.budget_id"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.budget_id }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="quick-description"
                                    class="mb-2 block text-sm font-semibold
                                           text-[#294B3C]"
                                >
                                    Description
                                    <span class="font-normal text-[#92998F]">
                                        (optional)
                                    </span>
                                </label>

                                <input
                                    id="quick-description"
                                    v-model="form.description"
                                    type="text"
                                    placeholder="What was this for?"
                                    class="w-full rounded-xl border
                                           border-[#E8E5DD] bg-white
                                           px-4 py-3 text-sm
                                           focus:border-[#294B3C]
                                           focus:ring-[#294B3C]"
                                />
                            </div>

                            <!-- Date -->
                            <div>
                                <label
                                    for="quick-date"
                                    class="mb-2 block text-sm font-semibold
                                           text-[#294B3C]"
                                >
                                    Date
                                </label>

                                <div class="relative">
                                    <CalendarDays
                                        class="pointer-events-none absolute
                                               left-4 top-1/2 h-4 w-4
                                               -translate-y-1/2 text-[#899388]"
                                    />

                                    <input
                                        id="quick-date"
                                        v-model="form.transaction_date"
                                        type="date"
                                        class="w-full rounded-xl border
                                               border-[#E8E5DD] bg-white
                                               py-3 pl-11 pr-4 text-sm
                                               focus:border-[#294B3C]
                                               focus:ring-[#294B3C]"
                                    />

                                    <p
                                        v-if="form.errors.contribution_date"
                                        class="mt-2 text-xs text-red-600"
                                    >
                                        {{ form.errors.contribution_date }}
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- Bill Payment -->
                        <template v-else-if="activeType === 'bill'">
                            <div class="rounded-2xl border border-[#E8E5DD]
                                        bg-white p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <Receipt class="h-5 w-5 text-[#54705D]" />

                                    <h3 class="text-sm font-semibold text-[#294B3C]">
                                        Select an unpaid bill
                                    </h3>
                                </div>

                                <select
                                    v-model="selectedBillId"
                                    class="w-full rounded-xl border
                                           border-[#E8E5DD] bg-white
                                           px-4 py-3 text-sm
                                           focus:border-[#294B3C]
                                           focus:ring-[#294B3C]"
                                >
                                    <option value="">Choose a bill</option>

                                    <option
                                        v-for="bill in bills"
                                        :key="bill.id"
                                        :value="bill.id"
                                    >
                                        {{ bill.title }} — {{ formatCurrency(bill.amount) }}
                                    </option>
                                </select>

                                <p
                                    v-if="bills.length === 0"
                                    class="mt-3 text-xs text-[#92998F]"
                                >
                                    You don't have any unpaid bills right now.
                                </p>
                            </div>

                            <div
                                v-if="selectedBill"
                                class="rounded-2xl border border-[#E8E5DD]
                                       bg-white p-5"
                            >
                                <p class="text-xs font-medium uppercase
                                          tracking-wide text-[#92998F]">
                                    Payment amount
                                </p>

                                <p class="mt-2 text-3xl font-bold text-[#294B3C]">
                                    {{ formatCurrency(selectedBill.amount) }}
                                </p>

                                <div class="mt-4 space-y-2 border-t
                                            border-[#EEECE5] pt-4 text-sm">
                                    <div class="flex justify-between gap-3">
                                        <span class="text-[#92998F]">Bill</span>
                                        <span class="font-medium text-[#394B40]">
                                            {{ selectedBill.title }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between gap-3">
                                        <span class="text-[#92998F]">Category</span>
                                        <span class="font-medium text-[#394B40]">
                                            {{ selectedBill.category || 'Bills' }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between gap-3">
                                        <span class="text-[#92998F]">Due date</span>
                                        <span class="font-medium text-[#394B40]">
                                            {{ selectedBill.due_date }}
                                        </span>
                                    </div>
                                </div>

                                <p class="mt-4 rounded-lg bg-[#F7F5F0]
                                          p-3 text-xs leading-relaxed
                                          text-[#758477]">
                                    Paying this bill will automatically record
                                    an expense transaction and generate the
                                    next bill if it is recurring.
                                </p>
                            </div>
                        </template>

                        <!-- Savings Contribution -->
                        <template v-else-if="activeType === 'savings'">
                            <div class="rounded-2xl border border-[#E8E5DD]
                                        bg-white p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <PiggyBank class="h-5 w-5 text-[#54705D]" />

                                    <h3 class="text-sm font-semibold text-[#294B3C]">
                                        Choose a savings goal
                                    </h3>
                                </div>

                                <select
                                    v-model="selectedGoalId"
                                    class="w-full rounded-xl border
                                           border-[#E8E5DD] bg-white
                                           px-4 py-3 text-sm
                                           focus:border-[#294B3C]
                                           focus:ring-[#294B3C]"
                                >
                                    <option value="">Select a goal</option>

                                    <option
                                        v-for="goal in savingsGoals"
                                        :key="goal.id"
                                        :value="goal.id"
                                    >
                                        {{ goal.name }}
                                    </option>
                                </select>

                                <p
                                    v-if="savingsGoals.length === 0"
                                    class="mt-3 text-xs text-[#92998F]"
                                >
                                    You don't have any active savings goals.
                                </p>
                            </div>

                            <div
                                v-if="selectedGoal"
                                class="rounded-2xl border border-[#E8E5DD]
                                       bg-white p-4"
                            >
                                <div class="flex justify-between gap-3">
                                    <span class="text-sm text-[#758477]">
                                        Current savings
                                    </span>

                                    <strong class="text-sm text-[#294B3C]">
                                        {{ formatCurrency(selectedGoal.current_amount) }}
                                    </strong>
                                </div>

                                <div class="mt-2 flex justify-between gap-3">
                                    <span class="text-sm text-[#758477]">
                                        Target
                                    </span>

                                    <strong class="text-sm text-[#294B3C]">
                                        {{ formatCurrency(selectedGoal.target_amount) }}
                                    </strong>
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-[#E8E5DD]
                                       bg-white px-5 py-5 text-center"
                            >
                                <label
                                    for="quick-savings-amount"
                                    class="text-xs font-semibold uppercase
                                           tracking-[0.15em] text-[#899388]"
                                >
                                    Contribution amount
                                </label>

                                <div class="mt-2 flex items-center justify-center">
                                    <span class="mr-2 text-2xl font-semibold
                                                 text-[#A1AA9E]">
                                        ₱
                                    </span>

                                    <input
                                        id="quick-savings-amount"
                                        v-model="form.amount"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-52 border-0 bg-transparent
                                               p-0 text-center text-4xl
                                               font-bold text-[#294B3C]
                                               outline-none ring-0
                                               placeholder:text-[#D4D8D0]
                                               focus:ring-0"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.amount"
                                    class="mt-2 text-xs text-red-600"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="quick-savings-date"
                                    class="mb-2 block text-sm font-semibold
                                           text-[#294B3C]"
                                >
                                    Contribution date
                                </label>

                                <input
                                    id="quick-savings-date"
                                    v-model="form.transaction_date"
                                    type="date"
                                    :max="new Date().toLocaleDateString('en-CA')"
                                    class="w-full rounded-xl border
                                           border-[#E8E5DD] bg-white
                                           px-4 py-3 text-sm
                                           focus:border-[#294B3C]
                                           focus:ring-[#294B3C]"
                                />
                            </div>
                        </template>

                        <!-- Notes for savings -->
                        <div v-if="activeType === 'savings'">
                            <label
                                for="quick-savings-notes"
                                class="mb-2 block text-sm font-semibold
                                       text-[#294B3C]"
                            >
                                Note
                                <span class="font-normal text-[#92998F]">
                                    (optional)
                                </span>
                            </label>

                            <input
                                id="quick-savings-notes"
                                v-model="form.notes"
                                type="text"
                                placeholder="Add a short note..."
                                class="w-full rounded-xl border
                                       border-[#E8E5DD] bg-white
                                       px-4 py-3 text-sm
                                       focus:border-[#294B3C]
                                       focus:ring-[#294B3C]"
                            />
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div
                    v-if="!isLoading && !loadError"
                    class="sticky bottom-0 flex gap-3 border-t
                           border-[#E8E5DD] bg-white px-5 py-4
                           sm:px-6"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="flex-1 rounded-xl border
                               border-[#E5E2D9] px-4 py-3
                               text-sm font-semibold text-[#68756B]
                               transition hover:bg-[#F7F5F0]"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="submit"
                        :disabled="
                            isLoading ||
                            isSubmitting ||
                            form.processing ||
                            (
                                activeType === 'bill' && !selectedBill
                            ) ||
                            (
                                activeType === 'savings' && !selectedGoal
                            )
                        "
                        class="flex-1 rounded-xl bg-[#294B3C]
                               px-4 py-3 text-sm font-semibold
                               text-white transition hover:bg-[#1D392D]
                               disabled:cursor-not-allowed
                               disabled:opacity-50"
                    >
                        <span v-if="isSubmitting || form.processing">
                            Saving...
                        </span>

                        <span v-else-if="activeType === 'bill'">
                            Mark as paid
                        </span>

                        <span v-else-if="activeType === 'savings'">
                            Add contribution
                        </span>

                        <span v-else>
                            Save {{ activeType }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
