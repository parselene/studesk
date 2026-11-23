
<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import {
    Wallet,
    BookOpen,
    Receipt,
    CalendarDays,
    CircleDollarSign,
    Check,
    BookMarked,
    ArrowRight,
    UserRound,
    Sparkles,
    Clock,
    PiggyBank,
} from 'lucide-vue-next'

defineOptions({
    layout: AppLayout,
})

const page = usePage()

const userName = computed(() => page.props.auth?.user?.name || 'Student')

const statsData = computed(() => page.props.statsData ?? {
    availableBalance: '0.00',
    tasksDueSoon: 0,
    spentThisMonth: '0.00',
    paymentsComingUp: 0,
})

const formatCurrency = (amount) => {
    return '₱' + Number(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const stats = computed(() => [
    {
        label: 'Available balance',
        value: formatCurrency(statsData.value.availableBalance),
        note: 'Based on your recorded income and expenses',
        icon: Wallet,
        tone: 'sage',
    },
    {
        label: 'Tasks due soon',
        value: String(statsData.value.tasksDueSoon).padStart(2, '0'),
        note: 'Your unfinished tasks for the next 7 days',
        icon: BookOpen,
        tone: 'peach',
    },
    {
        label: 'Spent this month',
        value: formatCurrency(statsData.value.spentThisMonth),
        note: 'Your recorded expenses this month',
        icon: Receipt,
        tone: 'lavender',
    },
    {
        label: 'Payments coming up',
        value: String(statsData.value.paymentsComingUp).padStart(2, '0'),
        note: 'Pending bills due within 7 days',
        icon: CalendarDays,
        tone: 'butter',
    },
])

const events = computed(() => page.props.events ?? [])

const actions = [
    {
        icon: CircleDollarSign,
        title: 'Record an expense',
        subtitle: 'Keep track of where your money goes',
        href: '/finance/transactions',
        tone: 'sage',
    },
    {
        icon: Check,
        title: 'Add an academic task',
        subtitle: 'Remember assignments and exams',
        href: '/tasks',
        tone: 'peach',
    },
    {
        icon: BookMarked,
        title: 'Manage my subjects',
        subtitle: 'Organize classes and instructors',
        href: '/subjects',
        tone: 'lavender',
    },
    {
        icon: CalendarDays,
        title: 'Open your calendar',
        subtitle: 'See your schedule in one view',
        href: '/calendar',
        tone: 'lavender',
    },
]

const toneClasses = {
    sage: 'bg-[#E4EBDD] text-[#42654B]',
    peach: 'bg-[#F5E4DD] text-[#A85F4C]',
    lavender: 'bg-[#EBE5F3] text-[#796894]',
    butter: 'bg-[#F3ECD9] text-[#8E7846]',
}
</script>

<template>
    <Head title="Dashboard | StuDesk" />

    <main class="page-shell min-w-0 pb-6">

        <!-- Page Header -->
        <header class="page-header">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.17em] text-[#8B8C7D]">
                    YOUR PERSONAL SPACE
                </p>

                <h1 class="mt-2 font-heading text-3xl font-normal tracking-tight text-[#293B32] sm:text-4xl">
                    Hey, {{ userName }}
                    <Sparkles
                        :size="19"
                        :stroke-width="1.8"
                        class="ml-1 inline-block text-[#B97962]"
                    />
                </h1>

                <p class="mt-2 text-sm text-[#777D73]">
                    Let's make today a little more organized.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-[#344B3C]">
                        Your dashboard
                    </p>
                    <p class="mt-1 text-xs text-[#85877D]">
                        One step at a time
                    </p>
                </div>

                <Link
                    href="/profile"
                    aria-label="Edit account"
                    title="Edit account"
                    class="flex h-11 w-11 items-center justify-center rounded-full border border-[#E4DFD4] bg-white text-[#294B3C] transition hover:bg-[#E4EBDD] hover:shadow-sm"
                >
                    <UserRound :size="20" :stroke-width="1.8" />
                </Link>
            </div>
        </header>

        <!-- Welcome Banner -->
        <section class="relative overflow-hidden rounded-[26px] bg-[#E7EBDD] p-6 sm:p-8 lg:p-9">
            <div class="pointer-events-none absolute -right-10 -top-16 h-52 w-52 rounded-full border-[30px] border-white/25"></div>

            <div class="pointer-events-none absolute right-28 top-28 h-16 w-16 rounded-full bg-[#D1DCC8]/60"></div>

            <div class="relative max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-[#496550]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#71916F]"></span>
                    Your fresh start
                </span>

                <h2 class="mt-5 max-w-lg font-heading text-3xl font-normal leading-tight tracking-tight text-[#294B3C] sm:text-4xl lg:text-[42px]">
                    Make room for what matters.
                </h2>

                <p class="mt-3 max-w-md text-sm leading-6 text-[#637361]">
                    Keep track of your school deadlines and spending without
                    juggling different planners.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <Link
                        href="/calendar"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-[#203D30]"
                    >
                        Plan my week
                        <ArrowRight :size="16" :stroke-width="2" />
                    </Link>

                    <Link
                        href="/tasks"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#C8D2C1] bg-white/70 px-5 py-3 text-sm font-medium text-[#294B3C] transition hover:bg-white"
                    >
                        <Check :size="16" :stroke-width="1.8" />
                        View my tasks
                    </Link>
                </div>
            </div>
        </section>

        <!-- Overview -->
        <section class="mt-7">
            <div class="mb-4">
                <h2 class="font-heading text-xl font-normal text-[#293B32]">
                    Your overview
                </h2>
                <p class="mt-1 text-sm text-[#85877D]">
                    A quick look at your priorities.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="item in stats"
                    :key="item.label"
                    class="rounded-2xl border border-[#EAE6DD] bg-white p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md hover:shadow-[#293B32]/[0.04]"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm text-[#7D8177]">
                            {{ item.label }}
                        </span>

                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                            :class="toneClasses[item.tone]"
                        >
                            <component
                                :is="item.icon"
                                :size="19"
                                :stroke-width="1.8"
                            />
                        </span>
                    </div>

                    <p class="mt-5 font-heading text-2xl font-normal tracking-tight text-[#293B32]">
                        {{ item.value }}
                    </p>

                    <p class="mt-2 text-xs leading-5 text-[#909187]">
                        {{ item.note }}
                    </p>
                </article>
            </div>
        </section>

        <!-- Upcoming Events and Quick Actions -->
        <section class="mt-7 grid grid-cols-1 items-start gap-5 xl:grid-cols-[1.35fr_0.85fr]">

            <!-- Coming Up -->
            <div class="rounded-2xl border border-[#EAE6DD] bg-white p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-heading text-xl font-normal text-[#293B32]">
                            Coming up
                        </h2>
                        <p class="mt-1 text-sm text-[#85877D]">
                            School and money, side by side.
                        </p>
                    </div>

                    <Link
                        href="/calendar"
                        class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-[#42654B] hover:underline"
                    >
                        Full calendar
                        <ArrowRight :size="14" />
                    </Link>
                </div>

                <div v-if="events.length" class="mt-5 space-y-2">
                    <article
                        v-for="event in events"
                        :key="event.id"
                        class="flex items-center gap-3 rounded-2xl p-3 transition hover:bg-[#F9F8F4] sm:gap-4"
                    >
                        <div
                            class="flex h-[58px] w-[55px] shrink-0 flex-col items-center justify-center rounded-xl"
                            :class="toneClasses[event.tone] || toneClasses.sage"
                        >
                            <span class="text-[10px] font-semibold tracking-widest">
                                {{ event.month }}
                            </span>
                            <span class="mt-0.5 text-xl font-semibold">
                                {{ event.day }}
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-semibold text-[#354638]">
                                {{ event.title }}
                            </h3>
                            <p class="mt-1 text-xs text-[#85877D]">
                                {{ event.subject }}
                            </p>
                        </div>

                        <span
                            class="hidden rounded-full px-3 py-1.5 text-[10px] font-medium sm:inline-flex"
                            :class="toneClasses[event.tone] || toneClasses.sage"
                        >
                            {{ event.type }}
                        </span>
                    </article>
                </div>

                <div v-else class="mt-5 rounded-2xl bg-[#F7F5F0] px-4 py-8 text-center">
                    <CalendarDays :size="25" class="mx-auto text-[#8B9A83]" />
                    <p class="mt-3 font-heading text-lg text-[#354638]">
                        Nothing coming up yet
                    </p>
                    <p class="mt-1 text-xs text-[#85877D]">
                        Your upcoming deadlines and payments will appear here.
                    </p>
                    <Link
                        href="/calendar"
                        class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-[#42654B] hover:underline"
                    >
                        Open calendar
                        <ArrowRight :size="14" />
                    </Link>
                </div>

                <div class="mt-4 flex items-center gap-2 rounded-xl bg-[#F7F5F0] px-3 py-3 text-xs leading-5 text-[#85877D]">
                    <Clock :size="16" class="shrink-0 text-[#8B8C7D]" />
                    Your upcoming deadlines and payments will appear here.
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-[#EAE6DD] bg-white p-5 sm:p-6">
                <h2 class="font-heading text-xl font-normal text-[#293B32]">
                    Make it happen
                </h2>
                <p class="mt-1 text-sm text-[#85877D]">
                    A small action is a good start.
                </p>

                <div class="mt-5 space-y-3">
                    <Link
                        v-for="action in actions"
                        :key="action.title"
                        :href="action.href"
                        class="group flex items-center gap-3 rounded-2xl border border-[#EEEBE4] p-3.5 transition hover:border-[#C8D5C4] hover:bg-[#FAFAF6]"
                    >
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                            :class="toneClasses[action.tone]"
                        >
                            <component
                                :is="action.icon"
                                :size="21"
                                :stroke-width="1.8"
                            />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-[#354638]">
                                {{ action.title }}
                            </span>
                            <span class="mt-1 block text-xs leading-5 text-[#85877D]">
                                {{ action.subtitle }}
                            </span>
                        </span>

                        <ArrowRight
                            :size="16"
                            class="shrink-0 text-[#A1A296] transition group-hover:translate-x-1 group-hover:text-[#42654B]"
                        />
                    </Link>
                </div>

                <!-- Reminder -->
                <div class="mt-5 rounded-2xl bg-[#F7F5F0] p-4">
                    <div class="flex items-center gap-2">
                        <PiggyBank :size="18" class="text-[#66816A]" />
                        <p class="text-sm font-semibold text-[#354638]">
                            Remember this
                        </p>
                    </div>

                    <p class="mt-2 text-xs leading-5 text-[#7D8177]">
                        Your calendar connects your time and your money,
                        helping you prepare for deadlines and payments before
                        they arrive.
                    </p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="pb-3 pt-8 text-center text-xs text-[#99998D]">
            Made for student life · StuDesk
        </footer>

    </main>
</template>
