
<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ListTodo } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

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

    <div class="min-w-0 text-[#293B32]">
        <div class="w-full">

            <!-- Page header -->
            <header class="mb-6 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <p class="mb-2 text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                        YOUR ACADEMIC SPACE
                    </p>

                    <h1 class="font-[Georgia] text-[32px] font-medium leading-tight tracking-[-0.8px] text-[#294B3C] sm:text-[38px]">
                        Tasks & deadlines<span class="text-[#B97962]">.</span>
                    </h1>

                    <p class="mt-2 text-[13px] leading-relaxed text-[#777D73]">
                        One thing at a time. You've got this.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-2.5 text-[13px] font-semibold text-white transition hover:-translate-y-0.5 hover:bg-[#203D30] disabled:cursor-wait disabled:opacity-60"
                    @click="openCreateForm"
                >
                    <span class="text-[19px] font-normal leading-none">+</span>
                    New task
                </button>
            </header>

            <!-- Overview -->
            <section class="mb-7 rounded-2xl border border-[#E8E5DD] bg-[#EFEEE7] p-5 sm:p-6">
                <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#DFE7D8] text-[#42614A]">
                            <ListTodo :size="20" :strokeWidth="1.8" />
                        </div>

                        <div>
                            <h2 class="text-[14px] font-semibold text-[#354638]">
                                Your workload, at a glance
                            </h2>
                            <p class="mt-1 text-[12px] text-[#82877B]">
                                A little progress adds up.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-8 gap-y-5 sm:grid-cols-4 sm:gap-x-10">
                        <div class="flex items-start gap-2">
                            <span class="mt-[7px] h-2 w-2 shrink-0 rounded-full bg-[#9DA397]"></span>
                            <div>
                                <strong class="block text-[21px] font-semibold leading-none text-[#354638]">
                                    {{ tasks.length }}
                                </strong>
                                <span class="mt-1.5 block text-[11px] text-[#83877D]">Total</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <span class="mt-[7px] h-2 w-2 shrink-0 rounded-full bg-[#C48670]"></span>
                            <div>
                                <strong class="block text-[21px] font-semibold leading-none text-[#354638]">
                                    {{ pendingCount }}
                                </strong>
                                <span class="mt-1.5 block text-[11px] text-[#83877D]">To do</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <span class="mt-[7px] h-2 w-2 shrink-0 rounded-full bg-[#9B8DB4]"></span>
                            <div>
                                <strong class="block text-[21px] font-semibold leading-none text-[#354638]">
                                    {{ inProgressCount }}
                                </strong>
                                <span class="mt-1.5 block text-[11px] text-[#83877D]">In progress</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <span class="mt-[7px] h-2 w-2 shrink-0 rounded-full bg-[#668769]"></span>
                            <div>
                                <strong class="block text-[21px] font-semibold leading-none text-[#354638]">
                                    {{ completedCount }}
                                </strong>
                                <span class="mt-1.5 block text-[11px] text-[#83877D]">Completed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Task list -->
            <section class="min-w-0">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="mb-1.5 text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                            YOUR PLANNER
                        </p>

                        <h2 class="font-[Georgia] text-[26px] font-medium tracking-[-0.4px] text-[#294B3C]">
                            My tasks
                        </h2>

                        <p class="mt-1.5 text-[13px] text-[#777D73]">
                            Keep your next steps clear and manageable.
                        </p>
                    </div>

                    <span class="shrink-0 pb-1 text-[12px] text-[#85877D]">
                        {{ filteredTasks.length }}
                        {{ filteredTasks.length === 1 ? 'task' : 'tasks' }}
                    </span>
                </div>

                <!-- Search and filters -->
                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-h-[42px] w-full items-center gap-2.5 rounded-xl border border-[#E8E5DD] bg-white px-3.5 transition focus-within:border-[#A8B69F] focus-within:ring-2 focus-within:ring-[#E7EBDD] lg:max-w-[310px]">
                        <span class="text-[19px] text-[#92968E]">⌕</span>
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search tasks or subjects..."
                            aria-label="Search tasks or subjects"
                            class="w-full border-0 bg-transparent py-2 text-[12px] text-[#293B32] outline-none placeholder:text-[#A1A297] focus:ring-0"
                        />
                    </div>

                    <div class="flex max-w-full gap-1 overflow-x-auto rounded-xl border border-[#E8E5DD] bg-[#F0EEE7] p-1">
                        <button
                            v-for="filter in filters"
                            :key="filter.value"
                            type="button"
                            class="shrink-0 rounded-lg px-3 py-2 text-[11px] font-medium transition"
                            :class="activeFilter === filter.value
                                ? 'bg-white font-semibold text-[#294B3C] shadow-sm'
                                : 'text-[#85877D] hover:text-[#294B3C]'"
                            @click="activeFilter = filter.value"
                        >
                            {{ filter.label }}
                        </button>
                    </div>
                </div>

                <!-- Task cards -->
                <div v-if="filteredTasks.length" class="grid gap-3">
                    <article
                        v-for="task in filteredTasks"
                        :key="task.id"
                        class="flex min-w-0 items-start gap-3 rounded-2xl border border-[#EAE6DD] bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#D8E0D2] hover:shadow-[0_5px_18px_rgba(42,62,48,0.04)] sm:gap-4 sm:p-5"
                        :class="{ 'bg-[#FBFBF7]': task.status === 'completed' }"
                    >
                        <button
                            type="button"
                            class="mt-1 flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-[7px] border-[1.5px] text-[13px] transition"
                            :class="task.status === 'completed'
                                ? 'border-[#668769] bg-[#668769] text-white'
                                : 'border-[#C4CBBF] bg-white text-transparent hover:border-[#668769]'"
                            :aria-label="task.status === 'completed' ? 'Mark as pending' : 'Mark as completed'"
                            :title="task.status === 'completed' ? 'Mark as pending' : 'Mark as completed'"
                            @click="toggleComplete(task)"
                        >
                            <span v-if="task.status === 'completed'">✓</span>
                        </button>

                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="text-[11px] font-semibold text-[#85877D]">
                                    {{ formatType(task.task_type) }}
                                </span>

                                <span
                                    class="rounded-md px-2 py-1 text-[10px] font-semibold"
                                    :class="{
                                        'bg-[#F5E4DD] text-[#A85F4C]': task.status === 'pending',
                                        'bg-[#EBE5F3] text-[#796894]': task.status === 'in_progress',
                                        'bg-[#E7EBDD] text-[#557653]': task.status === 'completed',
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

                            <h3
                                class="break-words text-[14px] font-semibold leading-relaxed text-[#354638]"
                                :class="{ 'text-[#93968D] line-through decoration-[#B5B8AE]': task.status === 'completed' }"
                            >
                                {{ task.title }}
                            </h3>

                            <p
                                v-if="task.description"
                                class="mt-1.5 whitespace-pre-line break-words text-[12px] leading-relaxed text-[#85877D]"
                            >
                                {{ task.description }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-[#85877D]">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="text-[#9A9B8E]">▤</span>
                                    {{ task.subject?.name ?? 'Personal task' }}
                                </span>

                                <span
                                    v-if="task.due_at"
                                    class="inline-flex items-center gap-1.5"
                                    :class="isOverdue(task) ? 'font-semibold text-[#B65E4C]' : 'text-[#A16E58]'"
                                >
                                    <span>◷</span>
                                    {{ dueLabel(task) }}
                                </span>

                                <span
                                    v-if="task.estimated_minutes"
                                    class="rounded-md bg-[#F0EEE7] px-2 py-1 text-[#777D73]"
                                >
                                    {{ task.estimated_minutes }} min
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col items-end justify-between gap-4 self-stretch">
                            <span
                                class="rounded-md px-2 py-1 text-[10px] font-semibold"
                                :class="{
                                    'bg-[#F6E5DD] text-[#A45F4B]': task.priority === 'high',
                                    'bg-[#F3EEDF] text-[#8B7644]': task.priority === 'medium',
                                    'bg-[#E8EEE4] text-[#587553]': task.priority === 'low',
                                }"
                            >
                                {{ task.priority }} priority
                            </span>

                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-[16px] text-[#81887C] transition hover:bg-[#EDF0E8] hover:text-[#294B3C]"
                                    title="Edit task"
                                    aria-label="Edit task"
                                    @click="openEditForm(task)"
                                >
                                    ✎
                                </button>

                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-[18px] text-[#81887C] transition hover:bg-[#F8E9E3] hover:text-[#A85F4C]"
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

                <!-- Filtered empty state -->
                <div
                    v-else-if="searchQuery || activeFilter !== 'all'"
                    class="rounded-2xl border border-dashed border-[#DFDFD4] bg-white/60 px-5 py-12 text-center"
                >
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[23px] text-[#557653]">
                        ⌕
                    </div>

                    <h3 class="font-[Georgia] text-[21px] font-medium text-[#294B3C]">
                        No matching tasks
                    </h3>

                    <p class="mx-auto mt-2 max-w-[310px] text-[13px] leading-relaxed text-[#85877D]">
                        Try another search or choose a different filter.
                    </p>

                    <button
                        type="button"
                        class="mt-5 rounded-xl border border-[#E4E3DA] bg-white px-4 py-2.5 text-[12px] font-semibold text-[#4A5D4E] transition hover:bg-[#F0F0E8]"
                        @click="searchQuery = ''; activeFilter = 'all'"
                    >
                        Clear filters
                    </button>
                </div>

                <!-- First-time empty state -->
                <div
                    v-else
                    class="rounded-2xl border border-dashed border-[#DFDFD4] bg-white/60 px-5 py-12 text-center"
                >
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E7EBDD] text-[22px] text-[#557653]">
                        ✳
                    </div>

                    <h3 class="font-[Georgia] text-[21px] font-medium text-[#294B3C]">
                        A little space to begin
                    </h3>

                    <p class="mx-auto mt-2 max-w-[330px] text-[13px] leading-relaxed text-[#85877D]">
                        Add your first assignment, exam, or study session.
                        You can organize the rest as you go.
                    </p>

                    <button
                        type="button"
                        class="mt-5 inline-flex min-h-[42px] items-center justify-center gap-2 rounded-xl bg-[#294B3C] px-5 py-2.5 text-[12px] font-semibold text-white transition hover:bg-[#203D30]"
                        @click="openCreateForm"
                    >
                        <span class="text-[18px] font-normal">+</span>
                        Create your first task
                    </button>
                </div>

                <p class="mt-6 text-center font-[Georgia] text-[12px] italic text-[#A0A194]">
                    Small steps are still progress.
                </p>
            </section>
        </div>

        <!-- Create / edit modal -->
        <Transition name="modal">
            <div
                v-if="showForm"
                class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-[#222F26]/45 p-3 backdrop-blur-[4px] sm:p-6"
                @click.self="resetForm"
                @keydown.esc="resetForm"
            >
                <section
                    class="my-auto max-h-[calc(100vh-24px)] w-full max-w-[560px] overflow-y-auto rounded-2xl border border-white/60 bg-[#FFFEFA] shadow-[0_24px_80px_rgba(28,43,33,0.18)] sm:max-h-[calc(100vh-48px)] sm:rounded-[20px]"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-title"
                >
                    <header class="flex items-start justify-between gap-5 border-b border-[#EEECE4] px-5 py-5 sm:px-7 sm:py-6">
                        <div>
                            <p class="mb-2 text-xs font-medium font-semibold uppercase tracking-[0.17em] text-[#8B8C7D]">
                                {{ editingId ? 'UPDATE YOUR PLAN' : 'MAKE A PLAN' }}
                            </p>

                            <h2 id="modal-title" class="font-[Georgia] text-[25px] font-medium text-[#294B3C] sm:text-[27px]">
                                {{ editingId ? 'Edit task' : 'Create a task' }}
                            </h2>

                            <p class="mt-2 text-[12px] leading-relaxed text-[#85877D]">
                                Add the details now. You can always adjust them later.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#E9E7DE] bg-[#F8F7F2] text-[22px] text-[#6C796C] transition hover:bg-[#EEEE E5]"
                            aria-label="Close form"
                            @click="resetForm"
                        >
                            ×
                        </button>
                    </header>

                    <form class="space-y-4 px-5 py-5 sm:px-7 sm:py-6" @submit.prevent="saveTask">
                        <label class="block text-[12px] font-semibold text-[#48584A]">
                            Task title <span class="text-[#B56E58]">*</span>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="e.g. Finish database project"
                                required
                                autofocus
                                class="form-field"
                            />
                            <small v-if="form.errors.title" class="form-error">{{ form.errors.title }}</small>
                        </label>

                        <label class="block text-[12px] font-semibold text-[#48584A]">
                            Subject
                            <select v-model="form.subject_id" class="form-field">
                                <option value="">Personal / no subject</option>
                                <option
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    :value="String(subject.id)"
                                >
                                    {{ subject.name }}
                                </option>
                            </select>
                            <small v-if="form.errors.subject_id" class="form-error">{{ form.errors.subject_id }}</small>
                        </label>

                        <label class="block text-[12px] font-semibold text-[#48584A]">
                            Description <span class="font-normal text-[#9B9C91]">(optional)</span>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Notes, instructions, or things to remember..."
                                class="form-field resize-y leading-relaxed"
                            ></textarea>
                            <small v-if="form.errors.description" class="form-error">{{ form.errors.description }}</small>
                        </label>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block text-[12px] font-semibold text-[#48584A]">
                                Task type
                                <select v-model="form.task_type" required class="form-field">
                                    <option value="assignment">Assignment</option>
                                    <option value="project">Project</option>
                                    <option value="exam">Exam</option>
                                    <option value="quiz">Quiz</option>
                                    <option value="study">Study session</option>
                                </select>
                                <small v-if="form.errors.task_type" class="form-error">{{ form.errors.task_type }}</small>
                            </label>

                            <label class="block text-[12px] font-semibold text-[#48584A]">
                                Priority
                                <select v-model="form.priority" required class="form-field">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                <small v-if="form.errors.priority" class="form-error">{{ form.errors.priority }}</small>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block text-[12px] font-semibold text-[#48584A]">
                                Due date & time
                                <input v-model="form.due_at" type="datetime-local" class="form-field" />
                                <small v-if="form.errors.due_at" class="form-error">{{ form.errors.due_at }}</small>
                            </label>

                            <label class="block text-[12px] font-semibold text-[#48584A]">
                                Estimated time
                                <div class="relative">
                                    <input
                                        v-model="form.estimated_minutes"
                                        type="number"
                                        min="1"
                                        placeholder="e.g. 60"
                                        class="form-field pr-12"
                                    />
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-normal text-[#8D9186]">
                                        min
                                    </span>
                                </div>
                                <small v-if="form.errors.estimated_minutes" class="form-error">{{ form.errors.estimated_minutes }}</small>
                            </label>
                        </div>

                        <label class="block text-[12px] font-semibold text-[#48584A]">
                            Status
                            <select v-model="form.status" required class="form-field">
                                <option value="pending">To do</option>
                                <option value="in_progress">In progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <small v-if="form.errors.status" class="form-error">{{ form.errors.status }}</small>
                        </label>

                        <div class="flex flex-col-reverse justify-end gap-2 border-t border-[#EEECE4] pt-5 sm:flex-row">
                            <button
                                type="button"
                                class="min-h-[42px] rounded-xl border border-[#E4E3DA] bg-white px-5 py-2.5 text-[12px] font-semibold text-[#4A5D4E] transition hover:bg-[#F0F0E8] disabled:opacity-60"
                                :disabled="form.processing"
                                @click="resetForm"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="min-h-[42px] rounded-xl bg-[#294B3C] px-5 py-2.5 text-[12px] font-semibold text-white transition hover:bg-[#203D30] disabled:cursor-wait disabled:opacity-60"
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
.form-field {
    box-sizing: border-box;
    display: block;
    width: 100%;
    min-width: 0;
    margin-top: 7px;
    padding: 11px 12px;
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

.form-field:focus {
    border-color: #94A68F;
    box-shadow: 0 0 0 3px rgb(224 232 218 / 75%);
}

.form-field::placeholder {
    color: #A1A297;
}

.form-error {
    display: block;
    margin-top: 5px;
    color: #B15E4B;
    font-size: 11px;
    font-weight: 500;
    line-height: 1.5;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 180ms ease;
}

.modal-enter-active section,
.modal-leave-active section {
    transition: transform 180ms ease, opacity 180ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from section,
.modal-leave-to section {
    opacity: 0;
    transform: translateY(8px) scale(0.99);
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
    }
}
</style>
