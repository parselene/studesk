
<script setup>
import { Head } from '@inertiajs/vue3'
import AppSidebar from '@/Components/AppSidebar.vue'

defineProps({
    title: {
        type: String,
        default: 'StuDesk',
    },
})
</script>

<template>
    <div class="min-h-screen bg-[#F7F5F0] text-[#293B32] md:flex">
        <Head :title="title" />

        <AppSidebar />

        <main
            class="min-w-0 min-h-screen flex-1 px-4 py-6
                   sm:px-7 sm:py-8 lg:px-10 md:ml-[248px]"
        >
            <slot />
        </main>
    </div>
</template>
