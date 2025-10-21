<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks')->ignore($this->route('task')),
            ],
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => ['required', new Enum(TaskPriority::class)],
            'status' => ['required', new Enum(TaskStatus::class)],
            'project_id' => 'required|exists:projects,id',
            'delete_files' => ['nullable', 'array'],
            'delete_files.*' => ['integer', 'exists:task_files,id'],
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf|max:2048',
        ];
    }
}
