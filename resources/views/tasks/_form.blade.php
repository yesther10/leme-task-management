@csrf

<x-adminlte-select name="project_id" label="Proyecto" igroup-size="md" required>
    <option value="">Seleccione un proyecto</option>
    @foreach($projects as $project)
        <option value="{{ $project->id }}" {{ (old('project_id', $task->project_id ?? '') == $project->id) ? 'selected' : '' }}>
            {{ $project->title }}
        </option>
    @endforeach
</x-adminlte-select>
@error('project_id') <small class="text-danger">{{ $message }}</small> @enderror

<x-adminlte-input name="title" label="Título" igroup-size="md" value="{{ old('title', $task->title ?? '') }}" required/>
@error('title') <small class="text-danger">{{ $message }}</small> @enderror

<x-adminlte-textarea name="description" label="Descripción" igroup-size="md">{{ old('description', $task->description ?? '') }}</x-adminlte-textarea>
@error('description') <small class="text-danger">{{ $message }}</small> @enderror

<x-adminlte-select name="priority" label="Prioridad" igroup-size="md" required>
    @foreach(App\Enums\TaskPriority::cases() as $priority)
        <option value="{{ $priority->value }}" {{ (old('priority', $task->priority?->value ?? '') == $priority->value) ? 'selected' : '' }}>
            {{ $priority->label() }}
        </option>
    @endforeach
</x-adminlte-select>
@error('priority') <small class="text-danger">{{ $message }}</small> @enderror

<x-adminlte-select name="status" label="Estado" igroup-size="md" required>
    @foreach(App\Enums\TaskStatus::cases() as $status)
        <option value="{{ $status->value }}" {{ (old('status', $task->status?->value ?? '') == $status->value) ? 'selected' : '' }}>
            {{ $status->label() }}
        </option>
    @endforeach
</x-adminlte-select>
@error('status') <small class="text-danger">{{ $message }}</small> @enderror

{{-- <x-adminlte-input-date name="due_date" label="Fecha de vencimiento" igroup-size="md" value="{{ old('due_date', isset($task) ? $task->due_date->format('Y-m-d') : '') }}" required/> --}}
<x-adminlte-input type="date" name="due_date" id="due_date" label="Data de vencimiento"
    igroup-size="md" value="{{ old('due_date', isset($task) ? $task->due_date->format('Y-m-d') : '') }}"
    required />
@error('due_date') <small class="text-danger">{{ $message }}</small> @enderror

<h5>Archivos adjuntos</h5>
@if($task->files->isNotEmpty())
    <ul>
        @foreach($task->files as $file)
            <li>
                <a href="{{ Storage::url($file->file_path) }}" target="_blank">Archivo {{ $loop->iteration }}</a>
                <label>
                    <input type="checkbox" name="delete_files[]" value="{{ $file->id }}">
                    Eliminar
                </label>
            </li>
        @endforeach
    </ul>
@else
    <p>No hay archivos adjuntos.</p>
@endif

<x-adminlte-input-file name="files[]" label="Archivos PDF" multiple igroup-size="md" />
@error('files.*') <small class="text-danger">{{ $message }}</small> @enderror
