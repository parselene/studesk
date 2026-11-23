<script setup>
import { computed, ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import {
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Clock,
    Plus,
    Search,
    X,
    ArrowUpRight,
    Pencil,
    Trash2,
    BookOpen,
    Wallet,
    Sparkles,
} from 'lucide-vue-next'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    tasks: {
        type: Array,
        default: () => [],
    },
    bills: {
        type: Array,
        default: () => [],
    },
    savingsGoals: {
        type: Array,
        default: () => [],
    },
})

const today = new Date()
const currentMonth = ref(new Date(today.getFullYear(), today.getMonth(), 1))
const selectedDate = ref(toDateKey(today))
const showModal = ref(false)
const searchQuery = ref('')

const form = useForm({
    eventId: null,
    title: '',
    description: '',
    event_type: 'academic',
    starts_at: '',
    ends_at: '',
})

function toDateKey(date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

function parseDate(value) {
    if (!value) return null

    // Parse Laravel date strings as local time to avoid date shifts.
    const normalized = String(value).replace(' ', 'T')
    const match = normalized.match(
        /^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2}))?/
    )

    if (!match) return new Date(value)

    return new Date(
        Number(match[1]),
        Number(match[2]) - 1,
        Number(match[3]),
        Number(match[4] || 0),
        Number(match[5] || 0)
    )
}

function dateKeyForEvent(event) {
    const date = parseDate(event.starts_at)
    return date ? toDateKey(date) : ''
}

function formatTime(value) {
    const date = parseDate(value)

    if (!date) return ''

    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    })
}

function formatLongDate(value) {
    const date = parseDate(value)

    if (!date) return ''

    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    })
}

const monthLabel = computed(() =>
    currentMonth.value.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    })
)

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear()
    const month = currentMonth.value.getMonth()
    const firstDay = new Date(year, month, 1)

    // Monday-first calendar.
    const offset = (firstDay.getDay() + 6) % 7
    const gridStart = new Date(year, month, 1 - offset)

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(gridStart)
        date.setDate(gridStart.getDate() + index)

        const key = toDateKey(date)

        return {
            date,
            key,
            day: date.getDate(),
            inMonth: date.getMonth() === month,
            isToday: key === toDateKey(today),
        }
    })
})

const weekdayNames = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
]

function timePart(value) {
    if (!value) return '09:00'

    return String(value).slice(0, 5)
}

function dayMatchesClass(dayValue, date) {
    if (!Array.isArray(dayValue)) return false

    return dayValue.some(value => {
        const normalized = String(value).trim().toLowerCase()

        return normalized === weekdayNames[date.getDay()].toLowerCase()
            || normalized === weekdayNames[date.getDay()].slice(0, 3).toLowerCase()
            || normalized === String(date.getDay())
    })
}

const allCalendarItems = computed(() => {
    const items = []

    // Manually created calendar events.
    props.events.forEach(event => {
        items.push({
            ...event,
            id: `event-${event.id}`,
            source: 'event',
            sourceId: event.id,
            event_type: event.event_type || 'personal',
            starts_at: event.starts_at,
            link: null,
        })
    })

    // Tasks with due dates.
    props.tasks.forEach(task => {
        items.push({
            id: `task-${task.id}`,
            source: 'task',
            sourceId: task.id,
            title: task.title,
            description: task.description,
            event_type: 'task',
            starts_at: task.due_at,
            ends_at: null,
            status: task.status,
            priority: task.priority,
            subtitle: task.subject?.name || task.task_type || 'Task',
            link: '/tasks',
        })
    })

    // Bills with due dates.
    props.bills.forEach(bill => {
        items.push({
            id: `bill-${bill.id}`,
            source: 'bill',
            sourceId: bill.id,
            title: bill.title,
            description: `Amount: ₱${Number(bill.amount).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })}`,
            event_type: 'bill',
            starts_at: bill.due_date,
            ends_at: null,
            status: bill.status,
            amount: bill.amount,
            link: '/finance/bills',
        })
    })

    // Savings goals with target dates.
    props.savingsGoals.forEach(goal => {
        const progress = Number(goal.target_amount) > 0
            ? Math.min(
                100,
                Math.round(
                    Number(goal.current_amount) /
                    Number(goal.target_amount) * 100
                )
            )
            : 0

        items.push({
            id: `goal-${goal.id}`,
            source: 'goal',
            sourceId: goal.id,
            title: `Savings: ${goal.name}`,
            description: `${progress}% saved · ₱${Number(goal.current_amount).toLocaleString('en-PH')} of ₱${Number(goal.target_amount).toLocaleString('en-PH')}`,
            event_type: 'goal',
            starts_at: goal.target_date,
            ends_at: null,
            status: goal.status,
            progress,
            link: '/finance/savings-goals',
        })
    })

    // Generate class occurrences for the displayed calendar grid.
    const year = currentMonth.value.getFullYear()
    const month = currentMonth.value.getMonth()
    const firstOfMonth = new Date(year, month, 1)
    const offset = (firstOfMonth.getDay() + 6) % 7
    const gridStart = new Date(year, month, 1 - offset)

    for (let i = 0; i < 42; i++) {
        const date = new Date(gridStart)
        date.setDate(gridStart.getDate() + i)

        const dateKey = toDateKey(date)

        props.subjects.forEach(subject => {
            if (!dayMatchesClass(subject.class_days, date)) return

            const startTime = timePart(subject.start_time)
            const endTime = timePart(subject.end_time)

            items.push({
                id: `class-${subject.id}-${dateKey}`,
                source: 'class',
                sourceId: subject.id,
                title: subject.code
                    ? `${subject.code} · ${subject.name}`
                    : subject.name,
                description: [
                    subject.instructor ? `Instructor: ${subject.instructor}` : '',
                    subject.classroom ? `Room: ${subject.classroom}` : '',
                ].filter(Boolean).join(' · '),
                event_type: 'class',
                starts_at: `${dateKey}T${startTime}`,
                ends_at: `${dateKey}T${endTime}`,
                subtitle: subject.classroom || 'Class',
                link: '/subjects',
            })
        })
    }

    return items
})

const visibleEvents = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    return allCalendarItems.value
        .filter(item => {
            if (!query) return true

            return [
                item.title,
                item.description,
                item.event_type,
                item.subtitle,
                item.status,
            ].some(value =>
                String(value || '').toLowerCase().includes(query)
            )
        })
        .sort((a, b) => {
            return (parseDate(a.starts_at)?.getTime() || 0) -
                (parseDate(b.starts_at)?.getTime() || 0)
        })
})

function eventsForDay(key) {
    return visibleEvents.value.filter(
        item => dateKeyForEvent(item) === key
    )
}

const selectedEvents = computed(() => eventsForDay(selectedDate.value))

const upcomingEvents = computed(() => {
    const startOfToday = new Date(
        today.getFullYear(),
        today.getMonth(),
        today.getDate()
    )

    return visibleEvents.value
        .filter(item => {
            const date = parseDate(item.starts_at)
            return date && date >= startOfToday
        })
        .slice(0, 5)
})

const monthEventCount = computed(() => {
    const year = currentMonth.value.getFullYear()
    const month = currentMonth.value.getMonth()

    return visibleEvents.value.filter(item => {
        const date = parseDate(item.starts_at)

        return date
            && date.getFullYear() === year
            && date.getMonth() === month
    }).length
})

function changeMonth(amount) {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + amount,
        1
    )
}

function goToToday() {
    currentMonth.value = new Date(today.getFullYear(), today.getMonth(), 1)
    selectedDate.value = toDateKey(today)
}

function resetForm() {
    form.reset()
    form.clearErrors()
    form.eventId = null
    form.event_type = 'academic'
}

function openCreateModal(dateKey = selectedDate.value) {
    resetForm()

    const date = dateKey || toDateKey(today)
    form.starts_at = `${date}T09:00`
    form.ends_at = `${date}T10:00`

    showModal.value = true
}

function openEditModal(event) {
    // Only manually created calendar events can be edited here.
    if (event.source !== 'event') return

    resetForm()

    form.eventId = event.sourceId
    form.title = event.title
    form.description = event.description || ''
    form.event_type = event.event_type || 'personal'

    const start = parseDate(event.starts_at)
    const end = parseDate(event.ends_at)

    form.starts_at = start
        ? `${toDateKey(start)}T${String(start.getHours()).padStart(2, '0')}:${String(start.getMinutes()).padStart(2, '0')}`
        : ''

    form.ends_at = end
        ? `${toDateKey(end)}T${String(end.getHours()).padStart(2, '0')}:${String(end.getMinutes()).padStart(2, '0')}`
        : ''

    showModal.value = true
}

function closeModal() {
    if (form.processing) return

    showModal.value = false
    resetForm()
}

function saveEvent() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false
            resetForm()
        },
    }

    if (form.eventId) {
        form.put(`/calendar-events/${form.eventId}`, options)
    } else {
        form.post('/calendar-events', options)
    }
}

function deleteEvent(event) {
    // Only manually created calendar events can be deleted here.
    if (event.source !== 'event') return

    if (!confirm(`Delete "${event.title}" from your calendar?`)) return

    router.delete(`/calendar-events/${event.sourceId}`, {
        preserveScroll: true,
    })
}

function eventClass(type) {
    const classes = {
        academic: 'academic',
        class: 'academic',
        task: 'task',
        financial: 'financial',
        bill: 'financial',
        goal: 'goal',
        personal: 'personal',
    }

    return classes[type] || 'personal'
}

function eventToneClass(type) {
    const classes = {
        academic: 'bg-[#E7EBDD] text-[#42654B]',
        class: 'bg-[#E7EBDD] text-[#42654B]',
        task: 'bg-[#E7EBDD] text-[#42654B]',
        financial: 'bg-[#F5E4DD] text-[#A85F4C]',
        bill: 'bg-[#F5E4DD] text-[#A85F4C]',
        goal: 'bg-[#EBE5F3] text-[#796894]',
        personal: 'bg-[#EBE5F3] text-[#796894]',
    }

    return classes[type] || classes.personal
}
</script>

<template>
    <Head title="Calendar | StuDesk" />

    <main class="min-w-0 space-y-7 pb-8">
        <!-- Page heading -->
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                    YOUR LIFE, IN ONE VIEW
                </p>

                <h1 class="mt-2 font-heading text-3xl font-normal tracking-tight text-[#293B32] sm:text-4xl">
                    Your calendar<span class="text-[#B76C54]">.</span>
                </h1>

                <p class="mt-2 text-sm leading-6 text-[#777D73]">
                    Make room for deadlines, plans, and everything in between.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-[#294B3C] px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-[#203C30]"
                @click="openCreateModal()"
            >
                <Plus :size="17" />
                Add an event
            </button>
        </header>

        <!-- Welcome banner -->
        <section class="relative overflow-hidden rounded-[24px] border border-[#DCE5D8] bg-[#E7EBDD] px-6 py-7 sm:px-8 sm:py-8">
            <div class="pointer-events-none absolute -right-8 -top-16 h-52 w-52 rounded-full border-[28px] border-white/30"></div>
            <div class="pointer-events-none absolute right-32 top-24 h-16 w-16 rounded-full bg-[#D1DCC8]/60"></div>

            <div class="relative flex items-center justify-between gap-5">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-[#496550]">
                        <Sparkles :size="13" />
                        Keep your plans close
                    </span>

                    <h2 class="mt-4 font-heading text-2xl font-normal tracking-tight text-[#294B3C] sm:text-3xl">
                        One calendar. Less to juggle.
                    </h2>

                    <p class="mt-2 max-w-lg text-sm leading-6 text-[#637361]">
                        Bring your academic, financial, and personal plans together.
                        See what's coming and make time for what matters.
                    </p>
                </div>

                <div class="relative mr-5 hidden h-24 w-28 shrink-0 items-center justify-center sm:flex">
                    <div class="absolute h-20 w-20 rounded-full bg-[#D1DCC8]"></div>
                    <div class="absolute right-0 top-0 flex h-12 w-12 items-center justify-center rounded-2xl border border-[#B8CBB7] bg-[#F7F5F0] text-[#294B3C] shadow-sm">
                        <CalendarDays :size="24" :stroke-width="1.6" />
                    </div>
                    <BookOpen :size="27" class="relative mt-5 mr-5 text-[#54705D]" :stroke-width="1.6" />
                </div>
            </div>
        </section>

        <!-- Calendar and side panels -->
        <section class="grid grid-cols-1 items-start gap-5 2xl:grid-cols-[minmax(0,1.7fr)_minmax(300px,0.85fr)]">
            <!-- Main calendar -->
            <div class="min-w-0 rounded-2xl border border-[#E8E5DD] bg-white p-4 shadow-[0_2px_12px_rgba(41,75,60,0.04)] sm:p-6">
                <!-- Calendar toolbar -->
                <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                            YOUR SCHEDULE
                        </p>

                        <h2 class="mt-1 font-heading text-2xl font-normal text-[#294B3C]">
                            {{ monthLabel }}
                        </h2>

                        <p class="mt-1 text-xs text-[#85877D]">
                            {{ monthEventCount }}
                            {{ monthEventCount === 1 ? 'item' : 'items' }} this month
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl border border-[#E8E5DD] bg-[#F7F5F0] px-3.5 py-2.5 text-xs font-semibold text-[#42654B] transition hover:bg-[#E7EBDD]"
                            @click="goToToday"
                        >
                            Today
                        </button>

                        <button
                            type="button"
                            aria-label="Previous month"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#E8E5DD] bg-white text-[#54705D] transition hover:bg-[#E7EBDD]"
                            @click="changeMonth(-1)"
                        >
                            <ChevronLeft :size="18" />
                        </button>

                        <button
                            type="button"
                            aria-label="Next month"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#E8E5DD] bg-white text-[#54705D] transition hover:bg-[#E7EBDD]"
                            @click="changeMonth(1)"
                        >
                            <ChevronRight :size="18" />
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="mb-5">
                    <div class="relative">
                        <Search
                            :size="16"
                            class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[#92968E]"
                        />
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search events, tasks, subjects, or payments..."
                            class="w-full rounded-xl border border-[#E8E5DD] bg-[#FAF9F6] py-3 pl-10 pr-4 text-xs text-[#293B32] outline-none transition placeholder:text-[#A0A197] focus:border-[#91A68E] focus:ring-4 focus:ring-[#E7EBDD]/70"
                        />
                    </div>
                </div>

                <!-- Calendar grid -->
                <div class="grid grid-cols-7">
                    <div
                        v-for="day in weekdays"
                        :key="day"
                        class="pb-3 text-center text-[10px] font-semibold uppercase tracking-wide text-[#92968E]"
                    >
                        {{ day }}
                    </div>

                    <div
                        v-for="day in calendarDays"
                        :key="day.key"
                        role="button"
                        tabindex="0"
                        :aria-label="`${day.key}, ${eventsForDay(day.key).length} events`"
                        class="group min-w-0 min-h-[82px] cursor-pointer border-t border-r border-[#EEECE5] px-1.5 py-2 text-left transition last:border-r-0 hover:bg-[#FAFAF6] sm:min-h-[108px] sm:px-2"
                        :class="[
                            (calendarDays.indexOf(day) + 1) % 7 === 0 ? 'border-r-0' : '',
                            selectedDate === day.key ? 'bg-[#F0F3EC] shadow-[inset_0_0_0_1px_#C9D8C6]' : '',
                            !day.inMonth ? 'bg-[#FBFAF8]/70' : '',
                        ]"
                        @click="selectedDate = day.key"
                        @dblclick="openCreateModal(day.key)"
                        @keydown.enter.prevent="selectedDate = day.key"
                        @keydown.space.prevent="selectedDate = day.key"
                    >
                        <span
                            class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-medium"
                            :class="{
                                'bg-[#294B3C] font-semibold text-white': day.isToday,
                                'text-[#C1C1B8]': !day.inMonth && !day.isToday,
                                'text-[#46564A]': day.inMonth && !day.isToday,
                            }"
                        >
                            {{ day.day }}
                        </span>

                        <div class="grid gap-1">
                            <button
                                v-for="event in eventsForDay(day.key).slice(0, 2)"
                                :key="event.id"
                                type="button"
                                class="block w-full truncate rounded-md px-1.5 py-1 text-left text-[9px] font-medium leading-tight transition hover:brightness-[0.97] sm:text-[10px]"
                                :class="eventToneClass(event.event_type)"
                                :title="event.title"
                                @click.stop="
                                    event.source === 'event'
                                        ? openEditModal(event)
                                        : selectedDate = day.key
                                "
                            >
                                {{ event.title }}
                            </button>

                            <span
                                v-if="eventsForDay(day.key).length > 2"
                                class="pl-1 text-[9px] font-medium text-[#85877D]"
                            >
                                +{{ eventsForDay(day.key).length - 2 }} more
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Calendar legend -->
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-[#EEECE5] pt-4">
                    <span class="inline-flex items-center gap-2 text-[10px] text-[#747B71]">
                        <span class="h-2 w-2 rounded-full bg-[#668765]"></span>
                        Academic
                    </span>
                    <span class="inline-flex items-center gap-2 text-[10px] text-[#747B71]">
                        <span class="h-2 w-2 rounded-full bg-[#BD8065]"></span>
                        Financial
                    </span>
                    <span class="inline-flex items-center gap-2 text-[10px] text-[#747B71]">
                        <span class="h-2 w-2 rounded-full bg-[#9A83B3]"></span>
                        Personal & savings
                    </span>
                </div>
            </div>

            <!-- Right side panels -->
            <aside class="grid min-w-0 gap-5">
                <!-- Selected day -->
                <section class="rounded-2xl border border-[#E8E5DD] bg-white p-5 shadow-[0_2px_12px_rgba(41,75,60,0.04)] sm:p-6">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                                YOUR DAY
                            </p>
                            <h2 class="mt-1 font-heading text-xl font-normal leading-snug text-[#294B3C]">
                                {{ formatLongDate(selectedDate) }}
                            </h2>
                            <p class="mt-1 text-xs text-[#85877D]">
                                {{ selectedEvents.length }}
                                {{ selectedEvents.length === 1 ? 'item planned' : 'items planned' }}
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Add event on selected date"
                            title="Add event"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#294B3C] text-white transition hover:bg-[#203C30]"
                            @click="openCreateModal()"
                        >
                            <Plus :size="19" />
                        </button>
                    </div>

                    <div v-if="selectedEvents.length" class="space-y-4">
                        <article
                            v-for="event in selectedEvents"
                            :key="event.id"
                            class="flex gap-3"
                        >
                            <span
                                class="mt-1 w-[3px] shrink-0 self-stretch rounded-full"
                                :class="{
                                    'bg-[#668765]': ['academic', 'class', 'task'].includes(event.event_type),
                                    'bg-[#BD8065]': ['financial', 'bill'].includes(event.event_type),
                                    'bg-[#9A83B3]': ['personal', 'goal'].includes(event.event_type),
                                }"
                            ></span>

                            <div class="min-w-0 flex-1 pb-1">
                                <span
                                    class="inline-flex rounded-md px-2 py-1 text-[9px] font-semibold capitalize"
                                    :class="eventToneClass(event.event_type)"
                                >
                                    {{ event.event_type }}
                                </span>

                                <h3 class="mt-2 break-words text-sm font-semibold text-[#354638]">
                                    {{ event.title }}
                                </h3>

                                <p class="mt-1 flex items-center gap-1.5 text-[11px] text-[#85877D]">
                                    <Clock :size="12" />
                                    {{ formatTime(event.starts_at) }}
                                    <template v-if="event.ends_at">
                                        – {{ formatTime(event.ends_at) }}
                                    </template>
                                </p>

                                <p
                                    v-if="event.description"
                                    class="mt-2 whitespace-pre-line break-words text-xs leading-5 text-[#85877D]"
                                >
                                    {{ event.description }}
                                </p>

                                <div
                                    v-if="event.source === 'event'"
                                    class="mt-3 flex items-center gap-3"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#42654B] transition hover:text-[#203C30]"
                                        @click="openEditModal(event)"
                                    >
                                        <Pencil :size="12" />
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#B76C54] transition hover:text-[#92513E]"
                                        @click="deleteEvent(event)"
                                    >
                                        <Trash2 :size="12" />
                                        Delete
                                    </button>
                                </div>

                                <a
                                    v-else-if="event.link"
                                    :href="event.link"
                                    class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-[#42654B] hover:underline"
                                >
                                    View details
                                    <ArrowUpRight :size="12" />
                                </a>
                            </div>
                        </article>
                    </div>

                    <div
                        v-else
                        class="rounded-2xl bg-[#F7F5F0] px-5 py-8 text-center"
                    >
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[#54705D]">
                            <CalendarDays :size="22" :stroke-width="1.7" />
                        </div>

                        <h3 class="mt-4 font-heading text-lg font-normal text-[#294B3C]">
                            A little room in your day
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-[#85877D]">
                            No events planned for this date yet.
                        </p>

                        <button
                            type="button"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-[#DCE5D8] bg-white px-3.5 py-2.5 text-xs font-semibold text-[#42654B] transition hover:bg-[#E7EBDD]"
                            @click="openCreateModal()"
                        >
                            <Plus :size="14" />
                            Add an event
                        </button>
                    </div>
                </section>

                <!-- Upcoming events -->
                <section class="rounded-2xl border border-[#E8E5DD] bg-white p-5 shadow-[0_2px_12px_rgba(41,75,60,0.04)] sm:p-6">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                                COMING UP
                            </p>
                            <h2 class="mt-1 font-heading text-xl font-normal text-[#294B3C]">
                                On the horizon
                            </h2>
                            <p class="mt-1 text-xs text-[#85877D]">
                                Your next five upcoming items.
                            </p>
                        </div>

                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E7EBDD] text-[#54705D]">
                            <ArrowUpRight :size="18" />
                        </span>
                    </div>

                    <div v-if="upcomingEvents.length" class="space-y-1">
                        <button
                            v-for="event in upcomingEvents"
                            :key="event.id"
                            type="button"
                            class="group flex w-full items-center gap-3 rounded-xl p-2 text-left transition hover:bg-[#F7F5F0]"
                            @click="selectedDate = dateKeyForEvent(event)"
                        >
                            <span class="flex w-11 shrink-0 flex-col items-center rounded-xl bg-[#F0EEE7] py-2">
                                <strong class="font-heading text-lg font-normal leading-none text-[#294B3C]">
                                    {{ parseDate(event.starts_at)?.getDate() }}
                                </strong>
                                <small class="mt-1 text-[9px] font-semibold uppercase text-[#85877D]">
                                    {{ parseDate(event.starts_at)?.toLocaleDateString('en-US', { month: 'short' }) }}
                                </small>
                            </span>

                            <span class="min-w-0 flex-1">
                                <strong class="block truncate text-xs font-semibold text-[#354638]">
                                    {{ event.title }}
                                </strong>
                                <small class="mt-1 block text-[10px] text-[#85877D]">
                                    {{ formatTime(event.starts_at) }}
                                </small>
                            </span>

                            <span
                                class="h-2 w-2 shrink-0 rounded-full"
                                :class="{
                                    'bg-[#668765]': ['academic', 'class', 'task'].includes(event.event_type),
                                    'bg-[#BD8065]': ['financial', 'bill'].includes(event.event_type),
                                    'bg-[#9A83B3]': ['personal', 'goal'].includes(event.event_type),
                                }"
                            ></span>
                        </button>
                    </div>

                    <div
                        v-else
                        class="rounded-xl bg-[#F7F5F0] px-4 py-5 text-center text-xs leading-5 text-[#85877D]"
                    >
                        Your upcoming plans will appear here.
                    </div>
                </section>

                <!-- Calendar reminder -->
                <section class="rounded-2xl bg-[#F5EDE5] p-5">
                    <div class="flex items-center gap-2">
                        <Wallet :size="17" class="text-[#A56850]" />
                        <h3 class="text-sm font-semibold text-[#684B3D]">
                            Keep everything connected
                        </h3>
                    </div>

                    <p class="mt-2 text-xs leading-5 text-[#806D61]">
                        Your calendar brings school deadlines, bills, and savings
                        targets into one place, helping you prepare before they arrive.
                    </p>
                </section>
            </aside>
        </section>

        <!-- Create / edit event modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-[#1F2A22]/50 p-4 backdrop-blur-[3px]"
                @click.self="closeModal"
                @keydown.esc="closeModal"
            >
                <section
                    class="my-auto w-full max-w-[540px] overflow-hidden rounded-[22px] border border-[#E8E5DD] bg-white shadow-[0_22px_70px_rgba(22,35,26,0.18)]"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="event-modal-title"
                >
                    <!-- Modal header -->
                    <div class="flex items-start justify-between gap-4 border-b border-[#EEECE5] px-6 py-5 sm:px-7">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                                {{ form.eventId ? 'MAKE AN UPDATE' : 'MAKE A PLAN' }}
                            </p>

                            <h2 id="event-modal-title" class="mt-1 font-heading text-2xl font-normal text-[#294B3C]">
                                {{ form.eventId ? 'Edit event' : 'Add to your calendar' }}
                            </h2>

                            <p class="mt-1 text-xs leading-5 text-[#85877D]">
                                Give this part of your day a place.
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Close dialog"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#F0EEE7] text-[#54705D] transition hover:bg-[#E7EBDD]"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            <X :size="18" />
                        </button>
                    </div>

                    <!-- Modal form -->
                    <form class="px-6 py-6 sm:px-7" @submit.prevent="saveEvent">
                        <div class="space-y-4">
                            <label class="block">
                                <span class="text-xs font-semibold text-[#435348]">
                                    Event title <span class="text-[#B76C54]">*</span>
                                </span>

                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="e.g. Database Systems midterm"
                                    maxlength="255"
                                    required
                                    class="mt-2 w-full rounded-xl border border-[#E8E5DD] bg-[#FAF9F6] px-3.5 py-3 text-sm text-[#293B32] outline-none transition placeholder:text-[#A0A197] focus:border-[#91A68E] focus:bg-white focus:ring-4 focus:ring-[#E7EBDD]/70"
                                />

                                <small v-if="form.errors.title" class="mt-1.5 block text-xs text-[#B76C54]">
                                    {{ form.errors.title }}
                                </small>
                            </label>

                            <label class="block">
                                <span class="text-xs font-semibold text-[#435348]">
                                    Category
                                </span>

                                <select
                                    v-model="form.event_type"
                                    required
                                    class="mt-2 w-full rounded-xl border border-[#E8E5DD] bg-[#FAF9F6] px-3.5 py-3 text-sm text-[#293B32] outline-none transition focus:border-[#91A68E] focus:bg-white focus:ring-4 focus:ring-[#E7EBDD]/70"
                                >
                                    <option value="academic">Academic</option>
                                    <option value="financial">Financial</option>
                                    <option value="personal">Personal</option>
                                </select>

                                <small v-if="form.errors.event_type" class="mt-1.5 block text-xs text-[#B76C54]">
                                    {{ form.errors.event_type }}
                                </small>
                            </label>

                            <label class="block">
                                <span class="text-xs font-semibold text-[#435348]">
                                    Description
                                </span>

                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Add a note or anything you'll want to remember..."
                                    class="mt-2 w-full resize-y rounded-xl border border-[#E8E5DD] bg-[#FAF9F6] px-3.5 py-3 text-sm leading-6 text-[#293B32] outline-none transition placeholder:text-[#A0A197] focus:border-[#91A68E] focus:bg-white focus:ring-4 focus:ring-[#E7EBDD]/70"
                                ></textarea>

                                <small v-if="form.errors.description" class="mt-1.5 block text-xs text-[#B76C54]">
                                    {{ form.errors.description }}
                                </small>
                            </label>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="text-xs font-semibold text-[#435348]">
                                        Starts <span class="text-[#B76C54]">*</span>
                                    </span>

                                    <input
                                        v-model="form.starts_at"
                                        type="datetime-local"
                                        required
                                        class="mt-2 w-full min-w-0 rounded-xl border border-[#E8E5DD] bg-[#FAF9F6] px-3 py-3 text-xs text-[#293B32] outline-none transition focus:border-[#91A68E] focus:bg-white focus:ring-4 focus:ring-[#E7EBDD]/70"
                                    />

                                    <small v-if="form.errors.starts_at" class="mt-1.5 block text-xs text-[#B76C54]">
                                        {{ form.errors.starts_at }}
                                    </small>
                                </label>

                                <label class="block">
                                    <span class="text-xs font-semibold text-[#435348]">
                                        Ends
                                    </span>

                                    <input
                                        v-model="form.ends_at"
                                        type="datetime-local"
                                        class="mt-2 w-full min-w-0 rounded-xl border border-[#E8E5DD] bg-[#FAF9F6] px-3 py-3 text-xs text-[#293B32] outline-none transition focus:border-[#91A68E] focus:bg-white focus:ring-4 focus:ring-[#E7EBDD]/70"
                                    />

                                    <small v-if="form.errors.ends_at" class="mt-1.5 block text-xs text-[#B76C54]">
                                        {{ form.errors.ends_at }}
                                    </small>
                                </label>
                            </div>
                        </div>

                        <!-- Modal actions -->
                        <div class="mt-7 flex flex-wrap justify-end gap-2 border-t border-[#EEECE5] pt-5">
                            <button
                                type="button"
                                class="rounded-xl border border-[#E8E5DD] bg-white px-4 py-2.5 text-xs font-semibold text-[#54705D] transition hover:bg-[#F7F5F0]"
                                :disabled="form.processing"
                                @click="closeModal"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#203C30] disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>
                                    {{ form.eventId ? 'Save changes' : 'Add event' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </Teleport>
    </main>
</template>