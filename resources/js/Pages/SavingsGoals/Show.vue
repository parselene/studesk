
<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    ArrowLeft,
    ArrowUpRight,
    CalendarDays,
    CheckCircle2,
    Clock,
    Coins,
    PiggyBank,
    Plus,
    Target,
    TrendingUp,
} from 'lucide-vue-next'

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

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Header -->
        <header class="mb-7">
            <Link
                href="/finance/savings-goals"
                class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-[#777D73] transition hover:text-[#294B3C]"
            >
                <ArrowLeft :size="16" />
                Back to savings goals
            </Link>

            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                FINANCE / SAVINGS
            </p>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <h1 class="break-words font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                        {{ goal.name }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[#777D73]">
                        {{ goal.description || 'Every contribution brings you closer to your goal.' }}
                    </p>
                </div>

                <Link
                    :href="`/finance/savings-goals/${goal.id}/edit`"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-[#EAE6DD] bg-[#FFFEFA] px-4 py-2.5 text-sm font-semibold text-[#294B3C] transition hover:bg-[#F7F5F0]"
                >
                    Edit goal
                    <ArrowUpRight :size="15" />
                </Link>
            </div>
        </header>

        <!-- Flash message -->
        <div
            v-if="page.props.flash?.success"
            class="mb-6 flex items-center gap-3 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] px-4 py-3 text-sm font-medium text-[#294B3C]"
        >
            <CheckCircle2 :size="18" class="shrink-0" />
            {{ page.props.flash.success }}
        </div>

        <!-- Main content -->
        <div class="grid min-w-0 gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <!-- Goal progress card -->
            <section class="min-w-0 space-y-6 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:p-7">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#8B8C7D]">
                            TOTAL AMOUNT SAVED
                        </p>

                        <p class="mt-2 break-words font-serif text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-[36px]">
                            {{ money(saved) }}
                        </p>
                    </div>

                    <span
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold"
                        :class="goal.status === 'completed'
                            ? 'bg-[#E7EBDD] text-[#42614A]'
                            : 'bg-[#F3ECD9] text-[#8E7846]'"
                    >
                        <CheckCircle2 v-if="goal.status === 'completed'" :size="13" />
                        <Clock v-else :size="13" />
                        {{ goal.status === 'completed' ? 'Completed' : 'In progress' }}
                    </span>
                </div>

                <!-- Progress -->
                <div>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="text-sm font-medium text-[#777D73]">
                            Goal progress
                        </span>

                        <span class="text-sm font-bold tabular-nums text-[#294B3C]">
                            {{ progress }}%
                        </span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-[#EEEEE6]">
                        <div
                            class="h-full rounded-full bg-[#82977A] transition-all duration-500"
                            :style="{ width: `${progress}%` }"
                        />
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="text-[#85877D]">
                            Target: {{ money(target) }}
                        </span>

                        <span class="font-semibold text-[#294B3C]">
                            {{ money(remaining) }} to go
                        </span>
                    </div>
                </div>

                <!-- Goal details -->
                <div class="grid gap-3 border-t border-[#EAE6DD] pt-5 sm:grid-cols-2">
                    <div class="rounded-xl bg-[#F7F5F0] p-4">
                        <div class="flex items-center gap-2 text-[#85877D]">
                            <Target :size="15" />
                            <p class="text-xs font-medium">Target amount</p>
                        </div>

                        <p class="mt-2 break-words text-lg font-semibold text-[#294B3C]">
                            {{ money(target) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-[#F7F5F0] p-4">
                        <div class="flex items-center gap-2 text-[#85877D]">
                            <CalendarDays :size="15" />
                            <p class="text-xs font-medium">Target date</p>
                        </div>

                        <p class="mt-2 text-sm font-semibold text-[#294B3C]">
                            {{ formatDate(goal.target_date) }}
                        </p>
                    </div>
                </div>

                <!-- Completed message -->
                <div
                    v-if="goal.status === 'completed'"
                    class="flex gap-3 rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] p-4"
                >
                    <CheckCircle2 :size="19" class="mt-0.5 shrink-0 text-[#42614A]" />

                    <div>
                        <p class="text-sm font-semibold text-[#294B3C]">
                            You've reached your goal!
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-[#777D73]">
                            This goal is complete. You can still review your contribution history.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Add contribution card -->
            <section class="h-fit min-w-0 space-y-5 rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] p-5 shadow-[0_2px_10px_rgba(41,59,50,0.025)] sm:p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                        KEEP GOING
                    </p>

                    <h2 class="mt-2 font-serif text-2xl font-semibold text-[#294B3C]">
                        Add a contribution
                    </h2>

                    <p class="mt-2 text-sm leading-relaxed text-[#777D73]">
                        Record money you've set aside for this goal.
                    </p>
                </div>

                <form
                    v-if="goal.status !== 'completed'"
                    class="space-y-5"
                    @submit.prevent="submitContribution"
                >
                    <!-- Amount -->
                    <div>
                        <label for="amount" class="form-label">
                            Amount (₱) <span class="text-[#B97962]">*</span>
                        </label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-[#8B8C7D]">
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
                                placeholder="500.00"
                                class="form-input pl-10"
                            />
                        </div>

                        <p v-if="form.errors.amount" class="form-error">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <!-- Contribution date -->
                    <div>
                        <label for="contribution_date" class="form-label">
                            Contribution date <span class="text-[#B97962]">*</span>
                        </label>

                        <div class="relative">
                            <CalendarDays
                                :size="17"
                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#8B8C7D]"
                            />

                            <input
                                id="contribution_date"
                                v-model="form.contribution_date"
                                type="date"
                                :max="today()"
                                required
                                class="form-input pl-11"
                            />
                        </div>

                        <p v-if="form.errors.contribution_date" class="form-error">
                            {{ form.errors.contribution_date }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="form-label">
                            Notes
                            <span class="font-normal text-[#99998F]">(optional)</span>
                        </label>

                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            maxlength="2000"
                            placeholder="e.g. Saved from this week's allowance"
                            class="form-input resize-y"
                        />

                        <p v-if="form.errors.notes" class="form-error">
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Plus :size="17" />
                        {{ form.processing ? 'Saving...' : 'Record contribution' }}
                    </button>
                </form>

                <!-- Completed goal state -->
                <div
                    v-else
                    class="rounded-xl border border-[#C8D2C1] bg-[#E7EBDD] p-4"
                >
                    <div class="flex items-center gap-2 text-sm font-semibold text-[#294B3C]">
                        <CheckCircle2 :size="17" />
                        Goal completed
                    </div>

                    <p class="mt-2 text-xs leading-relaxed text-[#777D73]">
                        This goal has been completed, so new contributions are disabled. Create another goal to keep saving.
                    </p>
                </div>
            </section>
        </div>

        <!-- Contribution history -->
        <section class="overflow-hidden rounded-2xl border border-[#EAE6DD] bg-[#FFFEFA] shadow-[0_2px_10px_rgba(41,59,50,0.025)]">
            <div class="flex items-center gap-3 border-b border-[#EAE6DD] px-5 py-5 sm:px-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42614A]">
                    <PiggyBank :size="20" />
                </div>

                <div>
                    <h2 class="font-serif text-xl font-semibold text-[#294B3C]">
                        Contribution history
                    </h2>

                    <p class="mt-1 text-xs text-[#85877D]">
                        A record of the amounts you've set aside for this goal.
                    </p>
                </div>
            </div>

            <div
                v-if="goal.contributions?.length"
                class="divide-y divide-[#EAE6DD]"
            >
                <div
                    v-for="contribution in goal.contributions"
                    :key="contribution.id"
                    class="flex flex-col gap-3 px-5 py-4 transition hover:bg-[#FAF9F5] sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42614A]">
                            <Coins :size="18" />
                        </div>

                        <div class="min-w-0">
                            <p class="break-words text-sm font-semibold text-[#294B3C]">
                                {{ contribution.notes || 'Savings contribution' }}
                            </p>

                            <p class="mt-1 flex items-center gap-1.5 text-xs text-[#85877D]">
                                <CalendarDays :size="13" />
                                {{ formatDate(contribution.contribution_date) }}
                            </p>
                        </div>
                    </div>

                    <p class="shrink-0 text-base font-semibold tabular-nums text-[#42614A]">
                        +{{ money(contribution.amount) }}
                    </p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="px-5 py-12 text-center sm:px-6">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F7F5F0] text-[#8B8C7D]">
                    <TrendingUp :size="22" />
                </div>

                <p class="mt-4 text-sm font-semibold text-[#294B3C]">
                    No contributions yet
                </p>

                <p class="mt-1 text-xs text-[#85877D]">
                    Your first recorded contribution will appear here.
                </p>
            </div>
        </section>
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
    transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
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
