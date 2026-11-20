
<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    goal: {
        type: Object,
        required: true,
    },
})

const page = usePage()

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
    }).format(Number(value || 0))

const saved = computed(() => Number(props.goal.current_amount || 0))
const target = computed(() => Number(props.goal.target_amount || 0))

const progress = computed(() =>
    target.value > 0
        ? Math.min(100, Math.round((saved.value / target.value) * 100))
        : 0
)

const remaining = computed(() => Math.max(0, target.value - saved.value))

const formatDate = (date) => {
    if (!date) return 'No target date'
    return new Date(`${String(date).slice(0, 10)}T12:00:00`).toLocaleDateString(
        'en-PH',
        { month: 'long', day: 'numeric', year: 'numeric' }
    )
}

const today = () => {
    const date = new Date()
    const offset = date.getTimezoneOffset()
    return new Date(date.getTime() - offset * 60000).toISOString().slice(0, 10)
}

const form = useForm({
    amount: '',
    contribution_date: today(),
    notes: '',
})

const submitContribution = () => {
    form.post(`/finance/savings-goals/${props.goal.id}/contribute`, {
        preserveScroll: true,
        onSuccess: () => form.reset('amount', 'notes'),
    })
}
</script>

<template>
    <Head :title="goal.name" />

    <div class="mx-auto max-w-5xl space-y-7">
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

            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="break-words text-3xl font-bold text-[#294B3C]">
                        {{ goal.name }}
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        {{ goal.description || 'Every contribution brings you closer to your goal.' }}
                    </p>
                </div>

                <Link
                    :href="`/finance/savings-goals/${goal.id}/edit`"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-[#DEDCD4] px-4 py-2.5 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                >
                    Edit goal
                </Link>
            </div>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="rounded-xl border border-[#D8E4D5] bg-[#EFF5EC] px-4 py-3 text-sm font-medium text-[#294B3C]"
        >
            {{ page.props.flash.success }}
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="space-y-5 rounded-2xl border border-[#E8E5DD] bg-white p-6 sm:p-8">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-500">Total amount saved</p>
                        <p class="mt-2 text-3xl font-bold text-[#294B3C]">
                            {{ money(saved) }}
                        </p>
                    </div>

                    <span
                        class="rounded-full px-3 py-1.5 text-xs font-semibold"
                        :class="goal.status === 'completed'
                            ? 'bg-[#E6F0E4] text-[#294B3C]'
                            : 'bg-[#F3EFE4] text-[#806B43]'"
                    >
                        {{ goal.status === 'completed' ? 'Completed' : 'In progress' }}
                    </span>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-sm font-medium text-[#54705D]">
                            Goal progress
                        </span>
                        <span class="text-sm font-bold text-[#294B3C]">
                            {{ progress }}%
                        </span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-[#EEECE5]">
                        <div
                            class="h-full rounded-full bg-[#82977A] transition-all"
                            :style="{ width: `${progress}%` }"
                        />
                    </div>

                    <div class="mt-3 flex justify-between gap-3 text-sm">
                        <span class="text-gray-500">Target: {{ money(target) }}</span>
                        <span class="font-semibold text-[#294B3C]">
                            {{ money(remaining) }} to go
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 border-t border-[#EEECE5] pt-5 sm:grid-cols-2">
                    <div class="rounded-xl bg-[#F7F5F0] p-4">
                        <p class="text-xs text-gray-500">Target amount</p>
                        <p class="mt-1 text-lg font-bold text-[#294B3C]">
                            {{ money(target) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-[#F7F5F0] p-4">
                        <p class="text-xs text-gray-500">Target date</p>
                        <p class="mt-1 text-base font-bold text-[#294B3C]">
                            {{ formatDate(goal.target_date) }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="goal.status === 'completed'"
                    class="rounded-xl border border-[#D8E4D5] bg-[#EFF5EC] p-4"
                >
                    <p class="text-sm font-semibold text-[#294B3C]">
                        You've reached your goal!
                    </p>
                    <p class="mt-1 text-sm text-[#54705D]">
                        This goal is complete. You can still review your contribution history.
                    </p>
                </div>
            </section>

            <section class="h-fit space-y-5 rounded-2xl border border-[#E8E5DD] bg-white p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                        KEEP GOING
                    </p>
                    <h2 class="mt-2 text-xl font-bold text-[#294B3C]">
                        Add a contribution
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Record money you've set aside for this goal.
                    </p>
                </div>

                <form
                    v-if="goal.status !== 'completed'"
                    class="space-y-5"
                    @submit.prevent="submitContribution"
                >
                    <div class="space-y-2">
                        <label for="amount" class="text-sm font-semibold text-[#294B3C]">
                            Amount (₱) <span class="text-[#A15D35]">*</span>
                        </label>
                        <input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            min="0.01"
                            max="9999999999.99"
                            step="0.01"
                            required
                            placeholder="500.00"
                            class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                        />
                        <p v-if="form.errors.amount" class="text-sm text-red-600">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="contribution_date"
                            class="text-sm font-semibold text-[#294B3C]"
                        >
                            Contribution date <span class="text-[#A15D35]">*</span>
                        </label>
                        <input
                            id="contribution_date"
                            v-model="form.contribution_date"
                            type="date"
                            :max="today()"
                            required
                            class="w-full rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                        />
                        <p v-if="form.errors.contribution_date" class="text-sm text-red-600">
                            {{ form.errors.contribution_date }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="notes" class="text-sm font-semibold text-[#294B3C]">
                            Notes <span class="font-normal text-gray-400">(optional)</span>
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            maxlength="2000"
                            placeholder="e.g. Saved from this week's allowance"
                            class="w-full resize-y rounded-xl border border-[#DEDCD4] px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-[#82977A] focus:ring-2 focus:ring-[#82977A]/20"
                        />
                        <p v-if="form.errors.notes" class="text-sm text-red-600">
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? 'Saving...' : 'Record contribution' }}
                    </button>
                </form>

                <div
                    v-else
                    class="rounded-xl bg-[#F5F7F2] p-4 text-sm leading-6 text-gray-500"
                >
                    This goal has been completed, so new contributions are disabled.
                    Create another goal to keep saving.
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-[#E8E5DD] bg-white">
            <div class="border-b border-[#EEECE5] px-6 py-5">
                <h2 class="text-lg font-bold text-[#294B3C]">
                    Contribution history
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    A record of the amounts you've set aside for this goal.
                </p>
            </div>

            <div
                v-if="goal.contributions?.length"
                class="divide-y divide-[#EEECE5]"
            >
                <div
                    v-for="contribution in goal.contributions"
                    :key="contribution.id"
                    class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-[#294B3C]">
                            {{ contribution.notes || 'Savings contribution' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ formatDate(contribution.contribution_date) }}
                        </p>
                    </div>

                    <p class="text-base font-bold text-[#54705D]">
                        +{{ money(contribution.amount) }}
                    </p>
                </div>
            </div>

            <div v-else class="px-6 py-10 text-center">
                <p class="text-sm font-medium text-[#294B3C]">
                    No contributions yet
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Your first recorded contribution will appear here.
                </p>
            </div>
        </section>
    </div>
</template>
