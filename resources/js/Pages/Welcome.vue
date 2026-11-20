
<script setup>
import { Head, Link } from '@inertiajs/vue3'
import {
    BookOpen,
    CalendarDays,
    CheckCircle2,
    ArrowDownRight,
    ArrowRight,
    Wallet,
    Clock3,
    GraduationCap,
    ChartNoAxesCombined,
    CircleCheck,
    Leaf,
    Menu,
    X,
} from 'lucide-vue-next'
import { ref } from 'vue'

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
})

const mobileMenuOpen = ref(false)

const features = [
    {
        icon: CalendarDays,
        number: '01',
        title: 'Plan your days',
        description:
            'Keep classes, deadlines, exams, and personal commitments in one organized calendar.',
        color: 'bg-[#E7EBDD]',
    },
    {
        icon: Wallet,
        number: '02',
        title: 'Stay on budget',
        description:
            'Track your allowance, daily expenses, bills, and savings without losing sight of your goals.',
        color: 'bg-[#F0E2D5]',
    },
    {
        icon: CheckCircle2,
        number: '03',
        title: 'Get things done',
        description:
            'Turn busy weeks into manageable tasks and keep track of what still needs your attention.',
        color: 'bg-[#E9E3EF]',
    },
]
</script>

<template>
    <Head title="StuDesk — Your student life, organized" />

    <div class="min-h-screen overflow-hidden bg-[#F9F8F3] text-[#293B32]">
        <!-- Navigation -->
        <header class="relative z-30 border-b border-[#EAE8DF] bg-[#F9F8F3]/95">
            <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-12">
                <Link href="/" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#294B3C] text-[#F5F3E9]">
                        <BookOpen :size="22" :stroke-width="1.8" />
                    </div>
                    <div>
                        <span class="block text-xl font-bold tracking-tight text-[#294B3C]">StuDesk</span>
                        <span class="block text-[10px] tracking-[0.13em] text-[#858A7D]">YOUR STUDENT LIFE, ORGANIZED</span>
                    </div>
                </Link>

                <nav class="hidden items-center gap-8 md:flex">
                    <a href="#features" class="text-sm text-[#6F776D] transition hover:text-[#294B3C]">Features</a>
                    <a href="#how-it-works" class="text-sm text-[#6F776D] transition hover:text-[#294B3C]">How it works</a>
                </nav>

                <div v-if="canLogin" class="hidden items-center gap-3 md:flex">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            href="/dashboard"
                            class="rounded-xl px-4 py-2.5 text-sm font-medium text-[#294B3C] transition hover:bg-[#EAEDE4]"
                        >
                            Go to dashboard
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-xl px-4 py-2.5 text-sm font-medium text-[#294B3C] transition hover:bg-[#EAEDE4]"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#203D30]"
                        >
                            Get started
                            <ArrowRight :size="16" />
                        </Link>
                    </template>
                </div>

                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-[#294B3C] hover:bg-[#EAEDE4] md:hidden"
                    aria-label="Toggle navigation"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <X v-if="mobileMenuOpen" :size="22" />
                    <Menu v-else :size="22" />
                </button>
            </div>

            <!-- Mobile navigation -->
            <div v-if="mobileMenuOpen" class="border-t border-[#EAE8DF] bg-[#F9F8F3] px-5 py-4 md:hidden">
                <nav class="flex flex-col gap-1">
                    <a href="#features" class="rounded-xl px-3 py-3 text-sm hover:bg-[#EAEDE4]" @click="mobileMenuOpen = false">Features</a>
                    <a href="#how-it-works" class="rounded-xl px-3 py-3 text-sm hover:bg-[#EAEDE4]" @click="mobileMenuOpen = false">How it works</a>
                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth?.user"
                            href="/dashboard"
                            class="rounded-xl px-3 py-3 text-sm font-medium hover:bg-[#EAEDE4]"
                            @click="mobileMenuOpen = false"
                        >
                            Go to dashboard
                        </Link>
                        <template v-else>
                            <Link href="/login" class="rounded-xl px-3 py-3 text-sm hover:bg-[#EAEDE4]" @click="mobileMenuOpen = false">
                                Log in
                            </Link>
                            <Link v-if="canRegister" href="/register" class="rounded-xl bg-[#294B3C] px-3 py-3 text-sm font-medium text-white" @click="mobileMenuOpen = false">
                                Create your account
                            </Link>
                        </template>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main>
            <section class="relative">
                <div class="pointer-events-none absolute -right-32 -top-24 h-[440px] w-[440px] rounded-full bg-[#E9EDE1] opacity-70 blur-3xl"></div>
                <div class="pointer-events-none absolute -left-40 top-72 h-[300px] w-[300px] rounded-full bg-[#F0E3D6] opacity-50 blur-3xl"></div>

                <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-5 pb-20 pt-16 sm:px-8 sm:pt-24 lg:grid-cols-[0.95fr_1.05fr] lg:gap-12 lg:px-12 lg:pb-28 lg:pt-24">
                    <!-- Hero copy -->
                    <div class="max-w-2xl">
                        <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-[#DDE4D6] bg-white/70 px-3.5 py-2">
                            <span class="h-2 w-2 rounded-full bg-[#77916E]"></span>
                            <span class="text-xs font-medium tracking-wide text-[#526C53]">A little more organized, a lot less overwhelmed.</span>
                        </div>

                        <h1 class="text-[3.25rem] font-semibold leading-[1.07] tracking-[-0.055em] text-[#294B3C] sm:text-6xl lg:text-[4.5rem]">
                            Make room for
                            <span class="relative inline-block">
                                what matters.
                                <span class="absolute -bottom-1 left-0 -z-10 h-3 w-full rounded-full bg-[#D9E2CF]"></span>
                            </span>
                        </h1>

                        <p class="mt-7 max-w-xl text-base leading-8 text-[#737A70] sm:text-lg">
                            College is a lot to keep up with. StuDesk brings your
                            academic schedule, everyday tasks, and finances together
                            so you can spend less time juggling and more time moving forward.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <template v-if="canLogin && !$page.props.auth?.user">
                                <Link
                                    v-if="canRegister"
                                    href="/register"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-6 py-3.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(41,75,60,0.15)] transition hover:-translate-y-0.5 hover:bg-[#203D30]"
                                >
                                    Start organizing
                                    <ArrowRight :size="17" />
                                </Link>
                                <Link
                                    href="/login"
                                    class="inline-flex items-center justify-center rounded-xl border border-[#DFE2D8] bg-white/70 px-6 py-3.5 text-sm font-semibold text-[#294B3C] transition hover:bg-white"
                                >
                                    I already have an account
                                </Link>
                            </template>

                            <Link
                                v-else-if="$page.props.auth?.user"
                                href="/dashboard"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#203D30]"
                            >
                                Open my dashboard
                                <ArrowRight :size="17" />
                            </Link>
                        </div>

                        <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs text-[#858A7D]">
                            <span class="inline-flex items-center gap-2">
                                <CircleCheck :size="15" class="text-[#77916E]" />
                                Academic planning
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <CircleCheck :size="15" class="text-[#77916E]" />
                                Expense tracking
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <CircleCheck :size="15" class="text-[#77916E]" />
                                One organized space
                            </span>
                        </div>
                    </div>

                    <!-- Planner preview -->
                    <div class="relative mx-auto w-full max-w-[570px] lg:ml-auto">
                        <div class="absolute -right-4 -top-5 h-24 w-24 rounded-[28px] border border-[#DDE4D6] bg-[#E7EBDD] sm:-right-7 sm:-top-7"></div>
                        <div class="absolute -bottom-5 -left-5 h-28 w-28 rounded-full border-[12px] border-[#F0E2D5] sm:-bottom-7 sm:-left-7"></div>

                        <div class="relative rounded-[26px] border border-[#E7E4DA] bg-white p-4 shadow-[0_25px_80px_rgba(45,58,43,0.10)] sm:rounded-[30px] sm:p-6">
                            <!-- Preview top bar -->
                            <div class="flex items-center justify-between border-b border-[#F0EEE7] pb-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42654B]">
                                        <CalendarDays :size="20" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-[#293B32]">Your week at a glance</p>
                                        <p class="mt-1 text-xs text-[#92968B]">A little structure goes a long way.</p>
                                    </div>
                                </div>
                                <div class="rounded-lg bg-[#F5F5EF] px-3 py-1.5 text-xs font-medium text-[#687362]">Weekly</div>
                            </div>

                            <!-- Mini overview -->
                            <div class="mt-5 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl bg-[#F5F6F0] p-3.5">
                                    <div class="flex items-center gap-1.5 text-[#78816F]">
                                        <BookOpen :size="14" />
                                        <span class="text-[10px] font-medium sm:text-xs">Classes</span>
                                    </div>
                                    <p class="mt-2 text-2xl font-semibold tracking-tight text-[#294B3C]">04</p>
                                    <p class="mt-1 text-[10px] text-[#858A7D]">on your schedule</p>
                                </div>
                                <div class="rounded-2xl bg-[#F8F0E8] p-3.5">
                                    <div class="flex items-center gap-1.5 text-[#A37B5C]">
                                        <CheckCircle2 :size="14" />
                                        <span class="text-[10px] font-medium sm:text-xs">Tasks</span>
                                    </div>
                                    <p class="mt-2 text-2xl font-semibold tracking-tight text-[#795D47]">03</p>
                                    <p class="mt-1 text-[10px] text-[#A18A78]">to work on</p>
                                </div>
                                <div class="rounded-2xl bg-[#F0ECF5] p-3.5">
                                    <div class="flex items-center gap-1.5 text-[#88769D]">
                                        <Wallet :size="14" />
                                        <span class="text-[10px] font-medium sm:text-xs">Budget</span>
                                    </div>
                                    <p class="mt-2 text-2xl font-semibold tracking-tight text-[#68577C]">₱</p>
                                    <p class="mt-1 text-[10px] text-[#9387A1]">keep track</p>
                                </div>
                            </div>

                            <!-- Mini weekly calendar -->
                            <div class="mt-6">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-[#354638]">Upcoming</h3>
                                    <span class="text-xs text-[#92968B]">Your schedule</span>
                                </div>

                                <div class="space-y-2.5">
                                    <div class="flex items-center gap-3 rounded-xl border border-[#EAECE4] bg-[#FAFBF8] p-3">
                                        <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-[#E7EBDD]">
                                            <span class="text-[9px] font-semibold uppercase tracking-wide text-[#6E8065]">Mon</span>
                                            <span class="text-base font-semibold leading-5 text-[#294B3C]">12</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-semibold text-[#354638] sm:text-sm">Lecture & study session</p>
                                            <p class="mt-1 flex items-center gap-1 text-[10px] text-[#8B9084] sm:text-xs">
                                                <Clock3 :size="12" />
                                                9:00 AM – 11:00 AM
                                            </p>
                                        </div>
                                        <span class="h-8 w-1 rounded-full bg-[#829777]"></span>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl border border-[#F0E7DE] bg-[#FCF9F5] p-3">
                                        <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-[#F0E2D5]">
                                            <span class="text-[9px] font-semibold uppercase tracking-wide text-[#A17C5D]">Tue</span>
                                            <span class="text-base font-semibold leading-5 text-[#795D47]">13</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-semibold text-[#354638] sm:text-sm">Project submission</p>
                                            <p class="mt-1 flex items-center gap-1 text-[10px] text-[#8B9084] sm:text-xs">
                                                <Clock3 :size="12" />
                                                Due by 5:00 PM
                                            </p>
                                        </div>
                                        <span class="h-8 w-1 rounded-full bg-[#C59A78]"></span>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl border border-[#EAE5F0] bg-[#FAF8FC] p-3">
                                        <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-[#E9E3EF]">
                                            <span class="text-[9px] font-semibold uppercase tracking-wide text-[#8C7AA0]">Wed</span>
                                            <span class="text-base font-semibold leading-5 text-[#68577C]">14</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-semibold text-[#354638] sm:text-sm">Review weekly expenses</p>
                                            <p class="mt-1 flex items-center gap-1 text-[10px] text-[#8B9084] sm:text-xs">
                                                <Clock3 :size="12" />
                                                15 minutes to reset
                                            </p>
                                        </div>
                                        <span class="h-8 w-1 rounded-full bg-[#A795BD]"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center gap-3 rounded-2xl bg-[#294B3C] p-4 text-white">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                                    <GraduationCap :size="21" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold sm:text-sm">One step at a time.</p>
                                    <p class="mt-1 text-[10px] leading-4 text-white/65 sm:text-xs">Your academics and finances, finally in one place.</p>
                                </div>
                                <ArrowDownRight :size="19" class="shrink-0 text-[#D8E2CC]" />
                            </div>
                        </div>

                        <div class="absolute -left-5 top-28 hidden items-center gap-2 rounded-2xl border border-[#EAE7DE] bg-white px-4 py-3 shadow-lg sm:flex">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#587351]">
                                <CircleCheck :size="19" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#354638]">A clearer week</p>
                                <p class="mt-1 text-[10px] text-[#858A7D]">One thing at a time</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Small intro strip -->
            <section class="border-y border-[#EAE8DF] bg-[#F2F2EA]">
                <div class="mx-auto grid max-w-7xl gap-4 px-5 py-8 sm:px-8 md:grid-cols-[1fr_auto] md:items-center lg:px-12">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#7E8A75]">LESS JUGGLING. MORE LIVING.</p>
                        <p class="mt-2 max-w-2xl text-lg font-medium leading-7 text-[#354638] sm:text-xl">
                            Your deadlines, spending, and everyday plans belong together.
                        </p>
                    </div>
                    <div class="hidden h-12 w-12 items-center justify-center rounded-2xl border border-[#DDE2D5] bg-[#FAFAF6] text-[#587351] md:flex">
                        <ChartNoAxesCombined :size="23" />
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="scroll-mt-10 px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
                <div class="mx-auto max-w-7xl">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7E8A75]">MADE FOR STUDENT LIFE</p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-tight text-[#294B3C] sm:text-4xl">
                            Everything has its place.
                        </h2>
                        <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-[#777D73] sm:text-base">
                            A practical space for the things that compete for your time,
                            attention, and allowance.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-5 md:grid-cols-3 lg:mt-16">
                        <article
                            v-for="feature in features"
                            :key="feature.number"
                            class="group rounded-[24px] border border-[#E8E6DD] bg-white p-7 transition duration-300 hover:-translate-y-1 hover:border-[#D5DDCE] hover:shadow-[0_18px_45px_rgba(45,58,43,0.07)] sm:p-8"
                        >
                            <div class="flex items-center justify-between">
                                <div :class="['flex h-12 w-12 items-center justify-center rounded-2xl text-[#42654B]', feature.color]">
                                    <component :is="feature.icon" :size="23" :stroke-width="1.7" />
                                </div>
                                <span class="text-xs font-medium tracking-widest text-[#B5B8AC]">{{ feature.number }}</span>
                            </div>

                            <h3 class="mt-7 text-lg font-semibold text-[#354638]">{{ feature.title }}</h3>
                            <p class="mt-3 text-sm leading-7 text-[#777D73]">{{ feature.description }}</p>

                            <div class="mt-6 flex items-center gap-2 text-xs font-semibold text-[#64805D] transition group-hover:gap-3">
                                Built for your routine
                                <ArrowRight :size="14" />
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- How it works -->
            <section id="how-it-works" class="scroll-mt-10 bg-[#EAEDE4] px-5 py-20 sm:px-8 lg:px-12 lg:py-24">
                <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center lg:gap-20">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#71816A]">A SIMPLER ROUTINE</p>
                        <h2 class="mt-4 text-3xl font-semibold leading-tight tracking-tight text-[#294B3C] sm:text-4xl">
                            Get organized without making life more complicated.
                        </h2>
                        <p class="mt-5 text-sm leading-7 text-[#737A70] sm:text-base">
                            StuDesk is designed to help you see the bigger picture,
                            from your next school deadline to the money you have left
                            for the week.
                        </p>

                        <Link
                            v-if="canLogin && !$page.props.auth?.user && canRegister"
                            href="/register"
                            class="mt-8 inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[#203D30]"
                        >
                            Create your account
                            <ArrowRight :size="16" />
                        </Link>
                        <Link
                            v-else-if="$page.props.auth?.user"
                            href="/dashboard"
                            class="mt-8 inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[#203D30]"
                        >
                            Return to StuDesk
                            <ArrowRight :size="16" />
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-4 rounded-2xl border border-white/80 bg-white/75 p-5 sm:p-6">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#42654B]">
                                <CalendarDays :size="21" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#354638]">Bring your schedule together</h3>
                                <p class="mt-2 text-sm leading-6 text-[#777D73]">See classes, assignments, deadlines, and personal plans in one calendar.</p>
                            </div>
                        </div>

                        <div class="flex gap-4 rounded-2xl border border-white/80 bg-white/75 p-5 sm:p-6">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#F0E2D5] text-[#9B7455]">
                                <Wallet :size="21" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#354638]">Connect planning with your finances</h3>
                                <p class="mt-2 text-sm leading-6 text-[#777D73]">Keep track of expenses, budgets, bills, and savings alongside your academic responsibilities.</p>
                            </div>
                        </div>

                        <div class="flex gap-4 rounded-2xl border border-white/80 bg-white/75 p-5 sm:p-6">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E9E3EF] text-[#806C98]">
                                <CheckCircle2 :size="21" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#354638]">Make progress at your own pace</h3>
                                <p class="mt-2 text-sm leading-6 text-[#777D73]">Break your workload into manageable tasks and build a routine that works for you.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-[#294B3C] px-5 py-10 text-white sm:px-8 lg:px-12">
            <div class="mx-auto flex max-w-7xl flex-col gap-7 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-[#E5EBD9]">
                        <BookOpen :size="21" />
                    </div>
                    <div>
                        <p class="text-lg font-semibold tracking-tight">StuDesk</p>
                        <p class="mt-1 text-xs text-white/55">Your student life, organized.</p>
                    </div>
                </Link>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-3 text-xs text-white/65">
                    <a href="#features" class="transition hover:text-white">Features</a>
                    <a href="#how-it-works" class="transition hover:text-white">How it works</a>
                    <Link v-if="canLogin && !$page.props.auth?.user" href="/login" class="transition hover:text-white">Log in</Link>
                    <Link v-else-if="$page.props.auth?.user" href="/dashboard" class="transition hover:text-white">Dashboard</Link>
                </div>

                <p class="text-xs text-white/45">A little more organized, every day.</p>
            </div>
        </footer>
    </div>
</template>
