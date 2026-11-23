
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
    Menu,
    X,
} from 'lucide-vue-next'

const page = usePage()

const userName = computed(
    () => page.props.auth?.user?.name || 'Student'
)

const currentPath = computed(() => {
    return page.url.split('?')[0].replace(/\/+$/, '') || '/dashboard'
})

// Mobile sidebar
const mobileOpen = ref(false)

function closeMobileMenu() {
    mobileOpen.value = false
}

// Automatically close the mobile drawer after navigation.
watch(
    () => page.url,
    () => {
        closeMobileMenu()
    }
)

// Finance submenu
const isFinanceActive = computed(() =>
    currentPath.value === '/finance' ||
    currentPath.value.startsWith('/finance/')
)

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
    const active = path === '/finance'
        ? isFinanceActive.value
        : isActive(path)

    return [
        'group relative flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-sm transition-all duration-200',
        active
            ? 'bg-white text-[#294B3C] font-semibold shadow-sm'
            : 'text-white/75 hover:bg-white/10 hover:text-white',
    ]
}

function financeChildClass(path) {
    const active = path === '/finance'
        ? currentPath.value === '/finance'
        : currentPath.value === path ||
          currentPath.value.startsWith(`${path}/`)

    return [
        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition-all duration-200',
        active
            ? 'bg-white/15 font-semibold text-white'
            : 'text-white/65 hover:bg-white/10 hover:text-white',
    ]
}
</script>

<template>
    <!-- Mobile top navigation -->
    <header
        class="sticky top-0 z-40 flex h-[68px] items-center justify-between border-b border-[#EAE6DD] bg-[#FFFEFA] px-4 md:hidden"
    >
        <Link
            href="/dashboard"
            class="flex items-center gap-2.5"
            @click="closeMobileMenu"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E5EBD9] text-[#294B3C]"
            >
                <BookOpen :size="21" :stroke-width="1.8" />
            </div>

            <div>
                <div class="font-heading text-xl font-semibold tracking-tight text-[#294B3C]">
                    StuDesk
                </div>

                <div class="text-[10px] text-[#85877D]">
                    Your student life, organized.
                </div>
            </div>
        </Link>

        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#EAE6DD] text-[#294B3C] transition hover:bg-[#F7F5F0]"
            :aria-label="mobileOpen ? 'Close navigation menu' : 'Open navigation menu'"
            :aria-expanded="mobileOpen"
            @click="mobileOpen = !mobileOpen"
        >
            <X v-if="mobileOpen" :size="21" />
            <Menu v-else :size="21" />
        </button>
    </header>

    <!-- Mobile backdrop -->
    <button
        v-if="mobileOpen"
        type="button"
        aria-label="Close navigation menu"
        class="fixed inset-0 z-40 bg-[#17291F]/50 backdrop-blur-[2px] md:hidden"
        @click="closeMobileMenu"
    />

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-[280px] max-w-[85vw] flex-col overflow-y-auto bg-[#294B3C] px-5 py-6 text-white shadow-2xl transition-transform duration-300 ease-in-out md:z-40 md:w-[248px] md:max-w-none md:translate-x-0 md:shadow-none md:px-5 md:py-7"
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Branding -->
        <div class="flex items-center justify-between">
            <Link
                href="/dashboard"
                class="flex items-center gap-3 px-2"
                @click="closeMobileMenu"
            >
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#E5EBD9] text-[#294B3C]"
                >
                    <BookOpen :size="23" :stroke-width="1.8" />
                </div>

                <div>
                    <div class="font-heading text-2xl tracking-tight">
                        StuDesk
                    </div>

                    <div class="mt-0.5 text-[11px] tracking-wide text-white/60">
                        Your student life, organized.
                    </div>
                </div>
            </Link>

            <!-- Close button on mobile -->
            <button
                type="button"
                aria-label="Close sidebar"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-white/60 transition hover:bg-white/10 hover:text-white md:hidden"
                @click="closeMobileMenu"
            >
                <X :size="19" />
            </button>
        </div>

        <!-- Workspace label -->
        <div
            class="mb-3 mt-10 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-white/40"
        >
            Workspace
        </div>

        <!-- Main navigation -->
        <nav class="space-y-1.5">
            <Link
                href="/dashboard"
                :class="navClass('/dashboard')"
                @click="closeMobileMenu"
            >
                <LayoutDashboard :size="19" :stroke-width="1.8" />
                <span>Overview</span>
            </Link>

            <Link
                href="/calendar"
                :class="navClass('/calendar')"
                @click="closeMobileMenu"
            >
                <CalendarDays :size="19" :stroke-width="1.8" />
                <span>My calendar</span>
            </Link>

            <Link
                href="/tasks"
                :class="navClass('/tasks')"
                @click="closeMobileMenu"
            >
                <CheckSquare :size="19" :stroke-width="1.8" />
                <span>Academic planner</span>
            </Link>

            <Link
                href="/subjects"
                :class="navClass('/subjects')"
                @click="closeMobileMenu"
            >
                <BookMarked :size="19" :stroke-width="1.8" />
                <span>My subjects</span>
            </Link>

            <!-- Finance navigation -->
            <div>
                <button
                    type="button"
                    :class="navClass('/finance')"
                    :aria-expanded="financeOpen"
                    aria-controls="finance-submenu"
                    @click="financeOpen = !financeOpen"
                >
                    <CircleDollarSign :size="19" :stroke-width="1.8" />

                    <span class="flex-1 text-left">Finance</span>

                    <ChevronDown
                        :size="16"
                        class="transition-transform duration-200"
                        :class="financeOpen ? 'rotate-180' : ''"
                    />
                </button>

                <div
                    v-show="financeOpen"
                    id="finance-submenu"
                    class="ml-4 mt-1 space-y-1 border-l border-white/20 pl-3"
                >
                    <Link
                        href="/finance"
                        :class="financeChildClass('/finance')"
                        @click="closeMobileMenu"
                    >
                        <LayoutDashboard :size="17" :stroke-width="1.8" />
                        <span>Finance overview</span>
                    </Link>

                    <Link
                        href="/finance/transactions"
                        :class="financeChildClass('/finance/transactions')"
                        @click="closeMobileMenu"
                    >
                        <ArrowLeftRight :size="17" :stroke-width="1.8" />
                        <span>Transactions</span>
                    </Link>

                    <Link
                        href="/finance/savings-goals"
                        :class="financeChildClass('/finance/savings-goals')"
                        @click="closeMobileMenu"
                    >
                        <PiggyBank :size="17" :stroke-width="1.8" />
                        <span>Savings goals</span>
                    </Link>

                    <Link
                        href="/finance/budgets"
                        :class="financeChildClass('/finance/budgets')"
                        @click="closeMobileMenu"
                    >
                        <Wallet :size="17" :stroke-width="1.8" />
                        <span>Budgets</span>
                    </Link>

                    <Link
                        href="/finance/bills"
                        :class="financeChildClass('/finance/bills')"
                        @click="closeMobileMenu"
                    >
                        <ReceiptText :size="17" :stroke-width="1.8" />
                        <span>Bills &amp; payments</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Encouragement card -->
        <div class="mt-8 rounded-2xl border border-white/10 bg-white/[0.06] p-4">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#DDE7D6] text-[#294B3C]"
            >
                <Heart :size="19" :stroke-width="1.8" />
            </div>

            <p class="mt-4 font-heading text-lg">
                A note for you
            </p>

            <p class="mt-2 text-xs leading-5 text-white/65">
                You don't have to figure everything out at once.
                Start with one task and one small step.
            </p>
        </div>

        <!-- Account section -->
        <div class="mt-auto border-t border-white/15 pt-5">
            <div class="mb-4 flex items-center gap-3 px-2">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E5D7C8] font-semibold text-[#294B3C]"
                >
                    {{ userName.charAt(0).toUpperCase() }}
                </div>

                <Link
                    href="/profile"
                    class="min-w-0 flex-1 rounded-lg py-1 transition hover:opacity-80"
                    @click="closeMobileMenu"
                >
                    <p class="truncate text-sm font-semibold">
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
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-white/70 transition hover:bg-white/10 hover:text-white"
                @click="closeMobileMenu"
            >
                <LogOut :size="18" :stroke-width="1.8" />
                <span>Log out</span>
            </Link>
        </div>
    </aside>
</template>
