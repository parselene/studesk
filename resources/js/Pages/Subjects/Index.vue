
<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
})

const showForm = ref(false)
const searchQuery = ref('')

const form = useForm({
    subjectId: null,
    name: '',
    code: '',
    instructor: '',
    color: '#294B3C',
    class_days: [],
    start_time: '',
    end_time: '',
    classroom: '',
})

const weekDays = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
]

const isEditing = computed(() => form.subjectId !== null)

const filteredSubjects = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()

    if (!query) return props.subjects

    return props.subjects.filter(subject =>
        [
            subject.name,
            subject.code,
            subject.instructor,
        ].some(value => String(value ?? '').toLowerCase().includes(query))
    )
})

const colorPresets = [
    { name: 'Sage', value: '#789477' },
    { name: 'Forest', value: '#294B3C' },
    { name: 'Terracotta', value: '#C17D67' },
    { name: 'Lavender', value: '#A394BC' },
    { name: 'Dusty blue', value: '#7D9BA0' },
    { name: 'Warm gold', value: '#C3A565' },
]

function resetForm() {
    form.reset()
    form.clearErrors()
    form.subjectId = null
    form.color = '#294B3C'
    showForm.value = false
}

function openCreateForm() {
    resetForm()
    showForm.value = true
}

function editSubject(subject) {
    form.clearErrors()
    form.subjectId = subject.id
    form.name = subject.name ?? ''
    form.code = subject.code ?? ''
    form.instructor = subject.instructor ?? ''
    form.color = subject.color ?? '#294B3C'
    form.class_days = subject.class_days ?? []
    form.start_time = subject.start_time?.slice(0, 5) ?? ''
    form.end_time = subject.end_time?.slice(0, 5) ?? ''
    form.classroom = subject.classroom ?? ''

    showForm.value = true
}

function saveSubject() {
    const payload = {
        name: form.name,
        code: form.code || null,
        instructor: form.instructor || null,
        color: form.color,
        class_days: form.class_days,
        start_time: form.start_time || null,
        end_time: form.end_time || null,
        classroom: form.classroom || null,
    }

    if (isEditing.value) {
        form.transform(() => payload).put(`/subjects/${form.subjectId}`, {
            onSuccess: resetForm,
        })
    } else {
        form.transform(() => payload).post('/subjects', {
            onSuccess: resetForm,
        })
    }
}

function deleteSubject(subject) {
    const message = `Delete "${subject.name}"? This action cannot be undone.`

    if (confirm(message)) {
        router.delete(`/subjects/${subject.id}`)
    }
}

function selectColor(color) {
    form.color = color
}

function initials(name) {
    return (name || '?')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word => word.charAt(0).toUpperCase())
        .join('')
}
</script>

<template>
    <Head title="Subjects | StuDesk" />

    <div class="min-w-0 pb-6 text-[#293B32]">
        <!-- Page header -->
        <header class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                    YOUR ACADEMIC SPACE
                </p>

                <h1 class="font-serif text-[32px] font-medium leading-tight tracking-[-0.8px] text-[#294B3C] sm:text-[38px]">
                    My subjects<span class="text-[#B97962]">.</span>
                </h1>

                <p class="mt-2 text-[13px] leading-relaxed text-[#85877D]">
                    All your classes, together in one place.
                </p>
            </div>

            <button
                type="button"
                class="primary-button"
                @click="openCreateForm"
            >
                <span class="text-lg font-normal leading-none">+</span>
                Add subject
            </button>
        </header>

        <!-- Semester summary -->
        <section class="mb-7 flex items-center justify-between gap-4 rounded-2xl border border-[#DDE3D6] bg-[#E7EBDD] p-5 sm:p-6">
            <div class="flex min-w-0 items-center gap-4">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#D7E1D2] text-[#42614A]">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="21"
                        height="21"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#788A77]">
                        YOUR SEMESTER
                    </p>

                    <h2 class="mt-1 font-serif text-[17px] font-medium text-[#294B3C]">
                        {{ subjects.length === 0
                            ? 'A fresh start'
                            : subjects.length === 1
                                ? 'Your first class is here'
                                : 'Your classes, organized' }}
                    </h2>

                    <p class="mt-1 text-[11px] leading-relaxed text-[#81877B]">
                        {{ subjects.length === 0
                            ? 'Add a subject to begin building your academic planner.'
                            : 'Keep your classes organized and your deadlines connected.' }}
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-right">
                <strong class="font-serif text-[32px] font-medium leading-none text-[#294B3C]">
                    {{ subjects.length }}
                </strong>
                <p class="mt-1 text-[10px] text-[#7E877A]">
                    {{ subjects.length === 1 ? 'subject' : 'subjects' }}
                </p>
            </div>
        </section>

        <!-- Subject library -->
        <section class="min-w-0">
            <div class="mb-5 flex items-end justify-between gap-3">
                <div>
                    <p class="mb-1 text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                        YOUR CLASSES
                    </p>

                    <h2 class="font-serif text-[25px] font-medium tracking-[-0.4px] text-[#294B3C]">
                        Subject library
                    </h2>

                    <p class="mt-1 text-[12px] text-[#85877D]">
                        Your subjects and the tasks connected to them.
                    </p>
                </div>

                <span class="shrink-0 pb-1 text-[11px] text-[#85877D]">
                    {{ filteredSubjects.length }}
                    {{ filteredSubjects.length === 1 ? 'subject' : 'subjects' }}
                </span>
            </div>

            <!-- Search -->
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-h-[42px] w-full items-center gap-2.5 rounded-xl border border-[#EAE6DD] bg-white px-3.5 transition focus-within:border-[#AAB9A3] focus-within:ring-2 focus-within:ring-[#E7EBDD] sm:max-w-[390px]">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#8B8C7D"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>

                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search subjects, codes, or instructors..."
                        aria-label="Search subjects"
                        class="w-full border-0 bg-transparent py-2.5 text-[12px] text-[#293B32] outline-none placeholder:text-[#A1A297] focus:ring-0"
                    />
                </div>

                <span class="flex items-center gap-2 text-[10px] text-[#929488]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#8EA287]"></span>
                    Each subject has its own calendar color
                </span>
            </div>

            <!-- Subject cards -->
            <div
                v-if="filteredSubjects.length"
                class="grid grid-cols-1 gap-4 md:grid-cols-2"
            >
                <article
                    v-for="subject in filteredSubjects"
                    :key="subject.id"
                    class="group min-w-0 overflow-hidden rounded-2xl border border-[#EAE6DD] bg-white shadow-[0_2px_8px_rgba(39,58,48,0.025)] transition duration-200 hover:-translate-y-0.5 hover:border-[#D8E0D2] hover:shadow-[0_8px_22px_rgba(39,58,48,0.05)]"
                >
                    <div
                        class="h-1 w-full"
                        :style="{ backgroundColor: subject.color || '#294B3C' }"
                    ></div>

                    <div class="p-5">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl font-serif text-[16px] font-semibold"
                                :style="{
                                    backgroundColor: `${subject.color || '#294B3C'}16`,
                                    color: subject.color || '#294B3C',
                                }"
                            >
                                {{ initials(subject.name) }}
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="grid h-8 w-8 place-items-center rounded-lg text-[#81887C] transition hover:bg-[#E7EBDD] hover:text-[#294B3C]"
                                    title="Edit subject"
                                    aria-label="Edit subject"
                                    @click="editSubject(subject)"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="15"
                                        height="15"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="grid h-8 w-8 place-items-center rounded-lg text-[#81887C] transition hover:bg-[#F5E4DD] hover:text-[#A85F4C]"
                                    title="Delete subject"
                                    aria-label="Delete subject"
                                    @click="deleteSubject(subject)"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="15"
                                        height="15"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4h8v2" />
                                        <path d="m19 6-1 14H6L5 6" />
                                        <path d="M10 11v5M14 11v5" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-[15px] font-semibold leading-relaxed text-[#354638]">
                                {{ subject.name }}
                            </h3>

                            <span
                                v-if="subject.code"
                                class="rounded-md bg-[#F0EFE8] px-2 py-1 text-[9px] font-semibold text-[#818578]"
                            >
                                {{ subject.code }}
                            </span>
                        </div>

                        <div class="mt-3 space-y-2">
                            <p class="flex items-center gap-2 text-[11px] leading-relaxed text-[#878B80]">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="shrink-0"
                                >
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="10" cy="7" r="4" />
                                    <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                {{ subject.instructor || 'No instructor added' }}
                            </p>

                            <p
                                v-if="subject.class_days?.length"
                                class="flex items-center gap-2 text-[11px] leading-relaxed text-[#878B80]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="shrink-0"
                                >
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 6v6l4 2" />
                                </svg>

                                <span>
                                    {{ subject.class_days.map(day => day.slice(0, 3)).join(', ') }}

                                    <template v-if="subject.start_time && subject.end_time">
                                        · {{ subject.start_time.slice(0, 5) }} – {{ subject.end_time.slice(0, 5) }}
                                    </template>
                                </span>
                            </p>

                            <p
                                v-if="subject.classroom"
                                class="flex items-center gap-2 text-[11px] leading-relaxed text-[#878B80]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="shrink-0"
                                >
                                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                                {{ subject.classroom }}
                            </p>
                        </div>

                        <div class="my-4 h-px bg-[#EEEBE3]"></div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2 text-[10px] text-[#777F73]">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M8 6h13M8 12h13M8 18h13" />
                                    <path d="M3 6h.01M3 12h.01M3 18h.01" />
                                </svg>

                                {{ subject.tasks_count ?? 0 }}
                                {{ subject.tasks_count === 1 ? 'related task' : 'related tasks' }}
                            </span>

                            <span
                                class="h-2.5 w-2.5 shrink-0 rounded-full ring-[3px] ring-[#F2F1EB]"
                                :style="{ backgroundColor: subject.color || '#294B3C' }"
                                :title="`Subject color: ${subject.color || '#294B3C'}`"
                            ></span>
                        </div>
                    </div>
                </article>

                <!-- Add another subject -->
                <button
                    type="button"
                    class="flex min-h-[190px] flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-[#D9DDD1] bg-white/50 px-5 py-6 text-[#697A69] transition hover:-translate-y-0.5 hover:border-[#9CAD96] hover:bg-[#F0F2E9]"
                    @click="openCreateForm"
                >
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#E8EEE2] text-[22px] text-[#49694E]">
                        +
                    </span>

                    <strong class="text-[12px] font-semibold">
                        Add another subject
                    </strong>

                    <span class="text-[10px] text-[#96988D]">
                        Make space for another class
                    </span>
                </button>
            </div>

            <!-- Search empty state -->
            <div
                v-else-if="searchQuery"
                class="rounded-2xl border border-dashed border-[#DFDFD4] bg-white/60 px-5 py-12 text-center"
            >
                <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-[#E7EBDD] text-[#557653]">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </div>

                <h3 class="font-serif text-[21px] font-medium text-[#294B3C]">
                    No matching subjects
                </h3>

                <p class="mx-auto mt-2 max-w-[320px] text-[12px] leading-relaxed text-[#888B81]">
                    Try searching with a different subject name, code, or instructor.
                </p>

                <button
                    type="button"
                    class="secondary-button mt-5"
                    @click="searchQuery = ''"
                >
                    Clear search
                </button>
            </div>

            <!-- First-use empty state -->
            <div
                v-else
                class="rounded-2xl border border-dashed border-[#DFDFD4] bg-white/60 px-5 py-12 text-center"
            >
                <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-[#E7EBDD] text-[#557653]">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                    </svg>
                </div>

                <h3 class="font-serif text-[21px] font-medium text-[#294B3C]">
                    Your semester starts here
                </h3>

                <p class="mx-auto mt-2 max-w-[340px] text-[12px] leading-relaxed text-[#888B81]">
                    Add your first subject to start organizing classes, assignments, exams, and study time.
                </p>

                <button
                    type="button"
                    class="primary-button mt-5"
                    @click="openCreateForm"
                >
                    <span class="text-lg font-normal leading-none">+</span>
                    Add your first subject
                </button>
            </div>

            <p class="mt-5 text-center font-serif text-[12px] italic text-[#A0A194]">
                A little organization goes a long way.
            </p>
        </section>

        <!-- Create / edit modal -->
        <Transition name="modal">
            <div
                v-if="showForm"
                class="modal-overlay"
                @click.self="resetForm"
                @keydown.esc="resetForm"
            >
                <section
                    class="subject-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-title"
                >
                    <header class="modal-header">
                        <div>
                            <p class="mb-2 text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                                {{ isEditing ? 'UPDATE YOUR CLASSES' : 'BUILD YOUR SEMESTER' }}
                            </p>

                            <h2
                                id="modal-title"
                                class="font-serif text-[27px] font-medium text-[#294B3C]"
                            >
                                {{ isEditing ? 'Edit subject' : 'Add a subject' }}
                            </h2>

                            <p class="mt-2 text-[11px] leading-relaxed text-[#888B81]">
                                Add the class details so your academic plans stay organized.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-[#E9E7DE] bg-[#F8F7F2] text-[22px] leading-none text-[#6C796C] transition hover:bg-[#EEEEE5]"
                            aria-label="Close form"
                            @click="resetForm"
                        >
                            ×
                        </button>
                    </header>

                    <form class="subject-form" @submit.prevent="saveSubject">
                        <label class="form-label">
                            Subject name <span class="required">*</span>

                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Web Development"
                                required
                                autofocus
                            />

                            <small v-if="form.errors.name" class="error">
                                {{ form.errors.name }}
                            </small>
                        </label>

                        <div class="form-row">
                            <label class="form-label">
                                Subject code
                                <span class="optional">Optional</span>

                                <input
                                    v-model="form.code"
                                    type="text"
                                    placeholder="e.g. IT 302"
                                />

                                <small v-if="form.errors.code" class="error">
                                    {{ form.errors.code }}
                                </small>
                            </label>

                            <label class="form-label">
                                Instructor
                                <span class="optional">Optional</span>

                                <input
                                    v-model="form.instructor"
                                    type="text"
                                    placeholder="Instructor name"
                                />

                                <small v-if="form.errors.instructor" class="error">
                                    {{ form.errors.instructor }}
                                </small>
                            </label>
                        </div>

                        <!-- Class schedule -->
                        <div class="schedule-section">
                            <div>
                                <p class="text-[11px] font-bold text-[#48584A]">
                                    Class schedule
                                </p>
                                <p class="mt-1 text-[10px] leading-relaxed text-[#929488]">
                                    Select the days and times for this subject.
                                </p>
                            </div>

                            <div class="day-options">
                                <label
                                    v-for="day in weekDays"
                                    :key="day"
                                    class="day-option"
                                    :class="{ selected: form.class_days.includes(day) }"
                                >
                                    <input
                                        v-model="form.class_days"
                                        type="checkbox"
                                        :value="day"
                                    />
                                    <span>{{ day.slice(0, 3) }}</span>
                                </label>
                            </div>

                            <div class="form-row">
                                <label class="form-label">
                                    Start time
                                    <input
                                        v-model="form.start_time"
                                        type="time"
                                    />
                                </label>

                                <label class="form-label">
                                    End time
                                    <input
                                        v-model="form.end_time"
                                        type="time"
                                        :min="form.start_time || undefined"
                                    />
                                </label>
                            </div>

                            <label class="form-label mt-3">
                                Classroom / Location
                                <span class="optional">Optional</span>
                                <input
                                    v-model="form.classroom"
                                    type="text"
                                    placeholder="e.g. Room 302 or Online"
                                />
                            </label>

                            <small v-if="form.errors.class_days" class="error">
                                {{ form.errors.class_days }}
                            </small>

                            <small v-if="form.errors.start_time" class="error">
                                {{ form.errors.start_time }}
                            </small>

                            <small v-if="form.errors.end_time" class="error">
                                {{ form.errors.end_time }}
                            </small>
                        </div>

                        <!-- Subject color -->
                        <div class="color-section">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold text-[#48584A]">
                                        Subject color
                                    </p>
                                    <p class="mt-1 text-[10px] text-[#929488]">
                                        Choose a color to identify this class.
                                    </p>
                                </div>

                                <span
                                    class="h-6 w-6 shrink-0 rounded-full border-[3px] border-white shadow-[0_0_0_1px_#E1DFD5]"
                                    :style="{ backgroundColor: form.color }"
                                ></span>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <button
                                    v-for="preset in colorPresets"
                                    :key="preset.value"
                                    type="button"
                                    class="color-swatch"
                                    :class="{ selected: form.color.toLowerCase() === preset.value.toLowerCase() }"
                                    :style="{ backgroundColor: preset.value }"
                                    :title="preset.name"
                                    :aria-label="`Choose ${preset.name}`"
                                    :aria-pressed="form.color.toLowerCase() === preset.value.toLowerCase()"
                                    @click="selectColor(preset.value)"
                                >
                                    <span v-if="form.color.toLowerCase() === preset.value.toLowerCase()">✓</span>
                                </button>

                                <label
                                    class="custom-color"
                                    title="Choose a custom color"
                                >
                                    <input
                                        v-model="form.color"
                                        type="color"
                                        aria-label="Choose custom subject color"
                                    />
                                    <span>+</span>
                                </label>
                            </div>

                            <p class="mt-3 text-[9px] tracking-[1px] text-[#93968A]">
                                {{ form.color.toUpperCase() }}
                            </p>

                            <small v-if="form.errors.color" class="error">
                                {{ form.errors.color }}
                            </small>
                        </div>

                        <div class="modal-actions">
                            <button
                                class="secondary-button"
                                type="button"
                                :disabled="form.processing"
                                @click="resetForm"
                            >
                                Cancel
                            </button>

                            <button
                                class="primary-button"
                                type="submit"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'Saving...'
                                        : isEditing
                                            ? 'Save changes'
                                            : 'Create subject'
                                }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.primary-button,
.secondary-button {
    display: inline-flex;
    min-height: 40px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border-radius: 11px;
    padding: 0 16px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    transition: background 180ms ease, border-color 180ms ease, transform 180ms ease;
    cursor: pointer;
}

.primary-button {
    border: 1px solid #294B3C;
    background: #294B3C;
    color: #FFFEFA;
}

.primary-button:hover:not(:disabled) {
    transform: translateY(-1px);
    background: #203D30;
}

.primary-button:disabled {
    cursor: wait;
    opacity: 0.6;
}

.secondary-button {
    border: 1px solid #E4E3DA;
    background: #FFFEFA;
    color: #4A5D4E;
}

.secondary-button:hover:not(:disabled) {
    background: #F0F0E8;
}

.secondary-button:disabled {
    cursor: wait;
    opacity: 0.6;
}

.modal-overlay {
    position: fixed;
    z-index: 100;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 24px;
    background: rgb(34 47 38 / 42%);
    backdrop-filter: blur(4px);
}

.subject-modal {
    width: 100%;
    max-width: 540px;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    border: 1px solid rgb(255 255 255 / 65%);
    border-radius: 20px;
    background: #FFFEFA;
    box-shadow: 0 24px 80px rgb(28 43 33 / 18%);
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 24px 26px 19px;
    border-bottom: 1px solid #EEEBE3;
}

.subject-form {
    padding: 21px 26px 25px;
}

.form-label {
    display: block;
    min-width: 0;
    margin-bottom: 16px;
    color: #48584A;
    font-size: 11px;
    font-weight: 700;
}

.required {
    color: #B56E58;
}

.optional {
    display: block;
    margin-top: 3px;
    color: #A0A195;
    font-size: 9px;
    font-weight: 400;
}

.subject-form input:not([type="color"]) {
    box-sizing: border-box;
    display: block;
    width: 100%;
    min-width: 0;
    margin-top: 7px;
    padding: 10px 12px;
    border: 1px solid #E4E3D9;
    border-radius: 9px;
    outline: none;
    background: #FFFFFF;
    color: #293B32;
    font-family: inherit;
    font-size: 12px;
    font-weight: 400;
    transition: border-color 180ms ease, box-shadow 180ms ease;
}

.subject-form input:not([type="color"]):focus {
    border-color: #94A68F;
    box-shadow: 0 0 0 3px rgb(224 232 218 / 75%);
}

.form-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 14px;
}

.schedule-section,
.color-section {
    margin: 3px 0 18px;
    padding: 16px;
    border: 1px solid #ECEAE1;
    border-radius: 12px;
    background: #FAF9F5;
}

.day-options {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin: 15px 0;
}

.day-option {
    cursor: pointer;
}

.day-option input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
}

.day-option span {
    display: inline-flex;
    min-width: 39px;
    min-height: 34px;
    align-items: center;
    justify-content: center;
    border: 1px solid #E4E3D9;
    border-radius: 9px;
    background: #FFFEFA;
    color: #777F73;
    font-size: 10px;
    transition: 160ms ease;
}

.day-option.selected span {
    border-color: #789477;
    background: #E5EDE3;
    color: #294B3C;
    font-weight: 700;
}

.day-option input:focus-visible + span {
    outline: 2px solid #789477;
    outline-offset: 2px;
}

.color-swatch {
    display: grid;
    width: 29px;
    height: 29px;
    flex: 0 0 29px;
    place-items: center;
    border: 2px solid transparent;
    border-radius: 50%;
    color: white;
    font-size: 12px;
    cursor: pointer;
    transition: transform 160ms ease, box-shadow 160ms ease;
}

.color-swatch:hover {
    transform: scale(1.08);
}

.color-swatch.selected {
    box-shadow: 0 0 0 2px #FAF9F5, 0 0 0 3px #7C8C77;
}

.custom-color {
    position: relative;
    display: grid;
    width: 29px;
    height: 29px;
    flex: 0 0 29px;
    place-items: center;
    overflow: hidden;
    border: 1px dashed #C8CBBF;
    border-radius: 50%;
    background: white;
    color: #697B68;
    font-size: 18px;
    cursor: pointer;
}

.custom-color input[type="color"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    opacity: 0;
    cursor: pointer;
}

.custom-color span {
    pointer-events: none;
}

.error {
    display: block;
    margin-top: 5px;
    color: #B15E4B;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.5;
}

.modal-actions {
    position: sticky;
    bottom: 0;
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 22px;
    padding-top: 17px;
    border-top: 1px solid #EEEBE3;
    background: #FFFEFA;
}

/* Modal animation */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 180ms ease;
}

.modal-enter-active .subject-modal,
.modal-leave-active .subject-modal {
    transition: transform 180ms ease, opacity 180ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .subject-modal,
.modal-leave-to .subject-modal {
    opacity: 0;
    transform: translateY(8px) scale(0.99);
}

/* Responsive */
@media (max-width: 580px) {
    .modal-overlay {
        align-items: flex-end;
        padding: 10px;
    }

    .subject-modal {
        max-height: calc(100dvh - 20px);
        border-radius: 18px;
    }

    .modal-header {
        padding: 21px 19px 17px;
    }

    .subject-form {
        padding: 19px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
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
