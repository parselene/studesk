
<script setup>
import { Link } from '@inertiajs/vue3';
import { BookOpen, CalendarDays } from 'lucide-vue-next';
</script>

<template>
    <div class="min-h-screen bg-[#F7F5F0] text-[#263D32] lg:grid lg:grid-cols-2">
        <!-- Left branding panel -->
        <section
            class="relative hidden min-h-screen flex-col justify-between overflow-hidden bg-[#294B3C] p-12 text-[#F7F5F0] lg:flex xl:p-16"
        >
            <!-- Decorative circles -->
            <div
                class="pointer-events-none absolute -right-28 top-28 h-80 w-80 rounded-full border border-white/10"
            ></div>

            <div
                class="pointer-events-none absolute -right-12 top-44 h-48 w-48 rounded-full border border-white/10"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-[#C77D68]/20"
            ></div>

            <!-- Brand logo -->
            <Link href="/" class="relative z-10 flex w-fit items-center gap-3">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F0E9DC] text-[#294B3C]"
                >
                    <BookOpen :size="25" :stroke-width="1.8" />
                </div>

                <span class="text-2xl font-semibold tracking-tight">
                    StuDesk
                </span>
            </Link>

            <!-- Branding content -->
            <div class="relative z-10 max-w-lg py-12">
                <p
                    class="mb-5 text-xs font-semibold uppercase tracking-[0.25em] text-[#E7B4A3]"
                >
                    Make room for what matters
                </p>

                <h1
                    class="text-5xl font-medium leading-[1.12] tracking-tight xl:text-6xl"
                >
                    Your plans,<br />
                    your priorities,<br />
                    <span class="font-serif italic text-[#E7B4A3]">
                        in balance.
                    </span>
                </h1>

                <p class="mt-7 max-w-md text-base leading-7 text-white/75">
                    Bring your school deadlines, everyday expenses, and
                    personal goals into one calmer, more organized space.
                </p>

                <!-- Preview card -->
                <div
                    class="mt-12 max-w-sm rounded-2xl border border-white/15 bg-white/[0.07] p-5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">
                                A little more in sync
                            </p>

                            <p class="mt-1 text-xs text-white/60">
                                School, spending, and everything between
                            </p>
                        </div>

                        <!-- Calendar icon -->
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E7B4A3] text-[#294B3C]"
                        >
                            <CalendarDays :size="21" :stroke-width="1.8" />
                        </div>
                    </div>

                    <!-- App categories -->
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full bg-[#E7B4A3]/20 px-3 py-1.5 text-xs text-[#F2C9BC]"
                        >
                            Academic
                        </span>

                        <span
                            class="rounded-full bg-[#C9C0E8]/20 px-3 py-1.5 text-xs text-[#DCD4F2]"
                        >
                            Financial
                        </span>

                        <span
                            class="rounded-full bg-white/10 px-3 py-1.5 text-xs text-white/80"
                        >
                            Personal
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="relative z-10 text-xs text-white/50">
                StuDesk · Your student life, organized.
            </p>
        </section>

        <!-- Registration and login area -->
        <main
            class="flex min-h-screen flex-col items-center justify-center px-5 py-10 sm:px-10 lg:px-12"
        >
            <!-- Mobile branding -->
            <Link
                href="/"
                class="mb-10 flex items-center gap-2.5 lg:hidden"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#294B3C] text-[#F7F5F0]"
                >
                    <BookOpen :size="22" :stroke-width="1.8" />
                </span>

                <span
                    class="text-2xl font-semibold tracking-tight text-[#294B3C]"
                >
                    StuDesk
                </span>
            </Link>

            <!-- Login / registration form -->
            <div class="w-full max-w-md">
                <slot />
            </div>

            <!-- Footer message -->
            <p class="mt-10 text-center text-xs text-[#817F76]">
                Take it one day at a time.
            </p>
        </main>
    </div>
</template>
