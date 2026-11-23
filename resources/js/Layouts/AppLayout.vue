
<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Bell, CheckCheck } from 'lucide-vue-next'
import AppSidebar from '@/Components/AppSidebar.vue'

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

        <AppSidebar />

        <main
            class="min-w-0 min-h-screen flex-1 px-4 py-6
                   sm:px-7 sm:py-8 lg:px-10 md:ml-[248px]"
        >
            <!-- Top Bar -->
            <div class="flex justify-end mb-6">
                <div class="relative">
                    <!-- Notification Bell -->
                    <button
                        type="button"
                        @click="showNotifications = !showNotifications"
                        class="relative flex h-11 w-11 items-center justify-center
                               rounded-xl border border-[#E5E2D9] bg-white
                               text-[#293B32] transition hover:bg-[#F0EEE7]"
                        aria-label="Notifications"
                    >
                        <Bell class="h-5 w-5" />

                        <span
                            v-if="unreadCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5
                                   items-center justify-center rounded-full
                                   bg-[#D98F7C] px-1 text-[10px] font-bold
                                   text-white"
                        >
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div
                        v-if="showNotifications"
                        class="absolute right-0 top-14 z-50 w-[340px]
                               max-w-[calc(100vw-2rem)] overflow-hidden
                               rounded-2xl border border-[#E5E2D9]
                               bg-white shadow-xl"
                    >
                        <!-- Dropdown Header -->
                        <div
                            class="flex items-center justify-between
                                   border-b border-[#EEECE5] px-4 py-4"
                        >
                            <div>
                                <h3 class="font-semibold text-[#293B32]">
                                    Notifications
                                </h3>

                                <p class="mt-0.5 text-xs text-[#8B9189]">
                                    {{ unreadCount }} unread
                                </p>
                            </div>

                            <button
                                v-if="unreadCount > 0"
                                type="button"
                                @click="markAllAsRead"
                                class="flex items-center gap-1.5 text-xs
                                       font-medium text-[#174A43]
                                       hover:text-[#D98F7C]"
                            >
                                <CheckCheck class="h-4 w-4" />
                                Mark all as read
                            </button>
                        </div>

                        <!-- Notification List -->
                        <div class="max-h-[400px] overflow-y-auto">
                            <div
                                v-if="notifications.length === 0"
                                class="px-5 py-10 text-center"
                            >
                                <Bell
                                    class="mx-auto mb-3 h-8 w-8
                                           text-[#C9C8BF]"
                                />

                                <p class="text-sm font-medium text-[#293B32]">
                                    You're all caught up!
                                </p>

                                <p class="mt-1 text-xs text-[#92968E]">
                                    New reminders will appear here.
                                </p>
                            </div>

                            <div
                                v-for="notification in notifications"
                                :key="notification.id"
                                class="flex gap-3 border-b border-[#F0EEE7]
                                    px-4 py-4 last:border-b-0"
                                :class="{
                                    'bg-[#F8F7F2]': !notification.read_at
                                }"
                            >
                                <!-- Status Indicator -->
                                <div
                                    class="mt-1 h-2.5 w-2.5 shrink-0
                                           rounded-full"
                                    :class="
                                        notification.read_at
                                            ? 'bg-[#D8D8CF]'
                                            : 'bg-[#D98F7C]'
                                    "
                                ></div>

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
                                               text-[#737B72]"
                                    >
                                        {{ notification.data.message }}
                                    </p>

                                    <p
                                        class="mt-2 text-[11px] text-[#9A9D95]"
                                    >
                                        {{
                                            formatNotificationDate(
                                                notification.created_at
                                            )
                                        }}
                                    </p>

                                    <button
                                        v-if="!notification.read_at"
                                        type="button"
                                        @click.stop="markAsRead(notification)"
                                        class="mt-2 text-xs font-medium
                                               text-[#174A43] hover:underline"
                                    >
                                        Mark as read
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Footer -->
                        <div
                            class="border-t border-[#EEECE5] bg-[#FAF9F6]
                                   px-4 py-3 text-center"
                        >
                            <button
                                type="button"
                                @click="showNotifications = false"
                                class="text-xs font-medium text-[#737B72]
                                       hover:text-[#293B32]"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <slot />
        </main>
    </div>
</template>
