
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    prefillCategory: { type: String, default: '' },
})

const today = () => {
    const now = new Date()
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}

const lastDayOfMonth = () => {
    const now = new Date()
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()).padStart(2, '0')}`
}

const form = useForm({
    name: '',
    category: props.prefillCategory,
    amount: '',
    start_date: today(),
    end_date: lastDayOfMonth(),
})

const categories = [
    'Food',
    'Transportation',
    'Education',
    'Bills',
    'Shopping',
    'Health',
    'Entertainment',
    'Other',
]

function submit() {
    form.post('/finance/budgets')
}
</script>

<template>
    <Head title="Create Spending Plan" />

    <div class="mx-auto max-w-3xl space-y-7">
        <header>
            <Link
                href="/finance/budgets"
                class="text-sm font-medium text-[#54705D] hover:underline"
            >
                ← Back to budgets
            </Link>

            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                FINANCE / BUDGETS
            </p>

            <h1 class="mt-2 text-3xl font-bold text-[#294B3C]">
                Create spending plan
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Set a spending limit and track it automatically using your transactions.
            </p>
        </header>

        <form
            class="space-y-6 rounded-2xl border border-[#E8E5DD] bg-white p-6 sm:p-8"
            @submit.prevent="submit"
        >
            <div>
                <label for="name" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Plan name
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    maxlength="255"
                    required
                    placeholder="e.g. Monthly Food Budget"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <label for="category" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Spending category
                </label>
                <select
                    id="category"
                    v-model="form.category"
                    required
                    class="w-full rounded-xl border border-[#DEDCD4] bg-white px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                >
                    <option value="" disabled>Select a category</option>
                    <option
                        v-for="category in categories"
                        :key="category"
                        :value="category"
                    >
                        {{ category }}
                    </option>
                    <option
                        v-if="form.category && !categories.includes(form.category)"
                        :value="form.category"
                    >
                        {{ form.category }}
                    </option>
                </select>
                <p v-if="form.errors.category" class="mt-2 text-sm text-red-600">
                    {{ form.errors.category }}
                </p>
            </div>

            <div>
                <label for="amount" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                    Spending limit (₱)
                </label>
                <input
                    id="amount"
                    v-model="form.amount"
                    type="number"
                    min="0.01"
                    max="9999999999.99"
                    step="0.01"
                    required
                    placeholder="3000.00"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                    {{ form.errors.amount }}
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="start_date" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        Start date
                    </label>
                    <input
                        id="start_date"
                        v-model="form.start_date"
                        type="date"
                        required
                        class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    />
                    <p v-if="form.errors.start_date" class="mt-2 text-sm text-red-600">
                        {{ form.errors.start_date }}
                    </p>
                </div>

                <div>
                    <label for="end_date" class="mb-2 block text-sm font-semibold text-[#294B3C]">
                        End date
                    </label>
                    <input
                        id="end_date"
                        v-model="form.end_date"
                        type="date"
                        :min="form.start_date"
                        required
                        class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                    />
                    <p v-if="form.errors.end_date" class="mt-2 text-sm text-red-600">
                        {{ form.errors.end_date }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl bg-[#F5F5EF] p-4 text-sm leading-6 text-[#54705D]">
                Your existing expenses will be matched to this category and date range.
                Creating a spending plan does not create any transactions.
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#EEECE5] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/budgets"
                    class="rounded-xl border border-[#DEDCD4] px-5 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-xl bg-[#294B3C] px-6 py-3 text-sm font-semibold text-white hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save spending plan' }}
                </button>
            </div>
        </form>
    </div>
</template>
