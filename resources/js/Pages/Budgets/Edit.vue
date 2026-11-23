
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    ArrowLeft,
    Wallet,
    Save,
    Tag,
} from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const props = defineProps({
    budget: { type: Object, required: true },
})

const form = useForm({
    name: props.budget.name ?? '',
    category: props.budget.category ?? '',
    amount: props.budget.amount ?? '',
    start_date: String(props.budget.start_date ?? '').slice(0, 10),
    end_date: String(props.budget.end_date ?? '').slice(0, 10),
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
    form.put(`/finance/budgets/${props.budget.id}`)
}
</script>

<template>
    <Head title="Edit Spending Plan" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7">
            <Link
                href="/finance/budgets"
                class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-[#777D73] transition hover:text-[#294B3C]"
            >
                <ArrowLeft
                    :size="16"
                    :strokeWidth="2"
                    class="shrink-0"
                />
                Back to budgets
            </Link>

            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                FINANCE / BUDGETS
            </p>

            <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                Edit spending plan
            </h1>

            <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                Update your spending limit, category, or budget period.
            </p>
        </header>

        <!-- Form -->
        <form
            class="space-y-7 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:p-7 lg:p-8"
            @submit.prevent="submit"
        >
            <!-- Plan name -->
            <div>
                <label for="name" class="form-label">
                    Plan name <span class="text-[#B97962]">*</span>
                </label>

                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    maxlength="255"
                    required
                    placeholder="e.g. Monthly food budget"
                    class="form-input"
                />

                <p v-if="form.errors.name" class="form-error">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="form-label">
                    Spending category <span class="text-[#B97962]">*</span>
                </label>

                <div class="relative">
                    <Tag
                        :size="17"
                        :strokeWidth="1.8"
                        class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-[#8B8C7D]"
                    />

                    <select
                        id="category"
                        v-model="form.category"
                        required
                        class="form-input select-input pl-11"
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
                </div>

                <p v-if="form.errors.category" class="form-error">
                    {{ form.errors.category }}
                </p>
            </div>

            <!-- Spending limit -->
            <div>
                <label for="amount" class="form-label">
                    Spending limit (₱) <span class="text-[#B97962]">*</span>
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-sm font-semibold text-[#8B8C7D]"
                    >
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
                        placeholder="5000.00"
                        class="form-input pl-10"
                    />
                </div>

                <p class="mt-2 text-xs leading-5 text-[#85877D]">
                    Set the maximum amount you plan to spend in this category during the selected period.
                </p>

                <p v-if="form.errors.amount" class="form-error">
                    {{ form.errors.amount }}
                </p>
            </div>

            <!-- Budget period -->
            <div>
                <div class="mb-4">
                    <label class="form-label mb-1">
                        Budget period
                    </label>

                    <p class="text-xs leading-5 text-[#85877D]">
                        Choose the dates when this spending plan will be active.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <!-- Start date -->
                    <div>
                        <label for="start_date" class="form-label">
                            Start date <span class="text-[#B97962]">*</span>
                        </label>

                        <input
                            id="start_date"
                            v-model="form.start_date"
                            type="date"
                            required
                            class="form-input"
                        />

                        <p v-if="form.errors.start_date" class="form-error">
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <!-- End date -->
                    <div>
                        <label for="end_date" class="form-label">
                            End date <span class="text-[#B97962]">*</span>
                        </label>

                        <input
                            id="end_date"
                            v-model="form.end_date"
                            type="date"
                            :min="form.start_date"
                            required
                            class="form-input"
                        />

                        <p v-if="form.errors.end_date" class="form-error">
                            {{ form.errors.end_date }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Information panel -->
            <div class="flex gap-3 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 text-[#42614A]">
                    <Wallet
                        :size="19"
                        :strokeWidth="1.8"
                    />
                </div>

                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[#294B3C]">
                        Your spending plan stays connected
                    </p>

                    <p class="mt-1 text-xs leading-relaxed text-[#777D73]">
                        Your existing transactions remain recorded. Matching expenses will continue to be counted based on the updated category and budget dates.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 border-t border-[#EAE6DD] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/budgets"
                    class="inline-flex items-center justify-center rounded-xl border border-[#EAE6DD] bg-white px-5 py-3 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Save
                        :size="16"
                        :strokeWidth="2"
                        class="shrink-0"
                    />

                    {{ form.processing ? 'Saving...' : 'Save changes' }}
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
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        background-color 180ms ease;
}

/* Fix padding when an icon is inside the input */
.form-input.pl-11 {
    padding-left: 44px;
}

.form-input.pl-10 {
    padding-left: 40px;
}

.form-input::placeholder {
    color: #A2A197;
}

.form-input:focus {
    border-color: #8B9D80;
    background-color: #FFF;
    box-shadow: 0 0 0 3px rgba(139, 157, 128, 0.13);
}

.select-input {
    appearance: none;
    cursor: pointer;
}

.form-error {
    margin-top: 7px;
    color: #B4534B;
    font-size: 12px;
    line-height: 1.5;
}

@media (max-width: 640px) {
    .form-input {
        font-size: 16px;
    }
}
</style>
