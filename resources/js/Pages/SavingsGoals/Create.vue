
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

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

    <div class="mx-auto max-w-3xl space-y-7">
        <div class="space-y-2">
            <Link
                href="/finance/savings-goals"
                class="text-sm font-medium text-[#54705D] hover:underline"
            >
                ← Back to savings goals
            </Link>

            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                FINANCE / SAVINGS GOALS
            </p>

            <h1 class="text-3xl font-bold text-[#294B3C]">
                Create savings goal
            </h1>

            <p class="text-sm leading-6 text-gray-500">
                Give your savings a purpose by setting a target and a date to work toward.
            </p>
        </div>

        <form
            class="space-y-6 rounded-2xl border border-[#E8E5DD] bg-white p-6 sm:p-8"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <label for="name" class="text-sm font-semibold text-[#294B3C]">
                    Goal name <span class="text-[#A15D35]">*</span>
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    maxlength="255"
                    required
                    autofocus
                    placeholder="e.g. New laptop, Tuition, Emergency fund"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.name" class="text-sm text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>

            <div class="space-y-2">
                <label for="target_amount" class="text-sm font-semibold text-[#294B3C]">
                    Target amount (₱) <span class="text-[#A15D35]">*</span>
                </label>
                <input
                    id="target_amount"
                    v-model="form.target_amount"
                    type="number"
                    min="0.01"
                    max="9999999999.99"
                    step="0.01"
                    required
                    placeholder="15000.00"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p class="text-xs leading-5 text-gray-500">
                    Enter the total amount you want to save.
                </p>
                <p v-if="form.errors.target_amount" class="text-sm text-red-600">
                    {{ form.errors.target_amount }}
                </p>
            </div>

            <div class="space-y-2">
                <label for="target_date" class="text-sm font-semibold text-[#294B3C]">
                    Target date <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <input
                    id="target_date"
                    v-model="form.target_date"
                    type="date"
                    :min="new Date().toLocaleDateString('en-CA')"
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p class="text-xs leading-5 text-gray-500">
                    Choose when you hope to reach your goal.
                </p>
                <p v-if="form.errors.target_date" class="text-sm text-red-600">
                    {{ form.errors.target_date }}
                </p>
            </div>

            <div class="space-y-2">
                <label for="description" class="text-sm font-semibold text-[#294B3C]">
                    Description <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    maxlength="2000"
                    placeholder="What are you saving for?"
                    class="w-full resize-y rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.description" class="text-sm text-red-600">
                    {{ form.errors.description }}
                </p>
            </div>

            <div class="rounded-xl border border-[#E3E9DF] bg-[#F5F7F2] p-4">
                <p class="text-sm font-semibold text-[#294B3C]">
                    A goal you can work toward
                </p>
                <p class="mt-1 text-sm leading-5 text-gray-500">
                    Your goal starts at ₱0 saved. You can record contributions after creating it.
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#EEECE5] pt-5 sm:flex-row sm:justify-end">
                <Link
                    href="/finance/savings-goals"
                    class="inline-flex items-center justify-center rounded-xl border border-[#DEDCD4] px-5 py-3 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Create savings goal' }}
                </button>
            </div>
        </form>
    </div>
</template>
