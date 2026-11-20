
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    goal: {
        type: Object,
        required: true,
    },
})

const form = useForm({
    name: props.goal.name ?? '',
    description: props.goal.description ?? '',
    target_amount: props.goal.target_amount ?? '',
    target_date: props.goal.target_date
        ? String(props.goal.target_date).slice(0, 10)
        : '',
})

const submit = () => {
    form.put(`/finance/savings-goals/${props.goal.id}`)
}
</script>

<template>
    <Head title="Edit Savings Goal" />

    <div class="mx-auto max-w-3xl space-y-7">
        <div class="space-y-2">
            <Link
                :href="`/finance/savings-goals/${goal.id}`"
                class="text-sm font-medium text-[#54705D] hover:underline"
            >
                ← Back to goal
            </Link>

            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                FINANCE / SAVINGS GOALS
            </p>

            <h1 class="text-3xl font-bold text-[#294B3C]">
                Edit savings goal
            </h1>

            <p class="text-sm leading-6 text-gray-500">
                Update your goal details while keeping your recorded contribution history.
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
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
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
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
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
                    class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
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
                    class="w-full resize-y rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                />
                <p v-if="form.errors.description" class="text-sm text-red-600">
                    {{ form.errors.description }}
                </p>
            </div>

            <div class="rounded-xl border border-[#E8E5DD] bg-[#F7F5F0] p-4">
                <p class="text-sm font-semibold text-[#294B3C]">
                    Current progress
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ Number(goal.progress_percentage || 0).toFixed(0) }}% complete.
                    Updating the target won't delete your contribution history.
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#EEECE5] pt-5 sm:flex-row sm:justify-end">
                <Link
                    :href="`/finance/savings-goals/${goal.id}`"
                    class="inline-flex items-center justify-center rounded-xl border border-[#DEDCD4] px-5 py-3 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save changes' }}
                </button>
            </div>
        </form>
    </div>
</template>
