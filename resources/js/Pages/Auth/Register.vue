<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create account | StuDesk" />

        <div class="mb-8">
            <div
                class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#DCE5D8] bg-[#EAF0E6] px-3.5 py-2 text-xs font-semibold tracking-wide text-[#294B3C]"
            >
                <span class="h-2 w-2 rounded-full bg-[#66866A]"></span>
                YOUR FRESH START
            </div>

            <h1
                class="text-4xl font-semibold tracking-tight text-[#263D32] sm:text-[2.65rem]"
            >
                Create your space.
            </h1>

            <p class="mt-3 max-w-sm text-sm leading-6 text-[#77796F]">
                Bring your studies, schedule, and finances together with StuDesk.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel
                    for="name"
                    value="Full name"
                    class="!text-sm !font-medium !text-[#35483B]"
                />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full !rounded-xl !border-[#E5E1D8] !bg-[#FDFCF9] !px-4 !py-3 !text-sm !text-[#263D32] placeholder:!text-[#AAA99F] focus:!border-[#66866A] focus:!ring-[#66866A]/20"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel
                    for="email"
                    value="Email address"
                    class="!text-sm !font-medium !text-[#35483B]"
                />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full !rounded-xl !border-[#E5E1D8] !bg-[#FDFCF9] !px-4 !py-3 !text-sm !text-[#263D32] placeholder:!text-[#AAA99F] focus:!border-[#66866A] focus:!ring-[#66866A]/20"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel
                    for="password"
                    value="Password"
                    class="!text-sm !font-medium !text-[#35483B]"
                />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full !rounded-xl !border-[#E5E1D8] !bg-[#FDFCF9] !px-4 !py-3 !text-sm !text-[#263D32] placeholder:!text-[#AAA99F] focus:!border-[#66866A] focus:!ring-[#66866A]/20"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm password"
                    class="!text-sm !font-medium !text-[#35483B]"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full !rounded-xl !border-[#E5E1D8] !bg-[#FDFCF9] !px-4 !py-3 !text-sm !text-[#263D32] placeholder:!text-[#AAA99F] focus:!border-[#66866A] focus:!ring-[#66866A]/20"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Enter your password again"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="!flex !w-full !justify-center !rounded-xl !border-0 !bg-[#294B3C] !px-4 !py-3.5 !text-sm !font-semibold !text-white !shadow-sm transition duration-200 hover:!bg-[#203D30] focus:!ring-[#66866A] focus:!ring-offset-2"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Creating your account...' : 'Create account' }}
                </PrimaryButton>
            </div>

            <p class="pt-2 text-center text-sm text-[#77796F]">
                Already have an account?
                <Link
                    href="/login"
                    class="ml-1 font-semibold text-[#294B3C] underline-offset-4 hover:underline"
                >
                    Log in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
