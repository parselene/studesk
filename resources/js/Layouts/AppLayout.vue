
<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    Bell,
    CheckCheck,
    X,
} from 'lucide-vue-next'

import AppSidebar from '@/Components/AppSidebar.vue'
import QuickTransactionModal from '@/Components/QuickTransactionModal.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'StuDesk',
    },
})

const page = usePage()

const showNotifications = ref(false)

const notifications = computed(() => {
    return page.props.notifications?.recent ?? []
})

const unreadCount = computed(() => {
    return page.props.notifications?.unread_count ?? 0
})

function markAsRead(notification) {
    router.patch(
        `/notifications/${notification.id}/read`,
        {},
        {
            preserveScroll: true,
        }
    )
}

function openNotification(notification) {
    const targetUrl = notification.data?.url

    if (!notification.read_at) {
        router.patch(
            `/notifications/${notification.id}/read`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showNotifications.value = false

                    if (targetUrl) {
                        router.visit(targetUrl)
                    }
                },
            }
        )
    } else {
        showNotifications.value = false

        if (targetUrl) {
            router.visit(targetUrl)
        }
    }
}

function markAllAsRead() {
    router.post(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
        }
    )
}

function formatNotificationDate(date) {
    if (!date) return ''

    return new Date(date).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    })
}
</script>

<template>
    <div class="min-h-screen bg-[#F7F5F0] text-[#293B32] md:flex">
        <Head :title="props.title" />

        <!-- Sidebar -->
        <AppSidebar />

        <!-- Main Content -->
        <main
            class="min-w-0 min-h-screen flex-1 px-4 py-6
                   sm:px-7 sm:py-8 lg:px-10 md:ml-[248px]"
        >
            <!-- Top Bar -->
            <header class="mb-6 flex items-center justify-end">
                <div class="relative">
                    <!-- Notification Bell -->
                    <button
                        type="button"
                        @click="showNotifications = !showNotifications"
                        class="relative flex h-11 w-11 items-center
                               justify-center rounded-xl border
                               border-[#E8E5DD] bg-white
                               text-[#294B3C] shadow-sm
                               transition hover:bg-[#F0EEE7]"
                        aria-label="Notifications"
                        :aria-expanded="showNotifications"
                    >
                        <Bell :size="20" :stroke-width="1.8" />

                        <span
                            v-if="unreadCount > 0"
                            class="absolute -right-1 -top-1 flex h-5
                                   min-w-5 items-center justify-center
                                   rounded-full bg-[#B76C54] px-1
                                   text-[10px] font-bold text-white"
                        >
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div
                        v-if="showNotifications"
                        class="absolute right-0 top-14 z-50 w-[340px]
                               max-w-[calc(100vw-2rem)] overflow-hidden
                               rounded-2xl border border-[#E8E5DD]
                               bg-white shadow-[0_12px_32px_rgba(41,59,50,0.12)]"
                    >
                        <!-- Dropdown Header -->
                        <div
                            class="flex items-center justify-between
                                   border-b border-[#EEECE5] px-4 py-4"
                        >
                            <div>
                                <h3 class="font-heading text-xl text-[#294B3C]">
                                    Notifications
                                </h3>

                                <p class="mt-1 text-xs text-[#758477]">
                                    {{ unreadCount }} unread
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    v-if="unreadCount > 0"
                                    type="button"
                                    @click="markAllAsRead"
                                    class="flex items-center gap-1.5
                                           text-xs font-medium
                                           text-[#54705D]
                                           transition hover:text-[#B76C54]"
                                >
                                    <CheckCheck :size="15" />
                                    Mark all as read
                                </button>

                                <button
                                    type="button"
                                    @click="showNotifications = false"
                                    aria-label="Close notifications"
                                    class="flex h-7 w-7 items-center
                                           justify-center rounded-lg
                                           text-[#92968E] transition
                                           hover:bg-[#F0EEE7]
                                           hover:text-[#294B3C]"
                                >
                                    <X :size="16" />
                                </button>
                            </div>
                        </div>

                        <!-- Notification List -->
                        <div class="max-h-[400px] overflow-y-auto">
                            <!-- Empty State -->
                            <div
                                v-if="notifications.length === 0"
                                class="px-5 py-10 text-center"
                            >
                                <div
                                    class="mx-auto mb-3 flex h-12 w-12
                                           items-center justify-center
                                           rounded-2xl bg-[#E7EBDD]
                                           text-[#54705D]"
                                >
                                    <Bell :size="22" />
                                </div>

                                <p class="font-heading text-lg text-[#294B3C]">
                                    You're all caught up!
                                </p>

                                <p class="mt-1 text-xs text-[#92968E]">
                                    New reminders will appear here.
                                </p>
                            </div>

                            <!-- Notification Items -->
                            <button
                                v-for="notification in notifications"
                                :key="notification.id"
                                type="button"
                                @click="openNotification(notification)"
                                class="flex w-full gap-3 border-b
                                       border-[#F0EEE7] px-4 py-4
                                       text-left transition last:border-b-0
                                       hover:bg-[#F9F8F4]"
                                :class="{
                                    'bg-[#F7F8F3]': !notification.read_at
                                }"
                            >
                                <!-- Status Indicator -->
                                <div class="mt-1.5 shrink-0">
                                    <span
                                        class="block h-2.5 w-2.5 rounded-full"
                                        :class="
                                            notification.read_at
                                                ? 'bg-[#D8D8CF]'
                                                : 'bg-[#B76C54]'
                                        "
                                    ></span>
                                </div>

                                <!-- Notification Content -->
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold
                                               text-[#293B32]"
                                    >
                                        {{
                                            notification.data?.title
                                            || notification.data?.message
                                            || 'New notification'
                                        }}
                                    </p>

                                    <p
                                        v-if="
                                            notification.data?.title
                                            && notification.data?.message
                                        "
                                        class="mt-1 text-xs leading-relaxed
                                               text-[#758477]"
                                    >
                                        {{ notification.data.message }}
                                    </p>

                                    <p
                                        class="mt-2 text-[11px] text-[#92968E]"
                                    >
                                        {{
                                            formatNotificationDate(
                                                notification.created_at
                                            )
                                        }}
                                    </p>

                                    <span
                                        v-if="!notification.read_at"
                                        class="mt-2 inline-block text-xs
                                               font-medium text-[#54705D]"
                                    >
                                        Unread
                                    </span>
                                </div>
                            </button>
                        </div>

                        <!-- Dropdown Footer -->
                        <div
                            class="border-t border-[#EEECE5]
                                   bg-[#F7F5F0] px-4 py-3 text-center"
                        >
                            <button
                                type="button"
                                @click="showNotifications = false"
                                class="text-xs font-medium
                                       text-[#758477] transition
                                       hover:text-[#294B3C]"
                            >
                                Close notifications
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <slot />
        </main>

        <!-- Global Quick Add -->
        <QuickTransactionModal />
    </div>
</template>
