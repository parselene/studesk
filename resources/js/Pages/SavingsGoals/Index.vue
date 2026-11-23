
<script setup>
import { computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import {
    Plus,
    PiggyBank,
    Target,
    TrendingUp,
    CheckCircle2,
    CalendarDays,
    Pencil,
    Trash2,
    ArrowRight,
    Wallet,
} from 'lucide-vue-next'

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
        router.delete(`/finance/savings-goals/${goal.id}`, {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <Head title="Savings Goals" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                    FINANCE / SAVINGS
                </p>

                <h1 class="font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                    Savings goals
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[#777D73]">
                    Plan what you're saving for, track your progress, and work toward
                    your financial goals one contribution at a time.
                </p>
            </div>

            <Link
                href="/finance/savings-goals/create"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#203D30]"
            >
                <Plus :size="17" :strokeWidth="2" />
                Create a goal
            </Link>
        </header>

        <!-- Success Message -->
        <div
            v-if="page.props.flash?.success"
            class="mb-6 flex items-center gap-3 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] px-4 py-3 text-sm font-medium text-[#42614A]"
        >
            <CheckCircle2 :size="18" class="shrink-0" />
            {{ page.props.flash.success }}
        </div>

        <!-- Summary Cards -->
        <section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Total Saved -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-[#777D73]">
                        Total saved
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#54705D]">
                        <PiggyBank :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight text-[#294B3C] sm:text-2xl">
                    {{ money(totalSaved) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Based on recorded contributions
                </p>
            </article>

            <!-- Combined Target -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-[#777D73]">
                        Combined target
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#EBE5F3] text-[#796894]">
                        <Target :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight text-[#294B3C] sm:text-2xl">
                    {{ money(totalTarget) }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Across all your goals
                </p>
            </article>

            <!-- Overall Progress -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-[#777D73]">
                        Overall progress
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5E4DD] text-[#A85F4C]">
                        <TrendingUp :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight text-[#294B3C] sm:text-2xl">
                    {{ overallProgress }}%
                </p>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#EEECE5]">
                    <div
                        class="h-full rounded-full bg-[#82977A] transition-all duration-500"
                        :style="{ width: `${overallProgress}%` }"
                    />
                </div>

                <p class="mt-2 text-xs text-[#85877D]">
                    Combined savings progress
                </p>
            </article>

            <!-- Completed Goals -->
            <article class="rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-[#777D73]">
                        Goals completed
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#54705D]">
                        <CheckCircle2 :size="19" :strokeWidth="1.8" />
                    </div>
                </div>

                <p class="mt-4 text-xl font-semibold tracking-tight text-[#294B3C] sm:text-2xl">
                    {{ completedGoals }}
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Out of {{ goals.length }} {{ goals.length === 1 ? 'goal' : 'goals' }}
                </p>
            </article>
        </section>

        <!-- Goals Heading -->
        <section class="mb-5">
            <h2 class="font-serif text-xl font-semibold text-[#294B3C]">
                Your goals
            </h2>

            <p class="mt-1 text-sm text-[#85877D]">
                Keep track of what you're working toward.
            </p>
        </section>

        <!-- Goals Grid -->
        <section
            v-if="goals.length"
            class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="goal in goals"
                :key="goal.id"
                class="flex min-w-0 flex-col rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] transition duration-200 hover:-translate-y-0.5 hover:border-[#C8D2C1] hover:shadow-[0_5px_16px_rgba(41,59,50,0.06)]"
            >
                <!-- Goal Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#54705D]">
                            <PiggyBank :size="21" :strokeWidth="1.8" />
                        </div>

                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#8B8C7D]">
                                SAVINGS GOAL
                            </p>

                            <h3 class="mt-1 break-words text-base font-semibold text-[#294B3C]">
                                {{ goal.name }}
                            </h3>
                        </div>
                    </div>

                    <span
                        class="shrink-0 rounded-full border px-3 py-1 text-[11px] font-semibold"
                        :class="goal.status === 'completed'
                            ? 'border-[#C8D2C1] bg-[#E7EBDD] text-[#54705D]'
                            : 'border-[#E8DCC1] bg-[#F3ECD9] text-[#8E7846]'"
                    >
                        {{ goal.status === 'completed' ? 'Completed' : 'In progress' }}
                    </span>
                </div>

                <!-- Description -->
                <p class="mt-4 min-h-10 text-sm leading-relaxed text-[#85877D]">
                    {{ goal.description || 'A little progress each time brings you closer.' }}
                </p>

                <!-- Progress -->
                <div class="mt-5">
                    <div class="flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs text-[#85877D]">
                                Amount saved
                            </p>

                            <p class="mt-1 break-words text-xl font-semibold tracking-tight text-[#294B3C]">
                                {{ money(goal.current_amount) }}
                            </p>
                        </div>

                        <p class="shrink-0 text-sm font-semibold text-[#54705D]">
                            {{ Number(goal.progress_percentage || 0).toFixed(0) }}%
                        </p>
                    </div>

                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#EEECE5]">
                        <div
                            class="h-full rounded-full bg-[#82977A] transition-all duration-500"
                            :style="{
                                width: `${Math.min(100, Number(goal.progress_percentage || 0))}%`
                            }"
                        />
                    </div>

                    <div class="mt-2 flex flex-wrap justify-between gap-2 text-xs text-[#85877D]">
                        <span>
                            Target: {{ money(goal.target_amount) }}
                        </span>

                        <span class="font-medium text-[#697469]">
                            {{ money(Math.max(0, Number(goal.target_amount) - Number(goal.current_amount))) }}
                            to go
                        </span>
                    </div>
                </div>

                <!-- Target Date -->
                <div class="mt-5 border-t border-[#EAE6DD] pt-4">
                    <p class="text-xs text-[#85877D]">
                        Target date
                    </p>

                    <p class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-[#294B3C]">
                        <CalendarDays :size="15" class="text-[#8B8C7D]" />
                        {{ formatDate(goal.target_date) }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <Link
                        :href="`/finance/savings-goals/${goal.id}`"
                        class="inline-flex min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-[#203D30]"
                    >
                        {{ goal.status === 'completed' ? 'View goal' : 'Add savings' }}
                        <ArrowRight :size="15" />
                    </Link>

                    <Link
                        :href="`/finance/savings-goals/${goal.id}/edit`"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-[#DEDCD4] bg-white px-3 py-2.5 text-sm font-semibold text-[#54705D] transition hover:border-[#C8D2C1] hover:bg-[#F7F5F0]"
                    >
                        <Pencil :size="14" />
                        Edit
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-[#E8C9BC] bg-white px-3 py-2.5 text-sm font-semibold text-[#B97962] transition hover:bg-[#FBF3F0]"
                        title="Delete savings goal"
                        @click="removeGoal(goal)"
                    >
                        <Trash2 :size="15" />
                    </button>
                </div>
            </article>
        </section>

        <!-- Empty State -->
        <section
            v-else
            class="rounded-2xl border border-dashed border-[#D9D8CD] bg-[#FFFEFA] px-6 py-16 text-center"
        >
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[#54705D]">
                <PiggyBank :size="29" :strokeWidth="1.6" />
            </div>

            <h3 class="mt-5 font-serif text-xl font-semibold text-[#294B3C]">
                Start with your first savings goal
            </h3>

            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#85877D]">
                Whether it's tuition, a laptop, or an emergency fund, create a goal
                to give your savings a clear purpose.
            </p>

            <Link
                href="/finance/savings-goals/create"
                class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30]"
            >
                <Plus :size="17" />
                Create your first goal
            </Link>
        </section>
    </div>
</template>
