
<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in | StuDesk" />

        <div class="mb-8">
            <span class="inline-flex items-center rounded-full bg-[#E5EBDD] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#42654B]">
                Welcome back
            </span>

            <h1 class="mt-5 text-3xl font-semibold tracking-tight text-[#293B32] sm:text-[34px]">
                Pick up where you left off.
            </h1>

            <p class="mt-2 text-sm leading-6 text-[#7D8177]">
                Your plans are waiting. Sign in to get organized.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-5 rounded-xl border border-[#D5E4D1] bg-[#EEF4EA] px-4 py-3 text-sm text-[#42654B]"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel
                    for="email"
                    value="Email address"
                    class="!text-sm !font-medium !text-[#435447]"
                />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full !rounded-xl !border-[#E5E1D8] !bg-[#FDFCF9] !px-4 !py-3 !text-sm focus:!border-[#78957A] focus:!ring-[#78957A]"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                />

                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="!text-sm !font-medium !text-[#435447]"
                    />

                    <Link
                        v-if="canResetPassword"
                        href="/forgot-password"
                        class="text-xs font-medium text-[#42654B] underline-offset-4 hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full !rounded-xl !border-[#E5E1D8] !bg-[#FDFCF9] !px-4 !py-3 !text-sm focus:!border-[#78957A] focus:!ring-[#78957A]"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox
                    name="remember"
                    v-model:checked="form.remember"
                    class="!rounded-md !border-[#D7D5CA] !text-[#42654B] focus:!ring-[#78957A]"
                />

                <label for="remember" class="ms-2 text-sm text-[#777D73]">
                    Remember me
                </label>
            </div>

            <div class="pt-1">
                <PrimaryButton
                    class="w-full justify-center !rounded-xl !border-transparent !bg-[#294B3C] !px-4 !py-3 !text-sm !font-semibold !text-white shadow-sm transition hover:!bg-[#203D30] focus:!ring-[#78957A] focus:!ring-offset-2"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Signing you in...' : 'Log in to StuDesk →' }}
                </PrimaryButton>
            </div>

            <p class="pt-3 text-center text-sm text-[#7D8177]">
                New to StuDesk?
                <Link
                    href="/register"
                    class="font-semibold text-[#42654B] underline-offset-4 hover:underline"
                >
                    Create an account
                </Link>
            </p>
        </form>

        <p class="mt-8 text-center text-[11px] leading-5 text-[#99998D]">
            Your student life, organized.
        </p>
    </GuestLayout>
</template>
