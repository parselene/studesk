
<script setup>
import { ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { UserRound, Save, CheckCircle2 } from 'lucide-vue-next'

defineOptions({
    layout: AppLayout,
})

const page = usePage()
const user = page.props.auth?.user ?? {}

const form = useForm({
    name: user.name ?? '',
    email: user.email ?? '',
})

function submit() {
    form.patch('/profile', {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="My Account | StuDesk" />

    <main class="mx-auto max-w-3xl pb-10">
        <div class="mb-8">
            <p class="text-xs font-medium uppercase tracking-[0.17em] text-[#8B8C7D]">
                ACCOUNT SETTINGS
            </p>

            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#294B3C]">
                My account
            </h1>

            <p class="mt-2 text-sm text-[#777D73]">
                Manage your personal information and keep your account up to date.
            </p>
        </div>

        <section class="overflow-hidden rounded-2xl border border-[#EAE6DD] bg-white">
            <div class="flex items-center gap-4 border-b border-[#EEEAE2] p-6">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E4EBDD] text-[#42654B]">
                    <UserRound :size="26" :stroke-width="1.7" />
                </div>

                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-[#293B32]">
                        Personal information
                    </h2>
                    <p class="mt-1 text-sm text-[#85877D]">
                        Update the name and email associated with your account.
                    </p>
                </div>
            </div>

            <form class="space-y-5 p-6" @submit.prevent="submit">
                <div
                    v-if="page.props.flash?.success"
                    class="flex items-center gap-2 rounded-xl bg-[#E4EBDD] p-3 text-sm text-[#42654B]"
                >
                    <CheckCircle2 :size="18" />
                    {{ page.props.flash.success }}
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-[#354638]">
                        Full name
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        required
                        class="w-full rounded-xl border border-[#E2DED4] bg-[#FDFCF9] px-4 py-3 text-sm text-[#293B32] outline-none transition focus:border-[#789477] focus:ring-2 focus:ring-[#789477]/15"
                    />

                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-[#354638]">
                        Email address
                    </label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="w-full rounded-xl border border-[#E2DED4] bg-[#FDFCF9] px-4 py-3 text-sm text-[#293B32] outline-none transition focus:border-[#789477] focus:ring-2 focus:ring-[#789477]/15"
                    />

                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#EEEAE2] pt-5">
                    <p class="text-xs text-[#85877D]">
                        Your changes will be saved to your account.
                    </p>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#203D30] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Save :size="17" />
                        {{ form.processing ? 'Saving...' : 'Save changes' }}
                    </button>
                </div>
            </form>
        </section>

        <div class="mt-5 rounded-2xl border border-[#EAE6DD] bg-[#F7F5F0] p-5">
            <h2 class="text-sm font-semibold text-[#354638]">
                Account security
            </h2>
            <p class="mt-2 text-sm leading-6 text-[#777D73]">
                Keep your password private and use a strong password to protect your student account.
            </p>
            <p class="mt-2 text-xs text-[#85877D]">
                Password changes can be added using Laravel Breeze's existing password-update functionality.
            </p>
        </div>
    </main>
</template>
