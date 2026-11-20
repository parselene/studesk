<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = $user->tasks()
            ->with('subject:id,name,color')
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->get();

        $subjects = $user->subjects()
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'subjects' => $subjects,
        ]);
    }

    public function create()
    {
        return redirect()->route('tasks.index');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')
                    ->where('user_id', $user->id),
            ],
            'task_type' => [
                'required',
                Rule::in(['assignment', 'project', 'exam', 'quiz', 'study']),
            ],
            'priority' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'required',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
            'due_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $user->tasks()->create($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Request $request, string $id)
    {
        $task = $request->user()->tasks()
            ->with('subject')
            ->findOrFail($id);

        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    public function edit(Request $request, string $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        return redirect()->route('tasks.index')
            ->with('editTaskId', $task->id);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $task = $user->tasks()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')
                    ->where('user_id', $user->id),
            ],
            'task_type' => [
                'required',
                Rule::in(['assignment', 'project', 'exam', 'quiz', 'study']),
            ],
            'priority' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'required',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
            'due_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $validated['completed_at'] =
            $validated['status'] === 'completed'
                ? ($task->completed_at ?? now())
                : null;

        $task->update($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Request $request, string $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
