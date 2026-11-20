
<script setup>
import { computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    goals: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
    }).format(Number(value || 0))

const totalSaved = computed(() =>
    props.goals.reduce((sum, goal) => sum + Number(goal.current_amount || 0), 0)
)

const totalTarget = computed(() =>
    props.goals.reduce((sum, goal) => sum + Number(goal.target_amount || 0), 0)
)

const completedGoals = computed(() =>
    props.goals.filter((goal) => goal.status === 'completed').length
)

const overallProgress = computed(() =>
    totalTarget.value > 0
        ? Math.min(100, Math.round((totalSaved.value / totalTarget.value) * 100))
        : 0
)

const formatDate = (date) => {
    if (!date) return 'No target date'
    return new Date(`${String(date).slice(0, 10)}T12:00:00`).toLocaleDateString(
        'en-PH',
        { month: 'short', day: 'numeric', year: 'numeric' }
    )
}

const removeGoal = (goal) => {
    if (confirm(`Delete "${goal.name}" and its contribution history? This cannot be undone.`)) {
        router.delete(`/finance/savings-goals/${goal.id}`)
    }
}
</script>

<template>
    <Head title="Savings Goals" />

    <div class="mx-auto max-w-7xl space-y-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-2">

                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#758477]">
                    FINANCE / SAVINGS
                </p>

                <h1 class="text-3xl font-bold text-[#294B3C]">
                    Savings Goals
                </h1>

                <p class="max-w-2xl text-sm leading-6 text-gray-500">
                    Plan what you're saving for, track your progress, and work toward
                    your financial goals one contribution at a time.
                </p>
            </div>

            <Link
                href="/finance/savings-goals/create"
                class="inline-flex items-center justify-center rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203C30]"
            >
                + Create a goal
            </Link>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="rounded-xl border border-[#D8E4D5] bg-[#EFF5EC] px-4 py-3 text-sm font-medium text-[#294B3C]"
        >
            {{ page.props.flash.success }}
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-[#E8E5DD] bg-white p-5">
                <p class="text-sm text-gray-500">Total saved across goals</p>
                <p class="mt-2 text-2xl font-bold text-[#294B3C]">
                    {{ money(totalSaved) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Based on recorded contributions</p>
            </div>

            <div class="rounded-2xl border border-[#E8E5DD] bg-white p-5">
                <p class="text-sm text-gray-500">Combined target</p>
                <p class="mt-2 text-2xl font-bold text-[#294B3C]">
                    {{ money(totalTarget) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Across all your goals</p>
            </div>

            <div class="rounded-2xl border border-[#E8E5DD] bg-white p-5">
                <p class="text-sm text-gray-500">Overall progress</p>
                <p class="mt-2 text-2xl font-bold text-[#294B3C]">
                    {{ overallProgress }}%
                </p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#EEECE5]">
                    <div
                        class="h-full rounded-full bg-[#82977A] transition-all"
                        :style="{ width: `${overallProgress}%` }"
                    />
                </div>
            </div>

            <div class="rounded-2xl border border-[#E8E5DD] bg-white p-5">
                <p class="text-sm text-gray-500">Goals completed</p>
                <p class="mt-2 text-2xl font-bold text-[#294B3C]">
                    {{ completedGoals }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Out of {{ goals.length }} {{ goals.length === 1 ? 'goal' : 'goals' }}
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-[#294B3C]">Your goals</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Keep track of what you're working toward.
                </p>
            </div>
        </div>

        <div
            v-if="goals.length"
            class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="goal in goals"
                :key="goal.id"
                class="flex flex-col rounded-2xl border border-[#E8E5DD] bg-white p-5 transition hover:border-[#C8D3C4] hover:shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#758477]">
                            SAVINGS GOAL
                        </p>
                        <h3 class="mt-2 break-words text-lg font-bold text-[#294B3C]">
                            {{ goal.name }}
                        </h3>
                    </div>

                    <span
                        class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                        :class="goal.status === 'completed'
                            ? 'bg-[#E6F0E4] text-[#294B3C]'
                            : 'bg-[#F3EFE4] text-[#806B43]'"
                    >
                        {{ goal.status === 'completed' ? 'Completed' : 'In progress' }}
                    </span>
                </div>

                <p class="mt-3 min-h-10 text-sm leading-5 text-gray-500">
                    {{ goal.description || 'A little progress each time brings you closer.' }}
                </p>

                <div class="mt-5">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Amount saved</p>
                            <p class="mt-1 text-xl font-bold text-[#294B3C]">
                                {{ money(goal.current_amount) }}
                            </p>
                        </div>
                        <p class="text-sm font-semibold text-[#54705D]">
                            {{ Number(goal.progress_percentage || 0).toFixed(0) }}%
                        </p>
                    </div>

                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#EEECE5]">
                        <div
                            class="h-full rounded-full bg-[#82977A] transition-all"
                            :style="{
                                width: `${Math.min(100, Number(goal.progress_percentage || 0))}%`
                            }"
                        />
                    </div>

                    <div class="mt-2 flex justify-between gap-3 text-xs text-gray-500">
                        <span>Target: {{ money(goal.target_amount) }}</span>
                        <span>
                            {{ money(Math.max(0, Number(goal.target_amount) - Number(goal.current_amount))) }}
                            to go
                        </span>
                    </div>
                </div>

                <div class="mt-5 border-t border-[#EEECE5] pt-4">
                    <p class="text-xs text-gray-500">Target date</p>
                    <p class="mt-1 text-sm font-medium text-[#294B3C]">
                        {{ formatDate(goal.target_date) }}
                    </p>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <Link
                        :href="`/finance/savings-goals/${goal.id}`"
                        class="flex-1 rounded-xl bg-[#294B3C] px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-[#203C30]"
                    >
                        {{ goal.status === 'completed' ? 'View goal' : 'Add savings' }}
                    </Link>

                    <Link
                        :href="`/finance/savings-goals/${goal.id}/edit`"
                        class="rounded-xl border border-[#DEDCD4] px-3 py-2.5 text-sm font-medium text-[#294B3C] transition hover:bg-[#F7F5F0]"
                    >
                        Edit
                    </Link>

                    <button
                        type="button"
                        class="rounded-xl border border-[#E8D9D3] px-3 py-2.5 text-sm font-medium text-[#985C4A] transition hover:bg-[#FBF3F0]"
                        @click="removeGoal(goal)"
                    >
                        Delete
                    </button>
                </div>
            </article>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-[#D9D8CD] bg-white px-6 py-14 text-center"
        >
            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-[#E9EFE7]">
                <span class="text-2xl text-[#54705D]">◎</span>
            </div>

            <h3 class="mt-4 text-lg font-bold text-[#294B3C]">
                Start with your first savings goal
            </h3>

            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                Whether it's tuition, a laptop, or an emergency fund, create a goal
                to give your savings a clear purpose.
            </p>

            <Link
                href="/finance/savings-goals/create"
                class="mt-5 inline-flex rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white hover:bg-[#203C30]"
            >
                Create your first goal
            </Link>
        </div>
    </div>
</template>
