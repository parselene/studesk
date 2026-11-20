<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
})

const showForm = ref(false)
const editingId = ref(null)
const activeFilter = ref('all')
const searchQuery = ref('')

function localDateTime(value) {
    if (!value) return ''

    const date = new Date(value)
    const offset = date.getTimezoneOffset()

    return new Date(date.getTime() - offset * 60000)
        .toISOString()
        .slice(0, 16)
}

const form = useForm({
    title: '',
    description: '',
    subject_id: '',
    task_type: 'assignment',
    priority: 'medium',
    status: 'pending',
    due_at: '',
    estimated_minutes: '',
})

const filters = [
    { label: 'All tasks', value: 'all' },
    { label: 'To do', value: 'pending' },
    { label: 'In progress', value: 'in_progress' },
    { label: 'Completed', value: 'completed' },
]

const pendingCount = computed(() =>
    props.tasks.filter(task => task.status === 'pending').length
)

const inProgressCount = computed(() =>
    props.tasks.filter(task => task.status === 'in_progress').length
)

const completedCount = computed(() =>
    props.tasks.filter(task => task.status === 'completed').length
)

const filteredTasks = computed(() => {
    let result = [...props.tasks]

    if (activeFilter.value !== 'all') {
        result = result.filter(task => task.status === activeFilter.value)
    }

    const query = searchQuery.value.trim().toLowerCase()

    if (query) {
        result = result.filter(task => {
            const title = task.title?.toLowerCase() ?? ''
            const subject = task.subject?.name?.toLowerCase() ?? ''
            const type = task.task_type?.toLowerCase() ?? ''

            return title.includes(query)
                || subject.includes(query)
                || type.includes(query)
        })
    }

    return result.sort((a, b) => {
        if (!a.due_at && !b.due_at) return 0
        if (!a.due_at) return 1
        if (!b.due_at) return -1

        return new Date(a.due_at) - new Date(b.due_at)
    })
})

function resetForm() {
    form.reset()
    form.clearErrors()
    editingId.value = null

    form.subject_id = ''
    form.task_type = 'assignment'
    form.priority = 'medium'
    form.status = 'pending'
    form.due_at = ''
    form.estimated_minutes = ''

    showForm.value = false
}

function openCreateForm() {
    resetForm()
    showForm.value = true
}

function openEditForm(task) {
    form.clearErrors()

    editingId.value = task.id
    form.title = task.title ?? ''
    form.description = task.description ?? ''
    form.subject_id = task.subject_id ? String(task.subject_id) : ''
    form.task_type = task.task_type ?? 'assignment'
    form.priority = task.priority ?? 'medium'
    form.status = task.status ?? 'pending'
    form.due_at = localDateTime(task.due_at)
    form.estimated_minutes = task.estimated_minutes ?? ''

    showForm.value = true
}

function saveTask() {
    const payload = {
        title: form.title,
        description: form.description || null,
        subject_id: form.subject_id || null,
        task_type: form.task_type,
        priority: form.priority,
        status: form.status,
        due_at: form.due_at || null,
        estimated_minutes: form.estimated_minutes || null,
    }

    if (editingId.value) {
        form.transform(() => payload).put(`/tasks/${editingId.value}`, {
            onSuccess: () => resetForm(),
        })
    } else {
        form.transform(() => payload).post('/tasks', {
            onSuccess: () => resetForm(),
        })
    }
}

function toggleComplete(task) {
    const payload = {
        title: task.title,
        description: task.description ?? null,
        subject_id: task.subject_id ?? null,
        task_type: task.task_type ?? 'assignment',
        priority: task.priority ?? 'medium',
        status: task.status === 'completed' ? 'pending' : 'completed',
        due_at: task.due_at ?? null,
        estimated_minutes: task.estimated_minutes ?? null,
    }

    router.put(`/tasks/${task.id}`, payload)
}

function deleteTask(task) {
    if (confirm(`Delete "${task.title}"? This cannot be undone.`)) {
        router.delete(`/tasks/${task.id}`)
    }
}

function formatDate(value) {
    if (!value) return 'No due date'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) return 'No due date'

    return date.toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}

function formatType(value) {
    const labels = {
        assignment: 'Assignment',
        project: 'Project',
        exam: 'Exam',
        quiz: 'Quiz',
        study: 'Study session',
    }

    return labels[value] ?? value
}

function isOverdue(task) {
    return task.status !== 'completed'
        && task.due_at
        && new Date(task.due_at) < new Date()
}

function dueLabel(task) {
    if (!task.due_at) return 'No due date'
    if (task.status === 'completed') return `Completed · Due ${formatDate(task.due_at)}`
    if (isOverdue(task)) return `Overdue · ${formatDate(task.due_at)}`

    return `Due ${formatDate(task.due_at)}`
}
</script>

<template>
    <Head title="Tasks | StuDesk" />

    <div class="page">
        <div class="page-inner">
            <!-- Header -->
            <header class="page-header">
                <div>
                    <p class="eyebrow">YOUR ACADEMIC SPACE</p>
                    <h1>Tasks & deadlines<span class="title-period">.</span></h1>

                    <p class="subtitle">
                        One thing at a time. You've got this.
                    </p>
                </div>

                <button class="primary-button new-task-button" @click="openCreateForm">
                    <span class="plus-icon">+</span>
                    New task
                </button>
            </header>

            <!-- Compact overview -->
            <section class="overview">
                <div class="overview-intro">
                    <span class="overview-icon">✳</span>
                    <div>
                        <h2>Your workload, at a glance</h2>
                        <p>A little progress adds up.</p>
                    </div>
                </div>

                <div class="overview-stats">
                    <div class="mini-stat">
                        <span class="stat-dot dot-neutral"></span>
                        <div>
                            <strong>{{ tasks.length }}</strong>
                            <span>Total</span>
                        </div>
                    </div>

                    <div class="mini-stat">
                        <span class="stat-dot dot-peach"></span>
                        <div>
                            <strong>{{ pendingCount }}</strong>
                            <span>To do</span>
                        </div>
                    </div>

                    <div class="mini-stat">
                        <span class="stat-dot dot-lavender"></span>
                        <div>
                            <strong>{{ inProgressCount }}</strong>
                            <span>In progress</span>
                        </div>
                    </div>

                    <div class="mini-stat">
                        <span class="stat-dot dot-sage"></span>
                        <div>
                            <strong>{{ completedCount }}</strong>
                            <span>Completed</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tasks -->
            <section class="tasks-section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">YOUR PLANNER</p>
                        <h2>My tasks</h2>
                        <p class="section-description">
                            Keep your next steps clear and manageable.
                        </p>
                    </div>

                    <span class="result-count">
                        {{ filteredTasks.length }}
                        {{ filteredTasks.length === 1 ? 'task' : 'tasks' }}
                    </span>
                </div>

                <!-- Search and filters -->
                <div class="list-controls">
                    <div class="search-box">
                        <span class="search-icon">⌕</span>
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search tasks or subjects..."
                            aria-label="Search tasks or subjects"
                        />
                    </div>

                    <div class="filter-tabs" aria-label="Filter tasks">
                        <button
                            v-for="filter in filters"
                            :key="filter.value"
                            type="button"
                            class="filter-tab"
                            :class="{ active: activeFilter === filter.value }"
                            @click="activeFilter = filter.value"
                        >
                            {{ filter.label }}
                        </button>
                    </div>
                </div>

                <!-- Task list -->
                <div v-if="filteredTasks.length" class="task-list">
                    <article
                        v-for="task in filteredTasks"
                        :key="task.id"
                        class="task-card"
                        :class="{ 'task-is-complete': task.status === 'completed' }"
                    >
                        <button
                            type="button"
                            class="completion"
                            :class="{ done: task.status === 'completed' }"
                            :aria-label="task.status === 'completed' ? 'Mark as pending' : 'Mark as completed'"
                            :title="task.status === 'completed' ? 'Mark as pending' : 'Mark as completed'"
                            @click="toggleComplete(task)"
                        >
                            <span v-if="task.status === 'completed'">✓</span>
                        </button>

                        <div class="task-main">
                            <div class="task-topline">
                                <span class="task-type">{{ formatType(task.task_type) }}</span>

                                <span
                                    class="status-pill"
                                    :class="{
                                        'status-pending': task.status === 'pending',
                                        'status-progress': task.status === 'in_progress',
                                        'status-completed': task.status === 'completed',
                                    }"
                                >
                                    {{
                                        task.status === 'in_progress'
                                            ? 'In progress'
                                            : task.status === 'completed'
                                                ? 'Completed'
                                                : 'To do'
                                    }}
                                </span>
                            </div>

                            <h3 :class="{ 'completed-title': task.status === 'completed' }">
                                {{ task.title }}
                            </h3>

                            <p v-if="task.description" class="task-description">
                                {{ task.description }}
                            </p>

                            <div class="task-meta">
                                <span class="subject-meta">
                                    <span class="meta-icon">▤</span>
                                    {{ task.subject?.name ?? 'Personal task' }}
                                </span>

                                <span
                                    v-if="task.due_at"
                                    class="due-meta"
                                    :class="{ overdue: isOverdue(task) }"
                                >
                                    <span>◷</span>
                                    {{ dueLabel(task) }}
                                </span>

                                <span v-if="task.estimated_minutes" class="time-meta">
                                    {{ task.estimated_minutes }} min
                                </span>
                            </div>
                        </div>

                        <div class="task-side">
                            <span class="priority-label" :class="`priority-${task.priority}`">
                                {{ task.priority }} priority
                            </span>

                            <div class="task-actions">
                                <button
                                    type="button"
                                    class="icon-action"
                                    title="Edit task"
                                    aria-label="Edit task"
                                    @click="openEditForm(task)"
                                >
                                    ✎
                                </button>

                                <button
                                    type="button"
                                    class="icon-action delete-action"
                                    title="Delete task"
                                    aria-label="Delete task"
                                    @click="deleteTask(task)"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Empty states -->
                <div v-else-if="searchQuery || activeFilter !== 'all'" class="empty-state">
                    <div class="empty-illustration">⌕</div>
                    <h3>No matching tasks</h3>
                    <p>Try another search or choose a different filter.</p>

                    <button
                        class="secondary-button"
                        type="button"
                        @click="searchQuery = ''; activeFilter = 'all'"
                    >
                        Clear filters
                    </button>
                </div>

                <div v-else class="empty-state">
                    <div class="empty-illustration">✳</div>
                    <h3>A little space to begin</h3>
                    <p>
                        Add your first assignment, exam, or study session.
                        You can organize the rest as you go.
                    </p>

                    <button class="primary-button" type="button" @click="openCreateForm">
                        <span class="plus-icon">+</span>
                        Create your first task
                    </button>
                </div>

                <p class="list-footer">
                    Small steps are still progress.
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
                    class="task-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-title"
                >
                    <header class="modal-header">
                        <div>
                            <p class="eyebrow">{{ editingId ? 'UPDATE YOUR PLAN' : 'MAKE A PLAN' }}</p>
                            <h2 id="modal-title">
                                {{ editingId ? 'Edit task' : 'Create a task' }}
                            </h2>
                            <p>
                                Add the details now. You can always adjust them later.
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

                    <form class="task-form" @submit.prevent="saveTask">
                        <label>
                            Task title <span class="required">*</span>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="e.g. Finish database project"
                                required
                                autofocus
                            />
                            <small v-if="form.errors.title" class="error">{{ form.errors.title }}</small>
                        </label>

                        <label>
                            Subject
                            <select v-model="form.subject_id">
                                <option value="">Personal / no subject</option>
                                <option
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    :value="String(subject.id)"
                                >
                                    {{ subject.name }}
                                </option>
                            </select>
                            <small v-if="form.errors.subject_id" class="error">{{ form.errors.subject_id }}</small>
                        </label>

                        <label>
                            Description <span class="optional">(optional)</span>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Notes, instructions, or things to remember..."
                            ></textarea>
                            <small v-if="form.errors.description" class="error">{{ form.errors.description }}</small>
                        </label>

                        <div class="form-row">
                            <label>
                                Task type
                                <select v-model="form.task_type" required>
                                    <option value="assignment">Assignment</option>
                                    <option value="project">Project</option>
                                    <option value="exam">Exam</option>
                                    <option value="quiz">Quiz</option>
                                    <option value="study">Study session</option>
                                </select>
                                <small v-if="form.errors.task_type" class="error">{{ form.errors.task_type }}</small>
                            </label>

                            <label>
                                Priority
                                <select v-model="form.priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                <small v-if="form.errors.priority" class="error">{{ form.errors.priority }}</small>
                            </label>
                        </div>

                        <div class="form-row">
                            <label>
                                Due date & time
                                <input v-model="form.due_at" type="datetime-local" />
                                <small v-if="form.errors.due_at" class="error">{{ form.errors.due_at }}</small>
                            </label>

                            <label>
                                Estimated time
                                <div class="input-with-suffix">
                                    <input
                                        v-model="form.estimated_minutes"
                                        type="number"
                                        min="1"
                                        placeholder="e.g. 60"
                                    />
                                    <span>min</span>
                                </div>
                                <small v-if="form.errors.estimated_minutes" class="error">{{ form.errors.estimated_minutes }}</small>
                            </label>
                        </div>

                        <label>
                            Status
                            <select v-model="form.status" required>
                                <option value="pending">To do</option>
                                <option value="in_progress">In progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <small v-if="form.errors.status" class="error">{{ form.errors.status }}</small>
                        </label>

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
                                        : editingId
                                            ? 'Save changes'
                                            : 'Create task'
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

.new-task-button {
    flex-shrink: 0;
    margin-bottom: 2px;
}

/* Overview */

.overview {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 34px;
    padding: 21px 24px;
    border: 1px solid #e8e8de;
    border-radius: 17px;
    background: #efefe6;
}

.overview-intro {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 190px;
}

.overview-icon {
    display: grid;
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 13px;
    background: #dfe7d8;
    color: #42614a;
    font-size: 19px;
}

.overview-intro h2 {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
}

.overview-intro p {
    margin: 5px 0 0;
    color: #82877b;
    font-size: 11px;
}

.overview-stats {
    display: flex;
    align-items: center;
    gap: clamp(14px, 3vw, 32px);
}

.mini-stat {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.stat-dot {
    width: 7px;
    height: 7px;
    margin-top: 6px;
    flex-shrink: 0;
    border-radius: 50%;
}

.dot-neutral { background: #9da397; }
.dot-peach { background: #c48670; }
.dot-lavender { background: #9b8db4; }
.dot-sage { background: #668769; }

.mini-stat strong,
.mini-stat div > span {
    display: block;
}

.mini-stat strong {
    font-size: 19px;
    font-weight: 650;
    line-height: 1.1;
}

.mini-stat div > span {
    margin-top: 5px;
    color: #83877d;
    font-size: 10px;
}

/* Task section */

.tasks-section {
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

/* Search and filters */

.list-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 15px;
}

.search-box {
    display: flex;
    align-items: center;
    width: min(100%, 280px);
    min-height: 40px;
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

.filter-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 4px;
    border: 1px solid #e9e6de;
    border-radius: 11px;
    background: #f0efe9;
}

.filter-tab {
    padding: 8px 11px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #82867c;
    font: inherit;
    font-size: 10px;
    cursor: pointer;
    transition: background 180ms ease, color 180ms ease, box-shadow 180ms ease;
}

.filter-tab:hover {
    color: #344b3b;
}

.filter-tab.active {
    background: #fffefa;
    color: #294b3c;
    font-weight: 700;
    box-shadow: 0 1px 3px rgb(39 58 48 / 6%);
}

/* Task cards */

.task-list {
    display: grid;
    gap: 9px;
}

.task-card {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    min-width: 0;
    padding: 17px 18px;
    border: 1px solid #eae8df;
    border-radius: 14px;
    background: #fffefa;
    transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
}

.task-card:hover {
    border-color: #d8e0d2;
    box-shadow: 0 5px 18px rgb(42 62 48 / 4%);
    transform: translateY(-1px);
}

.task-is-complete {
    background: #fbfbf7;
}

.completion {
    display: grid;
    width: 21px;
    height: 21px;
    flex: 0 0 21px;
    margin-top: 2px;
    padding: 0;
    place-items: center;
    border: 1.5px solid #c4cbbf;
    border-radius: 7px;
    background: #fffefa;
    color: #fffefa;
    font-size: 13px;
    cursor: pointer;
    transition: border-color 180ms ease, background 180ms ease;
}

.completion:hover {
    border-color: #668769;
}

.completion.done {
    border-color: #668769;
    background: #668769;
}

.task-main {
    flex: 1;
    min-width: 0;
}

.task-topline {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 7px;
    margin-bottom: 7px;
}

.task-type {
    color: #8b8e82;
    font-size: 10px;
    font-weight: 600;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 7px;
    border-radius: 6px;
    font-size: 9px;
    font-weight: 650;
}

.status-pending {
    background: #f6e9e1;
    color: #a36b55;
}

.status-progress {
    background: #eee9f5;
    color: #7e6a9b;
}

.status-completed {
    background: #e7eee2;
    color: #557653;
}

.task-main h3 {
    margin: 0;
    color: #354638;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.55;
}

.task-main h3.completed-title {
    color: #93968d;
    text-decoration: line-through;
    text-decoration-color: #b5b8ae;
}

.task-description {
    margin: 5px 0 0;
    color: #8a8d83;
    font-size: 11px;
    line-height: 1.6;
    white-space: pre-line;
}

.task-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 15px;
    margin-top: 11px;
}

.subject-meta,
.due-meta,
.time-meta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #83877c;
    font-size: 10px;
    line-height: 1.5;
}

.meta-icon {
    color: #9a9b8e;
}

.due-meta {
    color: #a16e58;
}

.due-meta.overdue {
    color: #b65e4c;
    font-weight: 700;
}

.time-meta {
    padding: 3px 6px;
    border-radius: 5px;
    background: #f1f0e9;
    color: #777d72;
}

.task-side {
    display: flex;
    flex: 0 0 auto;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
    align-self: stretch;
    gap: 15px;
}

.priority-label {
    padding: 5px 7px;
    border-radius: 6px;
    background: #f1f0e9;
    color: #777d72;
    font-size: 9px;
    font-weight: 600;
    white-space: nowrap;
}

.priority-high {
    background: #f6e5dd;
    color: #a45f4b;
}

.priority-medium {
    background: #f3eedf;
    color: #8b7644;
}

.priority-low {
    background: #e8eee4;
    color: #587553;
}

.task-actions {
    display: flex;
    gap: 5px;
}

.icon-action {
    display: grid;
    width: 29px;
    height: 29px;
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

.icon-action:hover {
    background: #edf0e8;
    color: #294b3c;
}

.delete-action:hover {
    background: #f8e9e3;
    color: #a85f4c;
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
    max-width: 310px;
    margin: 8px auto 18px;
    color: #888b81;
    font-size: 12px;
    line-height: 1.7;
}

.list-footer {
    margin: 19px 0 0;
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

.task-modal {
    width: 100%;
    max-width: 560px;
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

.modal-header p:last-child {
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

.task-form {
    padding: 22px 27px 27px;
}

.task-form label {
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
    color: #9b9c91;
    font-size: 10px;
    font-weight: 400;
}

.task-form input,
.task-form select,
.task-form textarea {
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

.task-form textarea {
    resize: vertical;
    line-height: 1.6;
}

.task-form input:focus,
.task-form select:focus,
.task-form textarea:focus {
    border-color: #94a68f;
    box-shadow: 0 0 0 3px rgb(224 232 218 / 75%);
}

.form-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 14px;
}

.input-with-suffix {
    position: relative;
}

.input-with-suffix input {
    padding-right: 42px;
}

.input-with-suffix > span {
    position: absolute;
    top: 50%;
    right: 12px;
    color: #8d9186;
    font-size: 10px;
    font-weight: 400;
    transform: translateY(-35%);
    pointer-events: none;
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
    margin-top: 24px;
    padding-top: 18px;
    border-top: 1px solid #eeece4;
}

/* Subtle motion */

.modal-enter-active,
.modal-leave-active {
    transition: opacity 180ms ease;
}

.modal-enter-active .task-modal,
.modal-leave-active .task-modal {
    transition: transform 180ms ease, opacity 180ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .task-modal,
.modal-leave-to .task-modal {
    opacity: 0;
    transform: translateY(8px) scale(0.99);
}

/* Responsive layout */

@media (max-width: 800px) {
    .page {
        padding: 25px 18px 38px;
    }

    .overview {
        align-items: flex-start;
        flex-direction: column;
        gap: 22px;
    }

    .overview-stats {
        display: grid;
        width: 100%;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .list-controls {
        align-items: stretch;
        flex-direction: column;
    }

    .search-box {
        width: auto;
    }

    .filter-tabs {
        align-self: flex-start;
        max-width: 100%;
        overflow-x: auto;
    }
}

@media (max-width: 520px) {
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

    .new-task-button {
        width: 100%;
    }

    .overview {
        padding: 18px;
    }

    .overview-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        row-gap: 20px;
    }

    .section-heading h2 {
        font-size: 25px;
    }

    .filter-tabs {
        align-self: stretch;
        justify-content: space-between;
    }

    .filter-tab {
        flex: 1 0 auto;
        padding: 8px;
    }

    .task-card {
        gap: 10px;
        padding: 14px 12px;
    }

    .task-side {
        gap: 12px;
    }

    .priority-label {
        max-width: 75px;
        white-space: normal;
        text-align: center;
    }

    .task-meta {
        gap: 7px 11px;
    }

    .modal-overlay {
        align-items: flex-end;
        padding: 10px;
    }

    .task-modal {
        max-height: calc(100dvh - 20px);
        border-radius: 18px;
    }

    .modal-header {
        padding: 21px 19px 17px;
    }

    .task-form {
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
