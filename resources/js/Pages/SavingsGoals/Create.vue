
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    ArrowLeft,
    PiggyBank,
    Target,
} from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const form = useForm({
    name: '',
    description: '',
    target_amount: '',
    target_date: '',
})

const submit = () => {
    form.post('/finance/savings-goals')
}
</script>

<template>
    <Head title="Create Savings Goal" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7">
            <Link
                href="/finance/savings-goals"
                class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-[#777D73] transition hover:text-[#294B3C]"
            >
                <ArrowLeft
                    :size="16"
                    :strokeWidth="2"
                    class="shrink-0"
                />
                Back to savings goals
            </Link>

            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                FINANCE / SAVINGS
            </p>

            <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                Create savings goal
            </h1>

            <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                Give your savings a purpose by setting a target and a date to work toward.
            </p>
        </header>

        <!-- Form -->
        <form
            class="space-y-7 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:p-7 lg:p-8"
            @submit.prevent="submit"
        >
            <!-- Goal name -->
            <div>
                <label for="name" class="form-label">
                    Goal name <span class="text-[#B97962]">*</span>
                </label>

                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    maxlength="255"
                    required
                    autofocus
                    placeholder="e.g. New laptop, Tuition, Emergency fund"
                    class="form-input"
                />

                <p v-if="form.errors.name" class="form-error">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Target amount -->
            <div>
                <label for="target_amount" class="form-label">
                    Target amount (₱) <span class="text-[#B97962]">*</span>
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-sm font-semibold text-[#8B8C7D]"
                    >
                        ₱
                    </span>

                    <input
                        id="target_amount"
                        v-model="form.target_amount"
                        type="number"
                        min="0.01"
                        max="9999999999.99"
                        step="0.01"
                        required
                        placeholder="15000.00"
                        class="form-input pl-10"
                    />
                </div>

                <p class="mt-2 text-xs leading-5 text-[#85877D]">
                    Enter the total amount you want to save.
                </p>

                <p v-if="form.errors.target_amount" class="form-error">
                    {{ form.errors.target_amount }}
                </p>
            </div>

            <!-- Target date -->
            <div>
                <label for="target_date" class="form-label">
                    Target date
                    <span class="font-normal text-[#99998F]">(optional)</span>
                </label>

                <input
                    id="target_date"
                    v-model="form.target_date"
                    type="date"
                    :min="new Date().toLocaleDateString('en-CA')"
                    class="form-input"
                />

                <p class="mt-2 text-xs leading-5 text-[#85877D]">
                    Choose when you hope to reach your goal.
                </p>

                <p v-if="form.errors.target_date" class="form-error">
                    {{ form.errors.target_date }}
                </p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="form-label">
                    Description
                    <span class="font-normal text-[#99998F]">(optional)</span>
                </label>

                <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    maxlength="2000"
                    placeholder="What are you saving for?"
                    class="form-input resize-y"
                />

                <p v-if="form.errors.description" class="form-error">
                    {{ form.errors.description }}
                </p>
            </div>

            <!-- Information panel -->
            <div class="flex gap-3 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 text-[#42614A]">
                    <PiggyBank
                        :size="19"
                        :strokeWidth="1.8"
                    />
                </div>

                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[#294B3C]">
                        A goal you can work toward
                    </p>

                    <p class="mt-1 text-xs leading-relaxed text-[#777D73]">
                        Your goal starts at ₱0 saved. You can record contributions and track your progress after creating it.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 border-t border-[#EAE6DD] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/savings-goals"
                    class="inline-flex items-center justify-center rounded-xl border border-[#EAE6DD] bg-white px-5 py-3 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Target
                        :size="16"
                        :strokeWidth="2"
                        class="shrink-0"
                    />

                    {{ form.processing ? 'Saving...' : 'Create savings goal' }}
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
