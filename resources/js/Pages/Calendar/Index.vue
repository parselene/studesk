
<script setup>
import { computed, ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

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

    // Manually created calendar events
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

    // Tasks with due dates
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

    // Bills with due dates
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

    // Savings goals with target dates
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

    // Generate class occurrences for the displayed calendar grid
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

</script>

<template>
    <Head title="Calendar | StuDesk" />

    <div class="page">
        <header class="page-header">
            <div>
                <p class="eyebrow">YOUR LIFE, IN ONE VIEW</p>
                <h1>Your calendar<span class="title-period">.</span></h1>
                <p class="subtitle">
                    Make room for deadlines, plans, and everything in between.
                </p>
            </div>
        </header>

        <section class="welcome-strip">
            <div>
                <span class="strip-label">KEEP YOUR PLANS CLOSE</span>
                <h2>One calendar. Less to juggle.</h2>
                <p>Bring your academic, financial, and personal plans together.</p>
            </div>

            <div class="strip-decoration" aria-hidden="true">
                <span class="decoration-circle circle-one"></span>
                <span class="decoration-circle circle-two"></span>
                <span class="decoration-star">✳</span>
            </div>
        </section>

        <section class="calendar-layout">
            <div class="calendar-panel">
                <div class="calendar-toolbar">
                    <div>
                        <p class="eyebrow">YOUR SCHEDULE</p>
                        <h2>{{ monthLabel }}</h2>
                        <p class="month-caption">
                            {{ monthEventCount }}
                            {{ monthEventCount === 1 ? 'event' : 'events' }} this month
                        </p>
                    </div>

                    <div class="month-actions">
                        <button class="today-button" type="button" @click="goToToday">
                            Today
                        </button>
                        <button
                            class="arrow-button"
                            type="button"
                            aria-label="Previous month"
                            @click="changeMonth(-1)"
                        >‹</button>
                        <button
                            class="arrow-button"
                            type="button"
                            aria-label="Next month"
                            @click="changeMonth(1)"
                        >›</button>
                    </div>
                </div>

                <div class="calendar-grid">
                    <div
                        v-for="day in weekdays"
                        :key="day"
                        class="weekday"
                    >
                        {{ day }}
                    </div>

                    <button
                        v-for="day in calendarDays"
                        :key="day.key"
                        type="button"
                        class="day-cell"
                        :class="{
                            'outside-month': !day.inMonth,
                            'today-cell': day.isToday,
                            'selected-cell': selectedDate === day.key,
                        }"
                        :aria-label="`${day.key}, ${eventsForDay(day.key).length} events`"
                        @click="selectedDate = day.key"
                        @dblclick="openCreateModal(day.key)"
                    >
                        <span class="day-number" :class="{ 'today-number': day.isToday }">
                            {{ day.day }}
                        </span>

                        <div class="day-events">
                            <button
                                v-for="event in eventsForDay(day.key).slice(0, 2)"
                                :key="event.id"
                                type="button"
                                class="day-event"
                                :class="eventClass(event.event_type)"
                                :title="event.source === 'event' ? 'Click to edit event' : event.title"
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
                                class="more-events"
                            >
                                +{{ eventsForDay(day.key).length - 2 }} more
                            </span>
                        </div>
                    </button>
                </div>

                <div class="calendar-footer">
                    <span><i class="legend-dot academic-dot"></i> Academic</span>
                    <span><i class="legend-dot financial-dot"></i> Financial</span>
                    <span><i class="legend-dot personal-dot"></i> Personal</span>
                </div>
            </div>

            <aside class="side-column">
                <section class="side-panel selected-day-panel">
                    <div class="side-heading">
                        <div>
                            <p class="eyebrow">YOUR DAY</p>
                            <h2>{{ formatLongDate(selectedDate) }}</h2>
                        </div>

                        <button
                            type="button"
                            class="add-icon-button"
                            aria-label="Add event on selected date"
                            @click="openCreateModal()"
                        >＋</button>
                    </div>

                    <div v-if="selectedEvents.length" class="event-list">
                        <article
                            v-for="event in selectedEvents"
                            :key="event.id"
                            class="event-item"
                        >
                            <span class="event-line" :class="eventClass(event.event_type)"></span>

                            <div class="event-content">
                                <span class="event-category" :class="eventClass(event.event_type)">
                                    {{ event.event_type }}
                                </span>
                                <h3>{{ event.title }}</h3>
                                <p class="event-time">
                                    {{ formatTime(event.starts_at) }}
                                    <template v-if="event.ends_at">
                                        – {{ formatTime(event.ends_at) }}
                                    </template>
                                </p>
                                <p v-if="event.description" class="event-description">
                                    {{ event.description }}
                                </p>

                                <div class="event-actions">
                                    <button type="button" @click="openEditModal(event)">
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="delete-action"
                                        @click="deleteEvent(event)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="mini-empty">
                        <div class="mini-empty-icon">✳</div>
                        <h3>A little room in your day</h3>
                        <p>No events planned for this date yet.</p>
                        <button type="button" class="inline-add" @click="openCreateModal()">
                            + Add an event
                        </button>
                    </div>
                </section>

                <section class="side-panel upcoming-panel">
                    <div class="side-heading">
                        <div>
                            <p class="eyebrow">COMING UP</p>
                            <h2>On the horizon</h2>
                        </div>
                        <span class="upcoming-symbol">↗</span>
                    </div>

                    <div v-if="upcomingEvents.length" class="upcoming-list">
                        <button
                            v-for="event in upcomingEvents"
                            :key="event.id"
                            type="button"
                            class="upcoming-item"
                            @click="selectedDate = dateKeyForEvent(event)"
                        >
                            <span class="upcoming-date">
                                <strong>{{ parseDate(event.starts_at)?.getDate() }}</strong>
                                <small>
                                    {{ parseDate(event.starts_at)?.toLocaleDateString('en-US', { month: 'short' }) }}
                                </small>
                            </span>

                            <span class="upcoming-info">
                                <strong>{{ event.title }}</strong>
                                <small>{{ formatTime(event.starts_at) }}</small>
                            </span>

                            <span class="upcoming-dot" :class="eventClass(event.event_type)"></span>
                        </button>
                    </div>

                    <div v-else class="upcoming-empty">
                        Your upcoming plans will appear here.
                    </div>
                </section>
            </aside>
        </section>

        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal" @keydown.esc="closeModal">
            <section
                class="event-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="event-modal-title"
            >
                <div class="modal-heading">
                    <div>
                        <p class="eyebrow">{{ form.eventId ? 'MAKE AN UPDATE' : 'MAKE A PLAN' }}</p>
                        <h2 id="event-modal-title">
                            {{ form.eventId ? 'Edit event' : 'Add to your calendar' }}
                        </h2>
                        <p class="modal-subtitle">
                            Give this part of your day a place.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="close-button"
                        aria-label="Close dialog"
                        @click="closeModal"
                    >×</button>
                </div>

                <form @submit.prevent="saveEvent">
                    <label class="form-label">
                        Event title <span class="required">*</span>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="e.g. Database Systems midterm"
                            maxlength="255"
                            required
                        />
                        <small v-if="form.errors.title" class="error">
                            {{ form.errors.title }}
                        </small>
                    </label>

                    <label class="form-label">
                        Category
                        <select v-model="form.event_type" required>
                            <option value="academic">Academic</option>
                            <option value="financial">Financial</option>
                            <option value="personal">Personal</option>
                        </select>
                        <small v-if="form.errors.event_type" class="error">
                            {{ form.errors.event_type }}
                        </small>
                    </label>

                    <label class="form-label">
                        Description
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Add a note or anything you'll want to remember..."
                        ></textarea>
                        <small v-if="form.errors.description" class="error">
                            {{ form.errors.description }}
                        </small>
                    </label>

                    <div class="form-two-columns">
                        <label class="form-label">
                            Starts <span class="required">*</span>
                            <input v-model="form.starts_at" type="datetime-local" required />
                            <small v-if="form.errors.starts_at" class="error">
                                {{ form.errors.starts_at }}
                            </small>
                        </label>

                        <label class="form-label">
                            Ends
                            <input v-model="form.ends_at" type="datetime-local" />
                            <small v-if="form.errors.ends_at" class="error">
                                {{ form.errors.ends_at }}
                            </small>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <button
                            type="button"
                            class="cancel-button"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="save-button"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Saving…'
                                    : form.eventId
                                        ? 'Save changes'
                                        : 'Add event'
                            }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</template>

<style scoped>
.page {
    min-height: 100vh;
    padding: 36px 24px 48px;
    background: #f7f5f0;
    color: #273a30;
    font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}

.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 26px;
}

.eyebrow {
    margin: 0 0 8px;
    color: #788679;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.7px;
    text-transform: uppercase;
}

h1 {
    margin: 0;
    font-family: Georgia, serif;
    font-size: clamp(32px, 4vw, 43px);
    font-weight: 500;
    letter-spacing: -1.2px;
}

.title-period {
    color: #b77a63;
}

.subtitle {
    margin: 10px 0 0;
    color: #7b8179;
    font-size: 14px;
    line-height: 1.6;
}

.welcome-strip {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 142px;
    padding: 26px 30px;
    margin-bottom: 24px;
    overflow: hidden;
    border: 1px solid #e0e7dc;
    border-radius: 19px;
    background: #e8eee4;
}

.strip-label {
    color: #687b6b;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.6px;
}

.welcome-strip h2 {
    margin: 9px 0 7px;
    font-family: Georgia, serif;
    font-size: clamp(21px, 3vw, 28px);
    font-weight: 500;
}

.welcome-strip p {
    margin: 0;
    color: #738075;
    font-size: 12px;
    line-height: 1.6;
}

.strip-decoration {
    position: relative;
    width: 125px;
    height: 95px;
    flex-shrink: 0;
}

.decoration-circle {
    position: absolute;
    display: block;
    border-radius: 50%;
}

.circle-one {
    top: 4px;
    right: 13px;
    width: 78px;
    height: 78px;
    background: #d4e0d0;
}

.circle-two {
    right: 0;
    bottom: 0;
    width: 45px;
    height: 45px;
    border: 1px solid #b8cbb7;
    background: #e8eee4;
}

.decoration-star {
    position: absolute;
    top: 20px;
    right: 36px;
    color: #294b3c;
    font-size: 29px;
}

.calendar-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(285px, 0.85fr);
    align-items: start;
    gap: 22px;
}

.calendar-panel,
.side-panel {
    min-width: 0;
    border: 1px solid #e9e5dc;
    border-radius: 18px;
    background: #fffefa;
    box-shadow: 0 2px 10px rgb(39 58 48 / 3%);
}

.calendar-panel {
    padding: 24px;
}

.calendar-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
}

.calendar-toolbar h2,
.side-heading h2 {
    margin: 0;
    font-family: Georgia, serif;
    font-size: 23px;
    font-weight: 500;
    overflow-wrap: anywhere;
}

.month-caption {
    margin: 7px 0 0;
    color: #83877e;
    font-size: 11px;
}

.month-actions {
    display: flex;
    align-items: center;
    gap: 7px;
}

.today-button,
.arrow-button {
    height: 35px;
    border: 1px solid #e5e3da;
    border-radius: 9px;
    background: #fffefa;
    color: #405447;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 180ms ease, transform 180ms ease;
}

.today-button {
    padding: 0 12px;
}

.arrow-button {
    width: 35px;
    font-size: 23px;
    line-height: 1;
}

.today-button:hover,
.arrow-button:hover {
    background: #e8eee4;
    transform: translateY(-1px);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
}

.weekday {
    padding: 0 0 13px;
    color: #8b8c83;
    font-size: 10px;
    font-weight: 800;
    text-align: center;
}

.day-cell {
    min-width: 0;
    min-height: 94px;
    padding: 8px 5px;
    border: 0;
    border-top: 1px solid #eeece5;
    border-right: 1px solid #eeece5;
    background: transparent;
    text-align: left;
    cursor: pointer;
    transition: background 180ms ease, box-shadow 180ms ease;
}

.day-cell:nth-child(7n) {
    border-right: 0;
}

.day-cell:hover {
    background: #f6f6f0;
}

.day-cell.selected-cell {
    background: #edf2e9;
    box-shadow: inset 0 0 0 1px #c9d8c6;
}

.day-number {
    display: grid;
    width: 25px;
    height: 25px;
    margin: 0 auto 6px 0;
    place-items: center;
    border-radius: 50%;
    color: #46564a;
    font-size: 11px;
}

.today-number {
    background: #294b3c;
    color: white;
    font-weight: 800;
}

.outside-month .day-number {
    color: #c1c1b8;
}

.day-events {
    display: grid;
    gap: 3px;
}

.day-event {
    display: block;
    width: 100%;
    padding: 4px 5px;
    overflow: hidden;
    border: 0;
    border-radius: 4px;
    font-size: 9px;
    line-height: 1.3;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
}

.academic {
    background: #e7eee2;
    color: #365b3e;
}

.financial {
    background: #f5e7df;
    color: #925a42;
}

.personal {
    background: #eee8f4;
    color: #6c5686;
}

.more-events {
    padding-left: 3px;
    color: #85877f;
    font-size: 9px;
}

.calendar-footer {
    display: flex;
    flex-wrap: wrap;
    gap: 17px;
    padding-top: 18px;
    margin-top: 4px;
    border-top: 1px solid #eeece5;
}

.calendar-footer span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #747b71;
    font-size: 10px;
}

.legend-dot,
.upcoming-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    border-radius: 50%;
}

.academic-dot {
    background: #668765;
}

.financial-dot {
    background: #bd8065;
}

.personal-dot {
    background: #9a83b3;
}

.side-column {
    display: grid;
    gap: 18px;
}

.side-panel {
    padding: 21px;
}

.side-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 19px;
}

.side-heading h2 {
    font-size: 20px;
    line-height: 1.35;
}

.add-icon-button {
    display: grid;
    width: 35px;
    height: 35px;
    flex-shrink: 0;
    place-items: center;
    border: 0;
    border-radius: 11px;
    background: #294b3c;
    color: white;
    font-size: 22px;
    cursor: pointer;
    transition: background 180ms ease, transform 180ms ease;
}

.add-icon-button:hover {
    background: #203d30;
    transform: translateY(-1px);
}

.event-list {
    display: grid;
    gap: 17px;
}

.event-item {
    display: flex;
    gap: 11px;
}

.event-line {
    width: 3px;
    min-height: 48px;
    flex-shrink: 0;
    border-radius: 3px;
}

.event-line.academic {
    background: #668765;
}

.event-line.financial {
    background: #bd8065;
}

.event-line.personal {
    background: #9a83b3;
}

.event-content {
    min-width: 0;
    flex: 1;
}

.event-category {
    display: inline-block;
    padding: 4px 7px;
    border-radius: 5px;
    font-size: 9px;
    font-weight: 800;
    text-transform: capitalize;
}

.event-content h3 {
    margin: 7px 0 4px;
    font-size: 13px;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.event-time {
    margin: 0;
    color: #81857d;
    font-size: 10px;
}

.event-description {
    margin: 8px 0 0;
    color: #81857d;
    font-size: 11px;
    line-height: 1.6;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.event-actions {
    display: flex;
    gap: 12px;
    margin-top: 9px;
}

.event-actions button {
    padding: 0;
    border: 0;
    background: transparent;
    color: #294b3c;
    font-size: 10px;
    font-weight: 750;
    cursor: pointer;
}

.event-actions .delete-action {
    color: #a85f4c;
}

.mini-empty {
    padding: 17px 6px 13px;
    text-align: center;
}

.mini-empty-icon {
    display: grid;
    width: 44px;
    height: 44px;
    margin: 0 auto 12px;
    place-items: center;
    border-radius: 14px;
    background: #e8eee4;
    color: #294b3c;
    font-size: 21px;
}

.mini-empty h3 {
    margin: 0 0 7px;
    font-family: Georgia, serif;
    font-size: 17px;
    font-weight: 500;
}

.mini-empty p,
.upcoming-empty {
    margin: 0;
    color: #83867e;
    font-size: 11px;
    line-height: 1.7;
}

.inline-add {
    margin-top: 14px;
    padding: 9px 12px;
    border: 1px solid #dce5d8;
    border-radius: 9px;
    background: #f0f4ed;
    color: #294b3c;
    font-size: 11px;
    font-weight: 750;
    cursor: pointer;
}

.upcoming-symbol {
    color: #8b9b84;
    font-size: 22px;
}

.upcoming-list {
    display: grid;
    gap: 5px;
}

.upcoming-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 7px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    text-align: left;
    cursor: pointer;
    transition: background 180ms ease;
}

.upcoming-item:hover {
    background: #f4f4ee;
}

.upcoming-date {
    display: flex;
    width: 37px;
    flex-shrink: 0;
    flex-direction: column;
    align-items: center;
    padding: 6px 0;
    border-radius: 8px;
    background: #f0eee7;
}

.upcoming-date strong {
    font-family: Georgia, serif;
    font-size: 18px;
    font-weight: 500;
}

.upcoming-date small {
    color: #81857d;
    font-size: 9px;
    text-transform: uppercase;
}

.upcoming-info {
    display: grid;
    min-width: 0;
    flex: 1;
    gap: 4px;
}

.upcoming-info strong {
    overflow: hidden;
    font-size: 11px;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.upcoming-info small {
    color: #81857d;
    font-size: 10px;
}

.upcoming-empty {
    padding: 4px 0 8px;
}

.modal-backdrop {
    position: fixed;
    z-index: 1000;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 20px;
    overflow-y: auto;
    background: rgb(31 42 34 / 48%);
    backdrop-filter: blur(4px);
    animation: fade-in 180ms ease both;
}

.event-modal {
    width: min(100%, 510px);
    max-height: calc(100vh - 40px);
    padding: 27px;
    overflow-y: auto;
    border: 1px solid #e9e5dc;
    border-radius: 20px;
    background: #fffefa;
    box-shadow: 0 22px 70px rgb(22 35 26 / 18%);
    animation: modal-up 220ms ease both;
}

.modal-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 25px;
}

.modal-heading h2 {
    margin: 0;
    font-family: Georgia, serif;
    font-size: 26px;
    font-weight: 500;
}

.modal-subtitle {
    margin: 7px 0 0;
    color: #83867e;
    font-size: 12px;
    line-height: 1.5;
}

.close-button {
    display: grid;
    width: 33px;
    height: 33px;
    flex-shrink: 0;
    place-items: center;
    border: 0;
    border-radius: 10px;
    background: #f0eee7;
    color: #4b5b4e;
    font-size: 23px;
    cursor: pointer;
}

.form-label {
    display: block;
    margin-bottom: 17px;
    color: #435348;
    font-size: 12px;
    font-weight: 750;
}

.required {
    color: #a85f4c;
}

.form-label input,
.form-label select,
.form-label textarea {
    box-sizing: border-box;
    display: block;
    width: 100%;
    margin-top: 8px;
    padding: 12px 13px;
    border: 1px solid #e2e0d7;
    border-radius: 10px;
    outline: none;
    background: white;
    color: #273a30;
    font: inherit;
    font-size: 12px;
    font-weight: 400;
    transition: border-color 180ms ease, box-shadow 180ms ease;
}

.form-label textarea {
    resize: vertical;
    line-height: 1.6;
}

.form-label input:focus,
.form-label select:focus,
.form-label textarea:focus {
    border-color: #91a68e;
    box-shadow: 0 0 0 4px rgb(228 235 221 / 75%);
}

.form-two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.error {
    display: block;
    margin-top: 6px;
    color: #a85f4c;
    font-size: 10px;
    font-weight: 500;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 7px;
}

.cancel-button,
.save-button {
    padding: 12px 17px;
    border: 0;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 750;
    cursor: pointer;
    transition: background 180ms ease, transform 180ms ease;
}

.cancel-button {
    background: #efeee8;
    color: #405447;
}

.save-button {
    background: #294b3c;
    color: white;
}

.cancel-button:hover {
    background: #e5e4db;
}

.save-button:hover:not(:disabled) {
    background: #203d30;
    transform: translateY(-1px);
}

.save-button:disabled,
.cancel-button:disabled {
    opacity: 0.6;
    cursor: wait;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modal-up {
    from {
        opacity: 0;
        transform: translateY(8px) scale(0.99);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 1100px) {
    .page {
        padding: 26px;
    }

    .calendar-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    .side-column {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: start;
    }
}

@media (max-width: 650px) {
    .page {
        padding: 22px 14px;
    }

    .page-header {
        flex-direction: column;
    }

    .welcome-strip {
        min-height: 120px;
        padding: 22px;
    }

    .strip-decoration {
        width: 72px;
        transform: scale(0.8);
        transform-origin: center right;
    }

    .calendar-panel {
        padding: 15px 10px;
    }

    .calendar-toolbar {
        align-items: flex-start;
        flex-direction: column;
        margin: 8px 5px 18px;
    }

    .month-actions {
        align-self: flex-end;
    }

    .day-cell {
        min-height: 70px;
        padding: 5px 2px;
    }

    .day-number {
        width: 22px;
        height: 22px;
        font-size: 10px;
    }

    .day-event {
        padding: 3px;
        font-size: 8px;
    }

    .more-events {
        font-size: 8px;
    }

    .calendar-footer {
        gap: 10px;
        padding: 14px 4px 2px;
    }

    .calendar-footer span {
        font-size: 9px;
    }

    .side-column {
        grid-template-columns: 1fr;
    }

    .form-two-columns {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .event-modal {
        padding: 21px 17px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
