
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

    return props.subjects.filter(subject => {
        return [
            subject.name,
            subject.code,
            subject.instructor,
        ].some(value => String(value ?? '').toLowerCase().includes(query))
    })
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

    <div class="page">
        <div class="page-inner">
            <!-- Header -->
            <header class="page-header">
                <div>
                    <p class="eyebrow">YOUR ACADEMIC SPACE</p>
                    <h1>My subjects<span class="title-period">.</span></h1>
                    <p class="subtitle">
                        All your classes, together in one place.
                    </p>
                </div>

                <button
                    class="primary-button new-subject-button"
                    type="button"
                    @click="openCreateForm"
                >
                    <span class="plus-icon">+</span>
                    Add subject
                </button>
            </header>

            <!-- Summary -->
            <section class="summary">
                <div class="summary-copy">
                    <span class="summary-icon">▤</span>

                    <div>
                        <p class="summary-overline">YOUR SEMESTER</p>
                        <h2>
                            {{ subjects.length === 0
                                ? 'A fresh start'
                                : subjects.length === 1
                                    ? 'Your first class is here'
                                    : 'Your classes, organized' }}
                        </h2>
                        <p>
                            {{
                                subjects.length === 0
                                    ? 'Add a subject to begin building your academic planner.'
                                    : 'Keep your classes organized and your deadlines connected.'
                            }}
                        </p>
                    </div>
                </div>

                <div class="summary-count">
                    <strong>{{ subjects.length }}</strong>
                    <span>{{ subjects.length === 1 ? 'subject' : 'subjects' }}</span>
                </div>
            </section>

            <!-- Subject list -->
            <section class="subjects-section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">YOUR CLASSES</p>
                        <h2>Subject library</h2>
                        <p class="section-description">
                            Your subjects and the tasks connected to them.
                        </p>
                    </div>

                    <span class="result-count">
                        {{ filteredSubjects.length }}
                        {{ filteredSubjects.length === 1 ? 'subject' : 'subjects' }}
                    </span>
                </div>

                <!-- Search -->
                <div class="list-controls">
                    <div class="search-box">
                        <span class="search-icon">⌕</span>
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search subjects, codes, or instructors..."
                            aria-label="Search subjects"
                        />
                    </div>

                    <span class="helper-text">
                        <span class="helper-dot"></span>
                        Each subject can have its own calendar color
                    </span>
                </div>

                <!-- Cards -->
                <div v-if="filteredSubjects.length" class="subject-grid">
                    <article
                        v-for="subject in filteredSubjects"
                        :key="subject.id"
                        class="subject-card"
                    >
                        <div
                            class="subject-color-strip"
                            :style="{ backgroundColor: subject.color || '#294B3C' }"
                        ></div>

                        <div class="subject-card-content">
                            <div class="subject-card-top">
                                <div
                                    class="subject-avatar"
                                    :style="{
                                        backgroundColor: `${subject.color || '#294B3C'}16`,
                                        color: subject.color || '#294B3C',
                                    }"
                                >
                                    {{ initials(subject.name) }}
                                </div>

                                <div class="subject-card-actions">
                                    <button
                                        type="button"
                                        class="icon-button"
                                        title="Edit subject"
                                        aria-label="Edit subject"
                                        @click="editSubject(subject)"
                                    >
                                        ✎
                                    </button>

                                    <button
                                        type="button"
                                        class="icon-button delete-icon"
                                        title="Delete subject"
                                        aria-label="Delete subject"
                                        @click="deleteSubject(subject)"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>

                            <div class="subject-heading">
                                <h3>{{ subject.name }}</h3>
                                <span
                                    v-if="subject.code"
                                    class="subject-code"
                                >
                                    {{ subject.code }}
                                </span>
                            </div>

                            <p class="instructor">
                                <span class="instructor-icon">♙</span>
                                {{ subject.instructor || 'No instructor added' }}
                            </p>

                            
<p
    v-if="subject.class_days?.length"
    class="instructor"
>
    <span class="instructor-icon">◷</span>
    {{ subject.class_days.map(day => day.slice(0, 3)).join(', ') }}

    <template v-if="subject.start_time && subject.end_time">
        · {{ subject.start_time.slice(0, 5) }}
        – {{ subject.end_time.slice(0, 5) }}
    </template>
</p>

<p
    v-if="subject.classroom"
    class="instructor"
>
    <span class="instructor-icon">⌖</span>
    {{ subject.classroom }}
</p>


                            <div class="card-divider"></div>

                            <div class="subject-card-footer">
                                <span class="task-summary">
                                    <span class="task-summary-icon">☷</span>
                                    {{ subject.tasks_count ?? 0 }}
                                    {{ subject.tasks_count === 1 ? 'related task' : 'related tasks' }}
                                </span>

                                <span
                                    class="color-indicator"
                                    :style="{ backgroundColor: subject.color || '#294B3C' }"
                                    :title="`Subject color: ${subject.color || '#294B3C'}`"
                                ></span>
                            </div>
                        </div>
                    </article>

                    <!-- Add card -->
                    <button
                        type="button"
                        class="add-subject-card"
                        @click="openCreateForm"
                    >
                        <span class="add-card-icon">+</span>
                        <strong>Add another subject</strong>
                        <span>Make space for another class</span>
                    </button>
                </div>

                <!-- Search empty state -->
                <div
                    v-else-if="searchQuery"
                    class="empty-state"
                >
                    <div class="empty-illustration">⌕</div>
                    <h3>No matching subjects</h3>
                    <p>
                        Try searching with a different subject name, code, or instructor.
                    </p>

                    <button
                        type="button"
                        class="secondary-button"
                        @click="searchQuery = ''"
                    >
                        Clear search
                    </button>
                </div>

                <!-- First-use empty state -->
                <div v-else class="empty-state">
                    <div class="empty-illustration">▤</div>
                    <h3>Your semester starts here</h3>
                    <p>
                        Add your first subject to start organizing classes,
                        assignments, exams, and study time.
                    </p>

                    <button
                        type="button"
                        class="primary-button"
                        @click="openCreateForm"
                    >
                        <span class="plus-icon">+</span>
                        Add your first subject
                    </button>
                </div>

                <p class="list-footer">
                    A little organization goes a long way.
                </p>
            </section>
        </div>

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
                            <p class="eyebrow">
                                {{ isEditing ? 'UPDATE YOUR CLASSES' : 'BUILD YOUR SEMESTER' }}
                            </p>

                            <h2 id="modal-title">
                                {{ isEditing ? 'Edit subject' : 'Add a subject' }}
                            </h2>

                            <p>
                                Add the class details so your academic plans stay organized.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="close-button"
                            aria-label="Close form"
                            @click="resetForm"
                        >
                            ×
                        </button>
                    </header>

                    <form class="subject-form" @submit.prevent="saveSubject">
                        <label>
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
                            <label>
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

                            <label>
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
                    <div class="schedule-section">
                        <div class="schedule-heading">
                            <label>Class schedule</label>
                            <p>Select the days and times for this subject.</p>
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

                        <div class="form-row schedule-times">
                            <label>
                                Start time
                                <input
                                    v-model="form.start_time"
                                    type="time"
                                />
                            </label>

                            <label>
                                End time
                                <input
                                    v-model="form.end_time"
                                    type="time"
                                    :min="form.start_time || undefined"
                                />
                            </label>
                        </div>

                        <label class="classroom-field">
                            Classroom / Location
                            <span class="optional">Optional</span>
                            <input
                                v-model="form.classroom"
                                type="text"
                                placeholder="e.g. Room 302 or Online"
                            />
                        </label>

                        <small
                            v-if="form.errors.class_days"
                            class="error"
                        >
                            {{ form.errors.class_days }}
                        </small>

                        <small
                            v-if="form.errors.start_time"
                            class="error"
                        >
                            {{ form.errors.start_time }}
                        </small>

                        <small
                            v-if="form.errors.end_time"
                            class="error"
                        >
                            {{ form.errors.end_time }}
                        </small>
                    </div>


                        <div class="color-section">
                            <div class="color-section-heading">
                                <div>
                                    <label class="color-label">Subject color</label>
                                    <p>Choose a color to identify this class.</p>
                                </div>

                                <span
                                    class="selected-color"
                                    :style="{ backgroundColor: form.color }"
                                ></span>
                            </div>

                            <div class="color-options">
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

                                <label class="custom-color" title="Choose a custom color">
                                    <input
                                        v-model="form.color"
                                        type="color"
                                        aria-label="Choose custom subject color"
                                    />
                                    <span>+</span>
                                </label>
                            </div>

                            <p class="color-code">{{ form.color.toUpperCase() }}</p>

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
:global(body) {
    margin: 0;
    background: #f8f7f2;
}

.page {
    min-height: 100vh;
    padding: 36px 24px 48px;
    color: #2c4034;
    background: #f8f7f2;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.page-inner {
    width: 100%;
    max-width: 1060px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
}


.eyebrow {
    margin: 0 0 8px;
    color: #8a9385;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.7px;
}

h1,
h2,
h3,
p {
    overflow-wrap: anywhere;
}

h1 {
    margin: 0;
    color: #2b4033;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(30px, 4vw, 40px);
    font-weight: 500;
    letter-spacing: -1.1px;
    line-height: 1.2;
}

.title-period {
    color: #bb806a;
}

.subtitle {
    margin: 10px 0 0;
    color: #81867c;
    font-size: 13px;
    line-height: 1.6;
}

.primary-button,
.secondary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 42px;
    padding: 0 17px;
    border: 1px solid transparent;
    border-radius: 11px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    cursor: pointer;
    transition: background 180ms ease, border-color 180ms ease, transform 180ms ease;
}

.primary-button {
    background: #294b3c;
    color: #fffefa;
}

.primary-button:hover:not(:disabled) {
    background: #203d30;
    transform: translateY(-1px);
}

.primary-button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.secondary-button {
    border-color: #e4e3da;
    background: #fffefa;
    color: #4a5d4e;
}

.secondary-button:hover:not(:disabled) {
    background: #f0f0e8;
}

.secondary-button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.plus-icon {
    font-size: 18px;
    font-weight: 400;
    line-height: 1;
}

.new-subject-button {
    flex-shrink: 0;
    margin-bottom: 2px;
}

/* Summary */

.summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 35px;
    padding: 23px 25px;
    border: 1px solid #e5e7dc;
    border-radius: 17px;
    background: #edf0e7;
}

.summary-copy {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
}

.summary-icon {
    display: grid;
    width: 45px;
    height: 45px;
    flex: 0 0 45px;
    place-items: center;
    border-radius: 14px;
    background: #dce5d5;
    color: #42614a;
    font-size: 21px;
}

.summary-overline {
    margin: 0 0 5px;
    color: #788a77;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.summary-copy h2 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
}

.summary-copy > div > p:last-child {
    margin: 5px 0 0;
    color: #81877b;
    font-size: 11px;
    line-height: 1.6;
}

.summary-count {
    display: flex;
    flex: 0 0 auto;
    flex-direction: column;
    align-items: flex-end;
}

.summary-count strong {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 32px;
    font-weight: 500;
    line-height: 1.1;
}

.summary-count span {
    margin-top: 5px;
    color: #7e877a;
    font-size: 10px;
}

/* Section header */

.subjects-section {
    min-width: 0;
}

.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 19px;
}

.section-heading .eyebrow {
    margin-bottom: 5px;
}

.section-heading h2 {
    margin: 0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 27px;
    font-weight: 500;
    letter-spacing: -0.5px;
}

.section-description {
    margin: 6px 0 0;
    color: #888b81;
    font-size: 12px;
    line-height: 1.6;
}

.result-count {
    flex-shrink: 0;
    padding-bottom: 3px;
    color: #8b8e84;
    font-size: 11px;
}

/* Search */

.list-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 17px;
}

.search-box {
    display: flex;
    align-items: center;
    width: min(100%, 360px);
    min-height: 41px;
    padding: 0 12px;
    border: 1px solid #e7e5dc;
    border-radius: 10px;
    background: #fffefa;
    transition: border-color 180ms ease, box-shadow 180ms ease;
}

.search-box:focus-within {
    border-color: #9cac97;
    box-shadow: 0 0 0 3px rgb(224 232 218 / 65%);
}

.search-icon {
    margin-right: 8px;
    color: #8a9385;
    font-size: 21px;
}

.search-box input {
    width: 100%;
    min-width: 0;
    padding: 10px 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: #2c4034;
    font: inherit;
    font-size: 11px;
}

.search-box input::placeholder {
    color: #a1a297;
}

.helper-text {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #929488;
    font-size: 10px;
}

.helper-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #8ea287;
}

/* Subject cards */

.subject-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 13px;
}

.subject-card {
    position: relative;
    min-width: 0;
    overflow: hidden;
    border: 1px solid #e9e7df;
    border-radius: 15px;
    background: #fffefa;
    box-shadow: 0 2px 7px rgb(39 58 48 / 2%);
    transition: border-color 200ms ease, box-shadow 200ms ease, transform 200ms ease;
}

.subject-card:hover {
    transform: translateY(-2px);
    border-color: #d8e0d2;
    box-shadow: 0 8px 22px rgb(39 58 48 / 5%);
}

.subject-color-strip {
    width: 100%;
    height: 4px;
}

.subject-card-content {
    padding: 17px 18px 15px;
}

.subject-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 17px;
}

.subject-avatar {
    display: grid;
    width: 43px;
    height: 43px;
    flex: 0 0 43px;
    place-items: center;
    border-radius: 13px;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.2px;
}

.subject-card-actions {
    display: flex;
    gap: 4px;
}

.icon-button {
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border: 1px solid transparent;
    border-radius: 8px;
    background: transparent;
    color: #81887c;
    font-family: inherit;
    font-size: 16px;
    cursor: pointer;
    transition: background 180ms ease, color 180ms ease;
}

.icon-button:hover {
    background: #edf0e8;
    color: #294b3c;
}

.icon-button.delete-icon:hover {
    background: #f8e9e3;
    color: #a85f4c;
}

.subject-heading {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.subject-heading h3 {
    margin: 0;
    color: #354638;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.5;
}

.subject-code {
    padding: 4px 7px;
    border-radius: 6px;
    background: #f0efe8;
    color: #818578;
    font-size: 9px;
    font-weight: 650;
}

.instructor {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 8px 0 0;
    color: #878b80;
    font-size: 11px;
    line-height: 1.6;
}

.instructor-icon {
    color: #9a9c8f;
    font-size: 13px;
}

.card-divider {
    height: 1px;
    margin: 17px 0 12px;
    background: #eeece5;
}

.subject-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.task-summary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #777f73;
    font-size: 10px;
}

.task-summary-icon {
    color: #8b9885;
    font-size: 14px;
}

.color-indicator {
    width: 9px;
    height: 9px;
    flex: 0 0 9px;
    border-radius: 50%;
    box-shadow: 0 0 0 3px #f2f1eb;
}

/* Add another subject card */

.add-subject-card {
    display: flex;
    min-height: 200px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 20px;
    border: 1px dashed #d9ddd1;
    border-radius: 15px;
    background: rgb(255 254 250 / 45%);
    color: #697a69;
    font-family: inherit;
    cursor: pointer;
    transition: border-color 180ms ease, background 180ms ease, transform 180ms ease;
}

.add-subject-card:hover {
    transform: translateY(-2px);
    border-color: #9cad96;
    background: #f0f2e9;
}

.add-card-icon {
    display: grid;
    width: 38px;
    height: 38px;
    margin-bottom: 3px;
    place-items: center;
    border-radius: 12px;
    background: #e8eee2;
    color: #49694e;
    font-size: 22px;
}

.add-subject-card strong {
    font-size: 12px;
    font-weight: 700;
}

.add-subject-card > span:last-child {
    color: #96988d;
    font-size: 10px;
}

/* Empty state */

.empty-state {
    padding: 48px 20px;
    border: 1px dashed #dfdfd4;
    border-radius: 15px;
    background: rgb(255 254 250 / 60%);
    text-align: center;
}

.empty-illustration {
    display: grid;
    width: 48px;
    height: 48px;
    margin: 0 auto 15px;
    place-items: center;
    border-radius: 16px;
    background: #e7eee2;
    color: #557653;
    font-size: 22px;
}

.empty-state h3 {
    margin: 0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 21px;
    font-weight: 500;
}

.empty-state p {
    max-width: 320px;
    margin: 8px auto 18px;
    color: #888b81;
    font-size: 12px;
    line-height: 1.7;
}

.list-footer {
    margin: 20px 0 0;
    color: #a0a194;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 12px;
    font-style: italic;
    text-align: center;
}

/* Modal */

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
    background: #fffefa;
    box-shadow: 0 24px 80px rgb(28 43 33 / 18%);
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 25px 27px 20px;
    border-bottom: 1px solid #eeece4;
}

.modal-header h2 {
    margin: 0;
    color: #2c4034;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 27px;
    font-weight: 500;
}

.modal-header > div > p:last-child {
    margin: 7px 0 0;
    color: #888b81;
    font-size: 11px;
    line-height: 1.6;
}

.close-button {
    display: grid;
    width: 33px;
    height: 33px;
    flex: 0 0 33px;
    place-items: center;
    border: 1px solid #e9e7de;
    border-radius: 10px;
    background: #f8f7f2;
    color: #6c796c;
    font-family: inherit;
    font-size: 23px;
    line-height: 1;
    cursor: pointer;
    transition: background 180ms ease;
}

.close-button:hover {
    background: #eeeee5;
}

.subject-form {
    padding: 22px 27px 27px;
}

.subject-form > label,
.form-row > label {
    display: block;
    min-width: 0;
    margin-bottom: 17px;
    color: #48584a;
    font-size: 11px;
    font-weight: 700;
}

.required {
    color: #b56e58;
}

.optional {
    display: block;
    margin-top: 3px;
    color: #a0a195;
    font-size: 9px;
    font-weight: 400;
}

.subject-form input:not([type="color"]) {
    box-sizing: border-box;
    display: block;
    width: 100%;
    min-width: 0;
    margin-top: 7px;
    padding: 11px 12px;
    border: 1px solid #e4e3d9;
    border-radius: 9px;
    outline: none;
    background: #fff;
    color: #2c4034;
    font-family: inherit;
    font-size: 12px;
    font-weight: 400;
    transition: border-color 180ms ease, box-shadow 180ms ease;
}

.subject-form input:not([type="color"]):focus {
    border-color: #94a68f;
    box-shadow: 0 0 0 3px rgb(224 232 218 / 75%);
}

.form-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 14px;
}

.color-section {
    margin-top: 2px;
    padding: 16px;
    border: 1px solid #eceae1;
    border-radius: 12px;
    background: #faf9f5;
}

.color-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.color-label {
    color: #48584a;
    font-size: 11px;
    font-weight: 700;
}

.color-section-heading p {
    margin: 5px 0 0;
    color: #929488;
    font-size: 10px;
}

.selected-color {
    width: 24px;
    height: 24px;
    flex: 0 0 24px;
    border: 3px solid white;
    border-radius: 50%;
    box-shadow: 0 0 0 1px #e1dfd5;
}

.color-options {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 11px;
    margin-top: 17px;
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
    box-shadow: 0 0 0 2px #faf9f5, 0 0 0 3px #7c8c77;
}

.custom-color {
    position: relative;
    display: grid;
    width: 29px;
    height: 29px;
    flex: 0 0 29px;
    place-items: center;
    overflow: hidden;
    border: 1px dashed #c8cbbf;
    border-radius: 50%;
    background: white;
    color: #697b68;
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

.color-code {
    margin: 11px 0 0;
    color: #93968a;
    font-size: 9px;
    letter-spacing: 1px;
}

.error {
    display: block;
    margin-top: 5px;
    color: #b15e4b;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.5;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 23px;
    padding-top: 18px;
    border-top: 1px solid #eeece4;
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


.schedule-section {
    margin: 3px 0 20px;
    padding: 16px;
    border: 1px solid #eceae1;
    border-radius: 12px;
    background: #faf9f5;
}

.schedule-heading > label,
.classroom-field {
    color: #48584a;
    font-size: 11px;
    font-weight: 700;
}

.schedule-heading p {
    margin: 5px 0 0;
    color: #929488;
    font-size: 10px;
    line-height: 1.6;
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
    border: 1px solid #e4e3d9;
    border-radius: 9px;
    background: #fffefa;
    color: #777f73;
    font-size: 10px;
    transition: 160ms ease;
}

.day-option.selected span {
    border-color: #789477;
    background: #e5ede3;
    color: #294b3c;
    font-weight: 700;
}

.day-option input:focus-visible + span {
    outline: 2px solid #789477;
    outline-offset: 2px;
}

.schedule-times {
    margin-top: 5px;
}

.classroom-field {
    display: block;
    margin-top: 3px;
}

.classroom-field input {
    font-weight: 400 !important;
}


/* Responsive */

@media (max-width: 800px) {
    .page {
        padding: 25px 18px 38px;
    }

    .list-controls {
        align-items: flex-start;
        flex-direction: column;
    }

    .search-box {
        width: 100%;
        box-sizing: border-box;
    }
}

@media (max-width: 580px) {
    .page {
        padding: 20px 14px 32px;
    }

    .page-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 18px;
    }

    .back-link {
        margin-bottom: 20px;
    }

    .new-subject-button {
        width: 100%;
    }

    .summary {
        padding: 18px;
    }

    .summary-copy {
        align-items: flex-start;
        gap: 11px;
    }

    .summary-icon {
        width: 38px;
        height: 38px;
        flex-basis: 38px;
    }

    .summary-count strong {
        font-size: 26px;
    }

    .section-heading h2 {
        font-size: 25px;
    }

    .subject-grid {
        grid-template-columns: 1fr;
    }

    .add-subject-card {
        min-height: 150px;
    }

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

    .modal-actions {
        position: sticky;
        bottom: 0;
        background: #fffefa;
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
