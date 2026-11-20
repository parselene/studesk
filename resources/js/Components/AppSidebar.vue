
<script setup>
import { computed, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import {
    BookOpen,
    CalendarDays,
    CheckSquare,
    ChevronDown,
    CircleDollarSign,
    LayoutDashboard,
    BookMarked,
    ArrowLeftRight,
    PiggyBank,
    Wallet,
    ReceiptText,
    Heart,
    LogOut,
} from 'lucide-vue-next'

const page = usePage()

const userName = computed(
    () => page.props.auth?.user?.name || 'Student'
)

const currentPath = computed(() => {
    return page.url.split('?')[0].replace(/\/+$/, '') || '/dashboard'
})

const isFinanceActive = computed(() =>
    currentPath.value === '/finance' ||
    currentPath.value.startsWith('/finance/')
)

// Open Finance automatically when a finance page is active.
const financeOpen = ref(isFinanceActive.value)

watch(isFinanceActive, (active) => {
    if (active) financeOpen.value = true
})

function isActive(path) {
    if (path === '/dashboard') {
        return currentPath.value === '/dashboard'
    }

    return (
        currentPath.value === path ||
        currentPath.value.startsWith(`${path}/`)
    )
}

function navClass(path) {
    return [
        'flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition duration-200',
        isActive(path)
            ? 'bg-white/15 font-medium text-white shadow-sm'
            : 'text-white/70 hover:bg-white/10 hover:text-white',
    ]
}

function financeChildClass(path) {
    const active = path === '/finance'
        ? currentPath.value === '/finance'
        : currentPath.value === path ||
          currentPath.value.startsWith(`${path}/`)

    return [
        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition duration-200',
        active
            ? 'bg-white/15 font-medium text-white'
            : 'text-white/65 hover:bg-white/10 hover:text-white',
    ]
}
</script>

<template>
    <aside
        class="flex w-full flex-col bg-[#294B3C] px-5 py-5 text-white
               md:fixed md:inset-y-0 md:left-0 md:z-40 md:w-[248px]
               md:overflow-y-auto md:px-6 md:py-7"
    >
        <!-- Branding -->
        <Link
            href="/dashboard"
            class="flex items-center gap-3 px-2"
        >
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center
                       rounded-2xl bg-[#E5EBD9] text-[#294B3C]"
            >
                <BookOpen
                    :size="24"
                    :stroke-width="1.8"
                />
            </div>

            <div>
                <div class="text-xl font-bold tracking-tight">
                    StuDesk
                </div>
                <div class="mt-0.5 text-[11px] tracking-wide text-white/65">
                    Your student life, organized.
                </div>
            </div>
        </Link>

        <!-- Navigation label -->
        <div
            class="mb-3 mt-10 px-3 text-[10px] font-semibold uppercase
                   tracking-[0.2em] text-white/45"
        >
            Workspace
        </div>

        <!-- Main navigation -->
        <nav class="space-y-1.5">
            <Link
                href="/dashboard"
                :class="navClass('/dashboard')"
            >
                <LayoutDashboard :size="19" :stroke-width="1.8" />
                <span>Overview</span>
            </Link>

            <Link
                href="/calendar"
                :class="navClass('/calendar')"
            >
                <CalendarDays :size="19" :stroke-width="1.8" />
                <span>My calendar</span>
            </Link>

            <Link
                href="/tasks"
                :class="navClass('/tasks')"
            >
                <CheckSquare :size="19" :stroke-width="1.8" />
                <span>Academic planner</span>
            </Link>

            <Link
                href="/subjects"
                :class="navClass('/subjects')"
            >
                <BookMarked :size="19" :stroke-width="1.8" />
                <span>My subjects</span>
            </Link>

            <!-- Finance dropdown -->
            <div>
                <button
                    type="button"
                    :class="navClass('/finance')"
                    class="w-full"
                    :aria-expanded="financeOpen || isFinanceActive"
                    aria-controls="finance-submenu"
                    @click="financeOpen = !financeOpen"
                >
                    <CircleDollarSign
                        :size="19"
                        :stroke-width="1.8"
                    />

                    <span class="flex-1 text-left">Finance</span>

                    <ChevronDown
                        :size="16"
                        class="transition-transform duration-200"
                        :class="(financeOpen || isFinanceActive) ? 'rotate-180' : ''"
                    />
                </button>

                <div
                    v-if="financeOpen || isFinanceActive"
                    id="finance-submenu"
                    class="ml-4 mt-1 space-y-1 border-l border-white/20 pl-3"
                >
                    <Link
                        href="/finance"
                        :class="financeChildClass('/finance')"
                    >
                        <LayoutDashboard
                            :size="17"
                            :stroke-width="1.8"
                        />
                        <span>Finance overview</span>
                    </Link>

                    <Link
                        href="/finance/transactions"
                        :class="financeChildClass('/finance/transactions')"
                    >
                        <ArrowLeftRight
                            :size="17"
                            :stroke-width="1.8"
                        />
                        <span>Transactions</span>
                    </Link>

                    <Link
                        href="/finance/savings-goals"
                        :class="financeChildClass('/finance/savings-goals')"
                    >
                        <PiggyBank
                            :size="17"
                            :stroke-width="1.8"
                        />
                        <span>Savings goals</span>
                    </Link>

                    <Link
                        href="/finance/budgets"
                        :class="financeChildClass('/finance/budgets')"
                    >
                        <Wallet
                            :size="17"
                            :stroke-width="1.8"
                        />
                        <span>Budgets</span>
                    </Link>

                    <Link
                        href="/finance/bills"
                        :class="financeChildClass('/finance/bills')"
                    >
                        <ReceiptText
                            :size="17"
                            :stroke-width="1.8"
                        />
                        <span>Bills &amp; payments</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Encouragement card -->
        <div
            class="mt-8 rounded-2xl border border-white/10
                   bg-white/[0.06] p-4"
        >
            <div
                class="flex h-9 w-9 items-center justify-center
                       rounded-xl bg-[#DDE7D6] text-[#294B3C]"
            >
                <Heart :size="19" :stroke-width="1.8" />
            </div>

            <p class="mt-4 text-sm font-semibold">
                A note for you
            </p>

            <p class="mt-2 text-xs leading-5 text-white/65">
                You don't have to figure everything out at once.
                Start with one task and one small step.
            </p>
        </div>

        <!-- Account section -->
        <div class="mt-8 border-t border-white/15 pt-5 md:mt-auto">
            <div class="mb-4 flex items-center gap-3 px-2">
                <div
                    class="flex h-10 w-10 shrink-0 items-center
                           justify-center rounded-full bg-[#E5D7C8]
                           font-semibold text-[#294B3C]"
                >
                    {{ userName.charAt(0).toUpperCase() }}
                </div>

                <Link
                    href="/profile"
                    class="min-w-0 flex-1 rounded-lg py-1 transition hover:opacity-80"
                >
                    <p class="truncate text-sm font-medium">
                        {{ userName }}
                    </p>
                    <p class="mt-0.5 text-xs text-white/55">
                        My account · Edit profile
                    </p>
                </Link>
            </div>

            <Link
                href="/logout"
                method="post"
                as="button"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2
                       text-left text-sm text-white/70 transition
                       hover:bg-white/10 hover:text-white"
            >
                <LogOut :size="18" :stroke-width="1.8" />
                <span>Log out</span>
            </Link>
        </div>
    </aside>
</template>
