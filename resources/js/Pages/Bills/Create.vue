
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    ArrowLeft,
    Receipt,
    Plus,
} from 'lucide-vue-next'

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

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7">
            <Link
                href="/finance/bills"
                class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-[#657A61] transition hover:text-[#294B3C]"
            >
                <ArrowLeft :size="16" :strokeWidth="2" class="shrink-0" />
                Back to bills
            </Link>

            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                FINANCE / BILLS
            </p>

            <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                Add bill
            </h1>

            <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                Record a payment obligation and keep track of its due date.
            </p>
        </header>

        <!-- Form -->
        <form
            class="space-y-7 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:p-7 lg:p-8"
            @submit.prevent="submit"
        >
            <!-- Bill name -->
            <div>
                <label for="title" class="form-label">
                    Bill name <span class="text-[#B97962]">*</span>
                </label>

                <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    maxlength="255"
                    required
                    placeholder="e.g. Electricity bill"
                    class="form-input"
                />

                <p v-if="form.errors.title" class="form-error">
                    {{ form.errors.title }}
                </p>
            </div>

            <!-- Category and amount -->
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="category" class="form-label">
                        Category
                    </label>

                    <select
                        id="category"
                        v-model="form.category"
                        class="form-input"
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

                    <p v-if="form.errors.category" class="form-error">
                        {{ form.errors.category }}
                    </p>
                </div>

                <div>
                    <label for="amount" class="form-label">
                        Amount <span class="text-[#B97962]">*</span>
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-sm font-semibold text-[#8B8C7D]">
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
                            class="form-input pl-10"
                        />
                    </div>

                    <p v-if="form.errors.amount" class="form-error">
                        {{ form.errors.amount }}
                    </p>
                </div>
            </div>

            <!-- Due date -->
            <div>
                <label for="due_date" class="form-label">
                    Due date <span class="text-[#B97962]">*</span>
                </label>

                <input
                    id="due_date"
                    v-model="form.due_date"
                    type="date"
                    required
                    class="form-input"
                />

                <p v-if="form.errors.due_date" class="form-error">
                    {{ form.errors.due_date }}
                </p>
            </div>

            <!-- Recurring bill -->
            <div class="space-y-4">
                <div class="rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] p-4">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            v-model="form.is_recurring"
                            type="checkbox"
                            class="mt-1 h-4 w-4 shrink-0 accent-[#294B3C]"
                        />

                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#294B3C]">
                                Recurring bill
                            </span>

                            <span class="mt-1 block text-sm leading-relaxed text-[#657A61]">
                                Enable this for payments that repeat regularly.
                            </span>
                        </span>
                    </label>

                    <div v-if="form.is_recurring" class="mt-5 border-t border-[#C8D2C1] pt-4">
                        <label for="recurrence_frequency" class="form-label">
                            Recurrence frequency
                            <span class="text-[#B97962]">*</span>
                        </label>

                        <select
                            id="recurrence_frequency"
                            v-model="form.recurrence_frequency"
                            required
                            class="form-input"
                        >
                            <option value="">Select frequency</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Every 3 months</option>
                            <option value="yearly">Yearly</option>
                        </select>

                        <p v-if="form.errors.recurrence_frequency" class="form-error">
                            {{ form.errors.recurrence_frequency }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="form-label">
                    Notes <span class="font-normal text-[#8B8C7D]">(optional)</span>
                </label>

                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="5000"
                    placeholder="Add payment instructions or reminders..."
                    class="form-input resize-y"
                />

                <p v-if="form.errors.notes" class="form-error">
                    {{ form.errors.notes }}
                </p>
            </div>

            <!-- Information panel -->
            <div class="flex gap-3 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 text-[#42614A]">
                    <Receipt :size="19" :strokeWidth="1.8" />
                </div>

                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[#294B3C]">
                        Keep your payments organized
                    </p>

                    <p class="mt-1 text-sm leading-relaxed text-[#657A61]">
                        Saving a bill records the obligation. An expense transaction is created when you mark the bill as paid.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 border-t border-[#EAE6DD] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/bills"
                    class="rounded-xl border border-[#E3E0D7] bg-white px-5 py-3 text-center text-sm font-semibold text-[#777D73] transition hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Plus :size="16" :strokeWidth="2" class="shrink-0" />
                    {{ form.processing ? 'Saving...' : 'Save bill' }}
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

/* Fix spacing for inputs with icons */
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
